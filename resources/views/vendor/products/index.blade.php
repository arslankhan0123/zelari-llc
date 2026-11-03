@extends('layouts.vendor')
@section('title','Products')

@section('content')
<div class="max-w-5xl">
  <div class="flex items-center justify-between mb-5">
    <div>
      <h2 class="text-xl font-bold text-gray-800">My Products</h2>
      <p class="text-sm text-gray-400">Add and manage your catalog. New products need admin approval.</p>
    </div>
    <a href="{{ route('vendor.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Add product
    </a>
  </div>

  @if(session('error'))<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm mb-4">{{ session('error') }}</div>@endif

  <form method="GET" class="flex gap-2 mb-4">
    <input name="search" value="{{ request('search') }}" placeholder="Search products…" class="flex-1 border border-gray-200 rounded-xl px-4 py-2 text-sm">
    <select name="approval" class="border border-gray-200 rounded-xl px-3 text-sm">
      <option value="">All</option>
      @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v)
      <option value="{{ $k }}" {{ request('approval')===$k?'selected':'' }}>{{ $v }}</option>
      @endforeach
    </select>
    <button class="px-4 py-2 rounded-xl bg-gray-800 text-white text-sm font-semibold">Filter</button>
  </form>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @if($products->count())
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-left text-xs uppercase tracking-wide">
        <tr><th class="px-5 py-3">Product</th><th class="px-5 py-3">Price</th><th class="px-5 py-3">Stock</th><th class="px-5 py-3">Approval</th><th class="px-5 py-3 text-right">Actions</th></tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($products as $p)
        <tr>
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">@if($p->feature_image)<img src="{{ asset('storage/'.$p->feature_image) }}" class="w-full h-full object-cover">@endif</div>
              <div><div class="font-medium text-gray-800">{{ $p->name }}</div><div class="text-[11px] text-gray-400">{{ $p->sku ?: '—' }}</div></div>
            </div>
          </td>
          <td class="px-5 py-3 text-gray-600">{{ $p->sale_price ? zprice($p->sale_price) : zprice($p->purchase_price) }}</td>
          <td class="px-5 py-3 text-gray-600">{{ $p->quantity }}</td>
          <td class="px-5 py-3">
            @php $m=['pending'=>['Pending','bg-amber-100 text-amber-700'],'approved'=>['Approved','bg-emerald-100 text-emerald-700'],'rejected'=>['Rejected','bg-red-100 text-red-700']][$p->approval_status] ?? ['—','bg-gray-100 text-gray-500']; @endphp
            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $m[1] }}">{{ $m[0] }}</span>
            @if($p->approval_status==='rejected' && $p->approval_note)<div class="text-[11px] text-red-400 mt-0.5">{{ $p->approval_note }}</div>@endif
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('vendor.products.edit',$p) }}" class="text-gray-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
              <form method="POST" action="{{ route('vendor.products.destroy',$p) }}" onsubmit="return confirm('Delete this product?')">@csrf @method('DELETE')<button class="text-gray-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-5 py-4">{{ $products->links() }}</div>
    @else
    <div class="text-center text-gray-400 py-16 text-sm">No products yet. Click "Add product" to list your first item.</div>
    @endif
  </div>
</div>
@endsection
