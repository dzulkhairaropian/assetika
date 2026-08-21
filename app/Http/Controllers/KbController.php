<?php

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KbController extends Controller
{
    public function index(Request $request)
    {
        $query = KbArticle::with(['category', 'author'])->where('is_published', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $articles = $query->latest()->paginate(12);
        $categories = KbCategory::where('is_active', true)->withCount('articles')->get();

        return view('pages.kb.index', compact('articles', 'categories'));
    }

    public function show(KbArticle $article)
    {
        if (! $article->is_published && ! auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            abort(404);
        }

        $article->increment('views_count');
        $relatedArticles = KbArticle::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->take(5)
            ->get();

        return view('pages.kb.show', compact('article', 'relatedArticles'));
    }

    public function suggestions(Request $request)
    {
        $search = trim((string) $request->get('query', ''));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $suggestions = KbArticle::where('is_published', true)
            ->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            })
            ->take(5)
            ->get(['id', 'title', 'slug', 'category_id']);

        return response()->json($suggestions);
    }
}
