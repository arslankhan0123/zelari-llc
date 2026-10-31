@extends('layouts.admin')
@section('title','Product Approvals')

@section('content')
<div class="p-6 space-y-5">
  <div>
    <h1 class="text-2xl font-bold text-gray-800">Product Approvals</h1>
    <p class="text-sm text-gray-400 mt-0.5">Review products submitted by marketplace vendors</p>
  </div>

  @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif

  <div class="flex gap-2">
    @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v)
    <a href="{{ route('admin.approvals.index',['status'=>$k]) }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ $status===$k ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
      {{ $v }} <span class="ml-1 opacity-70">{{ $counts[$k] }}</span>
    </a>
    @endforeach
  </div>

  <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
    @if($products->count())
    <div class="divide-y divide-gray-100">
      @foreach($products as $p)
      <div class="flex items-center gap-4 p-4">
        <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">@if($p->feature_image)<img src="{{ asset('storage/'.$p->feature_image) }}" class="w-full h-full object-cover">@endif</div>
        <div class="flex-1 min-w-0">
          <div class="font-semibold text-gray-800 truncate">{{ $p->name }}</div>
          <div class="text-[11px] text-gray-400">by <span class="font-medium text-gray-500">{{ $p->vendor->name ?? '—' }}</span> · {{ $p->sale_price ? zprice($p->sale_price) : zprice($p->purchase_price) }} · {{ $p->quantity }} in stock</div>
          @if($p->approval_status==='rejected' && $p->approval_note)<div class="text-[11px] text-red-400 mt-0.5">Note: {{ $p->approval_note }}</div>@endif
        </div>
        @if($p->approval_status==='pending')
        <div class="flex items-center gap-2" x-data="{ rej:false }">
          <form method="POST" action="{{ route('admin.approvals.approve',$p) }}">@csrf<button class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">Approve</button></form>
          <button @click="rej=!rej" class="px-3 py-1.5 rounded-lg bg-red-100 text-red-700 text-sm font-semibold hover:bg-red-200">Reject</button>
          <form method="POST" action="{{ route('admin.approvals.reject',$p) }}" x-show="rej" x-cloak class="flex gap-1">@csrf
            <input name="approval_note" placeholder="Reason…" class="border border-gray-200 rounded-lg px-2 py-1 text-sm w-40">
            <button class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm font-semibold">Confirm</button>
          </form>
        </div>
        @else
        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $p->approval_status==='approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($p->approval_status) }}</span>
        @endif
      </div>
      @endforeach
    </div>
    <div class="px-5 py-4">{{ $products->links() }}</div>
    @else
    <div class="text-center text-gray-400 py-16 text-sm">Nothing here. Vendor product submissions will appear in this queue.</div>
    @endif
  </div>
</div>
@endsection
