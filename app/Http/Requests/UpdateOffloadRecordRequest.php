<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOffloadRecordRequest extends FormRequest
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
}
