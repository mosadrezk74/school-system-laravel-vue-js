<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\FeeCategoryController;
use Modules\Finance\Http\Controllers\FeeDiscountController;
use Modules\Finance\Http\Controllers\FeeStructureController;
use Modules\Finance\Http\Controllers\FinanceDashboardController;
use Modules\Finance\Http\Controllers\FinanceReportController;
use Modules\Finance\Http\Controllers\InvoiceController;
use Modules\Finance\Http\Controllers\PaymentController;
use Modules\Finance\Http\Controllers\StudentFeeController;

Route::middleware(['web', 'auth'])->prefix('finance')->group(function () {
    Route::get('dashboard', [FinanceDashboardController::class, 'index'])
        ->name('finance.dashboard');

    Route::prefix('reports')->name('finance.reports.')->group(function () {
        Route::get('outstanding-fees', [FinanceReportController::class, 'outstandingFees'])->name('outstanding-fees');
        Route::get('student-ledger', [FinanceReportController::class, 'studentLedger'])->name('student-ledger');
        Route::get('daily-payments', [FinanceReportController::class, 'dailyPayments'])->name('daily-payments');
        Route::get('monthly-revenue', [FinanceReportController::class, 'monthlyRevenue'])->name('monthly-revenue');
        Route::get('category-revenue', [FinanceReportController::class, 'categoryRevenue'])->name('category-revenue');
    });

    Route::patch('fee-categories/{feeCategory}/restore', [FeeCategoryController::class, 'restore'])
        ->name('fee-categories.restore');
    Route::resource('fee-categories', FeeCategoryController::class)
        ->parameters(['fee-categories' => 'feeCategory']);

    Route::patch('fee-structures/{feeStructure}/restore', [FeeStructureController::class, 'restore'])
        ->name('fee-structures.restore');
    Route::resource('fee-structures', FeeStructureController::class)
        ->parameters(['fee-structures' => 'feeStructure']);

    Route::patch('fee-discounts/{feeDiscount}/restore', [FeeDiscountController::class, 'restore'])
        ->name('fee-discounts.restore');
    Route::resource('fee-discounts', FeeDiscountController::class)
        ->parameters(['fee-discounts' => 'feeDiscount']);

    Route::patch('student-fees/{studentFee}/restore', [StudentFeeController::class, 'restore'])
        ->name('student-fees.restore');
    Route::resource('student-fees', StudentFeeController::class)
        ->parameters(['student-fees' => 'studentFee']);

    Route::post('invoices/generate', [InvoiceController::class, 'generate'])
        ->name('invoices.generate');
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])
        ->name('invoices.cancel');
    Route::patch('invoices/{invoice}/restore', [InvoiceController::class, 'restore'])
        ->name('invoices.restore');
    Route::resource('invoices', InvoiceController::class);

    Route::resource('payments', PaymentController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);
});
