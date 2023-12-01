<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Vcard;

class CategoryControlle extends Controller
{
    public function getCategoriesOfVcard(Vcard $vcard)
    {
        return CategoryResource::collection(Category::where('vcard', $vcard->phone_number)->get());
    }

    public function view(Category $category)
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        return new CategoryResource($category);
    }
}
