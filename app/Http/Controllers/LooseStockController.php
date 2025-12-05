<?php

namespace App\Http\Controllers;
use App\Models\LooseStock;
use App\Models\Crate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LooseStockController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tankId' => 'required|exists:tanks,id',
            'size' => 'required',
            'kg' => 'required|numeric|min:0',
            'fromCrateId' => 'sometimes|exists:crates,id',
            'boatName' => 'sometimes|string',
            'offloadDate' => 'sometimes|date',
            'productId' => 'required|exists:products,id',
        ]);
        
        return DB::transaction(function () use ($validated) {
            $data = $validated;
            $data['status'] = 'stored';
            
            if (!empty($data['fromCrateId'])) {
                $crate = Crate::findOrFail($data['fromCrateId']);
                
                if ($crate->size !== $data['size']) {
                    abort(422, 'Size mismatch between crate and loose stock');
                }
                
                if ($crate->kg < $data['kg']) {
                    abort(422, 'Insufficient kg in crate');
                }
                
                $crate->kg -= $data['kg'];
                if ($crate->kg <= 0) {
                    $crate->kg = 0;
                    $crate->status = 'emptied';
                    $crate->originalKg = 0;
                } else {
                    $crate->status = 'stored';
                }
                $crate->save();
                
                
                $data['boatName'] = $data['boatName'] ?? $crate->boatName;
                $data['offloadDate'] = $data['offloadDate'] ?? $crate->offloadDate;
            }
            
            $data['user_id'] = Auth::id();
            $looseStock = LooseStock::create($data);
            
            return response()->json($looseStock, 201);
        });
    }
}
