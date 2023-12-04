<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Vcard;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    public function getCetegoriesOfVcard(Vcard $vcard)
    {
        return CategoryResource::collection(Category::where('vcard', $vcard->phone_number)->get());
    }
    
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    public function store(UpdateCategoryRequest $request)
    {
        try {
            $user = Auth::user();
            $category = new Category([
                'name' => $request->name,
                'type' => $request->type,
                'vcard' => $user->id,
            ]);
            $category->save();
            return response()->json(['message' => 'Category created successfully', 'data' => new CategoryResource($category)], 201);
        } catch (\Exception $ex) {
            if ($ex->getCode() == 23000)
                return response()->json(['message' => 'Category already exists'], 500);
            else
                return response()->json(['message' => 'Error creating category'], 500);
        }
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $category->update($request->validated());
            return new CategoryResource($category);
        } catch (\Exception $ex) {
            if ($ex->getCode() == 23000)
                return response()->json(['message' => 'Category already exists'], 500);
            else
                return response()->json(['message' => 'Error updating category'], 500);
        }
    }

    public function destroy(Category $category)
    {
        try {
            if ($category->transactions()->count() > 0)
                $category->delete();
            else
                $category->forceDelete();
            // return response()->json(['message' => 'Category deleted successfully'], 200);
            return new CategoryResource($category);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error deleting category'], 500);
        }
    }
}
