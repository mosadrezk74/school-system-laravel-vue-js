<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;
use Modules\Academic\Services\AcademicContextService;

abstract class TestCase extends BaseTestCase
{
    /**
     * The academic context memoises the current year and semester in statics
     * that outlive a single test. Without this reset, the year resolved by one
     * test leaks into the next and the result depends on execution order —
     * which is why several suites had to call flush() by hand.
     */
    protected function setUp(): void
    {
        parent::setUp();

        AcademicContextService::flush();
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
