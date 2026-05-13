<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $collections = Collection::with(['category', 'user'])
            ->where('status', 'published')
            ->latest()
            ->paginate(10);

        return view('collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();

        return view('collections.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'file' => 'required|file|mimes:csv,txt,json|max:10240',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'draft';
        $validated['file_path'] = $request->file('file')->store('collections');

        Collection::create($validated);

        return redirect()->route('collections.index')
            ->with('success', 'Collection created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Collection $collection)
    {
        $userId = Auth::id();

        $hasAccess = $collection->hasAccess($userId) || $collection->price == 0;

        return view('collections.show', compact('collection', 'hasAccess'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Collection $collection)
    {
        abort_if($collection->user_id !== Auth::id(), 403);

        $categories = Category::all();

        return view('collections.edit', compact('collection', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collection $collection)
    {
        abort_if($collection->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'file' => 'nullable|file|mimes:csv,txt,json|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($collection->file_path && Storage::exists($collection->file_path)) {
                Storage::delete($collection->file_path);
            }

            $validated['file_path'] = $request->file('file')->store('collections');
        }

        $collection->update($validated);

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Collection updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collection $collection)
    {
        abort_if($collection->user_id !== Auth::id(), 403);

        if ($collection->file_path && Storage::exists($collection->file_path)) {
            Storage::delete($collection->file_path);
        }

        $collection->delete();

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted successfully.');
    }

    /**
     * Download collection file.
     */
    public function download(Collection $collection)
    {
        $userId = Auth::id();

        if ($collection->price > 0 && !$collection->hasAccess($userId)) {
            abort(403);
        }

        if (!$collection->file_path || !Storage::exists($collection->file_path)) {
            abort(404);
        }

        return Storage::download($collection->file_path);
    }
}
