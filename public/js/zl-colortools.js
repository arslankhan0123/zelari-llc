/* Zelari Color Tools — browser-side engine */
(function () {
  'use strict';
  var ui=document.getElementById('toolUI'); if(!ui) return;
  var slug=(window.ZTOOL||{}).slug;
  function h(s){ ui.innerHTML=s; }
  function $(s){ return ui.querySelector(s); }
  function $all(s){ return Array.prototype.slice.call(ui.querySelectorAll(s)); }
  function esc(s){ var d=document.createElement('div'); d.textContent=s==null?'':s; return d.innerHTML; }
  function cbtn(v){ return '<button type="button" class="tt-copy" data-c="'+esc(v)+'"><i class="fa-solid fa-copy"></i></button>'; }
  function wireC(){ $all('[data-c]').forEach(function(b){ b.onclick=function(){ navigator.clipboard.writeText(b.dataset.c); var o=b.innerHTML;b.innerHTML='<i class="fa-solid fa-check"></i>';setTimeout(function(){b.innerHTML=o;},1100); }; }); }

  function hex2rgb(h){ h=h.replace('#',''); if(h.length===3)h=h.split('').map(function(c){return c+c;}).join(''); var n=parseInt(h,16); return {r:(n>>16)&255,g:(n>>8)&255,b:n&255}; }
  function rgb2hex(r,g,b){ return '#'+[r,g,b].map(function(x){return ('0'+Math.round(x).toString(16)).slice(-2);}).join(''); }
  function rgb2hsl(r,g,b){ r/=255;g/=255;b/=255; var mx=Math.max(r,g,b),mn=Math.min(r,g,b),hh,s,l=(mx+mn)/2; if(mx===mn){hh=s=0;}else{var d=mx-mn;s=l>0.5?d/(2-mx-mn):d/(mx+mn);switch(mx){case r:hh=(g-b)/d+(g<b?6:0);break;case g:hh=(b-r)/d+2;break;default:hh=(r-g)/d+4;}hh/=6;} return {h:Math.round(hh*360),s:Math.round(s*100),l:Math.round(l*100)}; }
  function hsl2rgb(h,s,l){ h/=360;s/=100;l/=100; var r,g,b; if(s===0){r=g=b=l;}else{ var hue2rgb=function(p,q,t){if(t<0)t+=1;if(t>1)t-=1;if(t<1/6)return p+(q-p)*6*t;if(t<1/2)return q;if(t<2/3)return p+(q-p)*(2/3-t)*6;return p;}; var q=l<0.5?l*(1+s):l+s-l*s,p=2*l-q; r=hue2rgb(p,q,h+1/3);g=hue2rgb(p,q,h);b=hue2rgb(p,q,h-1/3);} return {r:r*255,g:g*255,b:b*255}; }

  var tools={};

  tools['color-converter'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Pick a color</label><div class="in"><input type="color" id="pick" value="#6d28d9" style="height:48px"></div></div>'
      +'<div class="cl-f"><label>HEX</label><div class="in"><input id="hx" value="#6d28d9"></div></div></div>'
      +'<div class="tt-sw" id="sw" style="margin:4px 0 16px"></div>'
      +'<div class="cl-rows">'
      +'<div class="cl-row"><div class="l">HEX</div><div class="v" style="font-size:15px" id="oHex">—</div></div>'
      +'<div class="cl-row"><div class="l">RGB</div><div class="v" style="font-size:15px" id="oRgb">—</div></div>'
      +'<div class="cl-row"><div class="l">HSL</div><div class="v" style="font-size:15px" id="oHsl">—</div></div></div>');
    function upd(hex){ try{ var c=hex2rgb(hex); var hsl=rgb2hsl(c.r,c.g,c.b); var HEX=rgb2hex(c.r,c.g,c.b); var RGB='rgb('+c.r+', '+c.g+', '+c.b+')'; var HSL='hsl('+hsl.h+', '+hsl.s+'%, '+hsl.l+'%)'; $('#sw').style.background=HEX; $('#oHex').innerHTML=HEX+' '+cbtn(HEX); $('#oRgb').innerHTML=RGB+' '+cbtn(RGB); $('#oHsl').innerHTML=HSL+' '+cbtn(HSL); wireC(); }catch(e){} }
    $('#pick').addEventListener('input',function(){ $('#hx').value=this.value; upd(this.value); });
    $('#hx').addEventListener('input',function(){ var v=this.value.trim(); if(/^#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/.test(v)){ if(v[0]!=='#')v='#'+v; $('#pick').value=hex2rgb(v)&&rgb2hex(hex2rgb(v).r,hex2rgb(v).g,hex2rgb(v).b); upd(v); } });
    upd('#6d28d9');
  };

  tools['palette-generator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Base color</label><div class="in"><input type="color" id="base" value="#6d28d9" style="height:48px"></div></div>'
      +'<div class="cl-f"><label>Type</label><div class="in"><select id="type"><option value="shades">Shades &amp; Tints</option><option value="analogous">Analogous</option><option value="complementary">Complementary</option><option value="triadic">Triadic</option></select></div></div></div>'
      +'<div class="tt-pal" id="pal" style="margin-top:8px"></div>');
    function build(){
      var c=hex2rgb($('#base').value), hsl=rgb2hsl(c.r,c.g,c.b), t=$('#type').value, cols=[];
      if(t==='shades'){ for(var i=9;i>=1;i--){ var r=hsl2rgb(hsl.h,hsl.s,Math.min(95,i*10)); cols.push(rgb2hex(r.r,r.g,r.b)); } }
      else if(t==='analogous'){ [-60,-30,0,30,60].forEach(function(d){ var r=hsl2rgb((hsl.h+d+360)%360,hsl.s,hsl.l); cols.push(rgb2hex(r.r,r.g,r.b)); }); }
      else if(t==='complementary'){ [0,180].forEach(function(d){ for(var k=-1;k<=1;k++){ var r=hsl2rgb((hsl.h+d+360)%360,hsl.s,Math.min(90,Math.max(15,hsl.l+k*18))); cols.push(rgb2hex(r.r,r.g,r.b)); } }); }
      else { [0,120,240].forEach(function(d){ var r=hsl2rgb((hsl.h+d)%360,hsl.s,hsl.l); cols.push(rgb2hex(r.r,r.g,r.b)); }); }
      $('#pal').innerHTML=cols.map(function(hex){ return '<div class="c" data-c="'+hex+'"><div class="box" style="background:'+hex+'"></div><div class="hx">'+hex+'</div></div>'; }).join('');
      $all('#pal .c').forEach(function(b){ b.onclick=function(){ navigator.clipboard.writeText(b.dataset.c); var hx=b.querySelector('.hx'),o=hx.textContent; hx.textContent='Copied!'; setTimeout(function(){hx.textContent=o;},1000); }; });
    }
    $('#base').addEventListener('input',build); $('#type').addEventListener('change',build); build();
  };

  tools['gradient-generator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Color 1</label><div class="in"><input type="color" id="c1" value="#6d28d9" style="height:46px"></div></div>'
      +'<div class="cl-f"><label>Color 2</label><div class="in"><input type="color" id="c2" value="#e5322d" style="height:46px"></div></div></div>'
      +'<div class="cl-f full"><label>Angle: <span id="angL">90</span>&deg;</label><input type="range" class="tt-range" id="ang" min="0" max="360" value="90"></div>'
      +'<div class="tt-sw" id="prev" style="height:120px;margin:12px 0"></div>'
      +'<div class="tt-out mono" id="code" style="min-height:auto"></div>');
    function upd(){ var a=$('#ang').value; $('#angL').textContent=a; var css='linear-gradient('+a+'deg, '+$('#c1').value+', '+$('#c2').value+')'; $('#prev').style.background=css; var full='background: '+css+';'; $('#code').innerHTML=esc(full)+'<button type="button" class="tt-copy" data-c="'+esc(full)+'"><i class="fa-solid fa-copy"></i> Copy</button>'; wireC(); }
    $all('#c1,#c2,#ang').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['contrast-checker'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Text color</label><div class="in"><input type="color" id="fg" value="#333333" style="height:46px"></div></div>'
      +'<div class="cl-f"><label>Background</label><div class="in"><input type="color" id="bg" value="#ffffff" style="height:46px"></div></div></div>'
      +'<div id="prev" style="border-radius:14px;padding:24px;text-align:center;margin:14px 0;font-size:18px;font-weight:700">Almost before we knew it, we had left the ground.</div>'
      +'<div class="cl-big"><div class="l">Contrast ratio</div><div class="v" id="ratio">—</div></div>'
      +'<div class="cl-rows">'
      +'<div class="cl-row"><div class="l">Normal text (AA)</div><div class="v" id="aa">—</div></div>'
      +'<div class="cl-row"><div class="l">Normal text (AAA)</div><div class="v" id="aaa">—</div></div>'
      +'<div class="cl-row"><div class="l">Large text (AA)</div><div class="v" id="laa">—</div></div>'
      +'<div class="cl-row"><div class="l">Large text (AAA)</div><div class="v" id="laaa">—</div></div></div>');
    function lum(c){ var a=[c.r,c.g,c.b].map(function(v){ v/=255; return v<=0.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4); }); return 0.2126*a[0]+0.7152*a[1]+0.0722*a[2]; }
    function badge(ok){ return ok?'<span style="color:#15803d">✓ Pass</span>':'<span style="color:#dc2626">✗ Fail</span>'; }
    function upd(){ var fg=hex2rgb($('#fg').value),bg=hex2rgb($('#bg').value); var l1=lum(fg),l2=lum(bg); var ratio=(Math.max(l1,l2)+0.05)/(Math.min(l1,l2)+0.05); $('#prev').style.color=$('#fg').value; $('#prev').style.background=$('#bg').value; $('#ratio').textContent=ratio.toFixed(2)+':1'; $('#aa').innerHTML=badge(ratio>=4.5); $('#aaa').innerHTML=badge(ratio>=7); $('#laa').innerHTML=badge(ratio>=3); $('#laaa').innerHTML=badge(ratio>=4.5); }
    $all('#fg,#bg').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['shadow-generator'] = function(){
    h('<div class="cl-grid">'
      +rng('x','Offset X',-50,50,8)+rng('y','Offset Y',-50,50,8)+rng('blur','Blur',0,100,24)+rng('spread','Spread',-50,50,0)+'</div>'
      +'<div class="cl-grid"><div class="cl-f"><label>Shadow color</label><div class="in"><input type="color" id="col" value="#6d28d9" style="height:44px"></div></div>'
      +'<div class="cl-f"><label>Opacity: <span id="opL">25</span>%</label><input type="range" class="tt-range" id="op" min="0" max="100" value="25"></div></div>'
      +'<div class="tt-opts"><label class="tt-chk"><input type="checkbox" id="inset"> Inset</label></div>'
      +'<div style="display:grid;place-items:center;padding:40px;background:var(--surface);border-radius:14px;margin:6px 0"><div id="box" style="width:140px;height:140px;background:#fff;border-radius:16px"></div></div>'
      +'<div class="tt-out mono" id="code" style="min-height:auto"></div>');
    function rng(id,l,mn,mx,v){ return '<div class="cl-f"><label>'+l+': <span id="'+id+'L">'+v+'</span>px</label><input type="range" class="tt-range" id="'+id+'" min="'+mn+'" max="'+mx+'" value="'+v+'"></div>'; }
    function upd(){ var x=$('#x').value,y=$('#y').value,b=$('#blur').value,s=$('#spread').value,op=$('#op').value; ['x','y','blur','spread'].forEach(function(k){$('#'+k+'L').textContent=$('#'+k).value;}); $('#opL').textContent=op; var c=hex2rgb($('#col').value); var rgba='rgba('+c.r+','+c.g+','+c.b+','+(op/100)+')'; var val=($('#inset').checked?'inset ':'')+x+'px '+y+'px '+b+'px '+s+'px '+rgba; $('#box').style.boxShadow=val; var full='box-shadow: '+val+';'; $('#code').innerHTML=esc(full)+'<button type="button" class="tt-copy" data-c="'+esc(full)+'"><i class="fa-solid fa-copy"></i> Copy</button>'; wireC(); }
    $all('#x,#y,#blur,#spread,#op,#col,#inset').forEach(function(z){z.addEventListener('input',upd);}); upd();
  };

  (tools[slug] || function(){ h('<p style="color:var(--muted)">This tool is being set up.</p>'); })();
})();
