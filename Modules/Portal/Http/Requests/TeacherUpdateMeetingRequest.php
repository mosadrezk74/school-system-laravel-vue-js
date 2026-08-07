<?php

namespace Modules\Portal\Http\Requests;

use Modules\Meeting\Http\Requests\UpdateMeetingRequest;
use Modules\Portal\Traits\ForcesTeacherOwnership;

class TeacherUpdateMeetingRequest extends UpdateMeetingRequest
{
    use ForcesTeacherOwnership;
}
