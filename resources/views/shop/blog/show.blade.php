@extends('layouts.store')
@section('title', $post->meta_title ?: $post->title)
@push('styles')<style>
.article{max-width:780px;margin:0 auto;padding:34px 0 60px}
.article h1{font-size:clamp(26px,4vw,38px);font-weight:800;letter-spacing:-1px;margin:0 0 10px}
.article .meta{color:var(--faint);font-size:13px;margin-bottom:20px}
.article img.cover{width:100%;border-radius:var(--r);margin-bottom:24px;max-height:420px;object-fit:cover}
.article .body{font-size:16px;line-height:1.75;color:var(--ink-2);white-space:pre-wrap}
</style>@endpush
@section('content')
<section class="article wrap">
    <div style="font-size:13px;color:var(--faint);margin-bottom:14px"><a href="{{ route('shop.blog') }}">← Back to blog</a></div>
    <h1>{{ $post->title }}</h1>
    <div class="meta">{{ optional($post->published_at)->format('F d, Y') }}</div>
    @if($post->cover)<img class="cover" src="{{ asset('storage/'.$post->cover) }}" alt="{{ $post->title }}">@endif
    <div class="body">{!! nl2br(e($post->body)) !!}</div>
</section>
@endsection
