<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dispatch;
use App\Models\DispatchLineItem;
use App\Models\Tank;
use App\Models\Crate;
use Illuminate\Support\Facades\DB;
class DispatchController extends Controller
{
    /**
     * Display a listing of dispatches.
     */
    public function index()
    {
        $dispatches = Dispatch::with(['lineItems', 'user'])
            ->latest()
            ->get();

        return response()->json(['data' => $dispatches]);
    }

     /**
     * Store a newly created dispatch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:export,regrade',
            'clientAwb' => 'required|string|max:255',
            'dispatchDate' => 'required|date',
            'lineItems' => 'required|array|min:1',
            'lineItems.*.tankId' => 'required|string',
            'lineItems.*.tankNumber' => 'required|integer',
            'lineItems.*.crateId' => 'nullable',
            'lineItems.*.looseStockId' => 'nullable|string',
            'lineItems.*.size' => 'required|in:U,A,B,C,D,E',
            'lineItems.*.kg' => 'required|numeric|min:0.01',
            'lineItems.*.crateNumber' => 'nullable|integer',
            'lineItems.*.isLoose' => 'required|boolean',
            'totalKg' => 'required|numeric|min:0.01',
            'sizeU' => 'required|numeric|min:0',
            'sizeA' => 'required|numeric|min:0',
            'sizeB' => 'required|numeric|min:0',
            'sizeC' => 'required|numeric|min:0',
            'sizeD' => 'required|numeric|min:0',
            'sizeE' => 'required|numeric|min:0',
        ]);

        // Validate total matches sum
        $calculatedTotal = array_sum(array_column($validated['lineItems'], 'kg'));
        if (abs($calculatedTotal - $validated['totalKg']) > 0.01) {
            return response()->json([
                'message' => 'Total kg does not match sum of line items'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Validate stock availability
            foreach ($validated['lineItems'] as $item) {
                if ($item['isLoose']) {
                    // Validate loose stock
                    // Add your loose stock validation logic
                } else {
                    // Validate crate
                    $crate = Crate::where('id', $item['crateId'])
                        ->where('status', 'stored')
                        ->first();
                    
                    if (!$crate) {
                        throw new \Exception("Crate not found or not available");
                    }
                    
                    if ($crate->kg < $item['kg']) {
                        throw new \Exception(
                            "Insufficient stock in crate {$crate->crateNumber}"
                        );
                    }
                }
            }

            // Create dispatch
            $dispatch = $request->user()->dispatches()->create([
                'type' => $validated['type'],
                'clientAwb' => $validated['clientAwb'],
                'dispatchDate' => $validated['dispatchDate'],
                'totalKg' => $validated['totalKg'],
                'sizeU' => $validated['sizeU'],
                'sizeA' => $validated['sizeA'],
                'sizeB' => $validated['sizeB'],
                'sizeC' => $validated['sizeC'],
                'sizeD' => $validated['sizeD'],
                'sizeE' => $validated['sizeE'],
            ]);

            // Create line items and update stock
            foreach ($validated['lineItems'] as $itemData) {
                $dispatch->lineItems()->create($itemData);

                // Update crate status or deduct loose stock
                if (!$itemData['isLoose']) {
                    $crate = Crate::find($itemData['crateId']);
                    $crate->update(['status' => 'dispatched']);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Dispatch created successfully',
                'data' => $dispatch->load('lineItems'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating dispatch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified dispatch.
     */

     public function show(Dispatch $dispatch)
    {
        return response()->json([
            'data' => $dispatch->load(['lineItems', 'user']),
        ]);
    }

    /**
     * Update the specified dispatch.
     */
    public function update(Request $request, Dispatch $dispatch)
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:export,regrade',
            'clientAwb' => 'sometimes|string|max:255',
            'dispatchDate' => 'sometimes|date',
        ]);

        $dispatch->update($validated);

        return response()->json([
            'message' => 'Dispatch updated successfully',
            'data' => $dispatch->load('lineItems'),
        ]);
    }

    /**
     * Remove the specified dispatch.
     */
    public function destroy(Dispatch $dispatch)
    {
        DB::beginTransaction();
        try {
            // Restore stock before deleting
            foreach ($dispatch->lineItems as $item) {
                if (!$item->isLoose && $item->crateId) {
                    $crate = Crate::find($item->crateId);
                    if ($crate) {
                        $crate->update(['status' => 'stored']);
                    }
                }
            }

            $dispatch->delete();
            DB::commit();

            return response()->json([
                'message' => 'Dispatch deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting dispatch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dispatch summary for date range.
     */
    public function summary(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'nullable|in:export,regrade',
        ]);

        $query = Dispatch::whereBetween('dispatchDate', [
            $validated['start_date'],
            $validated['end_date']
        ]);

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        $dispatches = $query->get();

        $summary = [
            'period' => [
                'start' => $validated['start_date'],
                'end' => $validated['end_date'],
            ],
            'totalDispatches' => $dispatches->count(),
            'totalKg' => $dispatches->sum('totalKg'),
            'byType' => [
                'export' => [
                    'count' => $dispatches->where('type', 'export')->count(),
                    'totalKg' => $dispatches->where('type', 'export')->sum('totalKg'),
                ],
                'regrade' => [
                    'count' => $dispatches->where('type', 'regrade')->count(),
                    'totalKg' => $dispatches->where('type', 'regrade')->sum('totalKg'),
                ],
            ],
            'bySize' => [
                'U' => ['kg' => $dispatches->sum('sizeU')],
                'A' => ['kg' => $dispatches->sum('sizeA')],
                'B' => ['kg' => $dispatches->sum('sizeB')],
                'C' => ['kg' => $dispatches->sum('sizeC')],
                'D' => ['kg' => $dispatches->sum('sizeD')],
                'E' => ['kg' => $dispatches->sum('sizeE')],
            ],
            'dispatches' => $dispatches,
        ];

        return response()->json(['data' => $summary]);
    }
}
