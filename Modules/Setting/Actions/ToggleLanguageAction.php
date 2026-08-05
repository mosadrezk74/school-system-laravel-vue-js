<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Exceptions\BaseException;
use Modules\Setting\Models\Language;

/**
 * Switches a language on or off for the school.
 */
class ToggleLanguageAction extends BaseAction
{
    /**
     * @param  Language  $input
     */
    public function execute(mixed $input): Language
    {
        return $this->executeWithTransaction(function () use ($input): Language {
            $turningOff = $input->is_active;

            /*
             * The default language is what every fallback resolves to, and
             * disabling it would leave translatable records with no readable
             * value. Require another default first.
             */
            if ($turningOff && $input->is_default) {
                throw BaseException::badRequest(
                    __('setting::languages.cannot_disable_default'),
                );
            }

            if ($turningOff && Language::query()->where('is_active', true)->count() <= 1) {
                throw BaseException::badRequest(
                    __('setting::languages.cannot_disable_last'),
                );
            }

            $input->update(['is_active' => ! $turningOff]);

            Language::forgetCache();

            return $input->refresh();
        });
    }
}
