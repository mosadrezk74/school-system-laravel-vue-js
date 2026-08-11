<?php

namespace Modules\Portal\Http\Requests;

use Modules\Meeting\Http\Requests\UpdateMeetingRequest;
use Modules\Portal\Traits\ForcesTeacherOwnership;
use Modules\Portal\Traits\ValidatesTeacherClassSubject;

class TeacherUpdateMeetingRequest extends UpdateMeetingRequest
{
    use ForcesTeacherOwnership;
    use ValidatesTeacherClassSubject;
}
