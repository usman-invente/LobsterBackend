<?php

namespace App\Http\Controllers;

use App\Models\Tank;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function stockByTanks()
    {
        $tanks = Tank::with(['crates', 'looseStock'])->get();

        $data = $tanks->map(function ($tank) {
            $totalKg = $tank->crates->sum('kg') + $tank->looseStock->sum('kg');
            return [
                'id' => $tank->id,
                'number' => $tank->tankNumber,
                'name' => $tank->tankName,
                'totalKg' => $totalKg,
                'crates' => $tank->crates->count(),
                'loose_stock' => $tank->looseStock->count(),
            ];
        });

        return response()->json(['data' => $data]);
    }
}
