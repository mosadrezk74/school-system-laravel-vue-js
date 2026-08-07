<?php

namespace Modules\StudyMaterial\Enums;

use Modules\Core\Traits\BaseEnum;

enum StudyResourceType: string
{
    use BaseEnum;

    case Book = 'book';
    case Worksheet = 'worksheet';
    case Homework = 'homework';
    case Presentation = 'presentation';
    case Video = 'video';
    case Audio = 'audio';
    case Other = 'other';

    public function label(): string
    {
        return __("studymaterial::study_resource.types.{$this->value}");
    }
}
