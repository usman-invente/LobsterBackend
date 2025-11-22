<?php

namespace App\Http\Controllers;
use App\Models\LooseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LooseStockController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tankId' => 'required|exists:tanks,id',
            'size' => 'required|in:U,A,B,C,D,E',
            'kg' => 'required|numeric|min:0',
            'fromCrateId' => 'sometimes|exists:crates,id',
            'boatName' => 'sometimes|string',
            'offloadDate' => 'sometimes|date',
        ]);
        
        $looseStock = LooseStock::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);
        
        return response()->json($looseStock, 201);
    }
}
