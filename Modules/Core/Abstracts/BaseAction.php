<?php

namespace Modules\Core\Abstracts;

use Modules\Core\Exceptions\BaseException;
use Modules\Core\Interfaces\ActionInterface;
use Modules\Core\Traits\CacheableTrait;

abstract class BaseAction implements ActionInterface
{
    use CacheableTrait;

    protected function executeWithTransaction(callable $callback)
    {
        \DB::beginTransaction();
        try {
            $result = $callback();
            \DB::commit();
            return $result;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    protected function executeWithCache(string $key, callable $callback, int $ttl = 3600)
    {
        return $this->cacheRemember($key, $callback, $ttl);
    }

    /**
     * Throws BaseException rather than a bare \Exception: an exception code is
     * not an HTTP status, so `new \Exception(..., 403)` still renders as a 500
     * and the client cannot tell "forbidden" from "server error".
     */
    protected function authorize(string $permission, mixed $resource = null): void
    {
        if (!auth()->user()?->can($permission, $resource)) {
            throw BaseException::forbidden();
        }
    }

    protected function validateData(array $data, array $rules): array
    {
        return validator($data, $rules)->validate();
    }
}
