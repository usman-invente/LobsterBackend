<?php

namespace App\Http\Controllers;

use App\Models\OffloadRecord;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOffloadRecordRequest;
use App\Http\Requests\UpdateOffloadRecordRequest;
use Illuminate\Support\Facades\DB;

class OffloadRecordController extends Controller
{
    /**
     * Display a listing of offload records.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = OffloadRecord::with('user:id,name,email');

            // Search across multiple fields
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('boatName', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('tripNumber', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('externalFactory', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'offloadDate');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $records = $query->paginate($perPage);

            return response()->json([
                'data' => $records->items(),
                'meta' => [
                    'total' => $records->total(),
                    'current_page' => $records->currentPage(),
                    'per_page' => $records->perPage(),
                    'last_page' => $records->lastPage(),
                ],
                'message' => 'Offload records retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving offload records',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(StoreOffloadRecordRequest $request)
    {
        DB::beginTransaction();
        try {
            // Get the product and its sizes
            $product = \App\Models\Product::with('sizes')->find($request->productId);
            if (!$product) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'productId' => ['Product not found']
                    ],
                ], 422);
            }

            // Build sizes array from request payload: sizes => {key: value}
            $sizesPayload = (array) $request->input('sizes', []);
            $validSizeNames = collect($product->sizes)->map(function ($s) {
                return is_array($s) ? ($s['size'] ?? null) : $s->size;
            })->filter()->values()->all();

            $sizes = [];
            $sizeTotal = 0.0;
            foreach ($validSizeNames as $sizeName) {
                $raw = $sizesPayload[$sizeName] ?? 0;
                $value = is_numeric($raw) ? (float) $raw : 0.0;
                $sizes[$sizeName] = $value;
                $sizeTotal += $value;
            }

            // Validate that size breakdown matches total live kg
            $totalLive = (float) ($request->input('totalLive', 0));
            if (abs($sizeTotal - $totalLive) > 0.01) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'totalLive' => [
                            'Size breakdown total does not match total live kg'
                        ]
                    ],
                ], 422);
            }

            // Prepare data, excluding hardcoded size fields and adding sizes
            $data = $request->validated();
            unset($data['sizeU'], $data['sizeA'], $data['sizeB'], $data['sizeC'], $data['sizeD'], $data['sizeE'], $data['sizeM']);
            $data['sizes'] = $sizes;

            // Create the offload record
            $offloadRecord = $request->user()->offloadRecords()->create($data);

            DB::commit();

            return response()->json([
                'message' => 'Offload record created successfully',
                'data' => $offloadRecord->load('user:id,name,email'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating offload record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified offload record.
     *
     * @param  \App\Models\OffloadRecord  $offloadRecord
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(OffloadRecord $offloadRecord)
    {
        try {
            return response()->json([
                'data' => $offloadRecord->load('user:id,name,email'),
                'message' => 'Offload record retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving offload record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified offload record.
     *
     * @param  \App\Http\Requests\UpdateOffloadRecordRequest  $request
     * @param  \App\Models\OffloadRecord  $offloadRecord
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateOffloadRecordRequest $request, OffloadRecord $offloadRecord)
    {
        DB::beginTransaction();
        try {
            $productId = (int) $request->input('productId', $offloadRecord->productId);
            $product = \App\Models\Product::with('sizes')->find($productId);
            if (!$product) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'productId' => ['Product not found']
                    ],
                ], 422);
            }

            $sizesPayload = (array) $request->input('sizes', $offloadRecord->sizes ?? []);
            $validSizeNames = collect($product->sizes)->map(function ($s) {
                return is_array($s) ? ($s['size'] ?? null) : $s->size;
            })->filter()->values()->all();

            $sizes = [];
            $sizeTotal = 0.0;
            foreach ($validSizeNames as $sizeName) {
                $raw = $sizesPayload[$sizeName] ?? 0;
                $value = is_numeric($raw) ? (float) $raw : 0.0;
                $sizes[$sizeName] = $value;
                $sizeTotal += $value;
            }

            $totalLive = (float) ($request->input('totalLive', $offloadRecord->totalLive ?? 0));
            if (abs($sizeTotal - $totalLive) > 0.01) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'totalLive' => [
                            'Size breakdown total does not match total live kg'
                        ]
                    ],
                ], 422);
            }

            $data = $request->validated();
            unset($data['sizeU'], $data['sizeA'], $data['sizeB'], $data['sizeC'], $data['sizeD'], $data['sizeE'], $data['sizeM']);
            $data['sizes'] = $sizes;

            $offloadRecord->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Offload record updated successfully',
                'data' => $offloadRecord->load('user:id,name,email'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating offload record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified offload record.
     *
     * @param  \App\Models\OffloadRecord  $offloadRecord
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(OffloadRecord $offloadRecord)
    {
        DB::beginTransaction();
        try {
            $offloadRecord->delete();

            DB::commit();

            return response()->json([
                'message' => 'Offload record deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting offload record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics for offload records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $records = OffloadRecord::dateRange($request->start_date, $request->end_date)->get();

            $statistics = [
                'period' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date,
                ],
                'totalRecords' => $records->count(),
                'totalCrates' => $records->sum('totalCrates'),
                'totalKgAlive' => $records->sum('totalKgAlive'),
                'totalDeadOnTanks' => $records->sum('deadOnTanks'),
                'totalRottenOnTanks' => $records->sum('rottenOnTanks'),
                'sizeBreakdown' => [
                    'U' => $records->sum('sizeU'),
                    'A' => $records->sum('sizeA'),
                    'B' => $records->sum('sizeB'),
                    'C' => $records->sum('sizeC'),
                    'D' => $records->sum('sizeD'),
                    'E' => $records->sum('sizeE'),
                    'M' => $records->sum('sizeM'),
                ],
                'averagePerRecord' => [
                    'crates' => $records->count() > 0 ? $records->avg('totalCrates') : 0,
                    'kg' => $records->count() > 0 ? $records->avg('totalKgAlive') : 0,
                ],
                'byBoat' => $records->groupBy('boatNumber')->map(function ($boatRecords) {
                    return [
                        'boatName' => $boatRecords->first()->boatName,
                        'totalRecords' => $boatRecords->count(),
                        'totalKg' => $boatRecords->sum('totalKgAlive'),
                    ];
                }),
            ];

            return response()->json([
                'data' => $statistics,
                'message' => 'Statistics retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
