<?php

namespace Modules\Core\Abstracts;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Services\Translation\TranslationService;
use Modules\Core\Traits\ApiResponseTrait;
use Propaganistas\LaravelPhone\PhoneNumber;

abstract class BaseRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalises phone numbers to E.164 when propaganistas/laravel-phone is
     * available. The class check is required: that package came from another
     * project and isn't installed here, so an unguarded call would fatal on
     * any request carrying a `phone` field.
     */
    protected function passedValidation(): void
    {
        if ($this->filled('phone') && class_exists(PhoneNumber::class)) {
            $normalized = (string) (new PhoneNumber($this->input('phone')))->formatE164();

            $this->merge(['phone' => $normalized]);

            /** @var \Illuminate\Validation\Validator $validator */
            $validator = $this->validator;
            $validator->setValue('phone', $normalized);
        }

        foreach ($this->translatableFields as $field) {
            $this->expandTranslatableField($field);
        }
    }

    /**
     * Field names registered via translatableRules(), expanded into the
     * per-locale array shape once validation passes.
     *
     * @var array<int, string>
     */
    private array $translatableFields = [];

    /**
     * The single-input UX submits one plain string per translatable field.
     * This expands it into the full `{locale => text}` shape the DTOs and
     * Actions already expect: the typed text is detected and placed under
     * its source locale, merged over the record's existing translations (if
     * editing) so untouched locales are preserved, and TranslationService
     * fills in whatever locales are still missing.
     */
    private function expandTranslatableField(string $field): void
    {
        $text = $this->input($field);

        if (! is_string($text)) {
            return;
        }

        $translationService = app(TranslationService::class);
        $sourceLocale = $translationService->detectSourceLocale($text);

        $existing = $this->existingTranslations($field);
        $translations = array_merge($existing, [$sourceLocale => $text]);
        $translations = $translationService->fillMissing($translations, $sourceLocale);

        $this->merge([$field => $translations]);

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = $this->validator;
        $validator->setValue($field, $translations);
    }

    /**
     * @return array<string, string|null>
     */
    private function existingTranslations(string $field): array
    {
        foreach ($this->route()?->parameters() ?? [] as $parameter) {
            if (! is_object($parameter) || ! method_exists($parameter, 'getTranslations')) {
                continue;
            }

            $translatable = $parameter->translatable ?? [];

            if (in_array($field, $translatable, true)) {
                return $parameter->getTranslations($field);
            }
        }

        return [];
    }

    public function attributes(): array
    {
        return [];
    }

    /**
     * Inertia visits are ordinary HTML requests, so errors have to go back
     * through the session for useForm() to expose them as form.errors.
     * Returning JSON here (the API behaviour) would leave every Vue form
     * silently unable to show why a save failed.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                $this->validationError($validator->errors()->toArray())
            );
        }

        parent::failedValidation($validator);
    }

    protected function failedAuthorization()
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException($this->forbidden());
        }

        parent::failedAuthorization();
    }

    public function validatedWithout($keys = []): array
    {
        $data = parent::validated();
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    public function onlyFilled(): array
    {
        return array_filter($this->validated(), function ($value) {
            return ! is_null($value) && $value !== '';
        });
    }

    /**
     * Validation rules for a translatable field. The form submits a single
     * plain-text value; its language is auto-detected and the other active
     * locales are filled in automatically once validation passes (see
     * expandTranslatableField()), so the field only needs a string rule here.
     *
     * @return array<string, mixed>
     */
    protected function translatableRules(string $field, bool $required = true): array
    {
        $this->translatableFields[] = $field;

        return [
            $field => [$required ? 'required' : 'nullable', 'string'],
        ];
    }
}
