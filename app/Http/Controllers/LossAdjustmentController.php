<?php

namespace App\Http\Controllers;

use App\Models\LossAdjustment;
use App\Models\Tank;
use App\Models\Crate;
use App\Models\LooseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LossAdjustmentController extends Controller
{
    /**
     * Display a listing of loss adjustments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = LossAdjustment::with('user:id,name,email');

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by tank number
        if ($request->has('tank_number')) {
            $query->where('tankNumber', $request->tank_number);
        }

        $lossAdjustments = $query->latest('date')->get();

        return response()->json([
            'data' => $lossAdjustments,
        ], 200);
    }

    /**
     * Store a newly created loss adjustment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'tankId' => 'required|integer',
            'type' => 'required|in:dead,rotten,lost',
            'size' => 'required|in:U,A,B,C,D,E',
            'kg' => 'required|numeric|min:0.01|max:9999.99',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Validate that tank has sufficient stock
            $tank = Tank::where('id', $validated['tankId'])->first();
            
            if (!$tank) {
                return response()->json([
                    'message' => 'Tank not found',
                ], 404);
            }

            // Check available stock for the specific size


        $size = $validated['size'];
        $crateKg = Crate::where('tankId', $validated['tankId'])
            ->where('size', $size)
            ->whereIn('status', ['stored', 'received']) // adjust statuses as needed
            ->sum('kg');

        $looseKg = LooseStock::where('tankId', $validated['tankId'])
            ->where('size', $size)
            ->sum('kg');

        $totalSizeStock = $crateKg + $looseKg;

            if ($totalSizeStock < $validated['kg']) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'error' => "Tank {$tank->tankName} has only {$totalSizeStock} kg of size {$validated['size']} available, cannot adjust {$validated['kg']} kg",
                ], 400);
            }

            // Create loss adjustment record
            $lossAdjustment = $request->user()->lossAdjustments()->create($validated);

            // Deduct from tank inventory
            $tank->decrement($sizeField, $validated['kg']);
            $tank->decrement('totalKg', $validated['kg']);

            DB::commit();

            return response()->json([
                'message' => 'Loss adjustment created successfully',
                'data' => $lossAdjustment->load('user:id,name,email'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating loss adjustment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified loss adjustment.
     *
     * @param  \App\Models\LossAdjustment  $lossAdjustment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(LossAdjustment $lossAdjustment)
    {
        return response()->json([
            'data' => $lossAdjustment->load('user:id,name,email'),
        ], 200);
    }

    /**
     * Update the specified loss adjustment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LossAdjustment  $lossAdjustment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, LossAdjustment $lossAdjustment)
    {
        $validated = $request->validate([
            'date' => 'sometimes|date|before_or_equal:today',
            'tankId' => 'sometimes|integer',
            'tankNumber' => 'sometimes|integer',
            'type' => 'sometimes|in:dead,rotten,lost',
            'size' => 'sometimes|in:U,A,B,C,D,E',
            'kg' => 'sometimes|numeric|min:0.01|max:9999.99',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // If kg or size changed, revert old adjustment and apply new one
            if (isset($validated['kg']) || isset($validated['size'])) {
                $tank = Tank::where('tankNumber', $lossAdjustment->tankNumber)->first();
                
                // Revert old adjustment
                $oldSizeField = 'size' . $lossAdjustment->size;
                $tank->increment($oldSizeField, $lossAdjustment->kg);
                $tank->increment('totalKg', $lossAdjustment->kg);

                // Apply new adjustment
                $newSize = $validated['size'] ?? $lossAdjustment->size;
                $newKg = $validated['kg'] ?? $lossAdjustment->kg;
                $newSizeField = 'size' . $newSize;

                // Validate new adjustment
                $availableStock = $tank->$newSizeField ?? 0;
                if ($availableStock < $newKg) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Insufficient stock for updated adjustment',
                        'error' => "Tank {$lossAdjustment->tankNumber} has only {$availableStock} kg of size {$newSize} available",
                    ], 400);
                }

                $tank->decrement($newSizeField, $newKg);
                $tank->decrement('totalKg', $newKg);
            }

            $lossAdjustment->update($validated);

            DB::commit();

            return response()->json([
                'message' => 'Loss adjustment updated successfully',
                'data' => $lossAdjustment->load('user:id,name,email'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating loss adjustment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified loss adjustment.
     *
     * @param  \App\Models\LossAdjustment  $lossAdjustment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(LossAdjustment $lossAdjustment)
    {
        DB::beginTransaction();
        try {
            // Restore stock when deleting adjustment
            $tank = Tank::where('tankNumber', $lossAdjustment->tankNumber)->first();
            
            if ($tank) {
                $sizeField = 'size' . $lossAdjustment->size;
                $tank->increment($sizeField, $lossAdjustment->kg);
                $tank->increment('totalKg', $lossAdjustment->kg);
            }

            $lossAdjustment->delete();

            DB::commit();

            return response()->json([
                'message' => 'Loss adjustment deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting loss adjustment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get loss summary for date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'nullable|in:dead,rotten,lost',
            'tank_number' => 'nullable|integer|between:1,8',
        ]);

        try {
            $query = LossAdjustment::whereBetween('date', [
                $validated['start_date'],
                $validated['end_date']
            ]);

            if (isset($validated['type'])) {
                $query->where('type', $validated['type']);
            }

            if (isset($validated['tank_number'])) {
                $query->where('tankNumber', $validated['tank_number']);
            }

            $records = $query->get();
            $totalKg = $records->sum('kg');
            $totalRecords = $records->count();

            // Calculate days in period
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $days = $startDate->diffInDays($endDate) + 1;

            $summary = [
                'period' => [
                    'start' => $validated['start_date'],
                    'end' => $validated['end_date'],
                ],
                'totalRecords' => $totalRecords,
                'totalKg' => round($totalKg, 2),
                'byType' => [
                    'dead' => [
                        'count' => $records->where('type', 'dead')->count(),
                        'totalKg' => round($records->where('type', 'dead')->sum('kg'), 2),
                        'percentage' => $totalKg > 0 ? round(($records->where('type', 'dead')->sum('kg') / $totalKg) * 100, 2) : 0,
                    ],
                    'rotten' => [
                        'count' => $records->where('type', 'rotten')->count(),
                        'totalKg' => round($records->where('type', 'rotten')->sum('kg'), 2),
                        'percentage' => $totalKg > 0 ? round(($records->where('type', 'rotten')->sum('kg') / $totalKg) * 100, 2) : 0,
                    ],
                    'lost' => [
                        'count' => $records->where('type', 'lost')->count(),
                        'totalKg' => round($records->where('type', 'lost')->sum('kg'), 2),
                        'percentage' => $totalKg > 0 ? round(($records->where('type', 'lost')->sum('kg') / $totalKg) * 100, 2) : 0,
                    ],
                ],
                'bySize' => [],
                'byTank' => [],
                'dailyAverage' => [
                    'records' => $days > 0 ? round($totalRecords / $days, 2) : 0,
                    'kg' => $days > 0 ? round($totalKg / $days, 2) : 0,
                ],
                'records' => $records,
            ];

            // By size
            foreach (['U', 'A', 'B', 'C', 'D', 'E'] as $size) {
                $sizeRecords = $records->where('size', $size);
                $sizeKg = $sizeRecords->sum('kg');
                $summary['bySize'][$size] = [
                    'kg' => round($sizeKg, 2),
                    'count' => $sizeRecords->count(),
                    'percentage' => $totalKg > 0 ? round(($sizeKg / $totalKg) * 100, 2) : 0,
                ];
            }

            // By tank
            for ($i = 1; $i <= 8; $i++) {
                $tankRecords = $records->where('tankNumber', $i);
                $summary['byTank'][$i] = [
                    'count' => $tankRecords->count(),
                    'totalKg' => round($tankRecords->sum('kg'), 2),
                ];
            }

            return response()->json([
                'data' => $summary,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving loss summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get loss trends over time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trends(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'nullable|in:dead,rotten,lost',
        ]);

        try {
            $query = LossAdjustment::whereBetween('date', [
                $validated['start_date'],
                $validated['end_date']
            ]);

            if (isset($validated['type'])) {
                $query->where('type', $validated['type']);
            }

            $records = $query->get();

            // Daily breakdown
            $daily = $records->groupBy('date')->map(function ($dayRecords) {
                return [
                    'date' => $dayRecords->first()->date->format('Y-m-d'),
                    'totalKg' => round($dayRecords->sum('kg'), 2),
                    'dead' => round($dayRecords->where('type', 'dead')->sum('kg'), 2),
                    'rotten' => round($dayRecords->where('type', 'rotten')->sum('kg'), 2),
                    'lost' => round($dayRecords->where('type', 'lost')->sum('kg'), 2),
                    'count' => $dayRecords->count(),
                ];
            })->values();

            // Weekly breakdown
            $weekly = $records->groupBy(function ($record) {
                return Carbon::parse($record->date)->format('Y-\WW');
            })->map(function ($weekRecords, $week) {
                $days = $weekRecords->groupBy('date')->count();
                return [
                    'week' => $week,
                    'totalKg' => round($weekRecords->sum('kg'), 2),
                    'averagePerDay' => $days > 0 ? round($weekRecords->sum('kg') / $days, 2) : 0,
                    'count' => $weekRecords->count(),
                ];
            })->values();

            // Peak loss days
            $peakLossDays = $daily->sortByDesc('totalKg')->take(5)->values();

            return response()->json([
                'data' => [
                    'daily' => $daily,
                    'weekly' => $weekly,
                    'peakLossDays' => $peakLossDays,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving loss trends',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get loss summary for specific tank.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $tankNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function byTank(Request $request, $tankNumber)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            if ($tankNumber < 1 || $tankNumber > 8) {
                return response()->json([
                    'message' => 'Invalid tank number. Must be between 1 and 8.',
                ], 400);
            }

            $records = LossAdjustment::where('tankNumber', $tankNumber)
                ->whereBetween('date', [
                    $validated['start_date'],
                    $validated['end_date']
                ])
                ->get();

            $totalKg = $records->sum('kg');

            $summary = [
                'tankNumber' => $tankNumber,
                'period' => [
                    'start' => $validated['start_date'],
                    'end' => $validated['end_date'],
                ],
                'totalRecords' => $records->count(),
                'totalKg' => round($totalKg, 2),
                'byType' => [
                    'dead' => [
                        'count' => $records->where('type', 'dead')->count(),
                        'kg' => round($records->where('type', 'dead')->sum('kg'), 2),
                    ],
                    'rotten' => [
                        'count' => $records->where('type', 'rotten')->count(),
                        'kg' => round($records->where('type', 'rotten')->sum('kg'), 2),
                    ],
                    'lost' => [
                        'count' => $records->where('type', 'lost')->count(),
                        'kg' => round($records->where('type', 'lost')->sum('kg'), 2),
                    ],
                ],
                'bySize' => [],
                'records' => $records,
            ];

            // By size
            foreach (['U', 'A', 'B', 'C', 'D', 'E'] as $size) {
                $sizeRecords = $records->where('size', $size);
                $summary['bySize'][$size] = [
                    'kg' => round($sizeRecords->sum('kg'), 2),
                    'count' => $sizeRecords->count(),
                ];
            }

            return response()->json([
                'data' => $summary,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving tank loss summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
