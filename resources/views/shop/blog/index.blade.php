@extends('layouts.store')
@section('title','Blog')
@push('styles')<style>
.blog-wrap{padding:34px 0 50px}
.blog-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
@media(max-width:900px){.blog-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.blog-grid{grid-template-columns:1fr}}
.bpost{background:#fff;border:1px solid var(--line);border-radius:var(--r);overflow:hidden;transition:transform .3s,box-shadow .3s}
.bpost:hover{transform:translateY(-5px);box-shadow:0 26px 48px -28px rgba(var(--shadow-rgb),.5)}
.bpost img{width:100%;height:180px;object-fit:cover;background:var(--surface)}
.bpost .bp{padding:18px}
.bpost h3{font-size:16px;font-weight:800;margin:0 0 6px}
.bpost p{font-size:13.5px;color:var(--muted);margin:0}
</style>@endpush
@section('content')
@include('shop.partials.page-hero', ['id'=>'blogHero','crumbs'=>[['Home',route('home')],['Blog',null]],'slides'=>[['title'=>'Our <span>Blog</span>','text'=>'News, guides and stories from Zelari.','icon'=>'fa-newspaper']]])
<section class="blog-wrap wrap">
    @if($posts->count())
    <div class="blog-grid">
        @foreach($posts as $post)
        <a href="{{ route('shop.blog.show',$post->slug) }}" class="bpost reveal">
            <img src="{{ $post->cover ? asset('storage/'.$post->cover) : zbg('hero1') }}" alt="{{ $post->title }}">
            <div class="bp">
                <h3>{{ $post->title }}</h3>
                <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->body), 110) }}</p>
                <p style="margin-top:8px;font-size:12px;color:var(--faint)">{{ optional($post->published_at)->format('M d, Y') }}</p>
            </div>
        </a>
        @endforeach
    </div>
    <div class="pager" style="margin-top:28px">{{ $posts->links('vendor.pagination.store') }}</div>
    @else
    <div style="text-align:center;padding:60px 20px;color:var(--muted)"><h3>No posts yet</h3><p>Check back soon.</p></div>
    @endif
</section>
@endsection
