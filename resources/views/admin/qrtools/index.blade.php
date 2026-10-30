@extends('layouts.admin')

@section('title', 'QR and Barcode')
@section('breadcrumb', 'Control which tools are visible on the storefront')

@section('content')
<div class="max-w-5xl">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6 flex items-center justify-between gap-4">
        <div>
            <div class="font-bold text-gray-800">QR and Barcode section</div>
            <p class="text-sm text-gray-500 mt-0.5">Turn the whole QR and Barcode section on or off. When off, its menu entry disappears from the store and every tool URL redirects back with a message.</p>
        </div>
        <label class="zl-switch">
            <input type="checkbox" id="masterSwitch" {{ $enabled ? 'checked' : '' }}>
            <span class="zl-slider"></span>
        </label>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 {{ $enabled ? '' : 'opacity-50' }}" id="toolsPanel">
        <div class="flex items-center justify-between mb-4">
            <div class="font-bold text-gray-800">Individual tools</div>
            <span class="text-xs text-gray-400">LIVE = usable &nbsp;·&nbsp; OFF = shows &ldquo;Coming soon&rdquo;</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($tools as $slug => $t)
                <div class="flex items-center gap-3 border border-gray-100 rounded-lg px-3 py-2.5">
                    <span class="w-8 h-8 rounded-lg grid place-items-center text-white text-sm shrink-0" style="background:{{ $t['color'] }}"><i class="fa-solid {{ $t['icon'] }}"></i></span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 truncate">{{ $t['name'] }}</div>
                        <div class="text-[11px] font-bold tracking-wide {{ ($t['live'] ?? false) ? 'text-green-600' : 'text-amber-600' }}" data-state="{{ $slug }}">{{ ($t['live'] ?? false) ? 'LIVE' : 'COMING SOON' }}</div>
                    </div>
                    <label class="zl-switch sm">
                        <input type="checkbox" class="js-tool" data-slug="{{ $slug }}" {{ ($t['live'] ?? false) ? 'checked' : '' }}>
                        <span class="zl-slider"></span>
                    </label>
                </div>
            @endforeach
        </div>
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
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
  const chip = document.createElement('span');
  chip.style.cssText = 'position:fixed;bottom:22px;right:22px;z-index:99;padding:9px 16px;border-radius:999px;font-size:13px;font-weight:700;box-shadow:0 8px 22px rgba(0,0,0,.18);display:none';
  document.body.appendChild(chip);
  let chipT = null;
  const flash = (msg, ok) => {
      chip.textContent = msg;
      chip.style.background = ok ? '#dcfce7' : '#fee2e2';
      chip.style.color = ok ? '#15803d' : '#b91c1c';
      chip.style.display = 'inline-block';
      clearTimeout(chipT); chipT = setTimeout(() => chip.style.display = 'none', 2600);
  };
  const post = async (url, data) => {
      const fd = new FormData();
      Object.entries(data).forEach(([k, v]) => fd.append(k, v));
      const r = await fetch(url, {method:'POST', credentials:'same-origin', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body: fd});
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
  };
  document.getElementById('masterSwitch').addEventListener('change', function(){
      const on = this.checked;
      post('{{ route('admin.qrtools.master') }}', {enabled: on ? '1' : '0'})
        .then(j => {
            this.checked = !!j.enabled;
            document.getElementById('toolsPanel').classList.toggle('opacity-50', !j.enabled);
            flash(j.enabled ? 'Saved ✓ — section is ON' : 'Saved ✓ — section is hidden', true);
        })
        .catch(e => { this.checked = !on; flash('Not saved (' + e.message + ') — try again', false); });
  });
  document.querySelectorAll('.js-tool').forEach(sw => sw.addEventListener('change', function(){
      const on = this.checked, slug = this.dataset.slug;
      post('{{ route('admin.qrtools.toggle') }}', {slug: slug, live: on ? '1' : '0'})
        .then(j => {
            this.checked = !!j.live;
            const st = document.querySelector('[data-state="' + slug + '"]');
            st.textContent = j.live ? 'LIVE' : 'COMING SOON';
            st.className = 'text-[11px] font-bold tracking-wide ' + (j.live ? 'text-green-600' : 'text-amber-600');
            flash('Saved ✓', true);
        })
        .catch(e => { this.checked = !on; flash('Not saved (' + e.message + ') — try again', false); });
  }));
})();
</script>
@endpush
