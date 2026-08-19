<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::where('is_active', true)
                ->withCount(['vehicles' => function ($query) {
                    $query->where('status', 'available');
                }])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data'    => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'data'    => null,
            ], 500);
        }
    }

    public function show($slug)
    {
        try {
            $category = Category::where('slug', $slug)
                ->where('is_active', true)
                ->with(['vehicles' => function ($query) {
                    $query->where('status', 'available');
                }])
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data'    => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'data'    => null,
            ], 500);
        }
    }
}
