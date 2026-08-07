<?php

namespace Modules\Portal\Http\Requests;

use Modules\Portal\Traits\ForcesTeacherOwnership;
use Modules\StudyMaterial\Http\Requests\StoreStudyResourceRequest;

class TeacherStoreStudyResourceRequest extends StoreStudyResourceRequest
{
    use ForcesTeacherOwnership;
}
