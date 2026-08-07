<?php

namespace Modules\Portal\Http\Requests;

use Modules\Meeting\Http\Requests\StoreMeetingRequest;
use Modules\Portal\Traits\ForcesTeacherOwnership;

class TeacherStoreMeetingRequest extends StoreMeetingRequest
{
    use ForcesTeacherOwnership;
}
