@extends('layouts.admin')
@section('title','Dashboard')
@section('breadcrumb','Your store overview')

@section('content')
<div class="p-6 max-w-5xl">

  @if(!$vendor || $vendor->status === 'pending')
  <div class="rounded-2xl p-5 mb-6 bg-amber-50 border border-amber-200 flex items-start gap-3">
    <svg class="w-6 h-6 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z"/></svg>
    <div>
      <p class="font-semibold text-amber-800">Your store is pending approval</p>
      <p class="text-sm text-amber-700 mt-0.5">A master admin will review your store shortly. You can add products now — they'll go live once your store and products are approved.</p>
    </div>
  </div>
  @elseif($vendor->status === 'suspended')
  <div class="rounded-2xl p-5 mb-6 bg-red-50 border border-red-200"><p class="font-semibold text-red-800">Your store is suspended</p><p class="text-sm text-red-700 mt-0.5">Please contact the marketplace admin.</p></div>
  @endif

  <div class="rounded-3xl p-7 mb-6 text-white" style="background:linear-gradient(135deg,#0f0c29,#302b63 55%,#6d28d9)">
    <p class="text-white/60 text-sm">{{ now()->format('l, d M Y') }}</p>
    <h2 class="text-2xl font-extrabold mt-1">Welcome, {{ $vendor->name ?? auth()->user()->name }} 👋</h2>
    <p class="text-white/70 mt-1 text-sm">Here's your store at a glance.</p>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Products',$stats['products'],'#6366f1'],['Live',$stats['active'],'#16a34a'],['Pending',$stats['pending'],'#d97706'],['Rejected',$stats['rejected'],'#dc2626']] as [$l,$v,$c])
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
      <p class="text-sm text-gray-500">{{ $l }}</p>
      <p class="text-2xl font-bold mt-1" style="color:{{ $c }}">{{ $v }}</p>
    </div>
    @endforeach
  </div>

  <div class="flex gap-3 mb-6">
    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Add product
    </a>
    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 font-semibold text-sm">My products</a>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h3 class="font-bold text-gray-800 mb-4">Recent products</h3>
    @if($recent->count())
    <div class="divide-y divide-gray-100">
      @foreach($recent as $p)
      <div class="flex items-center gap-3 py-2.5">
        <div class="w-9 h-9 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">@if($p->feature_image)<img src="{{ asset('storage/'.$p->feature_image) }}" class="w-full h-full object-cover">@endif</div>
        <div class="flex-1 min-w-0"><div class="text-sm font-medium text-gray-800 truncate">{{ $p->name }}</div><div class="text-[11px] text-gray-400">{{ $p->sku ?: '—' }}</div></div>
        @php $m=['pending'=>['Pending','bg-amber-100 text-amber-700'],'approved'=>['Approved','bg-emerald-100 text-emerald-700'],'rejected'=>['Rejected','bg-red-100 text-red-700']][$p->approval_status] ?? ['—','bg-gray-100 text-gray-500']; @endphp
        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $m[1] }}">{{ $m[0] }}</span>
      </div>
      @endforeach
    </div>
    @else
    <p class="text-center text-gray-400 py-10 text-sm">No products yet. Click "Add product" to list your first item.</p>
    @endif
  </div>

</div>
@endsection
