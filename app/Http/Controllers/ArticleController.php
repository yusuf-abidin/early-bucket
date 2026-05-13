<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArticleController extends Controller
{

    public function index()
    {
        $articles = Article::where('status', 'published')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('rlqh/articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function authorIndex(Request $request)
    {
        $articles = Article::query()
            ->when($request->input('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('admin/rlqh/articles/Index', [
            'articles' => $articles,
            'status' => $request->input('status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/rlqh/articles/ArticleForm', [
            'article' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('articles', 'public');
        }

        $publishedAt = null;
        if ($validated['status'] === 'published') {
            $publishedAt = now();
        }

        Article::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('rlqh.news.authorIndex')->with('success', 'News berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        if (auth()->user()->role !== 'admin' && $article->status !== 'published') {
            abort(403);
        }

        return Inertia::render('rlqh/articles/Show', [
            'article' => $article,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        return Inertia::render('admin/rlqh/articles/ArticleForm', [
            'article' => $article,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $validated = $request->validated();

        $imagePath = $article->image;

        if ($request->hasFile('image')) {
            if ($article->image) {
                \Storage::disk('public')->delete($article->image);
            }
            $imagePath = $request->file('image')->store('articles', 'public');
        }

        if ($request->boolean('image_removed') && !$request->hasFile('image')) {
            if ($article->image) {
                \Storage::disk('public')->delete($article->image);
            }
            $imagePath = null;
        }

        $publishedAt = $article->published_at;
        if ($validated['status'] === 'published' && is_null($article->published_at)) {
            $publishedAt = now();
        } elseif ($validated['status'] === 'draft') {
            $publishedAt = null;
        }

        $article->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('rlqh.news.authorIndex')->with('success', 'News berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        if ($article->image) {
            \Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return redirect()->route('rlqh.news.authorIndex')->with('success', 'News berhasil dihapus.');
    }
}
