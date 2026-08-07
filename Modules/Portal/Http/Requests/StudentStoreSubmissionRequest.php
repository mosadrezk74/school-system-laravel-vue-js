<?php

namespace Modules\Portal\Http\Requests;

use Modules\Assignment\Http\Requests\StoreAssignmentSubmissionRequest;
use Modules\Portal\Traits\ForcesStudentOwnership;

class StudentStoreSubmissionRequest extends StoreAssignmentSubmissionRequest
{
    use ForcesStudentOwnership;
}
