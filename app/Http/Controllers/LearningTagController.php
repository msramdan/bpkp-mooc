<?php

namespace App\Http\Controllers;

use App\Http\Requests\LearningTags\StoreLearningTagRequest;
use App\Http\Requests\LearningTags\UpdateLearningTagRequest;
use App\Models\LearningTag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LearningTagController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:learning tag view', only: ['index', 'show']),
            new Middleware('permission:learning tag create', only: ['create', 'store']),
            new Middleware('permission:learning tag edit', only: ['edit', 'update']),
            new Middleware('permission:learning tag delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(LearningTag::query())
                ->addColumn('action', fn (LearningTag $learningTag) => view('learning-tags.include.action', ['model' => $learningTag])->render())
                ->removeColumn('created_at')
                ->removeColumn('updated_at')
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('learning-tags.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return to_route('learning-tags.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLearningTagRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request): RedirectResponse {
            LearningTag::create($request->validated());

            return to_route('learning-tags.index')->with('success', __('The learning tag was created successfully.'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, LearningTag $learningTag): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'name' => $learningTag->name,
                'created_at' => $learningTag->created_at?->format('Y-m-d H:i'),
                'updated_at' => $learningTag->updated_at?->format('Y-m-d H:i'),
                'edit_url' => null,
            ]);
        }

        return to_route('learning-tags.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LearningTag $learningTag): RedirectResponse
    {
        unset($learningTag);

        return to_route('learning-tags.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLearningTagRequest $request, LearningTag $learningTag): RedirectResponse
    {
        return DB::transaction(function () use ($request, $learningTag): RedirectResponse {
            $learningTag->update($request->validated());

            return to_route('learning-tags.index')->with('success', __('The learning tag was updated successfully.'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningTag $learningTag): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($learningTag): RedirectResponse {
                $learningTag->delete();

                return to_route('learning-tags.index')->with('success', __('The learning tag was deleted successfully.'));
            });
        } catch (\Exception $e) {
            return to_route('learning-tags.index')->with('error', __("The learning tag can't be deleted because it's related to another table."));
        }
    }
}
