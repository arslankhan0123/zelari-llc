/* Zelari Seller Tools — browser-side engine */
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
  function num(v){ v=parseFloat(v); return isNaN(v)?0:v; }
  function money(n){ return (Math.round(n*100)/100).toFixed(2); }
  var loaded={};
  function loadScript(src){ return new Promise(function(res,rej){ if(loaded[src]){res();return;} var s=document.createElement('script'); s.src=src; s.onload=function(){loaded[src]=1;res();}; s.onerror=function(){rej();}; document.head.appendChild(s); }); }
  var tools={};

  tools['profit-margin'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Cost price</label><div class="in"><input type="number" id="cost" value="50" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Selling price</label><div class="in"><input type="number" id="sell" value="80" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Profit per unit</div><div class="v" id="profit">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Profit margin</div><div class="v" id="margin">—</div></div>'
      +'<div class="cl-row"><div class="l">Markup</div><div class="v" id="markup">—</div></div></div>');
    function upd(){ var c=num($('#cost').value),s=num($('#sell').value),p=s-c; $('#profit').textContent=money(p); $('#margin').textContent=s?money(p/s*100)+'%':'—'; $('#markup').textContent=c?money(p/c*100)+'%':'—'; }
    $all('#cost,#sell').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['markup-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Cost price</label><div class="in"><input type="number" id="cost" value="50" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Markup %</label><div class="in"><input type="number" id="mk" value="40" step="0.01"><span class="sfx">%</span></div></div></div>'
      +'<div class="cl-big"><div class="l">Selling price</div><div class="v" id="sell">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Profit</div><div class="v" id="profit">—</div></div>'
      +'<div class="cl-row"><div class="l">Margin</div><div class="v" id="margin">—</div></div></div>');
    function upd(){ var c=num($('#cost').value),m=num($('#mk').value),p=c*m/100,s=c+p; $('#sell').textContent=money(s); $('#profit').textContent=money(p); $('#margin').textContent=s?money(p/s*100)+'%':'—'; }
    $all('#cost,#mk').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['shipping-estimator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Weight</label><div class="in"><input type="number" id="wt" value="2" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Rate per unit weight</label><div class="in"><input type="number" id="rate" value="3.5" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Base / handling fee</label><div class="in"><input type="number" id="base" value="2" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Number of items</label><div class="in"><input type="number" id="qty" value="1" min="1"></div></div></div>'
      +'<div class="cl-big"><div class="l">Estimated shipping</div><div class="v" id="total">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Per item</div><div class="v" id="per">—</div></div>'
      +'<div class="cl-row"><div class="l">Weight cost</div><div class="v" id="wc">—</div></div></div>');
    function upd(){ var w=num($('#wt').value),r=num($('#rate').value),b=num($('#base').value),q=Math.max(1,num($('#qty').value)); var perItem=b+w*r; var total=perItem*q; $('#total').textContent=money(total); $('#per').textContent=money(perItem); $('#wc').textContent=money(w*r); }
    $all('#wt,#rate,#base,#qty').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['sku-generator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Brand / prefix</label><div class="in"><input id="brand" value="ZEL" placeholder="ZEL"></div></div>'
      +'<div class="cl-f"><label>Category</label><div class="in"><input id="cat" placeholder="Shirt"></div></div>'
      +'<div class="cl-f"><label>Attribute (color/size)</label><div class="in"><input id="attr" placeholder="Red-M"></div></div>'
      +'<div class="cl-f"><label>Start number</label><div class="in"><input type="number" id="numStart" value="1" min="0"></div></div></div>'
      +'<div class="cl-grid"><div class="cl-f"><label>How many</label><div class="in"><input type="number" id="count" value="5" min="1" max="200"></div></div>'
      +'<div class="cl-f"><label>Separator</label><div class="in"><select id="sep"><option value="-">Hyphen ( - )</option><option value="_">Underscore ( _ )</option><option value="">None</option></select></div></div></div>'
      +'<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-barcode"></i> Generate SKUs</button></div>'
      +'<div class="tt-out mono" id="out" style="margin-top:12px"></div>');
    function clean(s){ return (s||'').toString().toUpperCase().replace(/[^A-Z0-9]+/g,''); }
    $('#go').onclick=function(){
      var sep=$('#sep').value, parts0=[clean($('#brand').value),clean($('#cat').value).slice(0,4),clean($('#attr').value)].filter(Boolean);
      var start=parseInt($('#numStart').value)||0, n=Math.max(1,Math.min(200,parseInt($('#count').value)||1)),out=[];
      for(var i=0;i<n;i++){ var num=(start+i).toString().padStart(3,'0'); out.push(parts0.concat([num]).join(sep)); }
      var txt=out.join('\n'); $('#out').innerHTML=esc(txt)+cbtn(); wireCopy($('#out [data-copy]'),function(){return txt;});
    };
    $('#go').click();
  };

  tools['invoice-generator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Your business</label><div class="in"><input id="from" value="Zelari"></div></div>'
      +'<div class="cl-f"><label>Bill to</label><div class="in"><input id="to" placeholder="Customer name"></div></div>'
      +'<div class="cl-f"><label>Invoice #</label><div class="in"><input id="inv" value="INV-001"></div></div>'
      +'<div class="cl-f"><label>Date</label><div class="in"><input type="date" id="date"></div></div></div>'
      +'<label style="font-size:12.5px;font-weight:700;color:var(--ink-2);display:block;margin:14px 0 8px">Line items</label>'
      +'<div id="items"></div><button type="button" class="cl-add" id="add"><i class="fa-solid fa-plus"></i> Add item</button>'
      +'<div class="cl-grid" style="margin-top:14px"><div class="cl-f"><label>Currency symbol</label><div class="in"><input id="cur" value="$" style="max-width:80px"></div></div>'
      +'<div class="cl-f"><label>Tax %</label><div class="in"><input type="number" id="tax" value="0" step="0.01"><span class="sfx">%</span></div></div></div>'
      +'<div class="cl-big" style="margin-top:8px"><div class="l">Total</div><div class="v" id="gt">—</div></div>'
      +'<div class="tt-row"><button class="tt-btn" id="pdf"><i class="fa-solid fa-file-pdf"></i> Download PDF</button><span class="tt-err" id="err"></span></div>');
    $('#date').valueAsDate=new Date();
    function row(d,q,p){ return '<div class="gpa-row" style="grid-template-columns:1fr 70px 100px 38px"><input class="it-d" placeholder="Description" value="'+(d||'')+'"><input class="it-q" type="number" value="'+(q||1)+'" min="0"><input class="it-p" type="number" step="0.01" value="'+(p||0)+'" placeholder="Price"><button type="button" class="it-x">×</button></div>'; }
    function add(d,q,p){ $('#items').insertAdjacentHTML('beforeend', row(d,q,p)); bind(); calc(); }
    function bind(){ $all('.it-x').forEach(function(b){ b.onclick=function(){ b.parentNode.remove(); calc(); }; }); $all('.it-d,.it-q,.it-p').forEach(function(x){ x.oninput=calc; }); }
    function rows(){ return $all('#items .gpa-row').map(function(r){ return {d:r.querySelector('.it-d').value, q:num(r.querySelector('.it-q').value), p:num(r.querySelector('.it-p').value)}; }); }
    function totals(){ var sub=0; rows().forEach(function(r){ sub+=r.q*r.p; }); var tax=sub*num($('#tax').value)/100; return {sub:sub,tax:tax,total:sub+tax}; }
    function calc(){ var t=totals(); $('#gt').textContent=$('#cur').value+money(t.total); }
    $('#add').onclick=function(){ add('',1,0); };
    $all('#cur,#tax').forEach(function(x){x.addEventListener('input',calc);});
    $('#pdf').onclick=function(){
      $('#err').textContent='';
      loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js').then(function(){
        var J=(window.jspdf||{}).jsPDF; if(!J){ $('#err').textContent='PDF library failed to load.'; return; }
        var doc=new J(), cur=$('#cur').value, t=totals(), y=20;
        doc.setFontSize(20); doc.text($('#from').value||'Invoice', 20, y); y+=10;
        doc.setFontSize(11); doc.setTextColor(120);
        doc.text('Invoice: '+$('#inv').value, 20, y); doc.text('Date: '+$('#date').value, 150, y); y+=7;
        doc.text('Bill to: '+($('#to').value||'-'), 20, y); y+=12;
        doc.setTextColor(0); doc.setFontSize(10);
        doc.setFillColor(243,240,250); doc.rect(20,y-5,170,8,'F');
        doc.text('Description',22,y); doc.text('Qty',120,y); doc.text('Price',140,y); doc.text('Amount',168,y); y+=8;
        rows().forEach(function(r){ doc.text(String(r.d||'-').slice(0,55),22,y); doc.text(String(r.q),120,y); doc.text(cur+money(r.p),140,y); doc.text(cur+money(r.q*r.p),168,y); y+=7; if(y>270){doc.addPage();y=20;} });
        y+=4; doc.line(120,y,190,y); y+=7;
        doc.text('Subtotal',140,y); doc.text(cur+money(t.sub),168,y); y+=7;
        doc.text('Tax',140,y); doc.text(cur+money(t.tax),168,y); y+=8;
        doc.setFontSize(13); doc.text('Total',140,y); doc.text(cur+money(t.total),168,y);
        doc.save(($('#inv').value||'invoice')+'.pdf');
      }).catch(function(){ $('#err').textContent='Could not load the PDF library. Check your connection and retry.'; });
    };
    add('Sample product',1,100); 
  };

  (tools[slug] || function(){ h('<p style="color:var(--muted)">This tool is being set up.</p>'); })();
})();
