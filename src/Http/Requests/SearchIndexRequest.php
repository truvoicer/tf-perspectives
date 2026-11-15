<?php

namespace Truvoicer\TfPerspectives\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'        => ['nullable', 'string'],
            'tag'      => ['nullable', 'string'],
            'mood'     => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'sort'     => ['nullable', 'string'],
        ];
    }
}
