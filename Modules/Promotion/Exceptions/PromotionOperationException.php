<?php

namespace Modules\Promotion\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class PromotionOperationException extends BaseException
{
    public static function studentNotFound(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.student_not_found'), HttpStatus::NOT_FOUND);
    }

    /** Only students sitting in the current year are up for a decision. */
    public static function studentNotInSourceYear(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.student_not_in_source_year'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function studentAlreadyDecided(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.student_already_decided'), HttpStatus::CONFLICT);
    }

    public static function studentAlreadyGraduated(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.student_already_graduated'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function targetYearInvalid(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.target_year_invalid'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    /** Promoting into the year the student is already in is not a promotion. */
    public static function targetYearSameAsSource(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.target_year_same_as_source'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function targetSemesterInvalid(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.target_semester_invalid'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function targetSemesterRequired(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.target_semester_required'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function targetClassInvalid(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.target_class_invalid'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    /** The target grade must sit at or above the student's current grade. */
    public static function gradeLevelRegression(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.grade_level_regression'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function targetClassFull(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.target_class_full'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function targetClassRequired(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.target_class_required'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function promotionNotFound(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.promotion_not_found'), HttpStatus::NOT_FOUND);
    }

    /**
     * Reverting an older decision would write the student back to a state a
     * later decision has already moved them out of.
     */
    public static function supersededByNewerPromotion(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.superseded_by_newer_promotion'), HttpStatus::CONFLICT);
    }

    public static function noStudentsSelected(string $message = ''): static
    {
        return new static($message ?: __('promotion::promotions.errors.no_students_selected'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
