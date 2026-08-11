<?php

namespace Modules\Portal\Http\Requests;

use Modules\Meeting\Http\Requests\StoreMeetingRequest;
use Modules\Portal\Traits\ForcesTeacherOwnership;
use Modules\Portal\Traits\ValidatesTeacherClassSubject;

class TeacherStoreMeetingRequest extends StoreMeetingRequest
{
    use ForcesTeacherOwnership;
    use ValidatesTeacherClassSubject;
}
