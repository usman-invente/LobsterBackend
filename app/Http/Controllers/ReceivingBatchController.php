<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReceivingBatch;
use App\Models\Crate;

use Illuminate\Support\Facades\DB;
class ReceivingBatchController extends Controller
{
    /**
     * Display a listing of receiving batches.
     */
    public function index()
    {
        $batches = ReceivingBatch::with(['crates', 'user'])
            ->latest()
            ->get();

        return response()->json(['data' => $batches]);
    }

    /**
     * Store a newly created receiving batch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'batchNumber' => 'required|string|max:255|unique:receiving_batches,batchNumber',
            'crates' => 'required|array|min:1',
            'crates.*.boatName' => 'required|string|max:255',
            'crates.*.offloadDate' => 'required|date',
            'crates.*.crateNumber' => 'required|integer|unique:crates,crateNumber',
            'crates.*.size' => 'required|in:U,A,B,C,D,E',
            'crates.*.kg' => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/',
        ], [
            'batchNumber.unique' => 'This batch number already exists.',
            'crates.*.crateNumber.unique' => 'Crate number :input has already been registered.',
            'crates.*.crateNumber.required' => 'Crate number is required for each crate.',
            'crates.*.crateNumber.integer' => 'Crate number must be a valid number.',
            'crates.*.boatName.required' => 'Boat name is required for each crate.',
            'crates.*.offloadDate.required' => 'Offload date is required for each crate.',
            'crates.*.offloadDate.date' => 'Offload date must be a valid date.',
            'crates.*.size.required' => 'Size is required for each crate.',
            'crates.*.size.in' => 'Size must be one of: U, A, B, C, D, E.',
            'crates.*.kg.required' => 'Weight is required for each crate.',
            'crates.*.kg.numeric' => 'Weight must be a valid number.',
            'crates.*.kg.min' => 'Weight must be greater than 0.',
            'crates.*.kg.regex' => 'Weight must have at most 2 decimal places.',
            'date.required' => 'Batch date is required.',
            'date.date' => 'Batch date must be a valid date.',
        ]);

        DB::beginTransaction();
        try {
            // Create batch
            $batch = $request->user()->receivingBatches()->create([
                'date' => $validated['date'],
                'batchNumber' => $validated['batchNumber'],
            ]);

            // Create crates
            $crates = [];
            foreach ($validated['crates'] as $crateData) {
                $crate = $batch->crates()->create([
                    'boatName' => $crateData['boatName'],
                    'offloadDate' => $crateData['offloadDate'],
                    'crateNumber' => $crateData['crateNumber'],
                    'size' => $crateData['size'],
                    'kg' => $crateData['kg'],
                    'originalKg' => $crateData['kg'],
                    'originalSize' => $crateData['size'],
                    'status' => 'received',
                    'user_id' => $request->user()->id,
                ]);
                $crates[] = $crate;
            }

            DB::commit();

            return response()->json([
                'message' => 'Receiving batch created successfully',
                'data' => $batch->load('crates'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating receiving batch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     /**
     * Display the specified receiving batch.
     */
    public function show(ReceivingBatch $receivingBatch)
    {
        return response()->json([
            'data' => $receivingBatch->load(['crates', 'user']),
        ]);
    }

     /**
     * Update the specified receiving batch.
     */
    public function update(Request $request, ReceivingBatch $receivingBatch)
    {
        $validated = $request->validate([
            'date' => 'sometimes|date',
            'batchNumber' => 'sometimes|string|max:255|unique:receiving_batches,batchNumber,' . $receivingBatch->id,
        ]);

        $receivingBatch->update($validated);

        return response()->json([
            'message' => 'Receiving batch updated successfully',
            'data' => $receivingBatch->load('crates'),
        ]);
    }

    /**
     * Remove the specified receiving batch.
     */
    public function destroy(ReceivingBatch $receivingBatch)
    {
        $receivingBatch->delete();

        return response()->json([
            'message' => 'Receiving batch deleted successfully',
        ]);
    }
}
