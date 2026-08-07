<?php

namespace Modules\Portal\Http\Requests;

use Modules\Portal\Traits\ForcesTeacherOwnership;
use Modules\StudyMaterial\Http\Requests\UpdateStudyResourceRequest;

class TeacherUpdateStudyResourceRequest extends UpdateStudyResourceRequest
{
    use ForcesTeacherOwnership;
}
