@extends('layouts.vendor')
@section('title','Dashboard')

@section('content')
<div class="max-w-5xl">

    @if(!$vendor || $vendor->status === 'pending')
    <div class="rounded-2xl p-5 mb-6 bg-amber-50 border border-amber-200 flex items-start gap-3">
        <svg class="w-6 h-6 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z"/></svg>
        <div>
            <p class="font-semibold text-amber-800">Your store is pending approval</p>
            <p class="text-sm text-amber-700 mt-0.5">An admin will review your application shortly. You'll be able to add products and manage orders once approved.</p>
        </div>
    </div>
    @elseif($vendor->status === 'suspended')
    <div class="rounded-2xl p-5 mb-6 bg-red-50 border border-red-200">
        <p class="font-semibold text-red-800">Your store is suspended</p>
        <p class="text-sm text-red-700 mt-0.5">Please contact the marketplace admin for details.</p>
    </div>
    @endif

    <div class="rounded-3xl p-7 mb-6 text-white" style="background:linear-gradient(135deg,#0f0c29,#302b63 55%,#6d28d9)">
        <p class="text-white/60 text-sm">{{ now()->format('l, d M Y') }}</p>
        <h2 class="text-2xl font-extrabold mt-1">Welcome, {{ $vendor->name ?? auth()->user()->name }} 👋</h2>
        <p class="text-white/70 mt-1 text-sm">Here's your store at a glance.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([['Products',$stats['products'],'#6366f1'],['Active',$stats['active'],'#16a34a'],['Orders','—','#0ea5e9'],['Earnings','—','#f59e0b']] as [$l,$v,$c])
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">{{ $l }}</p>
            <p class="text-2xl font-bold mt-1" style="color:{{ $c }}">{{ $v }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center">
        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        </div>
        <h3 class="font-bold text-gray-800">Product &amp; order management coming next</h3>
        <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">Once your store is approved, you'll manage your catalog, fulfil orders, and track earnings right here.</p>
    </div>

</div>
@endsection
