<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    /**
     * Display the form to create a new post
     * 
     * @return View
     */
    public function create(): View
    {
        // Render the create post form view
        // postsCreate.blade.php should contain the HTML form
        return view('postsCreate');
    }

    /**
     * Store a newly created post in the database
     * 
     * @param Request $request Incoming form data
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ Laravel 12 validation rules
        // title: required, string, max 255 characters
        // body: required (no max length - handles rich HTML content)
        $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required'
        ]);

        // ✅ Create new post using mass assignment
        // Model's $fillable and body mutator will automatically:
        // 1. Validate fillable fields
        // 2. Process base64 images in body content
        Post::create([
            'title' => $request->title,
            'body'  => $request->body
        ]);

        // Redirect back to form with success message
        // Flash message will be displayed in the view
        return back()->with('success', 'Post created successfully.');
    }
}
