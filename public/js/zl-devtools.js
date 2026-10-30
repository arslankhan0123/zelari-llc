/* Zelari Developer Tools — browser-side engine */
(function () {
  'use strict';
  var ui = document.getElementById('toolUI'); if (!ui) return;
  var slug = (window.ZTOOL || {}).slug;
  function h(s){ ui.innerHTML = s; }
  function $(s){ return ui.querySelector(s); }
  function $all(s){ return Array.prototype.slice.call(ui.querySelectorAll(s)); }
  function esc(s){ var d=document.createElement('div'); d.textContent=s==null?'':s; return d.innerHTML; }
  function cbtn(){ return '<button type="button" class="tt-copy" data-copy><i class="fa-solid fa-copy"></i> Copy</button>'; }
  function wireCopy(btn, fn){ if(!btn) return; btn.addEventListener('click', function(){ navigator.clipboard.writeText(fn()).then(function(){ var o=btn.innerHTML; btn.innerHTML='<i class="fa-solid fa-check"></i> Copied'; setTimeout(function(){btn.innerHTML=o;},1400); }).catch(function(){}); }); }

  /* compact MD5 (public domain, Blueimp) */
  function md5(str){
    function rl(n,c){return(n<<c)|(n>>>(32-c));}
    function au(x,y){var l=(x&0xFFFF)+(y&0xFFFF);return(((x>>16)+(y>>16)+(l>>16))<<16)|(l&0xFFFF);}
    function cmn(q,a,b,x,s,t){return au(rl(au(au(a,q),au(x,t)),s),b);}
    function ff(a,b,c,d,x,s,t){return cmn((b&c)|(~b&d),a,b,x,s,t);}
    function gg(a,b,c,d,x,s,t){return cmn((b&d)|(c&~d),a,b,x,s,t);}
    function hh(a,b,c,d,x,s,t){return cmn(b^c^d,a,b,x,s,t);}
    function ii(a,b,c,d,x,s,t){return cmn(c^(b|~d),a,b,x,s,t);}
    function tb(s){var n=s.length,b=[],i;for(i=0;i<n*8;i+=8)b[i>>5]|=(s.charCodeAt(i/8)&0xFF)<<(i%32);return b;}
    function bh(b){var h='0123456789abcdef',s='',i;for(i=0;i<b.length*4;i++)s+=h.charAt((b[i>>2]>>((i%4)*8+4))&0xF)+h.charAt((b[i>>2]>>((i%4)*8))&0xF);return s;}
    function u8(s){return unescape(encodeURIComponent(s));}
    var x=tb(u8(str)),len=u8(str).length*8;
    x[len>>5]|=0x80<<(len%32);x[(((len+64)>>>9)<<4)+14]=len;
    var a=1732584193,b=-271733879,c=-1732584194,d=271733878,i;
    for(i=0;i<x.length;i+=16){var oa=a,ob=b,oc=c,od=d;
      a=ff(a,b,c,d,x[i],7,-680876936);d=ff(d,a,b,c,x[i+1],12,-389564586);c=ff(c,d,a,b,x[i+2],17,606105819);b=ff(b,c,d,a,x[i+3],22,-1044525330);
      a=ff(a,b,c,d,x[i+4],7,-176418897);d=ff(d,a,b,c,x[i+5],12,1200080426);c=ff(c,d,a,b,x[i+6],17,-1473231341);b=ff(b,c,d,a,x[i+7],22,-45705983);
      a=ff(a,b,c,d,x[i+8],7,1770035416);d=ff(d,a,b,c,x[i+9],12,-1958414417);c=ff(c,d,a,b,x[i+10],17,-42063);b=ff(b,c,d,a,x[i+11],22,-1990404162);
      a=ff(a,b,c,d,x[i+12],7,1804603682);d=ff(d,a,b,c,x[i+13],12,-40341101);c=ff(c,d,a,b,x[i+14],17,-1502002290);b=ff(b,c,d,a,x[i+15],22,1236535329);
      a=gg(a,b,c,d,x[i+1],5,-165796510);d=gg(d,a,b,c,x[i+6],9,-1069501632);c=gg(c,d,a,b,x[i+11],14,643717713);b=gg(b,c,d,a,x[i],20,-373897302);
      a=gg(a,b,c,d,x[i+5],5,-701558691);d=gg(d,a,b,c,x[i+10],9,38016083);c=gg(c,d,a,b,x[i+15],14,-660478335);b=gg(b,c,d,a,x[i+4],20,-405537848);
      a=gg(a,b,c,d,x[i+9],5,568446438);d=gg(d,a,b,c,x[i+14],9,-1019803690);c=gg(c,d,a,b,x[i+3],14,-187363961);b=gg(b,c,d,a,x[i+8],20,1163531501);
      a=gg(a,b,c,d,x[i+13],5,-1444681467);d=gg(d,a,b,c,x[i+2],9,-51403784);c=gg(c,d,a,b,x[i+7],14,1735328473);b=gg(b,c,d,a,x[i+12],20,-1926607734);
      a=hh(a,b,c,d,x[i+5],4,-378558);d=hh(d,a,b,c,x[i+8],11,-2022574463);c=hh(c,d,a,b,x[i+11],16,1839030562);b=hh(b,c,d,a,x[i+14],23,-35309556);
      a=hh(a,b,c,d,x[i+1],4,-1530992060);d=hh(d,a,b,c,x[i+4],11,1272893353);c=hh(c,d,a,b,x[i+7],16,-155497632);b=hh(b,c,d,a,x[i+10],23,-1094730640);
      a=hh(a,b,c,d,x[i+13],4,681279174);d=hh(d,a,b,c,x[i],11,-358537222);c=hh(c,d,a,b,x[i+3],16,-722521979);b=hh(b,c,d,a,x[i+6],23,76029189);
      a=hh(a,b,c,d,x[i+9],4,-640364487);d=hh(d,a,b,c,x[i+12],11,-421815835);c=hh(c,d,a,b,x[i+15],16,530742520);b=hh(b,c,d,a,x[i+2],23,-995338651);
      a=ii(a,b,c,d,x[i],6,-198630844);d=ii(d,a,b,c,x[i+7],10,1126891415);c=ii(c,d,a,b,x[i+14],15,-1416354905);b=ii(b,c,d,a,x[i+5],21,-57434055);
      a=ii(a,b,c,d,x[i+12],6,1700485571);d=ii(d,a,b,c,x[i+3],10,-1894986606);c=ii(c,d,a,b,x[i+10],15,-1051523);b=ii(b,c,d,a,x[i+1],21,-2054922799);
      a=ii(a,b,c,d,x[i+8],6,1873313359);d=ii(d,a,b,c,x[i+15],10,-30611744);c=ii(c,d,a,b,x[i+6],15,-1560198380);b=ii(b,c,d,a,x[i+13],21,1309151649);
      a=ii(a,b,c,d,x[i+4],6,-145523070);d=ii(d,a,b,c,x[i+11],10,-1120210379);c=ii(c,d,a,b,x[i+2],15,718787259);b=ii(b,c,d,a,x[i+9],21,-343485551);
      a=au(a,oa);b=au(b,ob);c=au(c,oc);d=au(d,od);
    }
    return bh([a,b,c,d]);
  }

  var tools = {};

  tools['json-formatter'] = function(){
    h('<textarea class="tt-ta mono" id="src" placeholder=\'{"paste":"your JSON here"}\'></textarea>'
      +'<div class="tt-row mt"><button class="tt-btn" id="beau"><i class="fa-solid fa-wand-magic-sparkles"></i> Beautify</button>'
      +'<button class="tt-btn ghost" id="min">Minify</button><button class="tt-btn ghost" id="val">Validate</button>'
      +'<span class="tt-ok" id="msg"></span><span class="tt-err" id="err"></span></div>'
      +'<div class="tt-out mono" id="out" data-ph="Formatted JSON appears here" style="margin-top:14px"></div>');
    var src=$('#src');
    function parse(){ $('#err').textContent='';$('#msg').textContent=''; try{ return {ok:true,v:JSON.parse(src.value)}; }catch(e){ $('#err').textContent='Invalid JSON: '+e.message; return {ok:false}; } }
    function set(t){ $('#out').innerHTML=esc(t)+cbtn(); wireCopy($('#out [data-copy]'),function(){return t;}); }
    $('#beau').onclick=function(){ var r=parse(); if(r.ok){ set(JSON.stringify(r.v,null,2)); $('#msg').textContent='Valid JSON'; } };
    $('#min').onclick=function(){ var r=parse(); if(r.ok){ set(JSON.stringify(r.v)); $('#msg').textContent='Minified'; } };
    $('#val').onclick=function(){ var r=parse(); if(r.ok){ $('#msg').textContent='✓ Valid JSON'; } };
  };

  tools['css-minifier'] = function(){
    h('<textarea class="tt-ta mono" id="src" placeholder="Paste your CSS here..."></textarea>'
      +'<div class="tt-row mt"><button class="tt-btn" id="min"><i class="fa-solid fa-compress"></i> Minify</button>'
      +'<button class="tt-btn ghost" id="beau">Beautify</button><span class="tt-ok" id="msg"></span></div>'
      +'<div class="tt-out mono" id="out" data-ph="Result appears here" style="margin-top:14px"></div>');
    var src=$('#src');
    function set(t,m){ $('#out').innerHTML=esc(t)+cbtn(); wireCopy($('#out [data-copy]'),function(){return t;}); $('#msg').textContent=m; }
    $('#min').onclick=function(){ var c=src.value.replace(/\/\*[\s\S]*?\*\//g,'').replace(/\s*([{}:;,>])\s*/g,'$1').replace(/;}/g,'}').replace(/\s+/g,' ').trim(); var saved=src.value.length?Math.round((1-c.length/src.value.length)*100):0; set(c,'Minified — '+saved+'% smaller'); };
    $('#beau').onclick=function(){ var c=src.value.replace(/\/\*[\s\S]*?\*\//g,'').replace(/\s*{\s*/g,' {\n  ').replace(/;\s*/g,';\n  ').replace(/\s*}\s*/g,'\n}\n\n').replace(/\n\s*\n\s*\n/g,'\n\n').trim(); set(c,'Beautified'); };
  };

  tools['base64'] = function(){
    h('<textarea class="tt-ta" id="src" placeholder="Type text or paste Base64..."></textarea>'
      +'<div class="tt-row mt"><button class="tt-btn" id="enc"><i class="fa-solid fa-lock"></i> Encode</button>'
      +'<button class="tt-btn ghost" id="dec"><i class="fa-solid fa-lock-open"></i> Decode</button><span class="tt-err" id="err"></span></div>'
      +'<div class="tt-out mono" id="out" data-ph="Result appears here" style="margin-top:14px"></div>');
    var src=$('#src');
    function set(t){ $('#out').innerHTML=esc(t)+cbtn(); wireCopy($('#out [data-copy]'),function(){return t;}); }
    $('#enc').onclick=function(){ $('#err').textContent=''; try{ set(btoa(unescape(encodeURIComponent(src.value)))); }catch(e){ $('#err').textContent='Could not encode.'; } };
    $('#dec').onclick=function(){ $('#err').textContent=''; try{ set(decodeURIComponent(escape(atob(src.value.trim())))); }catch(e){ $('#err').textContent='Not valid Base64.'; } };
  };

  tools['url-encode'] = function(){
    h('<textarea class="tt-ta" id="src" placeholder="Type a URL or text..."></textarea>'
      +'<div class="tt-opts"><label class="tt-chk"><input type="checkbox" id="whole"> Encode whole URL (keep / : ?)</label></div>'
      +'<div class="tt-row"><button class="tt-btn" id="enc"><i class="fa-solid fa-lock"></i> Encode</button>'
      +'<button class="tt-btn ghost" id="dec">Decode</button><span class="tt-err" id="err"></span></div>'
      +'<div class="tt-out mono" id="out" data-ph="Result appears here" style="margin-top:14px"></div>');
    var src=$('#src');
    function set(t){ $('#out').innerHTML=esc(t)+cbtn(); wireCopy($('#out [data-copy]'),function(){return t;}); }
    $('#enc').onclick=function(){ $('#err').textContent=''; set($('#whole').checked?encodeURI(src.value):encodeURIComponent(src.value)); };
    $('#dec').onclick=function(){ $('#err').textContent=''; try{ set(decodeURIComponent(src.value)); }catch(e){ $('#err').textContent='Cannot decode this text.'; } };
  };

  tools['hash-generator'] = function(){
    h('<textarea class="tt-ta" id="src" placeholder="Type text to hash..."></textarea>'
      +'<div class="tt-row mt"><button class="tt-btn" id="go"><i class="fa-solid fa-fingerprint"></i> Generate hashes</button></div>'
      +'<div id="res" style="margin-top:14px"></div>');
    async function sha(algo,msg){ var buf=await crypto.subtle.digest(algo,new TextEncoder().encode(msg)); return Array.prototype.map.call(new Uint8Array(buf),function(b){return b.toString(16).padStart(2,'0');}).join(''); }
    $('#go').onclick=async function(){
      var t=$('#src').value;
      var rows=[['MD5',md5(t)]];
      try{ rows.push(['SHA-1',await sha('SHA-1',t)],['SHA-256',await sha('SHA-256',t)],['SHA-512',await sha('SHA-512',t)]); }catch(e){}
      $('#res').innerHTML=rows.map(function(r){ return '<div class="cl-f full" style="margin-bottom:10px"><label>'+r[0]+'</label><div class="tt-out mono" style="min-height:auto;padding:11px 44px 11px 13px">'+esc(r[1])+'<button type="button" class="tt-copy" data-h="'+esc(r[1])+'"><i class="fa-solid fa-copy"></i></button></div></div>'; }).join('');
      $all('[data-h]').forEach(function(b){ b.onclick=function(){ navigator.clipboard.writeText(b.dataset.h); var o=b.innerHTML;b.innerHTML='<i class="fa-solid fa-check"></i>';setTimeout(function(){b.innerHTML=o;},1200); }; });
    };
  };

  tools['uuid-generator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>How many</label><div class="in"><input type="number" id="cnt" value="5" min="1" max="100"></div></div>'
      +'<div class="cl-f"><label>Format</label><div class="in"><select id="fmt"><option value="low">lowercase</option><option value="up">UPPERCASE</option><option value="nod">no dashes</option></select></div></div></div>'
      +'<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-hashtag"></i> Generate</button></div>'
      +'<div class="tt-out mono" id="out" data-ph="UUIDs appear here" style="margin-top:14px"></div>');
    function uuid(){ return (crypto.randomUUID)?crypto.randomUUID():'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,function(c){var r=Math.random()*16|0,v=c==='x'?r:(r&0x3|0x8);return v.toString(16);}); }
    $('#go').onclick=function(){
      var n=Math.max(1,Math.min(100,parseInt($('#cnt').value)||1)),f=$('#fmt').value,a=[];
      for(var i=0;i<n;i++){ var u=uuid(); if(f==='up')u=u.toUpperCase(); if(f==='nod')u=u.replace(/-/g,''); a.push(u); }
      var t=a.join('\n'); $('#out').innerHTML=esc(t)+cbtn(); wireCopy($('#out [data-copy]'),function(){return t;});
    };
    $('#go').click();
  };

  tools['regex-tester'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Regular expression</label><div class="in"><input class="mono" id="pat" placeholder="\\d{3}-\\d{4}"></div></div>'
      +'<div class="cl-f"><label>Flags</label><div class="in"><input id="flags" value="g" placeholder="g i m"></div></div></div>'
      +'<div class="cl-f full"><label>Test text</label><textarea class="tt-ta" id="src" placeholder="Paste text to search..."></textarea></div>'
      +'<div class="tt-ok" id="msg" style="margin-top:8px"></div><span class="tt-err" id="err"></span>'
      +'<div class="tt-out" id="out" data-ph="Matches are highlighted here" style="margin-top:10px"></div>');
    function run(){
      $('#err').textContent='';
      var pat=$('#pat').value, fl=$('#flags').value, txt=$('#src').value;
      if(!pat){ $('#out').innerHTML=esc(txt); $('#msg').textContent=''; return; }
      var re; try{ re=new RegExp(pat, fl.indexOf('g')<0?fl+'g':fl); }catch(e){ $('#err').textContent='Invalid regex: '+e.message; return; }
      var count=0, html=txt.replace(re,function(m){ count++; return '\u0001'+m+'\u0002'; });
      html=esc(html).replace(/\u0001/g,'<mark style="background:var(--p-soft);color:var(--p);font-weight:700;border-radius:3px;padding:0 2px">').replace(/\u0002/g,'</mark>');
      $('#out').innerHTML=html||'<span style="color:var(--muted)">No text</span>';
      $('#msg').textContent=count+' match'+(count===1?'':'es')+' found';
    }
    $all('#pat,#flags,#src').forEach(function(x){x.addEventListener('input',run);});
  };

  tools['timestamp-converter'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Unix timestamp (s or ms)</label><div class="in"><input id="ts" placeholder="1700000000"></div></div>'
      +'<div class="cl-f"><label>or pick a date/time</label><div class="in"><input type="datetime-local" id="dt"></div></div></div>'
      +'<div class="tt-row"><button class="tt-btn ghost sm" id="now"><i class="fa-solid fa-clock"></i> Use current time</button></div>'
      +'<div id="res" class="cl-res" style="display:none"><div class="cl-rows">'
      +'<div class="cl-row"><div class="l">Unix (seconds)</div><div class="v" id="rS">—</div></div>'
      +'<div class="cl-row"><div class="l">Unix (ms)</div><div class="v" id="rM">—</div></div>'
      +'<div class="cl-row"><div class="l">UTC</div><div class="v" id="rU" style="font-size:13px">—</div></div>'
      +'<div class="cl-row"><div class="l">Local</div><div class="v" id="rL" style="font-size:13px">—</div></div></div></div>');
    function show(d){ if(isNaN(d)) return; var s=Math.floor(d.getTime()/1000); $('#res').style.display=''; $('#rS').textContent=s; $('#rM').textContent=d.getTime(); $('#rU').textContent=d.toUTCString(); $('#rL').textContent=d.toString().replace(/ \(.*\)$/,''); }
    $('#ts').addEventListener('input',function(){ var v=this.value.trim(); if(!v){return;} var n=Number(v); if(isNaN(n))return; if(v.length<=10)n*=1000; show(new Date(n)); });
    $('#dt').addEventListener('input',function(){ if(this.value) show(new Date(this.value)); });
    $('#now').onclick=function(){ var d=new Date(); $('#ts').value=Math.floor(d.getTime()/1000); show(d); };
  };

  (tools[slug] || function(){ h('<p style="color:var(--muted)">This tool is being set up.</p>'); })();
})();
