<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Exceptions\BaseException;
use Modules\Setting\Models\Language;

/**
 * Promotes one language to the school default and demotes the rest, as a
 * single transaction. Exactly one row holds is_default at any time.
 */
class SetDefaultLanguageAction extends BaseAction
{
    /**
     * @param  Language  $input
     */
    public function execute(mixed $input): Language
    {
        return $this->executeWithTransaction(function () use ($input): Language {
            // A disabled language can't be the fallback: nothing would render
            // for users whose own locale is missing a translation.
            if (! $input->is_active) {
                throw BaseException::badRequest(
                    __('setting::languages.cannot_default_inactive'),
                );
            }

            Language::query()->where('is_default', true)->update(['is_default' => false]);

            $input->update(['is_default' => true]);

            Language::forgetCache();

            return $input->refresh();
        });
    }
}
