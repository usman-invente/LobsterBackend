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
            'boatName' => 'required|string|max:255',
            'offloadDate' => 'required|date',
            'tripNumber' => 'required|string|max:255',
            'externalFactory' => 'required|string|max:255',
            'totalKgOffloaded' => 'required|numeric|min:0.01',
            'totalKgReceived' => 'required|numeric|min:0.01',
            'totalKgDead' => 'nullable|numeric|min:0',
            'totalKgRotten' => 'nullable|numeric|min:0',
            'totalLive' => 'nullable|numeric|min:0',
            'sizeU' => 'nullable|numeric|min:0',
            'sizeA' => 'nullable|numeric|min:0',
            'sizeB' => 'nullable|numeric|min:0',
            'sizeC' => 'nullable|numeric|min:0',
            'sizeD' => 'nullable|numeric|min:0',
            'sizeE' => 'nullable|numeric|min:0',
            'sizeM' => 'nullable|numeric|min:0',
            'productId' => 'required|exists:products,id',
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
            'boatName.required' => 'The boat name is required.',
            'offloadDate.required' => 'The offload date is required.',
            'offloadDate.before_or_equal' => 'The offload date cannot be in the future.',
            'tripNumber.required' => 'The trip number is required.',
            'externalFactory.required' => 'The external factory is required.',
            'totalKgOffloaded.required' => 'The total kg offloaded is required.',
            'totalKgReceived.required' => 'The total kg received is required.',
            'totalKgDead.required' => 'The total kg dead is required.',
            'totalKgRotten.required' => 'The total kg rotten is required.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure numeric fields are properly formatted
        $this->merge([
            'totalKgOffloaded' => (float) ($this->totalKgOffloaded ?? 0),
            'totalKgReceived' => (float) ($this->totalKgReceived ?? 0),
            'totalKgDead' => (float) ($this->totalKgDead ?? 0),
            'totalKgRotten' => (float) ($this->totalKgRotten ?? 0),
            'sizeU' => (float) ($this->sizeU ?? 0),
            'sizeA' => (float) ($this->sizeA ?? 0),
            'sizeB' => (float) ($this->sizeB ?? 0),
            'sizeC' => (float) ($this->sizeC ?? 0),
            'sizeD' => (float) ($this->sizeD ?? 0),
            'sizeE' => (float) ($this->sizeE ?? 0),
            'sizeM' => (float) ($this->sizeM ?? 0),
        ]);
    }
}
