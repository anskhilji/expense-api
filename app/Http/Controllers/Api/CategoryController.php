<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Categories\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categories,
    ) {
    }

    public function index(Request $request)
    {
        return CategoryResource::collection(
            $this->categories->listFor($request->user()->current_org_id)
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categories->create(
            $request->user()->current_org_id,
            $request->string('name')->value(),
            $request->input('icon'),
        );

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $this->authorizeOrgOwnership($request, $category);

        return new CategoryResource(
            $this->categories->update($category, $request->string('name')->value(), $request->input('icon'))
        );
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorizeOrgOwnership($request, $category);

        $this->categories->delete($category);

        return response()->noContent();
    }
}
