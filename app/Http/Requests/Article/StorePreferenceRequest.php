<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class StorePreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'category' => 'nullable|array',
            'category.*' => 'string|max:255',
            'author' => 'nullable|array',
            'author.*' => 'string|max:255',
            'source' => 'nullable|array',
            'source.*' => 'string|max:255',
        ];
    }
}
