<?php

namespace Modules\Auth\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class AdminOperationException extends BaseException
{
    public static function emailAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('auth::admins.errors.email_already_exists'), HttpStatus::CONFLICT);
    }

    /**
     * Only an owner may mint another owner — otherwise an admin could promote
     * itself to the role that bypasses every permission check.
     */
    public static function ownerAssignableByOwnerOnly(string $message = ''): static
    {
        return new static($message ?: __('auth::admins.errors.owner_assignable_by_owner'), HttpStatus::FORBIDDEN);
    }

    public static function cannotModifyOwner(string $message = ''): static
    {
        return new static($message ?: __('auth::admins.errors.cannot_modify_owner'), HttpStatus::FORBIDDEN);
    }

    public static function cannotDeleteSelf(string $message = ''): static
    {
        return new static($message ?: __('auth::admins.errors.cannot_delete_self'), HttpStatus::FORBIDDEN);
    }

    public static function cannotDeactivateSelf(string $message = ''): static
    {
        return new static($message ?: __('auth::admins.errors.cannot_deactivate_self'), HttpStatus::FORBIDDEN);
    }

    /**
     * Removing the last account that can still administer the school would
     * leave nobody able to sign in and fix it.
     */
    public static function wouldLeaveNoAdministrator(string $message = ''): static
    {
        return new static($message ?: __('auth::admins.errors.would_leave_no_administrator'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
