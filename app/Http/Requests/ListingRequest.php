<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10',
            'location' => 'required|string|min:3|max:30',
            'price' => 'required|numeric|min:3|max:10',
            'capacity' => 'required|numeric|min:1|max:10',
            'is_available' => 'required|boolean|in:0,1',
        ];
    }
}
