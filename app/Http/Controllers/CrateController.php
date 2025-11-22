<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Crate;
use Illuminate\Support\Facades\Auth;
class CrateController extends Controller
{
     public function index(Request $request)
    {
        $query = Crate::with(['receivingBatch', 'user']);
        
        // Filter by status
        if ($request->has('status')) {
            $statuses = explode(',', $request->input('status'));
            $query->whereIn('status', $statuses);
        }
        
        $perPage = $request->input('per_page', 10);
        $crates = $query->paginate($perPage);
        
        return response()->json([
            'data' => $crates->items(),
            'meta' => [
                'total' => $crates->total(),
                'current_page' => $crates->currentPage(),
                'per_page' => $crates->perPage(),
                'last_page' => $crates->lastPage(),
            ]
        ]);
    }
    
     public function update(Request $request, $id)
    {

      
        $crate = Crate::findOrFail($id);
        
        $validated = $request->validate([
            'kg' => 'sometimes|numeric|min:0',
            'size' => 'sometimes|in:U,A,B,C,D,E',
            'status' => 'sometimes|in:received,rechecked,stored,emptied,dispatched',
            'tankId' => 'sometimes|exists:tanks,id',
        ]);
        
        $crate->update($validated);
        
        return response()->json($crate->fresh());
    }
}
