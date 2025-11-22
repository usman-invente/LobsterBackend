<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Crate;
use App\Models\Tank;
use App\Models\ReceivingBatch;
use App\Models\OffloadRecord;
use App\Models\LooseStock;
use App\Models\Dispatch;
use App\Models\LossAdjustment;

class DashboardController extends Controller
{
    public function stats()
    {
        $query = Tank::query();
        // Sum of all crate kg
        $cratesKg = \App\Models\Crate::sum('kg');
        // Sum of all loose stock kg
        $looseStockKg = \App\Models\LooseStock::sum('kg');
        // Sum of all losses kg
        $lossesKg = LossAdjustment::sum('kg'); // Make sure you have a Loss model/table

        // Total stock = crates + loose - losses
        $totalStock = $cratesKg + $looseStockKg - $lossesKg;
        return response()->json([
            'total_stock_kg' => $totalStock,
            'total_crates' => Crate::count(),
            'total_tanks' => Tank::count(),
            'total_batches' => ReceivingBatch::count(),
            'total_offloads' => OffloadRecord::count(),
            'total_loss_stock' => LossAdjustment::sum('kg'),
            'crates_stored' => Crate::where('status', 'stored')->count(),
            'crates_received' => Crate::where('status', 'received')->count(),
            'crates_rechecked' => Crate::where('status', 'rechecked')->count(),
            'recent_offloads' => OffloadRecord::orderBy('offloadDate', 'desc')->take(5)->get(),
            'recent_dispatches' => Dispatch::orderBy('dispatchDate', 'desc')->take(5)
                ->get(['id', 'clientAwb', 'type', 'totalKg', 'dispatchDate']),
            'tank_summary' => Tank::where('status', 1)
                ->withCount('crates')
                ->orderBy('tankName')
                ->get()
                ->map(function ($tank) {
                    // Sum of kg for all crates in this tank
                    $totalKg = \App\Models\Crate::where('tankId', $tank->id)->sum('kg');
                    // Sum of loose stock for this tank (optional)
                    $looseStock = \App\Models\LooseStock::where('tankId', $tank->id)->sum('kg');
                    return [
                        'id' => $tank->id,
                        'tankName' => $tank->tankName,
                        'tankNumber' => $tank->tankNumber,
                        'crates_count' => $tank->crates_count,
                        'totalKg' => $totalKg,
                        'looseStock' => $looseStock,
                    ];
                }),

            // Add more stats as needed
        ]);
    }
}
