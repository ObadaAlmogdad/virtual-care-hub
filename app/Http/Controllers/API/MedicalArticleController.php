<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MedicalArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MedicalArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalArticle::query()
            ->with(['doctor.user'])
            ->when(!$request->user() || !$request->user()->isDoctor(), function ($q) {
                $q->where('is_published', true);
            })
            ->latest();

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->integer('doctor_id'));
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(15));
    }

    public function show($id)
    {
        $article = MedicalArticle::with(['doctor.user'])->findOrFail($id);

        if ($article->is_published) {
            $article->increment('views_count');
        }

        return response()->json($article);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isDoctor()) {
            return response()->json(['message' => 'Only doctors can create articles'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:4096',
            'publish' => 'nullable|boolean',
        ]);

        $slug = Str::slug($validated['title']) . '-' . Str::random(6);

        $data = [
            'doctor_id' => $user->doctor->id,
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'is_published' => (bool)($validated['publish'] ?? false),
            'published_at' => ($validated['publish'] ?? false) ? now() : null,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('medical_articles', 'public');
            $data['image_url'] = $path;
        }

        $article = MedicalArticle::create($data);

        return response()->json($article->load(['doctor.user']), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $article = MedicalArticle::findOrFail($id);
        if (!$user || !$user->isDoctor() || $article->doctor_id !== optional($user->doctor)->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'sometimes|nullable|image|max:4096',
        ]);

        if (isset($validated['title'])) {
            $article->title = $validated['title'];
        }
        if (isset($validated['content'])) {
            $article->content = $validated['content'];
        }
        if ($request->hasFile('image')) {
            if ($article->image_url) {
                $oldPath = str_replace('/storage/', '', $article->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('medical_articles', 'public');
            $article->image_url = $path;
        }

        // regenerate slug if title changed
        if ($article->isDirty('title')) {
            $article->slug = Str::slug($article->title) . '-' . Str::random(6);
        }

        $article->save();

        return response()->json($article->fresh()->load(['doctor.user']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $article = MedicalArticle::findOrFail($id);
        if (!$user || !$user->isDoctor() || $article->doctor_id !== optional($user->doctor)->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        if ($article->image_url) {
            $oldPath = str_replace('/storage/', '', $article->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $article->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function togglePublish(Request $request, $id)
    {
        $user = $request->user();
        $article = MedicalArticle::findOrFail($id);
        if (!$user || !$user->isDoctor() || $article->doctor_id !== optional($user->doctor)->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $article->is_published = !$article->is_published;
        $article->published_at = $article->is_published ? now() : null;
        $article->save();

        return response()->json($article);
    }
}


