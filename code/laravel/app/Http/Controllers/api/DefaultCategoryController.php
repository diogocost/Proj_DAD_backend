<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;
use App\Http\Resources\DefaultCategoryResource;
use App\Models\DefaultCategory;

class DefaultCategoryController extends Controller
{
    public function index()
    {
        return DefaultCategoryResource::collection(DefaultCategory::all());
    }

    public function show(DefaultCategory $defaultCategory)
    {
        return new DefaultCategoryResource($defaultCategory);
    }

    public function store(CreateCategoryRequest $request)
    {
        try {
            $defaultCategory = new DefaultCategory([
                'name' => $request->name,
                'type' => $request->type,
            ]);
            $defaultCategory->save();
            return new DefaultCategoryResource($defaultCategory);
        } catch (\Exception $ex) {
            if ($ex->getCode() == 23000)
                return response()->json(['message' => 'Category already exists'], 500);
            else
                return response()->json(['message' => 'Error creating category'], 500);
        }
    }

    public function update(UpdateCategoryRequest $request, DefaultCategory $defaultCategory)
    {
        try {
            $defaultCategory->update($request->all());
            return new DefaultCategoryResource($defaultCategory);
        } catch (\Exception $ex) {
            if ($ex->getCode() == 23000)
                return response()->json(['message' => 'Category already exists'], 500);
            else
                return response()->json(['message' => 'Error updating category'], 500);
        }
    }

    public function destroy(DefaultCategory $defaultCategory)
    {
        $defaultCategory->delete();
        return new DefaultCategoryResource($defaultCategory);;
    }
}
