@extends('layouts.admin')

@section('title', 'Live Chat')
@section('breadcrumb', 'Chat with customers in real time')

@section('content')
<style>
.lc{display:grid;grid-template-columns:320px 1fr;gap:0;background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;height:calc(100vh - 280px);min-height:440px}
@media(max-width:900px){.lc{grid-template-columns:1fr};.lc-right{display:none}.lc.chatting{grid-template-columns:1fr}.lc.chatting .lc-left{display:none}.lc.chatting .lc-right{display:flex}}
.lc-left{border-right:1px solid #e5e7eb;display:flex;flex-direction:column;min-width:0;min-height:0}
.lc-left h3{margin:0;padding:14px 16px;font-size:14px;font-weight:800;border-bottom:1px solid #f1f2f6}
.lc-list{flex:1;overflow-y:auto;min-height:0}
.lc-t{display:flex;gap:10px;align-items:flex-start;width:100%;text-align:left;padding:12px 14px;border:0;border-bottom:1px solid #f4f5f9;background:#fff;cursor:pointer;font-family:inherit;transition:background .15s}
.lc-t:hover{background:#f8f9fe}
.lc-t.on{background:#eef0fb}
.lc-t .av{width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,#1e1b4b,#3b3680);color:#fff;font-weight:800;font-size:14px;display:grid;place-items:center;flex-shrink:0}
.lc-t b{display:block;font-size:13.5px;color:#111827}
.lc-t small{display:block;font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:190px}
.lc-t .meta{margin-left:auto;text-align:right;flex-shrink:0}
.lc-t .meta time{font-size:10.5px;color:#9ca3af;display:block}
.lc-t .meta .n{display:inline-grid;place-items:center;min-width:19px;height:19px;border-radius:10px;background:#f59e0b;color:#fff;font-size:11px;font-weight:800;padding:0 5px;margin-top:4px}
.lc-right{display:flex;flex-direction:column;min-width:0;min-height:0}
.lc-head{display:flex;align-items:center;gap:11px;padding:12px 16px;border-bottom:1px solid #f1f2f6}
.lc-head .av{width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,#1e1b4b,#3b3680);color:#fff;font-weight:800;display:grid;place-items:center}
.lc-head b{font-size:14px}.lc-head small{display:block;font-size:12px;color:#6b7280}
.lc-msgs{flex:1;overflow-y:auto;min-height:0;padding:16px;display:flex;flex-direction:column;gap:9px;background:#f8f9fc}
.lc-m{max-width:72%;padding:9px 13px;border-radius:13px;font-size:13.5px;line-height:1.5;white-space:pre-wrap;word-wrap:break-word}
.lc-m.user{background:#fff;border:1px solid #e5e7eb;align-self:flex-start;border-bottom-left-radius:4px}
.lc-m.agent{background:linear-gradient(135deg,#1e1b4b,#3b3680);color:#fff;align-self:flex-end;border-bottom-right-radius:4px}
.lc-m time{display:block;font-size:10px;opacity:.65;margin-top:4px}
.lc-empty{flex:1;display:grid;place-items:center;color:#9ca3af;font-size:14px;padding:40px;text-align:center}
.lc-send{display:flex;gap:9px;padding:12px;border-top:1px solid #f1f2f6;flex-shrink:0}
.lc-send input{flex:1;border:1.5px solid #e5e7eb;border-radius:999px;padding:0 16px;height:44px;font:inherit;font-size:13.5px;outline:0;min-width:0}
.lc-send input:focus{border-color:#1e1b4b}
.lc-send button{border:0;border-radius:999px;height:44px;padding:0 20px;background:linear-gradient(135deg,#1e1b4b,#3b3680);color:#fff;font-weight:700;font-size:13.5px;cursor:pointer;font-family:inherit}
.lc-back{display:none;background:none;border:0;cursor:pointer;font-size:16px;color:#374151}
@media(max-width:900px){.lc-back{display:block}}
</style>

<style>
.lc-hours{display:flex;align-items:center;gap:12px;flex-wrap:wrap;width:100%;background:#fff;border:1px solid #eef0f5;border-radius:16px;padding:16px 20px;margin-bottom:24px;box-shadow:0 2px 12px rgba(0,0,0,.05)}
.lc-hours label{display:inline-flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#374151}
.lc-hours input[type=time]{border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 12px;font:inherit;font-size:13.5px;font-weight:600;color:#1e293b;outline:0;background:#fff;transition:border-color .2s,box-shadow .2s}
.lc-hours input[type=time]:focus{border-color:var(--brand,#6366f1);box-shadow:0 0 0 3px rgba(var(--shadow-rgb,99 102 241),.12)}
.lc-hours .lc-spacer{flex:1 1 auto}
.lc-hours .sv{border:0;border-radius:10px;padding:11px 22px;background:var(--brand-grad,linear-gradient(135deg,#1e1b4b,#3b3680));color:#fff;font-weight:800;font-size:13px;cursor:pointer;font-family:inherit;transition:filter .15s,transform .15s}
.lc-hours .sv:hover{filter:brightness(1.08);transform:translateY(-1px)}
.lc-st{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:800;padding:5px 11px;border-radius:999px}
.lc-st.on{background:#dcfce7;color:#166534}.lc-st.off{background:#fee2e2;color:#991b1b}
.lc-st i{width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block}
.lc-ok{font-size:12px;font-weight:700;color:#16a34a;opacity:0;transition:opacity .3s}
</style>

<div class="page-header">
  <div>
    <h2 class="text-xl font-bold text-gray-800">Live Chat</h2>
    <p class="text-sm text-gray-500 mt-0.5">Conversations from the website chat widget — replies reach the visitor instantly.</p>
  </div>
</div>

<form class="lc-hours" id="lcHours">
  <span class="lc-st {{ $hours['open'] ? 'on' : 'off' }}" id="lcStatus"><i></i> {{ $hours['open'] ? 'Agents ONLINE now' : 'Agents OFFLINE now' }}</span>
  <label>From <input type="time" name="start" value="{{ $hours['start'] }}" required></label>
  <label>To <input type="time" name="end" value="{{ $hours['end'] }}" required></label>
  <select name="tz">
    @foreach(['America/New_York'=>'US Eastern','America/Chicago'=>'US Central','America/Denver'=>'US Mountain','America/Los_Angeles'=>'US Pacific','UTC'=>'UTC','Europe/London'=>'UK','Asia/Karachi'=>'Pakistan'] as $tz => $lbl)
      <option value="{{ $tz }}" {{ $hours['tz'] === $tz ? 'selected' : '' }}>{{ $lbl }}</option>
    @endforeach
  </select>
  <span class="lc-spacer"></span>
  <button type="submit" class="sv">Save hours</button>
  <span class="lc-ok" id="lcOk">Saved ✓</span>
</form>

<div class="lc" id="lc">
  <div class="lc-left">
    <h3>Conversations</h3>
    <div class="lc-list" id="lcList"><div class="lc-empty">Loading…</div></div>
  </div>
  <div class="lc-right">
    <div class="lc-head" id="lcHead" style="display:none">
      <button class="lc-back" id="lcBack"><i class="fa-solid fa-arrow-left">←</i></button>
      <span class="av" id="lcAv">?</span>
      <div><b id="lcName"></b><small id="lcEmail"></small></div>
    </div>
    <div class="lc-msgs" id="lcMsgs"><div class="lc-empty">Select a conversation to start replying.<br>New messages appear here automatically.</div></div>
    <form class="lc-send" id="lcForm" style="display:none">
      <input type="text" id="lcInput" placeholder="Type your reply…" maxlength="2000" autocomplete="off">
      <button type="submit">Send</button>
    </form>
  </div>
</div>

<script>
(function(){
  const THREADS='{{ route('admin.chats.threads') }}', MSGS='{{ route('admin.chats.messages') }}', SEND='{{ route('admin.chats.send') }}';
  const TOKEN=document.querySelector('meta[name=csrf-token]').content;
  const list=document.getElementById('lcList'), msgs=document.getElementById('lcMsgs'),
        form=document.getElementById('lcForm'), input=document.getElementById('lcInput'),
        head=document.getElementById('lcHead'), lc=document.getElementById('lc');
  let cur=null, lastId=0, busy=false;
  let autoOpen=new URLSearchParams(location.search).get('open');
  const esc=s=>{const d=document.createElement('div');d.textContent=s||'';return d.innerHTML;};

  async function loadThreads(){
    try{
      const r=await fetch(THREADS,{headers:{'Accept':'application/json'}}); const j=await r.json();
      if(!j.threads.length){ list.innerHTML='<div class="lc-empty">No chats yet.</div>'; return; }
      list.innerHTML='';
      j.threads.forEach(t=>{
        const b=document.createElement('button'); b.type='button'; b.className='lc-t'+(cur===t.token?' on':'');
        b.innerHTML='<span class="av">'+esc((t.name||'V').charAt(0).toUpperCase())+'</span><span style="min-width:0"><b>'+esc(t.name)+'</b><small>'+esc((t.sender==='agent'?'You: ':'')+t.last)+'</small></span><span class="meta"><time>'+esc(t.time)+'</time>'+(t.unread>0?'<span class="n">'+t.unread+'</span>':'')+'</span>';
        b.onclick=()=>open(t);
        list.appendChild(b);
        if(autoOpen && t.token===autoOpen){ autoOpen=null; setTimeout(()=>{ if(!cur) open(t); },60); }
      });
    }catch(_){}
  }
  function render(m){
    const d=document.createElement('div'); d.className='lc-m '+m.sender;
    d.innerHTML=esc(m.message)+'<time>'+esc((m.sender==='agent'?(m.name||'You')+' · ':'')+m.time)+'</time>';
    msgs.appendChild(d); msgs.scrollTop=msgs.scrollHeight;
  }
  async function loadMsgs(){
    if(!cur) return;
    try{
      const r=await fetch(MSGS+'?token='+encodeURIComponent(cur)+'&after='+lastId,{headers:{'Accept':'application/json'}});
      const j=await r.json();
      (j.messages||[]).forEach(m=>{ render(m); lastId=Math.max(lastId,m.id); });
    }catch(_){}
  }
  function open(t){
    cur=t.token; lastId=0; msgs.innerHTML=''; lc.classList.add('chatting');
    head.style.display='flex'; form.style.display='flex';
    document.getElementById('lcName').textContent=t.name;
    document.getElementById('lcEmail').textContent=t.email;
    document.getElementById('lcAv').textContent=(t.name||'V').charAt(0).toUpperCase();
    loadMsgs(); loadThreads(); input.focus();
  }
  document.getElementById('lcBack').onclick=()=>{ lc.classList.remove('chatting'); cur=null; };
  form.addEventListener('submit',async e=>{
    e.preventDefault();
    const text=input.value.trim(); if(!text||!cur||busy) return; busy=true;
    try{
      const r=await fetch(SEND,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TOKEN,'Accept':'application/json'},body:JSON.stringify({token:cur,message:text})});
      const j=await r.json();
      if(j.ok){ render(j.message); lastId=Math.max(lastId,j.message.id); input.value=''; loadThreads(); }
    }catch(_){}
    busy=false;
  });

  loadThreads();
  setInterval(loadThreads, 5000);
  setInterval(loadMsgs, 3000);

  /* agent availability hours */
  const hForm=document.getElementById('lcHours');
  hForm.addEventListener('submit', async e=>{
    e.preventDefault();
    const fd=new FormData(hForm);
    try{
      const r=await fetch('{{ route('admin.chats.hours') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TOKEN,'Accept':'application/json'},body:JSON.stringify({start:fd.get('start'),end:fd.get('end'),tz:fd.get('tz')})});
      const j=await r.json();
      if(j.ok){
        const st=document.getElementById('lcStatus');
        st.className='lc-st '+(j.hours.open?'on':'off');
        st.innerHTML='<i></i> '+(j.hours.open?'Agents ONLINE now':'Agents OFFLINE now');
        const ok=document.getElementById('lcOk'); ok.style.opacity=1; setTimeout(()=>ok.style.opacity=0, 2500);
      }
    }catch(_){}
  });
})();
</script>
@endsection
