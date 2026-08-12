<?php

namespace Modules\Auth\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class RoleOperationException extends BaseException
{
    public static function nameAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('auth::roles.errors.name_already_exists'), HttpStatus::CONFLICT);
    }

    /** System roles are resolved by name in seeders and assignRole() calls. */
    public static function systemRoleCannotBeRenamed(string $message = ''): static
    {
        return new static($message ?: __('auth::roles.errors.system_cannot_rename'), HttpStatus::FORBIDDEN);
    }

    public static function systemRoleCannotBeDeleted(string $message = ''): static
    {
        return new static($message ?: __('auth::roles.errors.system_cannot_delete'), HttpStatus::FORBIDDEN);
    }

    public static function ownerRoleIsOwnerManaged(string $message = ''): static
    {
        return new static($message ?: __('auth::roles.errors.owner_managed_by_owner'), HttpStatus::FORBIDDEN);
    }

    /**
     * Stops an admin from quietly widening its own reach by granting a
     * permission it does not itself hold.
     */
    public static function cannotGrantUnheldPermissions(string $message = ''): static
    {
        return new static($message ?: __('auth::roles.errors.cannot_grant_unheld'), HttpStatus::FORBIDDEN);
    }

    public static function roleStillAssigned(string $message = ''): static
    {
        return new static($message ?: __('auth::roles.errors.still_assigned'), HttpStatus::CONFLICT);
    }

    /**
     * The admin role is the only non-owner route back into role management,
     * so stripping its own role.* grants would lock the school out.
     */
    public static function wouldCauseLockout(string $message = ''): static
    {
        return new static($message ?: __('auth::roles.errors.would_cause_lockout'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
