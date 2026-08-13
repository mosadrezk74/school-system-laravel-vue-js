<?php

namespace Modules\Classroom\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * Replaces the nine-value `in:` list that was spelled out in both classroom
 * requests and, as a hand-built `{title, value}` array, twice more in
 * ClassroomController — four copies that had to be edited together.
 */
enum RoomType: string
{
    use BaseEnum;

    case Classroom = 'classroom';
    case Lab = 'lab';
    case Library = 'library';
    case Auditorium = 'auditorium';
    case Gym = 'gym';
    case MusicRoom = 'music_room';
    case ArtRoom = 'art_room';
    case ComputerLab = 'computer_lab';
    case ScienceLab = 'science_lab';

    public function label(): string
    {
        return __("classroom::classroom.types.{$this->value}");
    }
}
