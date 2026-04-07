<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        // Wyszukiwanie po tytule i zawartości
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('lead', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
        }

        // Filtrowanie po kategorii
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        // Sortowanie - najnowsze najpierw
        $posts = $query->latest()->get();

        return view('posts.index', [
            'posts' => $posts,
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $comments = $post->comments()->latest()->get();
        $recentPosts = Post::where('id', '!=', $post->id)->latest()->limit(3)->get();

        return view('posts.show', [
            'post' => $post,
            'comments' => $comments,
            'recentPosts' => $recentPosts,
        ]);
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $parameters = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'lead' => ['nullable', 'string'],
            'author' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
        ]);

        $post = new Post;

        $post->title = $parameters['title'];
        $post->slug = $parameters['slug'];
        $post->lead = $parameters['lead'] ?? null;
        $post->author = $parameters['author'];
        $post->content = $parameters['content'];
        $post->category = $parameters['category'] ?? null;

        // Konwersja tagsów ze stringa na array
        if (isset($parameters['tags'])) {
            $post->tags = array_map('trim', explode(',', $parameters['tags']));
        }

        $post->save();

        return redirect()->route('posts.index');
    }
}
