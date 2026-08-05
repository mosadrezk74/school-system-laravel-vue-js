<?php

namespace Modules\Core\Constants;

final class Pagination
{
    const DEFAULT_PER_PAGE = 15;
    const MAX_PER_PAGE = 100;
    const MIN_PER_PAGE = 1;

    const DEFAULT_SORT = 'created_at';
    const DEFAULT_ORDER = 'desc';

    const PAGE_PARAM = 'page';
    const PER_PAGE_PARAM = 'per_page';
    const SORT_PARAM = 'sort_by';
    const ORDER_PARAM = 'sort_order';
    const SEARCH_PARAM = 'search';
}
