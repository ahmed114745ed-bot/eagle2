<?php

namespace App\Http\Requests\Modules\AreaManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $regionId = $this->route('region');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                $regionId
                    ? 'unique:regions,name,' . $regionId
                    : 'unique:regions,name'
            ],
            'manager_id' => 'required|exists:area_managers,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Region name is required'),
            'name.min' => __('Region name must be at least 2 characters'),
            'name.max' => __('Region name cannot exceed 255 characters'),
            'name.unique' => __('This region name already exists'),
            'manager_id.required' => __('Manager is required'),
            'manager_id.exists' => __('Selected manager does not exist'),
        ];
    }
}
