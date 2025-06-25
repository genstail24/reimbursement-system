<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::latest()->get();
        return $this->response()->success(CategoryResource::collection($categories), 'Category list retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'            => 'required|string|max:125|unique:categories,name',
                'limit_per_month' => 'required|numeric|min:0',
            ]);

            $category = Category::create($validated);

            return $this->response()->created(new CategoryResource($category), 'Category created successfully.');
        } catch (\Throwable $e) {
            return $this->response()->error($request, $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return $this->response()->success(new CategoryResource($category), 'Category retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        try {
            $validated = $request->validate([
                'name'            => 'required|string|max:125|unique:categories,name,' . $category->id,
                'limit_per_month' => 'required|numeric|min:0',
            ]);

            $category->update($validated);

            return $this->response()->success(new CategoryResource($category), 'Category updated successfully.');
        } catch (\Throwable $e) {
            return $this->response()->error($request, $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return $this->response()->success(new CategoryResource($category), 'Category deleted successfully.');
        } catch (\Throwable $e) {
            return $this->response()->error(request(), $e);
        }
    }
}
