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
           'boatName' => 'sometimes|string|max:255',
            'offloadDate' => 'sometimes|date|before_or_equal:today',
            'tripNumber' => 'sometimes|string|max:255',
            'externalFactory' => 'sometimes|string|max:255',
            'totalKgOffloaded' => 'sometimes|numeric|min:0',
            'totalLive' => 'sometimes|numeric|min:0',
            'totalKgReceived' => 'sometimes|numeric|min:0',
            'totalKgDead' => 'sometimes|numeric|min:0',
            'totalKgRotten' => 'sometimes|numeric|min:0',
            'sizeU' => 'sometimes|numeric|min:0',
            'sizeA' => 'sometimes|numeric|min:0',
            'sizeB' => 'sometimes|numeric|min:0',
            'sizeC' => 'sometimes|numeric|min:0',
            'sizeD' => 'sometimes|numeric|min:0',
            'sizeE' => 'sometimes|numeric|min:0',
        ];
    }
}
