@extends('layouts.admin')

@section('title', 'Notifications')
@section('breadcrumb', 'Everything that needs your attention')

@section('content')
<style>
.nf-filters{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.nf-chip{border:1.5px solid #e5e7eb;background:#fff;border-radius:999px;padding:7px 15px;font-size:13px;font-weight:700;color:#374151;cursor:pointer;font-family:inherit;transition:.15s}
.nf-chip:hover{border-color:#1e1b4b}
.nf-chip.on{background:linear-gradient(135deg,#1e1b4b,#3b3680);color:#fff;border-color:transparent}
.nf-toggle{margin-left:auto;display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:#374151;cursor:pointer}
.nf-list{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden}
.nf-row{display:flex;gap:13px;align-items:flex-start;padding:14px 18px;border-bottom:1px solid #f4f5f9;text-decoration:none;transition:background .15s;position:relative}
.nf-row:last-child{border-bottom:0}
.nf-row:hover{background:#f8f9fe}
.nf-row.unread{background:#f4f3ff}
.nf-row.unread::before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:linear-gradient(180deg,#f59e0b,#f97316)}
.nf-row .ic{width:40px;height:40px;border-radius:12px;background:#eef0fb;display:grid;place-items:center;font-size:17px;flex-shrink:0}
.nf-row b{display:block;font-size:14px;color:#111827}
.nf-row small{display:block;font-size:12.5px;color:#6b7280;margin-top:2px;line-height:1.45}
.nf-row .meta{margin-left:auto;text-align:right;flex-shrink:0}
.nf-row .meta time{font-size:11.5px;color:#9ca3af;display:block;white-space:nowrap}
.nf-row .meta .nw{display:inline-block;margin-top:5px;font-size:10px;font-weight:800;letter-spacing:.05em;background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:999px}
.nf-empty{padding:50px 20px;text-align:center;color:#9ca3af;font-size:14px}
</style>

<div class="page-header">
  <div>
    <h2 class="text-xl font-bold text-gray-800">Notifications</h2>
    <p class="text-sm text-gray-500 mt-0.5">Live chats, contact messages, orders and sourcing requests — newest first.</p>
  </div>
</div>

<div class="nf-filters">
  <button class="nf-chip on" data-f="all">All ({{ $items->count() }})</button>
  <button class="nf-chip" data-f="chat">💬 Chats ({{ $items->where('type','chat')->count() }})</button>
  <button class="nf-chip" data-f="message">✉️ Messages ({{ $items->where('type','message')->count() }})</button>
  <button class="nf-chip" data-f="order">🛒 Orders ({{ $items->where('type','order')->count() }})</button>
  <button class="nf-chip" data-f="sourcing">📦 Sourcing ({{ $items->where('type','sourcing')->count() }})</button>
  <label class="nf-toggle"><input type="checkbox" id="nfUnread" class="w-4 h-4 accent-indigo-900"> Unread only</label>
</div>

<div class="nf-list" id="nfList">
  @forelse($items as $i)
  <a href="{{ $i['url'] }}" class="nf-row {{ $i['unread'] ? 'unread' : '' }}" data-type="{{ $i['type'] }}" data-unread="{{ $i['unread'] ? 1 : 0 }}">
    <span class="ic">{{ $i['icon'] }}</span>
    <span style="min-width:0">
      <b>{{ $i['title'] }}</b>
      <small>{{ $i['sub'] }}</small>
    </span>
    <span class="meta">
      <time title="{{ $i['date'] }}">{{ $i['time'] }}</time>
      @if($i['unread'])<span class="nw">NEW</span>@endif
    </span>
  </a>
  @empty
  <div class="nf-empty">No notifications yet — new chats, messages, orders and sourcing requests will show up here.</div>
  @endforelse
  <div class="nf-empty" id="nfNone" style="display:none">Nothing matches this filter.</div>
</div>

<script>
(function(){
  let type='all', unreadOnly=false;
  const rows=[...document.querySelectorAll('.nf-row')];
  function apply(){
    let shown=0;
    rows.forEach(r=>{
      const ok=(type==='all'||r.dataset.type===type)&&(!unreadOnly||r.dataset.unread==='1');
      r.style.display=ok?'':'none'; if(ok) shown++;
    });
    document.getElementById('nfNone').style.display = (rows.length&&!shown) ? '' : 'none';
  }
  document.querySelectorAll('.nf-chip').forEach(c=>c.onclick=()=>{
    document.querySelectorAll('.nf-chip').forEach(x=>x.classList.remove('on'));
    c.classList.add('on'); type=c.dataset.f; apply();
  });
  document.getElementById('nfUnread').onchange=function(){ unreadOnly=this.checked; apply(); };
})();
</script>
@endsection
