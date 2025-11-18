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
            'date' => 'sometimes|date|before_or_equal:today',
            'boatName' => 'sometimes|string|max:255',
            'boatNumber' => 'sometimes|string|max:100',
            'captainName' => 'sometimes|string|max:255',
            'totalCrates' => 'sometimes|integer|min:1',
            'totalKgAlive' => 'sometimes|numeric|min:0.01',
            'sizeU' => 'sometimes|numeric|min:0',
            'sizeA' => 'sometimes|numeric|min:0',
            'sizeB' => 'sometimes|numeric|min:0',
            'sizeC' => 'sometimes|numeric|min:0',
            'sizeD' => 'sometimes|numeric|min:0',
            'sizeE' => 'sometimes|numeric|min:0',
            'deadOnTanks' => 'sometimes|numeric|min:0',
            'rottenOnTanks' => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
