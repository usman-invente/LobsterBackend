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
    public function index(Request $request)
    {
        $query = ReceivingBatch::with(['crates', 'user']);

        // Add search filter for batch number
        if ($request->has('search') && !empty($request->search)) {
            $query->where('batchNumber', 'like', '%' . $request->search . '%');
        }

        $batches = $query->latest()->get();

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
            'lineItems' => 'required|array|min:1',
            'lineItems.*.boatName' => 'required|string|max:255',
            'lineItems.*.offloadDate' => 'required|date',
            'lineItems.*.crateNumber' => 'required|integer|unique:crates,crateNumber',
            'lineItems.*.size' => 'required|in:U,A,B,C,D,E,M',
            'lineItems.*.kg' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
        ], [
            'batchNumber.unique' => 'This batch number already exists.',
            'lineItems.*.crateNumber.unique' => 'Crate number :input has already been registered.',
            'lineItems.*.crateNumber.required' => 'Crate number is required for each line item.',
            'lineItems.*.boatName.required' => 'Boat name is required for each line item.',
            'lineItems.*.offloadDate.required' => 'Offload date is required for each line item.',
            'lineItems.*.offloadDate.date' => 'Offload date must be a valid date.',
            'lineItems.*.size.required' => 'Size is required for each line item.',
            'lineItems.*.size.in' => 'Size must be one of: U, A, B, C, D, E.',
            'lineItems.*.kg.required' => 'Weight is required for each line item.',
            'lineItems.*.kg.numeric' => 'Weight must be a valid number.',
            'lineItems.*.kg.min' => 'Weight must be greater than or equal to 0.',
            'lineItems.*.kg.regex' => 'Weight must have at most 2 decimal places.',
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

            // Create crates from line items
            $crates = [];
            foreach ($validated['lineItems'] as $lineItem) {
                $crate = $batch->crates()->create([
                    'boatName' => $lineItem['boatName'],
                    'offloadDate' => $lineItem['offloadDate'],
                    'crateNumber' => $lineItem['crateNumber'],
                    'size' => $lineItem['size'],
                    'kg' => $lineItem['kg'],
                    'originalKg' => $lineItem['kg'],
                    'originalSize' => $lineItem['size'],
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
            'lineItems' => 'required|array|min:1',
            'lineItems.*.boatName' => 'required|string|max:255',
            'lineItems.*.offloadDate' => 'required|date',
            'lineItems.*.crateNumber' => 'required|integer|min:1|max:300',
            'lineItems.*.size' => 'required|in:U,A,B,C,D,E,M',
            'lineItems.*.kg' => 'required|numeric|min:0.01',
        ]);

        // Update the batch basic info
        $receivingBatch->update([
            'date' => $validated['date'] ?? $receivingBatch->date,
            'batchNumber' => $validated['batchNumber'] ?? $receivingBatch->batchNumber,
        ]);

        // Get IDs of items being sent (existing items only)
        $sentItemIds = collect($request->lineItems)->pluck('id')->filter()->values()->toArray();

        // Delete crates that are no longer in the list (removed by user)
        $receivingBatch->crates()->whereNotIn('id', $sentItemIds)->delete();

        // Process each line item
        foreach ($request->lineItems as $item) {
            if (isset($item['id']) && $item['id']) {
                // Update existing crate
                $crate = $receivingBatch->crates()->find($item['id']);
                if ($crate) {
                    $crate->update([
                        'boatName' => $item['boatName'],
                        'offloadDate' => $item['offloadDate'],
                        'crateNumber' => $item['crateNumber'],
                        'size' => $item['size'],
                        'kg' => $item['kg'],
                    ]);
                }
            } else {
                // Create new crate
                $receivingBatch->crates()->create([
                    'boatName' => $item['boatName'],
                    'offloadDate' => $item['offloadDate'],
                    'crateNumber' => $item['crateNumber'],
                    'size' => $item['size'],
                    'kg' => $item['kg'],
                    'originalSize' => $item['size'],  // Add this
                    'originalKg' => $item['kg'],      // Add this
                    'status' => 'received',
                    'user_id' => $request->user()->id,
                ]);
            }
        }

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
