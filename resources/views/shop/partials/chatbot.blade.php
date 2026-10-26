{{-- ═════════════ STORE CHATBOT — answers from live store data ═════════════ --}}
<style>
.zcb-btn{position:fixed;right:20px;bottom:150px;z-index:1250;width:58px;height:58px;border-radius:50%;border:0;cursor:pointer;background:var(--brand-grad);color:#fff;font-size:24px;display:grid;place-items:center;box-shadow:0 18px 40px -12px rgba(var(--shadow-rgb),.7);transition:transform .25s,box-shadow .25s}
.zcb-btn:hover{transform:translateY(-3px) scale(1.05)}
.zcb-btn .dot{position:absolute;top:4px;right:4px;width:13px;height:13px;border-radius:50%;background:var(--ok);border:2.5px solid #fff}
.zcb-btn .ping{position:absolute;top:-4px;left:-4px;min-width:20px;height:20px;border-radius:10px;background:var(--sale);color:#fff;font-size:11px;font-weight:800;display:none;place-items:center;padding:0 5px;border:2px solid #fff}
.zcb-btn.hasnew .ping{display:grid}
@media(min-width:769px){.zcb-btn{bottom:92px;right:24px}}
.zcb{position:fixed;right:16px;bottom:218px;z-index:1251;width:min(380px,calc(100vw - 32px));height:min(560px,calc(100vh - 260px));background:#fff;border-radius:20px;box-shadow:0 40px 90px -24px rgba(var(--shadow-rgb),.55);display:flex;flex-direction:column;overflow:hidden;opacity:0;visibility:hidden;transform:translateY(16px) scale(.97);transform-origin:bottom right;transition:.28s cubic-bezier(.2,.7,.2,1)}
@media(min-width:769px){.zcb{right:24px;bottom:162px;height:min(600px,calc(100vh - 190px))}}
.zcb.open{opacity:1;visibility:visible;transform:none}
.zcb-head{background:var(--brand-grad);color:#fff;padding:14px 16px;display:flex;align-items:center;gap:11px;flex-shrink:0}
.zcb-head .av{width:40px;height:40px;border-radius:13px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);display:grid;place-items:center;font-size:17px}
.zcb-head b{display:block;font-size:14.5px}
.zcb-head small{display:flex;align-items:center;gap:5px;font-size:11.5px;color:rgba(255,255,255,.8)}
.zcb-head small i.on{width:7px;height:7px;border-radius:50%;background:#4ade80;display:inline-block}
.zcb-head .x{margin-left:auto;background:rgba(255,255,255,.12);border:0;color:#fff;width:32px;height:32px;border-radius:9px;cursor:pointer;font-size:13px}
.zcb-msgs{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;background:var(--surface);scrollbar-width:thin}
.zcb-m{max-width:85%;padding:10px 13px;border-radius:14px;font-size:13.5px;line-height:1.55;word-wrap:break-word}
.zcb-m.bot{background:#fff;border:1px solid var(--line);border-bottom-left-radius:5px;align-self:flex-start;color:var(--ink);box-shadow:0 4px 12px -8px rgba(var(--shadow-rgb),.25)}
.zcb-m.bot a{color:var(--p);font-weight:700;text-decoration:underline}
.zcb-m.user{background:var(--brand-grad);color:#fff;border-bottom-right-radius:5px;align-self:flex-end}
.zcb-typing{display:inline-flex;gap:4px;padding:12px 14px}
.zcb-typing span{width:7px;height:7px;border-radius:50%;background:var(--faint);animation:zcbB 1.1s infinite}
.zcb-typing span:nth-child(2){animation-delay:.18s}.zcb-typing span:nth-child(3){animation-delay:.36s}
@keyframes zcbB{0%,70%,100%{transform:translateY(0);opacity:.45}35%{transform:translateY(-5px);opacity:1}}
.zcb-cards{display:flex;flex-direction:column;gap:8px;align-self:stretch}
.zcb-card{display:flex;gap:10px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:13px;padding:9px;transition:.2s;box-shadow:0 4px 12px -8px rgba(var(--shadow-rgb),.25)}
.zcb-card:hover{border-color:var(--p);transform:translateY(-2px)}
.zcb-card img{width:52px;height:52px;border-radius:10px;object-fit:cover;flex-shrink:0;background:var(--surface)}
.zcb-card b{display:block;font-size:12.5px;color:var(--ink);line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.zcb-card .pr{font-size:13px;font-weight:800;color:var(--p)}
.zcb-card .pr s{color:var(--faint);font-weight:600;font-size:11px;margin-left:5px}
.zcb-card small{font-size:10.5px;font-weight:700;color:var(--ok)}
.zcb-card small.out{color:var(--danger)}
.zcb-card .go{margin-left:auto;color:var(--p);font-size:13px;flex-shrink:0}
.zcb-chips{display:flex;gap:7px;overflow-x:auto;padding:10px 12px 4px;flex-shrink:0;scrollbar-width:none;background:var(--surface)}
.zcb-chips::-webkit-scrollbar{display:none}
.zcb-chip{flex-shrink:0;background:#fff;border:1.5px solid var(--line-2);color:var(--ink-2);font-size:12px;font-weight:700;padding:7px 13px;border-radius:999px;cursor:pointer;transition:.15s;font-family:inherit}
.zcb-chip:hover{border-color:var(--p);color:var(--p);background:var(--brand-soft)}
.zcb-in{display:flex;gap:8px;padding:10px 12px 12px;background:var(--surface);flex-shrink:0}
.zcb-in input{flex:1;border:1.5px solid var(--line-2);border-radius:999px;padding:0 16px;height:44px;font:inherit;font-size:13.5px;outline:0;background:#fff;min-width:0}
.zcb-in input:focus{border-color:var(--p)}
.zcb-in button{width:44px;height:44px;border-radius:50%;border:0;background:var(--brand-grad);color:#fff;font-size:15px;cursor:pointer;flex-shrink:0;transition:filter .2s}
.zcb-in button:hover{filter:brightness(1.15)}
.zcb-form{display:flex;flex-direction:column;gap:7px;margin-top:9px}
.zcb-form input{border:1.5px solid var(--line-2);border-radius:10px;padding:9px 12px;font:inherit;font-size:13px;outline:0;background:#fff}
.zcb-form input:focus{border-color:var(--p)}
.zcb-form button{border:0;border-radius:10px;padding:10px;background:var(--brand-grad);color:#fff;font-weight:700;font-size:13px;cursor:pointer;font-family:inherit}
.zcb-agent-tag{display:inline-flex;align-items:center;gap:6px;font-size:10.5px;font-weight:800;letter-spacing:.04em;background:var(--accent-soft);color:#8a6100;border-radius:999px;padding:3px 9px;margin-bottom:4px;align-self:flex-start}
@media(max-width:768px){body.drawer-open .zcb-btn,body.drawer-open .zcb{display:none}}
</style>

<button class="zcb-btn" id="zcbBtn" aria-label="Chat with us"><i class="fa-solid fa-comments"></i><span class="dot"></span><span class="ping" id="zcbPing">1</span></button>

<div class="zcb" id="zcbPanel" role="dialog" aria-label="Store assistant">
  <div class="zcb-head">
    <span class="av"><i class="fa-solid fa-headset"></i></span>
    <div><b>{{ zstore('name','Zelari') }} Assistant</b><small><i class="on"></i> Online · replies instantly</small></div>
    <button class="x" id="zcbClose" aria-label="Close chat"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="zcb-msgs" id="zcbMsgs"></div>
  <div class="zcb-chips" id="zcbChips"></div>
  <form class="zcb-in" id="zcbForm" autocomplete="off">
    <input type="text" id="zcbInput" placeholder="Ask about products, delivery, prices…" maxlength="300">
    <button type="submit" aria-label="Send"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>

<script>
(function(){
  const API='{{ route('chatbot.message') }}', POLL='{{ route('chatbot.poll') }}', TOKEN=document.querySelector('meta[name=csrf-token]').content;
  let tok=''; try{ tok=localStorage.getItem('zcbTok')||''; if(!tok){ tok=(crypto.randomUUID?crypto.randomUUID():'t'+Date.now()+Math.random().toString(36).slice(2)); localStorage.setItem('zcbTok',tok);} }catch(_){ tok='t'+Date.now(); }
  const AUTH_CONTACT = @auth {name:@json(auth()->user()->name),email:@json(auth()->user()->email)} @else null @endauth;
  const panel=document.getElementById('zcbPanel'), btn=document.getElementById('zcbBtn'),
        msgs=document.getElementById('zcbMsgs'), chips=document.getElementById('zcbChips'),
        form=document.getElementById('zcbForm'), input=document.getElementById('zcbInput');
  const BACK='↩ Back to assistant';
  let history=[]; try{ history=JSON.parse(sessionStorage.getItem('zcbHist')||'[]'); }catch(_){ history=[]; }
  let agent={on:false,name:'',email:'',lastId:0};
  let pendingForm=null, unread=0; try{ agent=Object.assign(agent, JSON.parse(sessionStorage.getItem('zcbAgent')||'{}')); }catch(_){}
  let booted=false, busy=false;

  const save=()=>{ try{ sessionStorage.setItem('zcbHist', JSON.stringify(history.slice(-30))); }catch(_){} };
  const saveA=()=>{ try{ sessionStorage.setItem('zcbAgent', JSON.stringify(agent)); }catch(_){} };
  const scroll=()=>{ msgs.scrollTop=msgs.scrollHeight; };
  const esc=s=>{ const d=document.createElement('div'); d.textContent=s; return d.innerHTML; };

  function bubble(who, html){ const m=document.createElement('div'); m.className='zcb-m '+who; m.innerHTML=html; msgs.appendChild(m); scroll(); return m; }
  function cards(list){
    if(!list||!list.length) return;
    const w=document.createElement('div'); w.className='zcb-cards';
    list.forEach(p=>{
      const a=document.createElement('a'); a.className='zcb-card'; a.href=p.url;
      a.innerHTML='<img src="'+p.img+'" alt="" loading="lazy"><div><b>'+esc(p.name)+'</b><span class="pr">'+esc(p.price)+(p.old?'<s>'+esc(p.old)+'</s>':'')+'</span><br><small class="'+(p.stock==='In stock'?'':'out')+'">'+esc(p.stock)+'</small></div><i class="fa-solid fa-chevron-right go"></i>';
      w.appendChild(a);
    });
    msgs.appendChild(w); scroll();
  }
  function setChips(list){
    chips.innerHTML='';
    (list||[]).forEach(c=>{ const b=document.createElement('button'); b.type='button'; b.className='zcb-chip'; b.textContent=c; b.onclick=()=>{ c===BACK ? exitAgent() : send(c); }; chips.appendChild(b); });
  }
  function agentUi(){
    input.placeholder='Message our support team…';
    setChips([BACK]);
  }
  function exitAgent(){
    agent.on=false; saveA();
    input.placeholder='Ask about products, delivery, prices…';
    const e={who:'bot',reply:'You\'re back with the shopping assistant 🤖 — ask me anything!',chips:['Delivery time?',"Today's deals",'Browse categories']};
    history.push(e); save(); render(e);
  }
  function enterAgent(){
    agent.on=true; saveA(); agentUi();
    const e={who:'bot',reply:'Great, <b>'+esc(agent.name)+'</b>! Type your message below — it goes straight to our support team.',agentTag:true,chips:[BACK]};
    history.push(e); save(); render(e);
  }
  function contactForm(){
    const m=document.createElement('div'); m.className='zcb-m bot';
    m.innerHTML='<form class="zcb-form" id="zcbCForm" action="javascript:void(0)"><input type="text" id="zcbName" placeholder="Your name" maxlength="100" autocomplete="name"><input type="email" id="zcbEmail" placeholder="Your email" maxlength="150" autocomplete="email"><button type="submit" id="zcbStart"><i class="fa-solid fa-headset"></i> Start chat with our team</button></form>';
    msgs.appendChild(m); scroll(); pendingForm=m;
    const start=(e)=>{
      if(e){ e.preventDefault(); e.stopPropagation(); }
      const n=m.querySelector('#zcbName').value.trim(), em=m.querySelector('#zcbEmail').value.trim();
      if(!n || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(em)){ m.querySelector('#zcbName').style.borderColor='var(--danger)'; m.querySelector('#zcbEmail').style.borderColor='var(--danger)'; return false; }
      agent.name=n; agent.email=em; m.remove(); pendingForm=null; enterAgent();
      return false;
    };
    m.querySelector('#zcbCForm').addEventListener('submit', start);
    m.querySelector('#zcbStart').addEventListener('click', start);
  }
  function render(entry){
    if(entry.who==='user'){ bubble('user', esc(entry.text)); return; }
    let html='';
    if(entry.agentTag) html+='<span class="zcb-agent-tag"><i class="fa-solid fa-headset"></i> LIVE SUPPORT</span><br>';
    bubble('bot', html+entry.reply);
    cards(entry.products); setChips(entry.chips);
  }

  async function ask(text){
    const t=document.createElement('div'); t.className='zcb-m bot zcb-typing'; t.innerHTML='<span></span><span></span><span></span>'; msgs.appendChild(t); scroll();
    const body={message:text};
    if(agent.on){ body.agent_mode=true; body.name=agent.name; body.email=agent.email; body.token=tok; }
    try{
      const r=await fetch(API,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TOKEN,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(body)});
      const j=await r.json(); t.remove();
      if(j.agent_prompt){
        const e={who:'bot',reply:j.reply,agentTag:true,chips:[BACK]};
        history.push(e); save(); render(e);
        if(j.contact){ agent.name=j.contact.name; agent.email=j.contact.email; }
        if(j.need_contact && !(agent.name&&agent.email)){ contactForm(); }
        else { agent.on=true; saveA(); agentUi(); }
        busy=false; return;
      }
      const entry={who:'bot',reply:j.reply||'Sorry, something went wrong.',products:j.products||[],chips:j.chips||(agent.on?[BACK]:[]),agentTag:!!j.agent};
      history.push(entry); save(); render(entry);
      if(agent.on) agentUi();
    }catch(_){ t.remove(); bubble('bot','Network error — please try again.'); }
    busy=false;
  }
  function send(text){
    text=(text||'').trim(); if(!text||busy) return;
    if(pendingForm){
      const u={who:'user',text:text}; history.push(u); save(); render(u); input.value='';
      const em=(text.match(/[^\s@,]+@[^\s@,]+\.[^\s@,]+/)||[])[0];
      if(em){
        let n=text.replace(em,'').replace(/[\n,;]+/g,' ').replace(/\s+/g,' ').trim();
        agent.name=n||'Guest'; agent.email=em;
        if(pendingForm){ pendingForm.remove(); pendingForm=null; }
        enterAgent();
      } else {
        const e={who:'bot',reply:'Please share your <b>email</b> too (e.g. <i>John, john@email.com</i>) or use the form above.',agentTag:true,chips:[]};
        history.push(e); save(); render(e);
      }
      return;
    }
    busy=true;
    const u={who:'user',text:text}; history.push(u); save(); render(u);
    input.value=''; ask(text);
  }

  async function pollAgent(){
    if(!agent.on) return;
    try{
      const r=await fetch(POLL+'?token='+encodeURIComponent(tok)+'&after='+(agent.lastId||0),{headers:{'Accept':'application/json'}});
      const j=await r.json();
      (j.messages||[]).forEach(m=>{
        agent.lastId=Math.max(agent.lastId||0, m.id);
        const e={who:'bot',reply:'<b>'+esc(m.name)+':</b> '+esc(m.message),agentTag:true,chips:[BACK]};
        history.push(e); save(); render(e);
        if(!panel.classList.contains('open')){ unread++; document.getElementById('zcbPing').textContent=unread; btn.classList.add('hasnew'); }
      });
      if((j.messages||[]).length) saveA();
    }catch(_){}
  }
  setInterval(pollAgent, 4000);

  btn.onclick=()=>{
    panel.classList.toggle('open');
    if(panel.classList.contains('open')){
      unread=0; btn.classList.remove('hasnew');
      if(!booted){ booted=true;
        if(AUTH_CONTACT && !agent.email){ agent.name=AUTH_CONTACT.name; agent.email=AUTH_CONTACT.email; }
        if(history.length){ history.forEach(render); if(agent.on) agentUi(); }
        else { ask(''); }
      }
      setTimeout(()=>input.focus(),200); scroll();
    }
  };
  document.getElementById('zcbClose').onclick=()=>panel.classList.remove('open');
  form.addEventListener('submit',e=>{ e.preventDefault(); send(input.value); });
})();
</script>
