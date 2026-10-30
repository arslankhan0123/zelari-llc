/* Zelari Random & Productivity Tools — browser-side engine */
(function () {
  'use strict';
  var ui=document.getElementById('toolUI'); if(!ui) return;
  var slug=(window.ZTOOL||{}).slug;
  function h(s){ ui.innerHTML=s; }
  function $(s){ return ui.querySelector(s); }
  function $all(s){ return Array.prototype.slice.call(ui.querySelectorAll(s)); }
  function esc(s){ var d=document.createElement('div'); d.textContent=s==null?'':s; return d.innerHTML; }
  function cbtn(){ return '<button type="button" class="tt-copy" data-copy><i class="fa-solid fa-copy"></i> Copy</button>'; }
  function wireCopy(btn,fn){ if(!btn)return; btn.addEventListener('click',function(){ navigator.clipboard.writeText(fn()).then(function(){var o=btn.innerHTML;btn.innerHTML='<i class="fa-solid fa-check"></i> Copied';setTimeout(function(){btn.innerHTML=o;},1300);}).catch(function(){}); }); }
  function beep(){ try{ var a=new (window.AudioContext||window.webkitAudioContext)(); var o=a.createOscillator(),g=a.createGain(); o.connect(g);g.connect(a.destination); o.frequency.value=880; o.start(); g.gain.setValueAtTime(0.3,a.currentTime); g.gain.exponentialRampToValueAtTime(0.001,a.currentTime+0.6); o.stop(a.currentTime+0.6); }catch(e){} }
  function pad(n){ return (n<10?'0':'')+n; }
  var tools={};

  tools['password-generator'] = function(){
    h('<div class="cl-f full"><label>Length: <span id="lenL">16</span></label><input type="range" class="tt-range" id="len" min="4" max="64" value="16"></div>'
      +'<div class="tt-opts">'+ck('up','Uppercase A-Z',1)+ck('low','Lowercase a-z',1)+ck('num','Numbers 0-9',1)+ck('sym','Symbols !@#',1)+ck('amb','Exclude look-alikes (0 O l 1)',0)+'</div>'
      +'<div class="tt-out mono" id="out" style="min-height:auto;font-size:18px;padding:16px 46px 16px 16px"></div>'
      +'<div style="height:8px;border-radius:5px;background:#eee;margin:12px 0;overflow:hidden"><div id="bar" style="height:100%;width:0;transition:.25s"></div></div>'
      +'<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-rotate"></i> Generate new</button><span id="lbl" style="font-weight:700;font-size:13px"></span></div>');
    function ck(id,l,c){ return '<label class="tt-chk"><input type="checkbox" id="'+id+'"'+(c?' checked':'')+'> '+l+'</label>'; }
    function gen(){
      var sets=''; var U='ABCDEFGHIJKLMNOPQRSTUVWXYZ',L='abcdefghijklmnopqrstuvwxyz',N='0123456789',S='!@#$%^&*()-_=+[]{};:,.?';
      if($('#up').checked)sets+=U; if($('#low').checked)sets+=L; if($('#num').checked)sets+=N; if($('#sym').checked)sets+=S;
      if($('#amb').checked)sets=sets.replace(/[0O1lI]/g,'');
      if(!sets){ $('#out').innerHTML='<span style="color:var(--muted);font-size:14px">Select at least one character type</span>'; return; }
      var n=parseInt($('#len').value),arr=new Uint32Array(n),out=''; crypto.getRandomValues(arr);
      for(var i=0;i<n;i++) out+=sets[arr[i]%sets.length];
      $('#out').innerHTML=esc(out)+cbtn(); wireCopy($('#out [data-copy]'),function(){return out;});
      var types=[$('#up').checked,$('#low').checked,$('#num').checked,$('#sym').checked].filter(Boolean).length;
      var bits=n*Math.log2(sets.length), pct=Math.min(100,bits/80*100);
      $('#bar').style.width=pct+'%'; $('#bar').style.background=pct<40?'#dc2626':pct<70?'#f59e0b':'#16a34a';
      $('#lbl').textContent=pct<40?'Weak':pct<70?'Good':'Strong'; $('#lbl').style.color=$('#bar').style.background;
    }
    $('#len').addEventListener('input',function(){$('#lenL').textContent=this.value;gen();});
    $all('#up,#low,#num,#sym,#amb').forEach(function(x){x.addEventListener('change',gen);});
    $('#go').onclick=gen; gen();
  };

  tools['password-strength'] = function(){
    h('<div class="cl-f full"><label>Enter a password to test</label><div class="in"><input id="pw" placeholder="Type a password..."></div></div>'
      +'<div style="height:10px;border-radius:6px;background:#eee;margin:14px 0;overflow:hidden"><div id="bar" style="height:100%;width:0;transition:.25s"></div></div>'
      +'<div class="cl-big"><div class="l">Strength</div><div class="v" id="lbl" style="font-size:26px">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Length</div><div class="v" id="ln">0</div></div>'
      +'<div class="cl-row"><div class="l">Entropy</div><div class="v" id="ent">0 bits</div></div>'
      +'<div class="cl-row"><div class="l">Est. crack time</div><div class="v" id="ct" style="font-size:14px">instant</div></div></div>');
    function fmt(sec){ if(sec<1)return 'instant'; var u=[['seconds',60],['minutes',60],['hours',24],['days',365],['years',0]],v=sec; for(var i=0;i<u.length;i++){ if(u[i][1]===0||v<u[i][1])return Math.round(v)+' '+u[i][0]; v/=u[i][1]; } return '>1000 years'; }
    $('#pw').addEventListener('input',function(){
      var p=this.value,pool=0;
      if(/[a-z]/.test(p))pool+=26; if(/[A-Z]/.test(p))pool+=26; if(/[0-9]/.test(p))pool+=10; if(/[^a-zA-Z0-9]/.test(p))pool+=32;
      var bits=p.length?p.length*Math.log2(pool||1):0;
      var guesses=Math.pow(2,bits)/2, sec=guesses/1e10;
      var pct=Math.min(100,bits/80*100);
      $('#bar').style.width=pct+'%'; var col=bits<28?'#dc2626':bits<50?'#f59e0b':bits<70?'#eab308':'#16a34a';
      $('#bar').style.background=col;
      $('#lbl').textContent=bits<28?'Very weak':bits<50?'Weak':bits<70?'Good':'Strong'; $('#lbl').style.color=col;
      $('#ln').textContent=p.length; $('#ent').textContent=Math.round(bits)+' bits'; $('#ct').textContent=fmt(sec);
    });
  };

  tools['random-picker'] = function(){
    h('<div class="cl-f full"><label>Enter items — one per line</label><textarea class="tt-ta" id="src" placeholder="Alice\nBob\nCharlie"></textarea></div>'
      +'<div class="tt-row mt"><button class="tt-btn" id="pick"><i class="fa-solid fa-wand-magic-sparkles"></i> Pick a winner</button>'
      +'<button class="tt-btn ghost" id="shuf"><i class="fa-solid fa-shuffle"></i> Shuffle all</button></div>'
      +'<div class="cl-big" id="win" style="display:none;text-align:center"><div class="l">Winner</div><div class="v" id="winv"></div></div>'
      +'<div class="tt-out" id="out" data-ph="Shuffled list appears here" style="margin-top:12px;display:none"></div>');
    function items(){ return $('#src').value.split('\n').map(function(x){return x.trim();}).filter(Boolean); }
    $('#pick').onclick=function(){ var a=items(); if(!a.length)return; var w=a[Math.floor(Math.random()*a.length)]; $('#win').style.display=''; $('#winv').textContent=w; };
    $('#shuf').onclick=function(){ var a=items(); for(var i=a.length-1;i>0;i--){var j=Math.floor(Math.random()*(i+1));var t=a[i];a[i]=a[j];a[j]=t;} var txt=a.join('\n'); $('#out').style.display=''; $('#out').innerHTML=esc(txt)+cbtn(); wireCopy($('#out [data-copy]'),function(){return txt;}); };
  };

  tools['random-number'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Minimum</label><div class="in"><input type="number" id="min" value="1"></div></div>'
      +'<div class="cl-f"><label>Maximum</label><div class="in"><input type="number" id="max" value="100"></div></div>'
      +'<div class="cl-f"><label>How many</label><div class="in"><input type="number" id="cnt" value="1" min="1" max="1000"></div></div></div>'
      +'<div class="tt-opts"><label class="tt-chk"><input type="checkbox" id="uniq"> No duplicates</label></div>'
      +'<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-dice-five"></i> Generate</button><span class="tt-err" id="err"></span></div>'
      +'<div class="tt-out mono" id="out" data-ph="Numbers appear here" style="margin-top:12px"></div>');
    $('#go').onclick=function(){
      $('#err').textContent='';
      var mn=parseInt($('#min').value),mx=parseInt($('#max').value),n=Math.max(1,Math.min(1000,parseInt($('#cnt').value)||1)),uniq=$('#uniq').checked;
      if(mn>mx){ var t=mn;mn=mx;mx=t; }
      if(uniq && n>(mx-mn+1)){ $('#err').textContent='Range too small for '+n+' unique numbers.'; return; }
      var out=[]; if(uniq){ var pool=[]; for(var v=mn;v<=mx;v++)pool.push(v); for(var i=0;i<n;i++){ var j=Math.floor(Math.random()*pool.length); out.push(pool.splice(j,1)[0]); } } else { for(var k=0;k<n;k++) out.push(mn+Math.floor(Math.random()*(mx-mn+1))); }
      var txt=out.join(', '); $('#out').innerHTML=esc(txt)+cbtn(); wireCopy($('#out [data-copy]'),function(){return txt;});
    };
    $('#go').click();
  };

  tools['dice-coin'] = function(){
    h('<div class="cl-tabs"><button class="cl-tab on" data-t="dice">Dice</button><button class="cl-tab" data-t="coin">Coin</button></div>'
      +'<div id="pane"></div>');
    function dice(){ $('#pane').innerHTML='<div class="cl-grid"><div class="cl-f"><label>Number of dice</label><div class="in"><input type="number" id="nd" value="2" min="1" max="10"></div></div><div class="cl-f"><label>Sides</label><div class="in"><select id="sd"><option>6</option><option>4</option><option>8</option><option>10</option><option>12</option><option>20</option></select></div></div></div><div class="tt-row"><button class="tt-btn" id="roll"><i class="fa-solid fa-dice"></i> Roll</button></div><div class="tt-clock" id="res" style="margin-top:14px">—</div>';
      $('#roll').onclick=function(){ var n=Math.max(1,Math.min(10,parseInt($('#nd').value)||1)),s=parseInt($('#sd').value),vals=[],sum=0; for(var i=0;i<n;i++){var v=1+Math.floor(Math.random()*s);vals.push(v);sum+=v;} $('#res').innerHTML=vals.join(' + ')+(n>1?' = '+sum:''); }; $('#roll').click();
    }
    function coin(){ $('#pane').innerHTML='<div class="tt-row"><button class="tt-btn" id="flip"><i class="fa-solid fa-coins"></i> Flip coin</button></div><div class="tt-clock" id="res" style="margin-top:16px">—</div>';
      $('#flip').onclick=function(){ $('#res').textContent=Math.random()<0.5?'Heads':'Tails'; }; $('#flip').click();
    }
    $all('.cl-tab').forEach(function(b){ b.onclick=function(){ $all('.cl-tab').forEach(function(x){x.classList.remove('on');}); b.classList.add('on'); b.dataset.t==='dice'?dice():coin(); }; });
    dice();
  };

  function timerUI(label){ return '<div class="tt-clock" id="disp">'+label+'</div>'
      +'<div class="tt-row" style="justify-content:center;margin-top:18px"><button class="tt-btn" id="start"><i class="fa-solid fa-play"></i> Start</button>'
      +'<button class="tt-btn ghost" id="pause" style="display:none"><i class="fa-solid fa-pause"></i> Pause</button>'
      +'<button class="tt-btn ghost" id="reset"><i class="fa-solid fa-rotate-left"></i> Reset</button></div>'; }

  tools['countdown-timer'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Minutes</label><div class="in"><input type="number" id="mm" value="5" min="0" max="999"></div></div>'
      +'<div class="cl-f"><label>Seconds</label><div class="in"><input type="number" id="ss" value="0" min="0" max="59"></div></div></div>'
      +timerUI('05:00'));
    var left=300, iv=null, running=false;
    function render(){ var m=Math.floor(left/60),s=left%60; $('#disp').textContent=pad(m)+':'+pad(s); }
    function set(){ left=(parseInt($('#mm').value)||0)*60+(parseInt($('#ss').value)||0); render(); }
    $all('#mm,#ss').forEach(function(x){x.addEventListener('input',function(){ if(!running)set(); });});
    $('#start').onclick=function(){ if(running)return; if(left<=0)set(); if(left<=0)return; running=true; $('#start').style.display='none'; $('#pause').style.display=''; iv=setInterval(function(){ left--; render(); if(left<=0){ clearInterval(iv); running=false; beep(); $('#start').style.display=''; $('#pause').style.display='none'; $('#disp').style.color='#16a34a'; } },1000); };
    $('#pause').onclick=function(){ running=false; clearInterval(iv); $('#start').style.display=''; $('#pause').style.display='none'; };
    $('#reset').onclick=function(){ running=false; clearInterval(iv); $('#disp').style.color=''; set(); };
    set();
  };

  tools['pomodoro-timer'] = function(){
    h('<div class="cl-tabs" style="justify-content:center"><button class="cl-tab on" data-m="25">Focus 25</button><button class="cl-tab" data-m="5">Break 5</button><button class="cl-tab" data-m="15">Long 15</button></div>'
      +timerUI('25:00'));
    var total=25*60,left=total,iv=null,running=false;
    function render(){ $('#disp').textContent=pad(Math.floor(left/60))+':'+pad(left%60); }
    $all('.cl-tab').forEach(function(b){ b.onclick=function(){ $all('.cl-tab').forEach(function(x){x.classList.remove('on');}); b.classList.add('on'); clearInterval(iv);running=false;$('#start').style.display='';$('#pause').style.display='none';$('#disp').style.color=''; total=parseInt(b.dataset.m)*60; left=total; render(); }; });
    $('#start').onclick=function(){ if(running)return; running=true; $('#start').style.display='none'; $('#pause').style.display=''; iv=setInterval(function(){ left--; render(); if(left<=0){ clearInterval(iv); running=false; beep(); $('#disp').style.color='#16a34a'; $('#start').style.display=''; $('#pause').style.display='none'; } },1000); };
    $('#pause').onclick=function(){ running=false; clearInterval(iv); $('#start').style.display='';$('#pause').style.display='none'; };
    $('#reset').onclick=function(){ running=false; clearInterval(iv); left=total; $('#disp').style.color=''; render(); $('#start').style.display='';$('#pause').style.display='none'; };
    render();
  };

  tools['stopwatch'] = function(){
    h(timerUI('00:00.0')+'<div class="tt-row" style="justify-content:center;margin-top:10px"><button class="tt-btn ghost sm" id="lap"><i class="fa-solid fa-flag"></i> Lap</button></div>'
      +'<div id="laps" style="margin-top:14px"></div>');
    var t0=0,acc=0,iv=null,running=false,laps=[];
    function now(){ return acc+(running?(Date.now()-t0):0); }
    function fmt(ms){ var m=Math.floor(ms/60000),s=Math.floor(ms/1000)%60,d=Math.floor(ms/100)%10; return pad(m)+':'+pad(s)+'.'+d; }
    function render(){ $('#disp').textContent=fmt(now()); }
    $('#start').onclick=function(){ if(running)return; running=true; t0=Date.now(); iv=setInterval(render,100); $('#start').style.display='none'; $('#pause').style.display=''; };
    $('#pause').onclick=function(){ if(!running)return; running=false; acc+=Date.now()-t0; clearInterval(iv); render(); $('#start').style.display=''; $('#pause').style.display='none'; };
    $('#reset').onclick=function(){ running=false; clearInterval(iv); acc=0; laps=[]; render(); $('#laps').innerHTML=''; $('#start').style.display='';$('#pause').style.display='none'; };
    $('#lap').onclick=function(){ laps.unshift(now()); $('#laps').innerHTML=laps.map(function(l,i){ return '<div class="cl-row" style="display:flex;justify-content:space-between;margin-bottom:6px"><span class="l">Lap '+(laps.length-i)+'</span><span class="v" style="font-size:15px">'+fmt(l)+'</span></div>'; }).join(''); };
    render();
  };

  (tools[slug] || function(){ h('<p style="color:var(--muted)">This tool is being set up.</p>'); })();
})();
