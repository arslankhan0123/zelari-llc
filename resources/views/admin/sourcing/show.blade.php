@extends('layouts.admin')

@section('title', 'Sourcing ' . $r->reference)
@section('breadcrumb', 'Sourcing request details')

@section('content')
<div class="page-header">
  <div class="flex items-center gap-3">
    <a href="{{ route('admin.sourcing.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-gray-500" title="Back">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
      <h2 class="text-xl font-bold text-gray-800">Sourcing request <span class="font-mono text-indigo-600">{{ $r->reference }}</span></h2>
      <p class="text-sm text-gray-500 mt-0.5">Received {{ $r->created_at->format('d M Y, g:i A') }} · {{ $r->created_at->diffForHumans() }}</p>
    </div>
  </div>
  <div class="flex items-center gap-2">
    <form method="POST" action="{{ route('admin.sourcing.status', $r) }}">
      @csrf
      <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium bg-white">
        @foreach(['new' => 'New', 'reviewing' => 'Reviewing', 'quoted' => 'Quoted', 'closed' => 'Closed'] as $v => $lbl)
          <option value="{{ $v }}" {{ $r->status === $v ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
      </select>
    </form>
    <a href="mailto:{{ $r->email }}?subject={{ rawurlencode('Re: sourcing request ' . $r->reference) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-semibold" style="background:linear-gradient(135deg,#1e1b4b,#3b3680)">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      Reply by email
    </a>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
  <div class="lg:col-span-2 space-y-5">
    {{-- items --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Requested products ({{ $r->items->count() }})</div>
      <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-xs text-gray-500">
          <th class="text-left px-5 py-2.5 font-semibold">Product</th>
          <th class="text-right px-5 py-2.5 font-semibold whitespace-nowrap">Qty</th>
          <th class="text-left px-5 py-2.5 font-semibold">Target price</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($r->items as $item)
          <tr>
            <td class="px-5 py-3">
              <p class="font-medium text-gray-800">{{ $item->name }}</p>
              @if($item->specs)<p class="text-xs text-gray-500 mt-0.5 whitespace-pre-wrap">{{ $item->specs }}</p>@endif
              @if($item->reference_url)<a href="{{ $item->reference_url }}" target="_blank" rel="noopener" class="text-xs text-indigo-600 hover:underline">Reference link ↗</a>@endif
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap text-gray-700">{{ number_format($item->quantity) }}{{ $item->unit ? ' ' . $item->unit : '' }}</td>
            <td class="px-5 py-3 text-gray-600">{{ $item->target_price ?: '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="px-5 py-6 text-center text-gray-400">No items.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($r->notes)
    <div class="bg-white border border-gray-200 rounded-2xl p-5">
      <p class="text-[11px] uppercase tracking-wider font-bold text-gray-400 mb-2">Customer notes</p>
      <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $r->notes }}</p>
    </div>
    @endif

    {{-- admin note --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5">
      <p class="text-[11px] uppercase tracking-wider font-bold text-gray-400 mb-2">Internal note (only admins see this)</p>
      <form method="POST" action="{{ route('admin.sourcing.note', $r) }}" class="flex flex-col gap-3">
        @csrf
        <textarea name="admin_note" rows="3" maxlength="1000" class="w-full border border-gray-200 rounded-xl p-3 text-sm" placeholder="Add a note…">{{ $r->admin_note }}</textarea>
        <button class="self-start px-4 py-2 rounded-lg text-white text-sm font-semibold" style="background:linear-gradient(135deg,#1e1b4b,#3b3680)">Save note</button>
      </form>
    </div>
  </div>

  <div class="space-y-5">
    {{-- customer --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-11 h-11 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">{{ $r->initials }}</div>
        <div class="min-w-0">
          <p class="font-semibold text-gray-900">{{ $r->name }}</p>
          @if($r->company)<p class="text-xs text-gray-400">{{ $r->company }}</p>@endif
        </div>
      </div>
      <div class="space-y-2 text-sm">
        <p><span class="text-gray-400">Email:</span> <a href="mailto:{{ $r->email }}" class="text-indigo-600 hover:underline break-all">{{ $r->email }}</a></p>
        @if($r->phone)<p><span class="text-gray-400">Phone:</span> <a href="tel:{{ $r->phone }}" class="text-gray-700">{{ $r->phone }}</a></p>@endif
        @if($r->ip)<p><span class="text-gray-400">IP:</span> <span class="text-gray-600 font-mono text-xs">{{ $r->ip }}</span></p>@endif
      </div>
    </div>

    {{-- request facts --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-3">
      @foreach([
        ['Source from', $r->source_country],
        ['Deliver to',  $r->deliver_to],
        ['Budget',      $r->budget],
        ['Needed by',   $r->needed_by?->format('d M Y')],
      ] as [$k, $v])
      <div class="flex items-center justify-between gap-3">
        <span class="text-xs text-gray-400">{{ $k }}</span>
        <span class="text-sm font-medium text-gray-800 text-right">{{ $v ?: '—' }}</span>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
