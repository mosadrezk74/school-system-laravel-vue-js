<?php

namespace Modules\Finance\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class FinanceOperationException extends BaseException
{
    public static function feeCategoryCodeAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.fee_category_code_already_exists'), HttpStatus::CONFLICT);
    }

    public static function feeDiscountCodeAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.fee_discount_code_already_exists'), HttpStatus::CONFLICT);
    }

    public static function invoiceNumberAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.invoice_number_already_exists'), HttpStatus::CONFLICT);
    }

    public static function scopeMustBeGradeOrClassroom(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.scope_must_be_grade_or_classroom'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function feeAlreadyAssigned(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.fee_already_assigned'), HttpStatus::CONFLICT);
    }

    public static function invoiceNotEditable(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.invoice_not_editable'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function invoiceHasNoItems(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.invoice_has_no_items'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function paymentExceedsBalance(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.payment_exceeds_balance'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function paymentAmountMustBePositive(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.payment_amount_must_be_positive'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function invoiceAlreadyCancelled(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.invoice_already_cancelled'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function cannotCancelPaidInvoice(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.cannot_cancel_paid_invoice'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function noPendingFeesForStudent(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.no_pending_fees_for_student'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function discountNotApplicable(string $message = ''): static
    {
        return new static($message ?: __('finance::finance.discount_not_applicable'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
