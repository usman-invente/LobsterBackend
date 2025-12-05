<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('sizes');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Ensure sort_by is a valid column
        $validSortColumns = ['id', 'name', 'created_at'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'name';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 10);

        if ($perPage === 'all') {
            $products = $query->get();
            return response()->json(['data' => $products]);
        } else {
            $products = $query->paginate($perPage);
            return response()->json($products);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name',
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);

        foreach ($request->sizes as $size) {
            $product->sizes()->create(['size' => $size]);
        }

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product->load('sizes')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update([
            'name' => $request->name,
        ]);

        // Sync sizes
        $sizes = $request->sizes;
        // Remove all old sizes and add new ones
        $product->sizes()->delete();
        foreach ($sizes as $size) {
            $product->sizes()->create(['size' => $size]);
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product->load('sizes')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
