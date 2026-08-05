<?php

namespace Modules\Core\Abstracts;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponseTrait;

/**
 * Base for every endpoint that returns JSON. The SPA talks to these
 * exclusively, so the envelope from ApiResponseTrait is the client contract.
 */
abstract class BaseApiController extends Controller
{
    use ApiResponseTrait;
}
