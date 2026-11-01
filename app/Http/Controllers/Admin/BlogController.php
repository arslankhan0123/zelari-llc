<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::latest()->paginate(15);
        return view('admin.blog.index', compact('posts'));
    }
    public function create() { return view('admin.blog.form', ['post' => new BlogPost()]); }
    public function edit(BlogPost $post) { return view('admin.blog.form', compact('post')); }

    public function store(Request $request) { return $this->save($request, new BlogPost()); }
    public function update(Request $request, BlogPost $post) { return $this->save($request, $post); }

    private function save(Request $request, BlogPost $post)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:200',
            'excerpt' => 'nullable|string|max:500',
            'body'    => 'nullable|string',
            'status'  => 'required|in:draft,published',
            'meta_title'       => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:300',
            'cover'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        if ($request->hasFile('cover')) {
            if ($post->cover) Storage::disk('public')->delete($post->cover);
            $data['cover'] = $request->file('cover')->store('blog', 'public');
        }
        if ($data['status'] === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }
        $post->fill($data)->save();
        return redirect()->route('admin.blog.index')->with('success', 'Post saved.');
    }

    public function destroy(BlogPost $post)
    {
        if ($post->cover) Storage::disk('public')->delete($post->cover);
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }
}
