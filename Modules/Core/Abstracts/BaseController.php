<?php

namespace Modules\Core\Abstracts;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;
use Modules\Core\Constants\Pagination;
use Modules\Core\Traits\ApiResponseTrait;

abstract class BaseController extends LaravelController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponseTrait;

    protected function getPaginationPerPage(): int
    {
        return $this->getLimit();
    }
    protected function getLimit(): int
    {
        return min(
            max(request()->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE), Pagination::MIN_PER_PAGE),
            Pagination::MAX_PER_PAGE
        );
    }

    protected function getSortFields(): array
    {
        return [
            'sort_by' => request()->input('sort_by', 'created_at'),
            'sort_order' => request()->input('sort_order', 'desc'),
        ];
    }

    protected function getFilters(array $allowed = []): array
    {
        return request()->only($allowed);
    }

    protected function getSearchTerm(): ?string
    {
        return request()->input('search');
    }

    protected function getIncludes(): array
    {
        $includes = request()->input('include', '');
        return $includes ? explode(',', $includes) : [];
    }

    protected function getFields(): array
    {
        $fields = request()->input('fields', '');
        return $fields ? explode(',', $fields) : [];
    }
}
