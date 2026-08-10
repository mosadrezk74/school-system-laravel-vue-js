<?php

namespace Modules\Finance\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Finance\Models\FeeCategory;
use Modules\Finance\Models\FeeDiscount;
use Modules\Finance\Models\FeeStructure;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\PaymentMethod;
use Modules\Finance\Models\Refund;
use Modules\Finance\Models\StudentFee;
use Modules\Finance\Policies\FeeCategoryPolicy;
use Modules\Finance\Policies\FeeDiscountPolicy;
use Modules\Finance\Policies\FeeStructurePolicy;
use Modules\Finance\Policies\InvoicePolicy;
use Modules\Finance\Policies\PaymentMethodPolicy;
use Modules\Finance\Policies\PaymentPolicy;
use Modules\Finance\Policies\RefundPolicy;
use Modules\Finance\Policies\StudentFeePolicy;
use Modules\Finance\Repositories\FeeCategoryRepository;
use Modules\Finance\Repositories\FeeCategoryRepositoryInterface;
use Modules\Finance\Repositories\FeeDiscountRepository;
use Modules\Finance\Repositories\FeeDiscountRepositoryInterface;
use Modules\Finance\Repositories\FeeStructureRepository;
use Modules\Finance\Repositories\FeeStructureRepositoryInterface;
use Modules\Finance\Repositories\InvoiceRepository;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;
use Modules\Finance\Repositories\PaymentMethodRepository;
use Modules\Finance\Repositories\PaymentMethodRepositoryInterface;
use Modules\Finance\Repositories\PaymentRepository;
use Modules\Finance\Repositories\PaymentRepositoryInterface;
use Modules\Finance\Repositories\StudentFeeRepository;
use Modules\Finance\Repositories\StudentFeeRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class FinanceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Finance';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'finance';

    public function register(): void
    {
        parent::register();

        $this->app->bind(FeeCategoryRepositoryInterface::class, FeeCategoryRepository::class);
        $this->app->bind(FeeStructureRepositoryInterface::class, FeeStructureRepository::class);
        $this->app->bind(FeeDiscountRepositoryInterface::class, FeeDiscountRepository::class);
        $this->app->bind(StudentFeeRepositoryInterface::class, StudentFeeRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `finance::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(FeeCategory::class, FeeCategoryPolicy::class);
        Gate::policy(FeeStructure::class, FeeStructurePolicy::class);
        Gate::policy(FeeDiscount::class, FeeDiscountPolicy::class);
        Gate::policy(StudentFee::class, StudentFeePolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(PaymentMethod::class, PaymentMethodPolicy::class);
        Gate::policy(Refund::class, RefundPolicy::class);
    }

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
