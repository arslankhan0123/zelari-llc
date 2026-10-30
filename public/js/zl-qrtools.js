/* Zelari QR & Barcode — browser-side engine (loads qrcode/JsBarcode libs from CDN, no server) */
(function () {
  'use strict';
  var ui = document.getElementById('toolUI'); if (!ui) return;
  var slug = (window.ZTOOL || {}).slug;
  function h(s){ ui.innerHTML = s; }
  function $(s){ return ui.querySelector(s); }
  function $all(s){ return Array.prototype.slice.call(ui.querySelectorAll(s)); }
  function esc(s){ var d=document.createElement('div'); d.textContent=s==null?'':s; return d.innerHTML; }
  var loaded={};
  function loadScript(src){ return new Promise(function(res,rej){ if(loaded[src]){res();return;} var s=document.createElement('script'); s.src=src; s.onload=function(){loaded[src]=1;res();}; s.onerror=function(){rej(new Error('load failed'));}; document.head.appendChild(s); }); }
  function dl(dataUrl,name){ var a=document.createElement('a'); a.href=dataUrl; a.download=name; document.body.appendChild(a); a.click(); a.remove(); }

  var tools={};

  tools['qr-generator'] = function(){
    h('<div class="cl-tabs" id="tabs"><button class="cl-tab on" data-t="text">Text / URL</button><button class="cl-tab" data-t="wifi">WiFi</button><button class="cl-tab" data-t="vcard">Contact</button></div>'
      +'<div id="fields"></div>'
      +'<div class="cl-grid" style="margin-top:4px">'
      +'<div class="cl-f"><label>Size</label><div class="in"><select id="size"><option value="200">Small (200)</option><option value="300" selected>Medium (300)</option><option value="500">Large (500)</option></select></div></div>'
      +'<div class="cl-f"><label>Dot color</label><div class="in"><input type="color" id="fg" value="#111827"></div></div></div>'
      +'<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-qrcode"></i> Generate</button>'
      +'<button class="tt-btn ghost" id="dlBtn" style="display:none"><i class="fa-solid fa-download"></i> Download PNG</button>'
      +'<span class="tt-err" id="err"></span></div>'
      +'<div class="tt-canvas-wrap" id="out" style="display:none;margin-top:16px"></div>');
    var type='text';
    function fields(){
      if(type==='text') return '<div class="cl-f full"><label>Text or URL</label><textarea class="tt-ta" id="d1" style="min-height:90px" placeholder="https://zelarillc.com">https://zelarillc.com</textarea></div>';
      if(type==='wifi') return '<div class="cl-grid"><div class="cl-f"><label>Network name (SSID)</label><div class="in"><input id="d1" placeholder="MyWiFi"></div></div><div class="cl-f"><label>Password</label><div class="in"><input id="d2" placeholder="password"></div></div><div class="cl-f"><label>Security</label><div class="in"><select id="d3"><option>WPA</option><option>WEP</option><option value="nopass">None</option></select></div></div></div>';
      return '<div class="cl-grid"><div class="cl-f"><label>Full name</label><div class="in"><input id="d1" placeholder="Jane Doe"></div></div><div class="cl-f"><label>Phone</label><div class="in"><input id="d2" placeholder="+1..."></div></div><div class="cl-f full"><label>Email</label><div class="in"><input id="d3" placeholder="jane@example.com"></div></div></div>';
    }
    function payload(){
      if(type==='text') return $('#d1').value;
      if(type==='wifi'){ var s=$('#d1').value,p=$('#d2').value,t=$('#d3').value; return 'WIFI:T:'+(t==='nopass'?'nopass':t)+';S:'+s+';P:'+(t==='nopass'?'':p)+';;'; }
      return 'BEGIN:VCARD\nVERSION:3.0\nFN:'+$('#d1').value+'\nTEL:'+$('#d2').value+'\nEMAIL:'+$('#d3').value+'\nEND:VCARD';
    }
    $('#fields').innerHTML=fields();
    $all('#tabs .cl-tab').forEach(function(b){ b.onclick=function(){ $all('#tabs .cl-tab').forEach(function(x){x.classList.remove('on');}); b.classList.add('on'); type=b.dataset.t; $('#fields').innerHTML=fields(); }; });
    $('#go').onclick=function(){
      $('#err').textContent='';
      var txt=payload(); if(!txt || txt==='WIFI:T:WPA;S:;P:;;'){ $('#err').textContent='Enter some content first.'; return; }
      var size=parseInt($('#size').value), fg=$('#fg').value;
      loadScript('https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js').then(function(){
        var out=$('#out'); out.innerHTML=''; out.style.display='';
        new QRCode(out,{text:txt,width:size,height:size,colorDark:fg,colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});
        setTimeout(function(){ var c=out.querySelector('canvas'); $('#dlBtn').style.display=c?'':'none'; $('#dlBtn').onclick=function(){ if(c) dl(c.toDataURL('image/png'),'qr-code.png'); }; },120);
      }).catch(function(){ $('#err').textContent='Could not load the QR library. Check your connection and retry.'; });
    };
    $('#go').click();
  };

  tools['barcode-generator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Value</label><div class="in"><input id="val" value="123456789012" placeholder="Enter value"></div></div>'
      +'<div class="cl-f"><label>Format</label><div class="in"><select id="fmt"><option>CODE128</option><option>EAN13</option><option>UPC</option><option>CODE39</option><option>ITF14</option></select></div></div></div>'
      +'<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-barcode"></i> Generate</button>'
      +'<button class="tt-btn ghost" id="dlBtn" style="display:none"><i class="fa-solid fa-download"></i> Download PNG</button>'
      +'<span class="tt-err" id="err"></span></div>'
      +'<div class="tt-canvas-wrap" id="out" style="display:none;margin-top:16px"><canvas id="bc"></canvas></div>');
    $('#go').onclick=function(){
      $('#err').textContent='';
      var v=$('#val').value.trim(), f=$('#fmt').value;
      if(!v){ $('#err').textContent='Enter a value.'; return; }
      loadScript('https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js').then(function(){
        try{
          JsBarcode('#bc', v, {format:f, width:2, height:90, displayValue:true, margin:12, background:'#ffffff'});
          $('#out').style.display=''; var c=$('#bc');
          $('#dlBtn').style.display=''; $('#dlBtn').onclick=function(){ dl(c.toDataURL('image/png'),'barcode.png'); };
        }catch(e){ $('#out').style.display='none'; $('#dlBtn').style.display='none'; $('#err').textContent='That value is not valid for '+f+'. '+ (f==='EAN13'?'EAN13 needs 12–13 digits.':f==='UPC'?'UPC needs 11–12 digits.':''); }
      }).catch(function(){ $('#err').textContent='Could not load the barcode library. Check your connection and retry.'; });
    };
    $('#go').click();
  };

  (tools[slug] || function(){ h('<p style="color:var(--muted)">This tool is being set up.</p>'); })();
})();
