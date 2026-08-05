<?php

namespace Modules\Core\Exceptions;

use Modules\Core\Constants\HttpStatus;

class RestrictedDeleteException extends BaseException
{
    public function __construct(
        public readonly string $model,
        public readonly array $blocking = [],
    ) {
        parent::__construct(
            __('base.restricted_delete', [
                'relations' => implode(__('base.list_separator'), $blocking),
            ]),
            HttpStatus::CONFLICT,
        );
    }
}
