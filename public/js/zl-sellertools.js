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


  /* ===== Pricing ===== */
  tools['break-even-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Fixed costs (total)</label><div class="in"><input type="number" id="fc" value="1000" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Selling price / unit</label><div class="in"><input type="number" id="pr" value="25" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Variable cost / unit</label><div class="in"><input type="number" id="vc" value="10" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Break-even units</div><div class="v" id="u">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Break-even revenue</div><div class="v" id="rev">—</div></div>'
      +'<div class="cl-row"><div class="l">Contribution / unit</div><div class="v" id="cm">—</div></div></div>');
    function upd(){ var fc=num($('#fc').value),pr=num($('#pr').value),vc=num($('#vc').value),cm=pr-vc; $('#cm').textContent=money(cm); if(cm<=0){ $('#u').textContent='—'; $('#rev').textContent='Price must exceed variable cost'; return; } var u=Math.ceil(fc/cm); $('#u').textContent=u.toLocaleString(); $('#rev').textContent=money(u*pr); }
    $all('#fc,#pr,#vc').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['free-shipping-threshold'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Avg shipping cost you pay</label><div class="in"><input type="number" id="ship" value="6" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Your profit margin</label><div class="in"><input type="number" id="mg" value="35" step="0.01"><span class="sfx">%</span></div></div></div>'
      +'<div class="cl-big"><div class="l">Suggested free-shipping threshold</div><div class="v" id="th">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">At this order value</div><div class="v" id="note" style="font-size:13px">margin just covers shipping</div></div></div>');
    function upd(){ var s=num($('#ship').value),m=num($('#mg').value); if(m<=0){ $('#th').textContent='—'; return; } $('#th').textContent=money(s/(m/100)); }
    $all('#ship,#mg').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['landed-cost'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Unit cost</label><div class="in"><input type="number" id="uc" value="8" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Quantity</label><div class="in"><input type="number" id="qty" value="100" min="1"></div></div>'
      +'<div class="cl-f"><label>Shipping (total)</label><div class="in"><input type="number" id="ship" value="120" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Duty / customs</label><div class="in"><input type="number" id="duty" value="5" step="0.01"><span class="sfx">%</span></div></div>'
      +'<div class="cl-f"><label>Other fees (total)</label><div class="in"><input type="number" id="other" value="0" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Landed cost / unit</div><div class="v" id="per">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Total landed cost</div><div class="v" id="tot">—</div></div>'
      +'<div class="cl-row"><div class="l">Duty amount</div><div class="v" id="dut">—</div></div></div>');
    function upd(){ var uc=num($('#uc').value),q=Math.max(1,num($('#qty').value)),s=num($('#ship').value),d=num($('#duty').value),o=num($('#other').value); var goods=uc*q, duty=goods*d/100, tot=goods+s+duty+o; $('#dut').textContent=money(duty); $('#tot').textContent=money(tot); $('#per').textContent=money(tot/q); }
    $all('#uc,#qty,#ship,#duty,#other').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['tiered-pricing'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Base unit price</label><div class="in"><input type="number" id="base" value="20" step="0.01"></div></div></div>'
      +'<label style="font-size:12.5px;font-weight:700;color:var(--ink-2);display:block;margin:12px 0 8px">Quantity tiers</label>'
      +'<div id="tiers"></div><button type="button" class="cl-add" id="add"><i class="fa-solid fa-plus"></i> Add tier</button>'
      +'<div class="tt-out" id="out" style="margin-top:14px"></div>');
    function row(q,d){ return '<div class="gpa-row" style="grid-template-columns:1fr 1fr 38px"><input class="tq" type="number" placeholder="Min qty" value="'+(q||'')+'"><input class="td" type="number" placeholder="Discount %" value="'+(d||'')+'"><button type="button" class="tx">×</button></div>'; }
    function bind(){ $all('.tx').forEach(function(b){ b.onclick=function(){ b.parentNode.remove(); calc(); }; }); $all('.tq,.td').forEach(function(x){ x.oninput=calc; }); }
    function calc(){ var base=num($('#base').value); var rows=$all('#tiers .gpa-row').map(function(r){ return {q:num(r.querySelector('.tq').value),d:num(r.querySelector('.td').value)}; }).filter(function(r){return r.q>0;}).sort(function(a,b){return a.q-b.q;});
      if(!rows.length){ $('#out').innerHTML='<span style="color:var(--muted)">Add a tier to see pricing</span>'; return; }
      var html='<table style="width:100%;border-collapse:collapse;font-size:13.5px"><tr style="text-align:left;color:var(--muted)"><th style="padding:6px">Qty</th><th>Unit price</th><th>Save</th><th>Line total</th></tr>';
      rows.forEach(function(r){ var up=base*(1-r.d/100); html+='<tr style="border-top:1px solid var(--line)"><td style="padding:8px 6px">'+r.q+'+</td><td>'+money(up)+'</td><td style="color:var(--ok)">'+r.d+'%</td><td>'+money(up*r.q)+'</td></tr>'; });
      html+='</table>'; $('#out').innerHTML=html;
    }
    $('#add').onclick=function(){ $('#tiers').insertAdjacentHTML('beforeend',row('','')); bind(); };
    $('#base').addEventListener('input',calc);
    $('#tiers').innerHTML=row(10,5)+row(50,10)+row(100,15); bind(); calc();
  };

  tools['price-per-unit'] = function(){
    h('<div class="tt-grid2"><div><b style="font-size:14px">Product A</b><div class="cl-f" style="margin-top:8px"><label>Price</label><div class="in"><input type="number" id="pa" value="10" step="0.01"></div></div><div class="cl-f"><label>Quantity / size</label><div class="in"><input type="number" id="qa" value="500" step="0.01"></div></div></div>'
      +'<div><b style="font-size:14px">Product B</b><div class="cl-f" style="margin-top:8px"><label>Price</label><div class="in"><input type="number" id="pb" value="18" step="0.01"></div></div><div class="cl-f"><label>Quantity / size</label><div class="in"><input type="number" id="qb" value="1000" step="0.01"></div></div></div></div>'
      +'<div class="cl-big" style="margin-top:16px"><div class="l">Better deal</div><div class="v" id="win">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">A — per unit</div><div class="v" id="ua">—</div></div>'
      +'<div class="cl-row"><div class="l">B — per unit</div><div class="v" id="ub">—</div></div></div>');
    function upd(){ var pa=num($('#pa').value),qa=num($('#qa').value),pb=num($('#pb').value),qb=num($('#qb').value); var ua=qa?pa/qa:0,ub=qb?pb/qb:0; $('#ua').textContent=qa?ua.toFixed(4):'—'; $('#ub').textContent=qb?ub.toFixed(4):'—'; if(!ua||!ub){ $('#win').textContent='—'; return; } if(ua<ub){ $('#win').textContent='Product A ('+money((1-ua/ub)*100)+'% cheaper)'; } else if(ub<ua){ $('#win').textContent='Product B ('+money((1-ub/ua)*100)+'% cheaper)'; } else { $('#win').textContent='Same price per unit'; } }
    $all('#pa,#qa,#pb,#qb').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  /* ===== Fees ===== */
  tools['payment-fee-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Processor</label><div class="in"><select id="pre"><option value="2.9,0.30">PayPal / Stripe (2.9% + 0.30)</option><option value="3.49,0.49">PayPal Goods (3.49% + 0.49)</option><option value="2.9,0.30b">Square (2.9% + 0.30)</option><option value="custom">Custom</option></select></div></div>'
      +'<div class="cl-f"><label>Amount</label><div class="in"><input type="number" id="amt" value="100" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Fee</label><div class="in"><input type="number" id="pct" value="2.9" step="0.01"><span class="sfx">%</span></div></div>'
      +'<div class="cl-f"><label>Fixed fee</label><div class="in"><input type="number" id="fix" value="0.30" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">You receive</div><div class="v" id="net">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Processing fee</div><div class="v" id="fee">—</div></div>'
      +'<div class="cl-row"><div class="l">Effective rate</div><div class="v" id="eff">—</div></div></div>');
    function upd(){ var a=num($('#amt').value),p=num($('#pct').value),f=num($('#fix').value),fee=a*p/100+f; $('#fee').textContent=money(fee); $('#net').textContent=money(a-fee); $('#eff').textContent=a?money(fee/a*100)+'%':'—'; }
    $('#pre').addEventListener('change',function(){ if(this.value!=='custom'){ var p=this.value.replace('b','').split(','); $('#pct').value=p[0]; $('#fix').value=p[1]; } upd(); });
    $all('#amt,#pct,#fix').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['fba-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Selling price</label><div class="in"><input type="number" id="pr" value="30" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Product cost</label><div class="in"><input type="number" id="cost" value="10" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Referral fee</label><div class="in"><input type="number" id="ref" value="15" step="0.01"><span class="sfx">%</span></div></div>'
      +'<div class="cl-f"><label>FBA fulfillment fee</label><div class="in"><input type="number" id="ful" value="3.50" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Ship to Amazon / unit</label><div class="in"><input type="number" id="shp" value="0.50" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Net profit / unit</div><div class="v" id="profit">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Referral fee</div><div class="v" id="refv">—</div></div>'
      +'<div class="cl-row"><div class="l">Margin</div><div class="v" id="margin">—</div></div>'
      +'<div class="cl-row"><div class="l">ROI</div><div class="v" id="roi">—</div></div></div>'
      +'<div class="cl-note" style="color:var(--muted)"><i class="fa-solid fa-circle-info"></i> Fees are estimates — Amazon rates vary by category and size.</div>');
    function upd(){ var pr=num($('#pr').value),c=num($('#cost').value),rf=num($('#ref').value),fu=num($('#ful').value),sh=num($('#shp').value); var ref=pr*rf/100, profit=pr-c-ref-fu-sh; $('#refv').textContent=money(ref); $('#profit').textContent=money(profit); $('#margin').textContent=pr?money(profit/pr*100)+'%':'—'; $('#roi').textContent=(c+sh)?money(profit/(c+sh)*100)+'%':'—'; }
    $all('#pr,#cost,#ref,#ful,#shp').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['ebay-etsy-fees'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Marketplace</label><div class="in"><select id="mk"><option value="13.25,0.30">eBay (~13.25% + 0.30)</option><option value="9.5,0.45">Etsy (~6.5% + 3% pay + fixed)</option><option value="custom">Custom</option></select></div></div>'
      +'<div class="cl-f"><label>Sale price</label><div class="in"><input type="number" id="pr" value="40" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Fee</label><div class="in"><input type="number" id="pct" value="13.25" step="0.01"><span class="sfx">%</span></div></div>'
      +'<div class="cl-f"><label>Fixed fee</label><div class="in"><input type="number" id="fix" value="0.30" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Product cost (optional)</label><div class="in"><input type="number" id="cost" value="0" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Net payout</div><div class="v" id="net">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Total fees</div><div class="v" id="fee">—</div></div>'
      +'<div class="cl-row"><div class="l">Profit (after cost)</div><div class="v" id="profit">—</div></div></div>'
      +'<div class="cl-note" style="color:var(--muted)"><i class="fa-solid fa-circle-info"></i> Estimates — real rates vary by category and country.</div>');
    function upd(){ var pr=num($('#pr').value),p=num($('#pct').value),f=num($('#fix').value),c=num($('#cost').value),fee=pr*p/100+f,net=pr-fee; $('#fee').textContent=money(fee); $('#net').textContent=money(net); $('#profit').textContent=money(net-c); }
    $('#mk').addEventListener('change',function(){ if(this.value!=='custom'){ var a=this.value.split(','); $('#pct').value=a[0]; $('#fix').value=a[1]; } upd(); });
    $all('#pr,#pct,#fix,#cost').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['net-profit-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Sale price</label><div class="in"><input type="number" id="pr" value="50" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Product cost</label><div class="in"><input type="number" id="cost" value="20" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Platform / payment fee</label><div class="in"><input type="number" id="pct" value="5" step="0.01"><span class="sfx">%</span></div></div>'
      +'<div class="cl-f"><label>Fixed fee</label><div class="in"><input type="number" id="fix" value="0.30" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Shipping cost</label><div class="in"><input type="number" id="ship" value="4" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Other costs</label><div class="in"><input type="number" id="other" value="0" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Net profit / order</div><div class="v" id="net">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Net margin</div><div class="v" id="margin">—</div></div>'
      +'<div class="cl-row"><div class="l">Total costs</div><div class="v" id="tc">—</div></div></div>');
    function upd(){ var pr=num($('#pr').value),c=num($('#cost').value),p=num($('#pct').value),f=num($('#fix').value),s=num($('#ship').value),o=num($('#other').value); var fees=pr*p/100+f, costs=c+fees+s+o, net=pr-costs; $('#tc').textContent=money(costs); $('#net').textContent=money(net); $('#margin').textContent=pr?money(net/pr*100)+'%':'—'; }
    $all('#pr,#cost,#pct,#fix,#ship,#other').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  /* ===== Marketing ===== */
  tools['roas-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Revenue from ads</label><div class="in"><input type="number" id="rev" value="5000" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Ad spend</label><div class="in"><input type="number" id="spend" value="1000" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">ROAS</div><div class="v" id="roas">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">ACOS</div><div class="v" id="acos">—</div></div>'
      +'<div class="cl-row"><div class="l">Profit from ads</div><div class="v" id="pf">—</div></div></div>');
    function upd(){ var r=num($('#rev').value),s=num($('#spend').value); $('#roas').textContent=s?money(r/s)+'x':'—'; $('#acos').textContent=r?money(s/r*100)+'%':'—'; $('#pf').textContent=money(r-s); }
    $all('#rev,#spend').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['cac-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Total sales &amp; marketing spend</label><div class="in"><input type="number" id="spend" value="2000" step="0.01"></div></div>'
      +'<div class="cl-f"><label>New customers acquired</label><div class="in"><input type="number" id="cust" value="80" min="0"></div></div></div>'
      +'<div class="cl-big"><div class="l">Customer acquisition cost</div><div class="v" id="cac">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Per customer</div><div class="v" id="per" style="font-size:14px">cost to acquire one buyer</div></div></div>');
    function upd(){ var s=num($('#spend').value),c=num($('#cust').value); $('#cac').textContent=c?money(s/c):'—'; }
    $all('#spend,#cust').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['clv-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Avg order value</label><div class="in"><input type="number" id="aov" value="60" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Purchases / year</label><div class="in"><input type="number" id="freq" value="3" step="0.1"></div></div>'
      +'<div class="cl-f"><label>Customer lifespan (years)</label><div class="in"><input type="number" id="life" value="3" step="0.1"></div></div>'
      +'<div class="cl-f"><label>Gross margin</label><div class="in"><input type="number" id="mg" value="40" step="0.01"><span class="sfx">%</span></div></div></div>'
      +'<div class="cl-big"><div class="l">Customer lifetime value</div><div class="v" id="clv">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Total revenue / customer</div><div class="v" id="totrev">—</div></div></div>');
    function upd(){ var a=num($('#aov').value),f=num($('#freq').value),l=num($('#life').value),m=num($('#mg').value); var rev=a*f*l; $('#totrev').textContent=money(rev); $('#clv').textContent=money(rev*m/100); }
    $all('#aov,#freq,#life,#mg').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['aov-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Total revenue</label><div class="in"><input type="number" id="rev" value="12000" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Number of orders</label><div class="in"><input type="number" id="ord" value="200" min="0"></div></div></div>'
      +'<div class="cl-big"><div class="l">Average order value</div><div class="v" id="aov">—</div></div>');
    function upd(){ var r=num($('#rev').value),o=num($('#ord').value); $('#aov').textContent=o?money(r/o):'—'; }
    $all('#rev,#ord').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['conversion-rate'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Visitors</label><div class="in"><input type="number" id="vis" value="5000" min="0"></div></div>'
      +'<div class="cl-f"><label>Orders / conversions</label><div class="in"><input type="number" id="conv" value="120" min="0"></div></div>'
      +'<div class="cl-f"><label>Revenue (optional)</label><div class="in"><input type="number" id="rev" value="0" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Conversion rate</div><div class="v" id="cr">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Revenue / visitor</div><div class="v" id="rpv">—</div></div></div>');
    function upd(){ var v=num($('#vis').value),c=num($('#conv').value),r=num($('#rev').value); $('#cr').textContent=v?money(c/v*100)+'%':'—'; $('#rpv').textContent=(v&&r)?money(r/v):'—'; }
    $all('#vis,#conv,#rev').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['break-even-roas'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Gross profit margin</label><div class="in"><input type="number" id="mg" value="40" step="0.01"><span class="sfx">%</span></div></div></div>'
      +'<div class="cl-big"><div class="l">Break-even ROAS</div><div class="v" id="roas">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Break-even ACOS</div><div class="v" id="acos">—</div></div>'
      +'<div class="cl-row"><div class="l">Meaning</div><div class="v" id="note" style="font-size:13px">ads above this ROAS are profitable</div></div></div>');
    function upd(){ var m=num($('#mg').value); if(m<=0){ $('#roas').textContent='—'; $('#acos').textContent='—'; return; } $('#roas').textContent=money(100/m)+'x'; $('#acos').textContent=money(m)+'%'; }
    $('#mg').addEventListener('input',upd); upd();
  };

  /* ===== Inventory ===== */
  tools['reorder-point'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Avg daily sales (units)</label><div class="in"><input type="number" id="ds" value="20" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Lead time (days)</label><div class="in"><input type="number" id="lt" value="7" step="0.1"></div></div>'
      +'<div class="cl-f"><label>Safety stock (units)</label><div class="in"><input type="number" id="ss" value="30" min="0"></div></div></div>'
      +'<div class="cl-big"><div class="l">Reorder point</div><div class="v" id="rop">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Lead-time demand</div><div class="v" id="ltd">—</div></div></div>');
    function upd(){ var d=num($('#ds').value),l=num($('#lt').value),s=num($('#ss').value); var ltd=d*l; $('#ltd').textContent=Math.ceil(ltd)+' units'; $('#rop').textContent=Math.ceil(ltd+s).toLocaleString()+' units'; }
    $all('#ds,#lt,#ss').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['eoq-calculator'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Annual demand (units)</label><div class="in"><input type="number" id="d" value="12000" min="0"></div></div>'
      +'<div class="cl-f"><label>Cost per order</label><div class="in"><input type="number" id="s" value="50" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Holding cost / unit / year</label><div class="in"><input type="number" id="hh" value="3" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Economic order quantity</div><div class="v" id="eoq">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Orders / year</div><div class="v" id="opy">—</div></div>'
      +'<div class="cl-row"><div class="l">Days between orders</div><div class="v" id="dbo">—</div></div></div>');
    function upd(){ var d=num($('#d').value),s=num($('#s').value),hh=num($('#hh').value); if(hh<=0||d<=0){ $('#eoq').textContent='—'; return; } var eoq=Math.sqrt(2*d*s/hh); $('#eoq').textContent=Math.round(eoq).toLocaleString()+' units'; $('#opy').textContent=money(d/eoq); $('#dbo').textContent=money(365/(d/eoq))+' days'; }
    $all('#d,#s,#hh').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['inventory-turnover'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Cost of goods sold (year)</label><div class="in"><input type="number" id="cogs" value="80000" step="0.01"></div></div>'
      +'<div class="cl-f"><label>Average inventory value</label><div class="in"><input type="number" id="inv" value="16000" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Inventory turnover</div><div class="v" id="to">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Days inventory on hand</div><div class="v" id="days">—</div></div></div>');
    function upd(){ var c=num($('#cogs').value),i=num($('#inv').value); if(i<=0){ $('#to').textContent='—'; $('#days').textContent='—'; return; } var t=c/i; $('#to').textContent=money(t)+'x'; $('#days').textContent=money(365/t)+' days'; }
    $all('#cogs,#inv').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  tools['dim-weight'] = function(){
    h('<div class="cl-grid"><div class="cl-f"><label>Units</label><div class="in"><select id="unit"><option value="5000">Metric (cm, kg) ÷5000</option><option value="6000">Metric (cm, kg) ÷6000</option><option value="139">Imperial (in, lb) ÷139</option></select></div></div>'
      +'<div class="cl-f"><label>Length</label><div class="in"><input type="number" id="l" value="40" step="0.1"></div></div>'
      +'<div class="cl-f"><label>Width</label><div class="in"><input type="number" id="w" value="30" step="0.1"></div></div>'
      +'<div class="cl-f"><label>Height</label><div class="in"><input type="number" id="hgt" value="20" step="0.1"></div></div>'
      +'<div class="cl-f"><label>Actual weight</label><div class="in"><input type="number" id="aw" value="3" step="0.01"></div></div></div>'
      +'<div class="cl-big"><div class="l">Billable weight</div><div class="v" id="bill">—</div></div>'
      +'<div class="cl-rows"><div class="cl-row"><div class="l">Dimensional weight</div><div class="v" id="dim">—</div></div>'
      +'<div class="cl-row"><div class="l">Actual weight</div><div class="v" id="act">—</div></div></div>');
    function upd(){ var div=num($('#unit').value),l=num($('#l').value),w=num($('#w').value),hg=num($('#hgt').value),aw=num($('#aw').value); var dim=l*w*hg/div; var unit=div==139?' lb':' kg'; $('#dim').textContent=money(dim)+unit; $('#act').textContent=money(aw)+unit; $('#bill').textContent=money(Math.max(dim,aw))+unit; }
    $all('#unit,#l,#w,#hgt,#aw').forEach(function(x){x.addEventListener('input',upd);}); upd();
  };

  (tools[slug] || function(){ h('<p style="color:var(--muted)">This tool is being set up.</p>'); })();
})();
