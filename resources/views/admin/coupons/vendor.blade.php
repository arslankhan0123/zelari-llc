@extends('layouts.admin')

@section('title', 'My Coupons')
@section('breadcrumb', 'Discount codes for your own products')

@section('content')
<div class="max-w-5xl">

  <div class="bg-indigo-50 border border-indigo-100 text-indigo-800 text-sm rounded-xl p-4 mb-6">
    <i class="fa-solid fa-circle-info mr-1"></i>
    These coupons apply <b>only to your products</b>. When a customer uses one, the discount is capped to your items in their cart.
  </div>

  {{-- Create --}}
  @can('coupons.create')
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-ticket text-indigo-500 mr-1"></i> Create a coupon</div>
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="grid md:grid-cols-3 gap-4">
      @csrf
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Code</label>
        <input name="code" maxlength="40" placeholder="Auto-generate if blank" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm uppercase">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Type</label>
        <select name="type" x-data @change="$refs.val.disabled = $event.target.value==='freeship'" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          <option value="percent">Percentage off</option>
          <option value="fixed">Fixed amount off (USD)</option>
          <option value="freeship">Free shipping</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Value</label>
        <input x-ref="val" name="value" type="number" min="0" step="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Minimum spend (USD)</label>
        <input name="min_spend" type="number" min="0" step="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Max discount (USD)</label>
        <input name="max_discount" type="number" min="0" step="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Usage limit</label>
        <input name="usage_limit" type="number" min="1" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Expires on</label>
        <input name="expires_at" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="md:col-span-3">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Create coupon</button>
      </div>
    </form>
  </div>
  @endcan

  {{-- List --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">Your coupons</div>
    @if($coupons->count())
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="text-left font-bold px-5 py-3">Code</th>
            <th class="text-left font-bold px-5 py-3">Discount</th>
            <th class="text-left font-bold px-5 py-3">Min spend</th>
            <th class="text-left font-bold px-5 py-3">Used</th>
            <th class="text-left font-bold px-5 py-3">Expires</th>
            <th class="text-left font-bold px-5 py-3">Status</th>
            <th class="text-right font-bold px-5 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($coupons as $c)
          <tr>
            <td class="px-5 py-3 font-bold text-gray-800">{{ $c->code }}</td>
            <td class="px-5 py-3 text-gray-600">
              @if($c->type==='percent') {{ rtrim(rtrim(number_format($c->value,2),'0'),'.') }}% off
              @elseif($c->type==='fixed') ${{ number_format($c->value,2) }} off
              @else Free shipping @endif
            </td>
            <td class="px-5 py-3 text-gray-600">{{ $c->min_spend > 0 ? '$'.number_format($c->min_spend,2) : '—' }}</td>
            <td class="px-5 py-3 text-gray-600">{{ $c->used_count }}{{ $c->usage_limit ? ' / '.$c->usage_limit : '' }}</td>
            <td class="px-5 py-3 text-gray-600">{{ $c->expires_at ? \Illuminate\Support\Carbon::parse($c->expires_at)->format('d M Y') : 'Never' }}</td>
            <td class="px-5 py-3">
              @if($c->active)
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 border border-green-100 rounded-full px-2.5 py-1">Active</span>
              @else
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-full px-2.5 py-1">Off</span>
              @endif
            </td>
            <td class="px-5 py-3 text-right">
              @can('coupons.delete')
              <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}" onsubmit="return confirm('Delete coupon {{ $c->code }}?')" class="inline">
                @csrf @method('DELETE')
                <button class="text-red-500 hover:text-red-700" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </form>
              @endcan
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="px-5 py-4">{{ $coupons->links() }}</div>
    @else
    <div class="px-5 py-12 text-center text-gray-400">
      <i class="fa-solid fa-ticket text-3xl mb-2"></i>
      <p class="text-sm">You haven't created any coupons yet.</p>
    </div>
    @endif
  </div>
</div>
@endsection
