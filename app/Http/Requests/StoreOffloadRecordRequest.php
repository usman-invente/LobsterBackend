<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOffloadRecordRequest extends FormRequest
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
        return [
            'date' => 'required|date_format:Y-m-d|before_or_equal:today',
            'boatName' => 'required|string|max:255',
            'boatNumber' => 'required|string|max:100',
            'captainName' => 'required|string|max:255',
            'totalCrates' => 'required|integer|min:1',
            'totalKgAlive' => 'required|numeric|min:0.01',
            'sizeU' => 'required|numeric|min:0',
            'sizeA' => 'required|numeric|min:0',
            'sizeB' => 'required|numeric|min:0',
            'sizeC' => 'required|numeric|min:0',
            'sizeD' => 'required|numeric|min:0',
            'sizeE' => 'required|numeric|min:0',
            'deadOnTanks' => 'required|numeric|min:0',
            'rottenOnTanks' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

     /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'The offload date is required.',
            'date.before_or_equal' => 'The offload date cannot be in the future.',
            'boatName.required' => 'The boat name is required.',
            'boatNumber.required' => 'The boat number is required.',
            'captainName.required' => 'The captain name is required.',
            'totalCrates.required' => 'The total number of crates is required.',
            'totalCrates.min' => 'There must be at least 1 crate.',
            'totalKgAlive.required' => 'The total weight is required.',
            'totalKgAlive.min' => 'The total weight must be greater than 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure numeric fields are properly formatted
        $this->merge([
            'totalCrates' => (int) $this->totalCrates,
            'totalKgAlive' => (float) $this->totalKgAlive,
            'sizeU' => (float) ($this->sizeU ?? 0),
            'sizeA' => (float) ($this->sizeA ?? 0),
            'sizeB' => (float) ($this->sizeB ?? 0),
            'sizeC' => (float) ($this->sizeC ?? 0),
            'sizeD' => (float) ($this->sizeD ?? 0),
            'sizeE' => (float) ($this->sizeE ?? 0),
            'deadOnTanks' => (float) ($this->deadOnTanks ?? 0),
            'rottenOnTanks' => (float) ($this->rottenOnTanks ?? 0),
        ]);
    }
}
