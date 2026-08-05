<?php

namespace Modules\Core\Traits;

use Illuminate\Support\Str;


trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model) {
                    if (empty($model->slug)) {
                        $model->slug = $model->generateUniqueSlug();
                    }
        });
    }

    public function generateUniqueSlug(): string
    {
        $base = Str::slug($this->slugSourceValue()) ?: Str::lower(Str::random(8));
        $slug = $base;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($this->exists, fn ($query) => $query->where($this->getKeyName(), '!=', $this->getKey()))
                ->exists()
        ) {
            $slug = $base . '-' . Str::lower(Str::random(5));
        }

        return $slug;
    }

    private function slugSourceValue(): string
    {
        $column = $this->slugSource ?? 'name';

        $value = method_exists($this, 'getTranslations')
            ? $this->getTranslations($column)
            : $this->{$column};

        if (is_array($value)) {
            $value = $value['en'] ?? reset($value) ?: '';
        }

        return (string) ($value ?? '');
    }
}
