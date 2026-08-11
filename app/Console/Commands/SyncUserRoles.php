<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Auth\Enums\UserRole;
use Spatie\Permission\PermissionRegistrar;

/**
 * Attaches the Spatie role matching each account's users.role enum.
 *
 * Accounts created outside CreateTeacherAction/CreateStudentAction — imports,
 * manual inserts, restored dumps — carry the enum but no row in
 * model_has_roles. The enum still routes them to their portal, so they sign in
 * successfully and then hit 403 on every page, because permissions come from
 * the Spatie role that was never attached.
 */
class SyncUserRoles extends Command
{
    protected $signature = 'auth:sync-user-roles {--dry-run : List the accounts that would change without writing}';

    protected $description = 'Attach the Spatie role matching each account\'s role enum';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $repaired = 0;

        User::query()->with('roles')->chunkById(200, function ($users) use ($dryRun, &$repaired): void {
            foreach ($users as $user) {
                $expected = $user->role instanceof UserRole ? $user->role->value : $user->role;

                if ($expected === null || $user->getRoleNames()->contains($expected)) {
                    continue;
                }

                $this->line("  {$user->email}: [{$user->getRoleNames()->implode(', ')}] => [{$expected}]");
                $repaired++;

                if (! $dryRun) {
                    $user->syncRoles([$expected]);
                }
            }
        });

        if (! $dryRun) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $this->info($dryRun
            ? "{$repaired} account(s) would be updated."
            : "{$repaired} account(s) updated.");

        return self::SUCCESS;
    }
}
