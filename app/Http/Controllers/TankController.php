<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tank;

class TankController extends Controller
{
    public function index(Request $request)
    {
        $tanks = \App\Models\Tank::where('status', 1)
            ->withCount('crates')
            ->with([
                'crates', // assumes Tank hasMany Crate
                'looseStock' // assumes Tank hasMany LooseStock
            ])
            ->orderBy('tankName')
            ->get();

        // Map/append calculated fields for each tank
        $tanks = $tanks->map(function ($tank) {
            // Total weight from crates and loose stock
            $totalWeight = 0;
            $sizeWeights = [
                'U' => 0,
                'A' => 0,
                'B' => 0,
                'C' => 0,
                'D' => 0,
                'E' => 0
            ];

            // Crates
            foreach ($tank->crates as $crate) {
                $totalWeight += $crate->kg;
                if (isset($sizeWeights[$crate->size])) {
                    $sizeWeights[$crate->size] += $crate->kg;
                }
            }

            // Loose Stock
            $looseCount = 0;
            foreach ($tank->looseStock as $loose) {
                $totalWeight += $loose->kg;
                $looseCount++;
                if (isset($sizeWeights[$loose->size])) {
                    $sizeWeights[$loose->size] += $loose->kg;
                }
            }

            // Attach calculated fields
            $tank->totalWeight = $totalWeight;
            $tank->loose_count = $looseCount;
            foreach ($sizeWeights as $size => $weight) {
                $tank->{"size{$size}_kg"} = number_format($weight, 2, '.', '');
            }

            return $tank;
        });

        return response()->json([
            'data' => $tanks
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
