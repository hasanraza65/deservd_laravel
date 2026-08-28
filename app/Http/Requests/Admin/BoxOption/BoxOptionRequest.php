<?php

namespace App\Http\Requests\Admin\BoxOption;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoxOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $boxOptionId = $this->route('box_option')?->id;

        return [
            'size' => ['required', 'integer', 'min:1', 'max:100', Rule::unique('box_options', 'size')->ignore($boxOptionId)],
            'price' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
