@extends('layouts.admin')

@section('title', 'Spin Wheel')
@section('breadcrumb', 'Coupons, the prize wheel and captured emails')

@section('content')
<div class="max-w-6xl">

  {{-- Stats --}}
  <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @foreach([
      ['Coupons', $stats['coupons'], 'indigo'],
      ['Active', $stats['active'], 'green'],
      ['Redeemed', $stats['redeemed'], 'amber'],
      ['Emails captured', $stats['emails'], 'sky'],
      ['Wheel spins', $stats['spins'], 'pink'],
    ] as [$label, $val, $c])
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="text-2xl font-extrabold text-gray-800">{{ number_format($val) }}</div>
        <div class="text-xs font-semibold text-gray-500 mt-0.5">{{ $label }}</div>
      </div>
    @endforeach
  </div>

  {{-- ───── Prize wheel settings ───── --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <div class="font-bold text-gray-800"><i class="fa-solid fa-dharmachakra text-indigo-500 mr-1"></i> Spin-the-Wheel popup</div>
        <p class="text-sm text-gray-500 mt-0.5">A first-visit popup that captures the visitor's email and awards a coupon code. Great for building your email list.</p>
      </div>
      <a href="{{ route('admin.coupons.export') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 whitespace-nowrap"><i class="fa-solid fa-download mr-1"></i> Export emails (CSV)</a>
    </div>
    <form method="POST" action="{{ route('admin.coupons.wheel') }}" class="grid md:grid-cols-2 gap-4">
      @csrf
      <label class="flex items-center gap-3 md:col-span-2">
        <span class="zl-switch"><input type="checkbox" name="enabled" value="1" {{ $wheel['enabled'] ? 'checked' : '' }} onchange="this.form.querySelector('[name=enabled_hidden]').disabled=this.checked"><span class="zl-slider"></span></span>
        <input type="hidden" name="enabled" value="0" {{ $wheel['enabled'] ? 'disabled' : '' }} class="enabled_hidden">
        <span class="text-sm font-semibold text-gray-700">Show the prize wheel on the storefront</span>
      </label>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Headline</label>
        <input name="title" value="{{ $wheel['title'] }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Sub-text</label>
        <input name="subtitle" value="{{ $wheel['subtitle'] }}" maxlength="255" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Coupon valid for (days)</label>
        <input name="expiry_days" type="number" min="1" max="365" value="{{ $wheel['expiryDays'] }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Minimum spend to use won coupons (USD)</label>
        <input name="min_spend" type="number" min="0" step="0.01" value="{{ $wheel['minSpend'] }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="md:col-span-2">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Save wheel settings</button>
      </div>
    </form>
  </div>

  {{-- ───── Prize wheel segments ───── --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6" x-data="segEditor(@js($segments))">
    <div class="flex items-center justify-between mb-1">
      <div class="font-bold text-gray-800"><i class="fa-solid fa-dharmachakra text-indigo-500 mr-1"></i> Prize segments</div>
      <span class="text-xs text-gray-400">2&ndash;12 segments &middot; higher weight = more likely</span>
    </div>
    <p class="text-sm text-gray-500 mb-4">These are the slices shown on the wheel. “Try Again” (type <b>None</b>) means no prize. Weight controls how often each one is landed on.</p>

    <form method="POST" action="{{ route('admin.coupons.segments') }}">
      @csrf
      <div class="hidden md:grid grid-cols-12 gap-2 px-1 mb-1 text-[11px] font-bold text-gray-400">
        <div class="col-span-3">Label</div><div class="col-span-2">Type</div><div class="col-span-2">Value</div><div class="col-span-2">Weight</div><div class="col-span-2">Color</div><div class="col-span-1"></div>
      </div>
      <div class="space-y-2">
        <template x-for="(seg,i) in segs" :key="i">
          <div class="grid grid-cols-2 md:grid-cols-12 gap-2 items-center border border-gray-100 rounded-lg p-2 md:border-0 md:p-0">
            <input :name="`segments[${i}][label]`" x-model="seg.label" maxlength="30" placeholder="5% OFF" class="col-span-2 md:col-span-3 border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <select :name="`segments[${i}][type]`" x-model="seg.type" class="col-span-1 md:col-span-2 border border-gray-200 rounded-lg px-2 py-2 text-sm">
              <option value="percent">Percent %</option>
              <option value="fixed">Fixed $</option>
              <option value="freeship">Free shipping</option>
              <option value="none">None (lose)</option>
            </select>
            <input :name="`segments[${i}][value]`" x-model="seg.value" type="number" min="0" step="0.01" :disabled="seg.type==='freeship'||seg.type==='none'" class="col-span-1 md:col-span-2 border border-gray-200 rounded-lg px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-400">
            <input :name="`segments[${i}][weight]`" x-model="seg.weight" type="number" min="0" step="1" class="col-span-1 md:col-span-2 border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <div class="col-span-1 md:col-span-2 flex items-center gap-2">
              <input :name="`segments[${i}][color]`" x-model="seg.color" type="color" class="w-9 h-9 border border-gray-200 rounded-lg p-0.5 bg-white cursor-pointer">
              <span class="w-6 h-6 rounded" :style="`background:${seg.color}`"></span>
            </div>
            <button type="button" @click="segs.splice(i,1)" class="col-span-2 md:col-span-1 text-gray-300 hover:text-red-500 md:text-right" title="Remove"><i class="fa-solid fa-trash-can"></i></button>
          </div>
        </template>
      </div>
      <div class="flex flex-wrap items-center gap-3 mt-4">
        <button type="button" @click="segs.length<12 && segs.push({label:'',type:'percent',value:5,weight:10,color:'#6366f1'})" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-plus mr-1"></i> Add segment</button>
        <span class="text-xs text-gray-400" x-show="segs.length>=12">Maximum 12 segments</span>
        <div class="flex-1"></div>
        <button type="submit" :disabled="segs.length<2" class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Save segments</button>
      </div>
    </form>

    <form method="POST" action="{{ route('admin.coupons.segments') }}" onsubmit="return confirm('Reset the wheel to the default segments (3% / 5% / 8% / 10% / Free shipping / Try Again)?')" class="mt-3">
      @csrf
      <input type="hidden" name="reset" value="1">
      <button class="text-xs font-semibold text-gray-400 hover:text-gray-600"><i class="fa-solid fa-rotate-left mr-1"></i> Reset to default</button>
      @if($isCustomSegments)<span class="text-xs text-amber-600 ml-2">Custom segments are active</span>@else<span class="text-xs text-gray-400 ml-2">Using the default segments</span>@endif
    </form>
  </div>

  {{-- ───── Create coupon ───── --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-ticket text-amber-500 mr-1"></i> Create a coupon</div>
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="grid md:grid-cols-6 gap-3 items-end">
      @csrf
      <div class="md:col-span-2">
        <label class="block text-xs font-bold text-gray-500 mb-1">Code <span class="text-gray-400 font-normal">(blank = auto)</span></label>
        <input name="code" placeholder="SUMMER20" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm uppercase">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Type</label>
        <select name="type" id="cType" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          <option value="percent">Percent %</option>
          <option value="fixed">Fixed $</option>
          <option value="freeship">Free shipping</option>
        </select>
      </div>
      <div id="cValWrap">
        <label class="block text-xs font-bold text-gray-500 mb-1"><span id="cValLbl">Value (%)</span></label>
        <input name="value" id="cVal" type="number" min="0" step="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Min spend $</label>
        <input name="min_spend" type="number" min="0" step="0.01" value="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Usage limit</label>
        <input name="usage_limit" type="number" min="1" placeholder="∞" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-bold text-gray-500 mb-1">Max discount $ <span class="text-gray-400 font-normal">(percent cap, optional)</span></label>
        <input name="max_discount" type="number" min="0" step="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-bold text-gray-500 mb-1">Expires</label>
        <input name="expires_at" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="md:col-span-2">
        <button class="w-full bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Create coupon</button>
      </div>
    </form>
  </div>

  {{-- ───── Coupons table ───── --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4">All coupons</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs font-bold text-gray-400 border-b border-gray-100">
          <th class="py-2 pr-3">Code</th><th class="py-2 pr-3">Benefit</th><th class="py-2 pr-3">Min</th><th class="py-2 pr-3">Used</th><th class="py-2 pr-3">Expires</th><th class="py-2 pr-3">Source</th><th class="py-2 pr-3">Active</th><th class="py-2"></th>
        </tr></thead>
        <tbody>
        @forelse($coupons as $c)
          <tr class="border-b border-gray-50">
            <td class="py-2.5 pr-3 font-mono font-bold text-gray-800">{{ $c->code }}</td>
            <td class="py-2.5 pr-3">
              @if($c->type==='percent'){{ rtrim(rtrim(number_format($c->value,2),'0'),'.') }}% off
              @elseif($c->type==='fixed')${{ number_format($c->value,2) }} off
              @else Free shipping @endif
              @if($c->max_discount) <span class="text-gray-400 text-xs">(max ${{ number_format($c->max_discount,2) }})</span>@endif
            </td>
            <td class="py-2.5 pr-3 text-gray-500">{{ $c->min_spend>0 ? '$'.number_format($c->min_spend,2) : '—' }}</td>
            <td class="py-2.5 pr-3 text-gray-500">{{ $c->used_count }}{{ $c->usage_limit ? '/'.$c->usage_limit : '' }}</td>
            <td class="py-2.5 pr-3 text-gray-500">{{ $c->expires_at ? $c->expires_at->format('d M Y') : '—' }}</td>
            <td class="py-2.5 pr-3"><span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $c->source==='wheel' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($c->source) }}</span></td>
            <td class="py-2.5 pr-3">
              <label class="zl-switch sm"><input type="checkbox" class="js-coupon" data-id="{{ $c->id }}" {{ $c->active ? 'checked' : '' }}><span class="zl-slider"></span></label>
            </td>
            <td class="py-2.5 text-right">
              <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}" onsubmit="return confirm('Delete coupon {{ $c->code }}?')" class="inline">
                @csrf @method('DELETE')
                <button class="text-gray-300 hover:text-red-500"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="py-8 text-center text-gray-400">No coupons yet. Create one above, or they'll appear here when visitors win on the wheel.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-3">{{ $coupons->links() }}</div>
  </div>

  {{-- ───── Captured emails ───── --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="font-bold text-gray-800 mb-4">Wheel entries &amp; captured emails</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs font-bold text-gray-400 border-b border-gray-100">
          <th class="py-2 pr-3">Email</th><th class="py-2 pr-3">Prize</th><th class="py-2 pr-3">Code</th><th class="py-2 pr-3">When</th><th class="py-2"></th>
        </tr></thead>
        <tbody>
        @forelse($entries as $e)
          <tr class="border-b border-gray-50">
            <td class="py-2.5 pr-3 text-gray-800">{{ $e->email }}</td>
            <td class="py-2.5 pr-3 text-gray-600">{{ $e->prize_label }}</td>
            <td class="py-2.5 pr-3 font-mono text-gray-500">{{ $e->coupon?->code ?? '—' }}</td>
            <td class="py-2.5 pr-3 text-gray-400">{{ $e->created_at->diffForHumans() }}</td>
            <td class="py-2.5 text-right">
              <form method="POST" action="{{ route('admin.coupons.entry.destroy', $e) }}" onsubmit="return confirm('Delete this wheel entry? This cannot be undone.')" class="inline">
                @csrf @method('DELETE')
                <button class="text-gray-300 hover:text-red-500" title="Delete entry"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="py-8 text-center text-gray-400">No spins yet.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-3">{{ $entries->links() }}</div>
  </div>
</div>

<style>
.zl-switch{position:relative;display:inline-block;width:46px;height:26px;flex-shrink:0}
.zl-switch.sm{width:40px;height:22px}
.zl-switch input{opacity:0;width:0;height:0}
.zl-slider{position:absolute;cursor:pointer;inset:0;background:#d1d5db;border-radius:999px;transition:.2s}
.zl-slider:before{content:"";position:absolute;height:20px;width:20px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.25)}
.zl-switch.sm .zl-slider:before{height:16px;width:16px}
.zl-switch input:checked + .zl-slider{background:#16a34a}
.zl-switch input:checked + .zl-slider:before{transform:translateX(20px)}
.zl-switch.sm input:checked + .zl-slider:before{transform:translateX(18px)}
</style>
@endsection

@push('scripts')
<script>
function segEditor(initial){
  return { segs: (initial||[]).map(s=>({label:s.label,type:s.type,value:s.value,weight:s.weight,color:s.color})) };
}
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
  // type → value label / disable value for freeship
  const type = document.getElementById('cType'), valWrap = document.getElementById('cValWrap'), valLbl = document.getElementById('cValLbl'), val = document.getElementById('cVal');
  function syncType(){
    if(type.value==='freeship'){ valWrap.style.display='none'; val.value=''; }
    else { valWrap.style.display=''; valLbl.textContent = type.value==='percent' ? 'Value (%)' : 'Value ($)'; }
  }
  type.addEventListener('change', syncType); syncType();
  // toggle active
  document.querySelectorAll('.js-coupon').forEach(cb=>cb.addEventListener('change', async function(){
    try{
      const r = await fetch('{{ url('admin/coupons') }}/'+this.dataset.id+'/toggle', {method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, credentials:'same-origin'});
      const j = await r.json();
      if(!r.ok||!j.ok){ this.checked=!this.checked; }
    }catch(e){ this.checked=!this.checked; }
  }));
})();
</script>
@endpush
