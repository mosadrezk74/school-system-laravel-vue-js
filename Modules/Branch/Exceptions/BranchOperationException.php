<?php

namespace Modules\Branch\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class BranchOperationException extends BaseException
{
    public static function codeAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('branch::branch.code_already_exists'), HttpStatus::CONFLICT);
    }

    public static function branchNotFound(string $message = ''): static
    {
        return new static($message ?: __('branch::branch.branch_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function cannotDeleteMainBranch(string $message = ''): static
    {
        return new static($message ?: __('branch::branch.cannot_delete_main_branch'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function mainBranchAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('branch::branch.main_branch_already_exists'), HttpStatus::CONFLICT);
    }
}