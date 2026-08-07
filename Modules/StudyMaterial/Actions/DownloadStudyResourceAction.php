<?php

namespace Modules\StudyMaterial\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\StudyMaterial\Exceptions\StudyResourceOperationException;
use Modules\StudyMaterial\Models\StudyResource;
use Modules\StudyMaterial\Repositories\StudyResourceRepositoryInterface;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadStudyResourceAction extends BaseAction
{
    public function __construct(
        protected StudyResourceRepositoryInterface $repository,
    ) {}

    /**
     * @param  StudyResource  $input
     */
    public function execute(mixed $input): Media
    {
        /** @var StudyResource $studyResource */
        $studyResource = $input;

        return $this->executeWithTransaction(function () use ($studyResource): Media {
            $media = $studyResource->file();

            if (! $media instanceof Media) {
                throw StudyResourceOperationException::noFileToDownload();
            }

            $this->repository->incrementDownloads($studyResource);

            return $media;
        });
    }
}
