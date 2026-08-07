<?php

namespace Modules\Portal\Http\Requests;

use Modules\Assignment\Http\Requests\StoreAssignmentRequest;
use Modules\Portal\Traits\ForcesTeacherOwnership;

class TeacherStoreAssignmentRequest extends StoreAssignmentRequest
{
    use ForcesTeacherOwnership;
}
