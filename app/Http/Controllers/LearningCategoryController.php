<?php

namespace App\Http\Controllers;

use App\Http\Requests\LearningCategories\StoreLearningCategoryRequest;
use App\Http\Requests\LearningCategories\UpdateLearningCategoryRequest;
use App\Models\LearningCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LearningCategoryController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:learning category view', only: ['index', 'show']),
            new Middleware('permission:learning category create', only: ['create', 'store']),
            new Middleware('permission:learning category edit', only: ['edit', 'update']),
            new Middleware('permission:learning category delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(LearningCategory::query())
                ->addColumn('action', fn (LearningCategory $learningCategory) => view('learning-categories.include.action', ['model' => $learningCategory])->render())
                ->removeColumn('created_at')
                ->removeColumn('updated_at')
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('learning-categories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return to_route('learning-categories.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLearningCategoryRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request): RedirectResponse {
            LearningCategory::create($request->validated());

            return to_route('learning-categories.index')->with('success', __('The learning category was created successfully.'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, LearningCategory $learningCategory): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'name' => $learningCategory->name,
                'created_at' => $learningCategory->created_at?->format('Y-m-d H:i'),
                'updated_at' => $learningCategory->updated_at?->format('Y-m-d H:i'),
                'edit_url' => null,
            ]);
        }

        return to_route('learning-categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LearningCategory $learningCategory): RedirectResponse
    {
        unset($learningCategory);

        return to_route('learning-categories.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLearningCategoryRequest $request, LearningCategory $learningCategory): RedirectResponse
    {
        return DB::transaction(function () use ($request, $learningCategory): RedirectResponse {
            $learningCategory->update($request->validated());

            return to_route('learning-categories.index')->with('success', __('The learning category was updated successfully.'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningCategory $learningCategory): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($learningCategory): RedirectResponse {
                $learningCategory->delete();

                return to_route('learning-categories.index')->with('success', __('The learning category was deleted successfully.'));
            });
        } catch (\Exception $e) {
            return to_route('learning-categories.index')->with('error', __("The learning category can't be deleted because it's related to another table."));
        }
    }
}
