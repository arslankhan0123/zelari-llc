<?php
namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()->latest('published_at')->paginate(9);
        return view('shop.blog.index', compact('posts'));
    }
    public function show(string $slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();
        $recent = BlogPost::published()->where('id', '!=', $post->id)->latest('published_at')->take(3)->get();
        return view('shop.blog.show', compact('post', 'recent'));
    }
}
