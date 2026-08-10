<?php

namespace Modules\SchoolClass\Actions;

use Illuminate\Support\Collection;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseAction;
use Modules\SchoolClass\DTOs\CreateClassSectionsDTO;
use Modules\SchoolClass\Exceptions\ClassOperationException;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\SchoolClass\Repositories\ClassRepositoryInterface;

/**
 * Creates every section of a grade level in one atomic operation.
 *
 * Each row becomes an ordinary SchoolClass through the same repository the
 * single-class flow uses, so model events, audit columns and relationships
 * behave identically — this is a different way to submit the same records,
 * not a different kind of record.
 */
class CreateClassesForGradeLevelAction extends BaseAction
{
    public function __construct(
        protected ClassRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateClassSectionsDTO  $input
     * @return Collection<int, SchoolClass>
     */
    public function execute(mixed $input): Collection
    {
        /** @var CreateClassSectionsDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Collection {
            if ($dto->sections === []) {
                throw ClassOperationException::noSectionsProvided();
            }

            /*
             * Names are re-checked here rather than trusted from the request:
             * validation ran before the transaction opened, and this is the
             * last point where a duplicate can still be stopped without a
             * partial batch reaching the database.
             */
            $seen = [];

            $created = collect();

            foreach ($dto->sections as $section) {
                $key = mb_strtolower(trim($section->name));

                if (isset($seen[$key])) {
                    throw ClassOperationException::duplicateSectionName();
                }

                $seen[$key] = true;

                if ($section->capacity < 1) {
                    throw ClassOperationException::invalidCapacity();
                }

                if ($this->repository->existsForGradeLevel($section->academicYearId, $section->gradeLevelId, $section->name)) {
                    throw ClassOperationException::classAlreadyExists();
                }

                if ($section->classroomId && ! Classroom::find($section->classroomId)) {
                    throw ClassOperationException::classroomNotFound();
                }

                $created->push($this->repository->create($section, auth()->id()));
            }

            return $created;
        });
    }
}
