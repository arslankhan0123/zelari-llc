@extends('layouts.store')
@section('title', 'Unsubscribe')
@section('meta_robots', 'noindex, nofollow')
@section('content')
<section class="sec wrap" style="min-height:40vh;display:grid;place-items:center;text-align:center;padding:60px 0">
  <div>
    <i class="fa-solid {{ $ok ? 'fa-circle-check' : 'fa-circle-info' }}" style="font-size:48px;color:{{ $ok ? '#16a34a' : '#f59e0b' }}"></i>
    <h1 style="font-size:24px;font-weight:800;margin:16px 0 8px">{{ $ok ? 'You have been unsubscribed' : 'Link not found' }}</h1>
    <p style="color:var(--muted);max-width:420px;margin:0 auto 20px">{{ $ok ? "You won't receive marketing emails from us anymore. Changed your mind? You can re-subscribe anytime from our site." : 'This unsubscribe link is invalid or has already been used.' }}</p>
    <a href="{{ route('home') }}" class="btn-g">Back to store</a>
  </div>
</section>
@endsection
