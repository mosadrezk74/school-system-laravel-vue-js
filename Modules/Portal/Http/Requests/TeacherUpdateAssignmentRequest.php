<?php

namespace Modules\Portal\Http\Requests;

use Modules\Assignment\Http\Requests\UpdateAssignmentRequest;
use Modules\Portal\Traits\ForcesTeacherOwnership;

class TeacherUpdateAssignmentRequest extends UpdateAssignmentRequest
{
    use ForcesTeacherOwnership;
}
