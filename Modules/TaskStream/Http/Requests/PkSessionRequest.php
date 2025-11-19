<?php

namespace Modules\TaskStream\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PkSessionRequest extends FormRequest
{
    public function rules()
    {
        //Todo
        return [
            'team_1' => ['required', 'array'],
            'team_1.*' => ['required', 'integer', Rule::exists('rooms')],
            'team_2' => ['required', 'array'],
            'team_2.*' => ['required', 'integer', Rule::exists('rooms')],
        ];
    }
}
