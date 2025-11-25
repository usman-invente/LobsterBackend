<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tank;
use Illuminate\Validation\Rule;
use App\Models\Crate;
use App\Models\LooseStock;
class TankController extends Controller
{
    public function index(Request $request)
    {
        $query = Tank::query();

        // Handle search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tankNumber', 'LIKE', "%{$search}%")
                    ->orWhere('tankName', 'LIKE', "%{$search}%");
            });
        }

        // If ?page parameter is present, show all tanks; otherwise, only status 1
        if (!$request->has('page')) {
            $query->where('status', 1);
        }

        // Handle sorting
        if ($request->has('sort_by') && !empty($request->sort_by)) {
            $sortBy = $request->sort_by;
            $sortDirection = $request->sort_direction ?? 'asc';

            // Map frontend column names to database columns
            $columnMapping = [
                'number' => 'tankNumber',
                'name' => 'tankName',
                'status' => 'status'
            ];

            if (isset($columnMapping[$sortBy])) {
                $query->orderBy($columnMapping[$sortBy], $sortDirection);
            } else {
                $query->orderBy('tankName'); // Default sorting
            }
        } else {
            $query->orderBy('tankName'); // Default sorting
        }

        // Handle pagination
        if ($request->has('per_page')) {
            $perPage = $request->per_page;
            $tanks = $query
                ->withCount('crates')
                ->with(['crates', 'looseStock'])
                ->paginate($perPage);
        } else {
            // If no pagination requested, get all results
            $tanks = $query
                ->withCount('crates')
                ->with(['crates', 'looseStock'])
                ->get();
        }

        // Map/append calculated fields for each tank
        $tanksData = $tanks->map(function ($tank) {
            $totalWeight = 0;
            $sizeWeights = ['U' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];

            foreach ($tank->crates as $crate) {
                $totalWeight += $crate->kg;
                if (isset($sizeWeights[$crate->size])) {
                    $sizeWeights[$crate->size] += $crate->kg;
                }
            }

            $looseCount = 0;
            foreach ($tank->looseStock as $loose) {
                $totalWeight += $loose->kg;
                $looseCount++;
                if (isset($sizeWeights[$loose->size])) {
                    $sizeWeights[$loose->size] += $loose->kg;
                }
            }

            $tank->totalWeight = $totalWeight;
            $tank->loose_count = $looseCount;
            foreach ($sizeWeights as $size => $weight) {
                $tank->{"size{$size}_kg"} = number_format($weight, 2, '.', '');
            }

            return $tank;
        });

        // Return paginated response if pagination was used
        if ($request->has('per_page')) {
            return response()->json([
                'data' => $tanksData,
                'meta' => [
                    'total' => $tanks->total(),
                    'per_page' => $tanks->perPage(),
                    'current_page' => $tanks->currentPage(),
                    'last_page' => $tanks->lastPage(),
                    'from' => $tanks->firstItem(),
                    'to' => $tanks->lastItem()
                ]
            ]);
        }

        // Return regular response for non-paginated requests
        return response()->json([
            'data' => $tanksData
        ]);
    }

    /**
     * Store a newly created tank.
     */
    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|integer|min:1|unique:tanks,tankNumber',
            'name' => 'required|string|max:255|unique:tanks,tankName',
        ]);

        $tank = Tank::create([
            'tankNumber' => $request->number,
            'tankName' => $request->name,
            'status' => 1, // Active by default
        ]);

        return response()->json([
            'message' => 'Tank created successfully',
            'data' => $tank
        ], 201);
    }

    /**
     * Display the specified tank.
     */
    public function show(Tank $tank)
    {
        return response()->json(['data' => $tank]);
    }

    /**
     * Update the specified tank.
     */
    public function update(Request $request, Tank $tank)
    {
        $request->validate([
            'number' => ['required', 'integer', 'min:1', Rule::unique('tanks', 'tankNumber')->ignore($tank->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('tanks', 'tankName')->ignore($tank->id)],
            'active' => 'boolean',
        ]);

        $tank->update([
            'tankNumber' => $request->number,
            'tankName' => $request->name,
            'status' => $request->active ? 1 : 0,
        ]);

        return response()->json([
            'message' => 'Tank updated successfully',
            'data' => $tank
        ]);
    }

    /**
     * Remove the specified tank.
     */
    public function destroy(Tank $tank)
    {
        // Check if tank has any associated records before deleting
        // You might want to add this validation based on your business logic

        $tank->delete();

        return response()->json([
            'message' => 'Tank deleted successfully'
        ]);
    }

    /**
     * Toggle tank active status.
     */
    public function toggleStatus(Tank $tank)
    {
        $tank->update(['status' => !$tank->active]);

        return response()->json([
            'message' => 'Tank status updated successfully',
            'data' => $tank
        ]);
    }

     public function getTankStock()
    {
        $tanks = Tank::where('status', 1) // Only active tanks
            ->orderBy('tankName')
            ->get()
            ->map(function ($tank) {
                // Get crates stored in this tank
                $crates = Crate::where('tankId', $tank->id)
                    ->where('status', 'stored')
                    ->get()
                    ->map(function ($crate) {
                        return [
                            'id' => $crate->id,
                            'crateNumber' => $crate->crateNumber,
                            'size' => $crate->size,
                            'kg' => $crate->kg,
                        ];
                    });

                // Get loose stock in this tank
                $looseStock = LooseStock::where('tankId', $tank->id)
                    ->where('status', 'stored')
                    ->get()
                    ->map(function ($stock) {
                        return [
                            'id' => $stock->id,
                            'size' => $stock->size,
                            'kg' => $stock->kg,
                        ];
                    });

                // Calculate total kg for summary
                $totalKg = $crates->sum('kg') + $looseStock->sum('kg');

                return [
                    'tankId' => $tank->id,
                    'tankName' => $tank->tankName,
                    'tankNumber' => $tank->tankNumber,
                    'summary' => [
                        'totalKg' => $totalKg,
                    ],
                    'crates' => $crates,
                    'looseStock' => $looseStock,
                ];
            });

        return response()->json([
            'data' => $tanks,
        ]);
    }

    public function crates($tankId)
    {
        $tank = Tank::with('crates')->findOrFail($tankId);

        // Optionally, you can transform the crates if needed
        return response()->json([
            'data' => $tank->crates
        ]);
    }
}
