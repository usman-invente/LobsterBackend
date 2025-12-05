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
   public function stats(Request $request)
{
    $productId = $request->input('product');

    // Sum of all crate kg (global)
    $cratesKg = \App\Models\Crate::sum('kg');
    // Sum of all loose stock kg (global)
    $looseStockKg = \App\Models\LooseStock::sum('kg');
    // Sum of all losses kg (global)
    $lossesKg = LossAdjustment::sum('kg');

    // Total stock = crates + loose - losses (global)
    $totalStock = $cratesKg + $looseStockKg - $lossesKg;

    // Size breakdown (filtered by product if provided)
   
    $crateSizeQuery = \App\Models\Crate::query();
    if ($productId) {
        $crateSizeQuery->where('productId', $productId);
    }
   $sizeBreakdown = $crateSizeQuery
    ->selectRaw('size, sum(kg) as total_kg')
    ->groupBy('size')
    ->pluck('total_kg', 'size')
    ->toArray();

    return response()->json([
        'total_stock_kg' => $totalStock,
        'total_crates' => \App\Models\Crate::count(),
        'total_tanks' => \App\Models\Tank::count(),
        'total_batches' => \App\Models\ReceivingBatch::count(),
        'total_offloads' => \App\Models\OffloadRecord::count(),
        'total_loss_stock' => LossAdjustment::sum('kg'),
        'crates_stored' => \App\Models\Crate::where('status', 'stored')->count(),
        'crates_received' => \App\Models\Crate::where('status', 'received')->count(),
        'crates_rechecked' => \App\Models\Crate::where('status', 'rechecked')->count(),
        'recent_offloads' => \App\Models\OffloadRecord::orderBy('offloadDate', 'desc')->take(5)->get(),
        'recent_dispatches' => \App\Models\Dispatch::orderBy('dispatchDate', 'desc')->take(5)
            ->get(['id', 'clientAwb', 'type', 'totalKg', 'dispatchDate']),
        'tank_summary' => \App\Models\Tank::where('status', 1)
            ->withCount('crates')
            ->orderBy('tankName')
            ->get()
            ->map(function ($tank) {
                $totalKg = \App\Models\Crate::where('tankId', $tank->id)->sum('kg');
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
        'size_breakdown' => $sizeBreakdown,
        // Add more stats as needed
    ]);
}
}
