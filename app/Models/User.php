<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Auth\Enums\UserRole;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\Student\Models\StudentProfile;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $ulid
 * @property string|null $username
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string $role
 * @property bool $is_active
 * @property string $language
 * @property string $theme
 * @property string $timezone
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'username', 'name', 'email', 'phone', 'password',
    'gender', 'birth_date', 'national_id',
    'role', 'is_active', 'language', 'theme', 'timezone',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    /*
     * Fine-grained access. The `role` column is only a coarse account type for
     * UI branching; authorisation always goes through Spatie.
     *
     * hasPermissionTo is aliased so the override below can add the
     * is_active gate and still delegate to the trait's implementation.
     */
    use HasRoles {
        hasPermissionTo as private spatieHasPermissionTo;
    }
    use Notifiable;

    // Staff and student records are referenced by invoices, results and
    // attendance, so a user is never destroyed outright.
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class, 'user_id');
    }

    public function guardianProfile(): HasOne
    {
        return $this->hasOne(GuardianProfile::class, 'user_id');
    }

    protected static function booted(): void
    {
        // A stable public identifier that doesn't leak row counts the way a
        // sequential id does, and stays valid if rows are ever re-imported.
        static::creating(function (User $user): void {
            $user->ulid ??= (string) Str::ulid();
            $user->username ??= static::deriveUsername($user);
        });
    }

    private static function deriveUsername(User $user): string
    {
        $base = Str::of($user->email ?: $user->name)
            ->before('@')
            ->slug('.')
            ->limit(40, '')
            ->toString() ?: 'user';

        $candidate = $base;
        $suffix = 1;

        while (static::withTrashed()->where('username', $candidate)->exists()) {
            $candidate = "{$base}{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * A deactivated account can do nothing, whatever roles it still holds.
     *
     * The check lives here rather than only in Gate::before because Spatie
     * resolves a permission straight from the role when one matches, without
     * consulting the Gate — so a suspended admin would otherwise keep working.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->spatieHasPermissionTo($permission, $guardName);
    }
}
