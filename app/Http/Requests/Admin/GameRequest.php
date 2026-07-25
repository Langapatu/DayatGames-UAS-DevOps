<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title')),
            'is_featured' => $this->boolean('is_featured'),
            'price_is_demo' => $this->boolean('price_is_demo'),
            'new_developer_name' => $this->filled('new_developer_name')
                ? trim((string) $this->input('new_developer_name'))
                : null,
            'new_publisher_name' => $this->filled('new_publisher_name')
                ? trim((string) $this->input('new_publisher_name'))
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'developer_id' => ['nullable', 'integer', 'exists:developers,id', 'required_without:new_developer_name'],
            'new_developer_name' => ['nullable', 'string', 'max:255', 'required_without:developer_id'],
            'publisher_id' => ['nullable', 'integer', 'exists:publishers,id', 'required_without:new_publisher_name'],
            'new_publisher_name' => ['nullable', 'string', 'max:255', 'required_without:publisher_id'],
            'steam_app_id' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('games', 'slug')->ignore($this->route('game')?->id),
            ],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:10000'],
            'original_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:original_price'],
            'discount_percent' => ['required', 'integer', 'between:0,100'],
            'price_checked_at' => ['nullable', 'date'],
            'price_source_url' => ['nullable', 'url:http,https', 'max:255'],
            'price_is_demo' => ['required', 'boolean'],
            'release_date' => ['nullable', 'date'],
            'platform' => ['required', 'string', 'max:100'],
            'operating_system' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['required', 'boolean'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['integer', 'distinct', 'exists:genres,id'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasDiscount = $this->filled('discount_price');
            $percent = (int) $this->input('discount_percent', 0);

            if ($hasDiscount && $percent === 0) {
                $validator->errors()->add('discount_percent', 'Persentase diskon harus lebih dari 0 saat harga diskon diisi.');
            }

            if (! $hasDiscount && $percent > 0) {
                $validator->errors()->add('discount_price', 'Harga diskon wajib diisi saat persentase diskon lebih dari 0.');
            }
        });
    }
}
