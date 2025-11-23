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

    public function stockBySize()
    {
        $sizes = ['U', 'A', 'B', 'C', 'D', 'E'];
        $result = [
            'totalKg' => 0,
            'sizeU' => 0,
            'sizeA' => 0,
            'sizeB' => 0,
            'sizeC' => 0,
            'sizeD' => 0,
            'sizeE' => 0,
        ];

        foreach ($sizes as $size) {
            $crateKg = \App\Models\Crate::where('size', $size)->sum('kg');
            $looseKg = \App\Models\LooseStock::where('size', $size)->sum('kg');
            $totalKg = $crateKg + $looseKg;
            $result['size' . $size] = $totalKg;
            $result['totalKg'] += $totalKg;
        }

        return response()->json(['data' => $result]);
    }

    public function stockByBoat()
    {
        $batches = \App\Models\ReceivingBatch::all();
        $data = [];

        foreach ($batches as $batch) {
            $crates = \App\Models\Crate::where('receiving_batch_id', $batch->id)->get();

            $sizes = ['U', 'A', 'B', 'C', 'D', 'E'];
            $remaining = [];
            $totalKg = 0;

            foreach ($sizes as $size) {
                $kg = $crates->where('size', $size)->sum('kg');
                $remaining['size' . $size] = $kg;
                $totalKg += $kg;
            }
            $remaining['totalKg'] = $totalKg;
            $boatName = $crates->first() ? $crates->first()->boatName : null;
            $data[] = [
                'boatName' =>  $boatName,
                'offloadDate' => $batch->date,
                'tripNumber' => $batch->batchNumber,
                'totalLive' => $crates->where('status', 'received')->sum('kg'),
                'totalStored' => $crates->where('status', 'stored')->sum('kg'),
                'totalEmptied' => $crates->where('status', 'emptied')->sum('kg'),
                'totalDispatched' => $crates->where('status', 'dispatched')->sum('kg'),
                'remaining' => $remaining,
            ];
        }

        return response()->json(['data' => $data]);
    }
}
