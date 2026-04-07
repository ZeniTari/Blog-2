<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => ['required', 'exists:posts,id'],
            'author' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        Comment::create([
            'post_id' => $validated['post_id'],
            'author' => $validated['author'],
            'content' => $validated['content'],
            'is_approved' => true, // Komentarze są automatycznie zatwierdzane
        ]);

        return back();
    }
}
