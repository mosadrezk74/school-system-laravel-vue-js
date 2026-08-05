<?php

namespace Modules\Core\Filament\Concerns;

use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Password;
use Modules\Core\Constants\Regex;

trait HasCommonValidation
{
    protected static function phoneField(string $name = 'phone'): TextInput
    {
        return TextInput::make($name)
            ->tel()
            ->rule('phone:INTERNATIONAL,MOBILE')
            ->maxLength(20)
            ->validationMessages([
                'phone' => __('core::validation.phone'),
            ]);
    }

    protected static function emailField(string $name = 'email'): TextInput
    {
        return TextInput::make($name)
            ->email()
            ->rule('email:rfc,dns')
            ->maxLength(255)
            ->validationMessages([
                'email' => __('core::validation.email'),
            ]);
    }

    protected static function nameField(string $name = 'name'): TextInput
    {
        return TextInput::make($name)
            ->maxLength(255);
    }

    protected static function urlField(string $name = 'url'): TextInput
    {
        return TextInput::make($name)
            ->url()
            ->rule('url')
            ->maxLength(2048)
            ->validationMessages([
                'url' => __('core::validation.url'),
            ]);
    }

    protected static function passwordField(string $name = 'password'): TextInput
    {
        return TextInput::make($name)
            ->password()
            ->revealable()
            // Password::min(8)->letters()->numbers() (rather than three
            // separate ->rule('regex:...') calls) is what gives each
            // constraint its own validation message key below - plain
            // regex rules all collapse to the same "regex" key and can't
            // be told apart.
            ->rule(Password::min(8)->letters()->numbers())
            ->maxLength(255)
            ->validationMessages([
                'min' => __('core::validation.password.min'),
                'password.letters' => __('core::validation.password.letters'),
                'password.numbers' => __('core::validation.password.numbers'),
            ]);
    }

    protected static function priceField(string $name = 'price'): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->rule('regex:'.Regex::PRICE)
            ->minValue(0)
            ->validationMessages([
                'regex' => __('core::validation.price'),
            ]);
    }

    protected static function postalCodeField(string $name = 'postal_code'): TextInput
    {
        return TextInput::make($name)
            ->rule('regex:'.Regex::POSTAL_CODE)
            ->validationMessages([
                'regex' => __('core::validation.postal_code'),
            ]);
    }
}
