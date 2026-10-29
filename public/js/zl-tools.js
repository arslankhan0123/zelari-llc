/* Zelari PDF Tools — all tool logic (per-page config via window.ZTOOL) */
(function(){
  /* keep rendering alive in hidden/background tabs: rAF falls back to a timer */
  try{
    const _raf=window.requestAnimationFrame.bind(window);
    window.requestAnimationFrame=cb=>{ let done=false;
      const t=setTimeout(()=>{ if(!done){ done=true; try{ cb(performance.now()); }catch(_){} } },90);
      try{ _raf(ts=>{ if(!done){ done=true; clearTimeout(t); cb(ts); } }); }catch(_){}
      return t; };
  }catch(_){}
  const ZT=window.ZTOOL||{};
  const SLUG=ZT.slug||'', LIVE=!!ZT.live, MULTI=!!ZT.multi;
  const CSRF=ZT.csrf||'';
  const SRVTOOLS=['pdf-to-word','pdf-to-excel','word-to-pdf','ppt-to-pdf','excel-to-pdf'];
  let srvOk=false;
  if(LIVE && SRVTOOLS.includes(SLUG)){
    fetch('/tools/convert-status/'+SLUG).then(r=>r.json()).then(j=>{
      srvOk=!!j.available;
      if(srvOk){ const n=document.querySelector('.ts-priv'); if(n) n.innerHTML='<i class="fa-solid fa-bolt"></i> High-fidelity engine active — exact layout conversion (file is converted on our server and deleted right away).'; }
    }).catch(()=>{});
  }
  const drop=document.getElementById('tsDrop'), input=document.getElementById('tsInput'),
        list=document.getElementById('tsFiles'), go=document.getElementById('tsGo');
  let files=[];
  const fmt=n=>n>1048576?(n/1048576).toFixed(1)+' MB':Math.max(1,Math.round(n/1024))+' KB';
  function render(){
    list.innerHTML='';
    files.forEach((f,i)=>{
      const d=document.createElement('div'); d.className='ts-file';
      d.innerHTML='<i class="fa-solid fa-file-lines"></i><span></span><small class="meta"></small><small>'+fmt(f.size)+'</small><button type="button" class="x"><i class="fa-solid fa-xmark"></i></button>';
      d.children[1].textContent=f.name;
      d.querySelector('.meta').textContent = f._pages ? '· '+f._pages+' page'+(f._pages>1?'s':'') + (f._enc?' · 🔒 secured':'') : '';
      d.querySelector('.x').onclick=()=>{ files.splice(i,1); render(); if(SLUG==='organize-pdf') buildOrganize(); if(SLUG==='edit-pdf') buildEditor(); };
      list.appendChild(d);
    });
    go.disabled=!files.length;
  }
  async function inspect(f){
    if(!LIVE || SLUG==='jpg-to-pdf' || !/pdf$/i.test(f.type)&&!/\.pdf$/i.test(f.name)) return;
    try{
      const d=await PDFLib.PDFDocument.load(await f.arrayBuffer(),{ignoreEncryption:true});
      f._pages=d.getPageCount(); f._enc=d.isEncrypted;
      if(f._enc) zToast('<b>'+f.name.slice(0,40)+'</b> is a <b>secured (password-protected)</b> PDF — page detection and editing may not work correctly on it. Try an unlocked copy.','err');
      render();
    }catch(_){}
    if(SLUG==='organize-pdf') buildOrganize();
    if(SLUG==='edit-pdf') buildEditor();
  }
  async function buildOrganize(){
    const grid=document.getElementById('orgGrid'), hint=document.getElementById('orgHint');
    if(!grid) return;
    grid.innerHTML=''; hint.style.display='none';
    if(!files[0]) return;
    let doc=null, n=files[0]._pages||0;
    try{ if(window.pdfjsLib){ doc=await pdfjsLib.getDocument({data:new Uint8Array(await files[0].arrayBuffer())}).promise; n=doc.numPages; } }catch(_){}
    const max=Math.min(n,150);
    for(let i=1;i<=max;i++){
      const card=document.createElement('div'); card.className='org-pg'; card.draggable=true; card.dataset.idx=i-1;
      if(doc){
        try{
          const pg=await doc.getPage(i);
          const vp1=pg.getViewport({scale:1}), vp=pg.getViewport({scale:110/vp1.width});
          const c=document.createElement('canvas'); c.width=vp.width|0; c.height=vp.height|0;
          await pg.render({canvasContext:c.getContext('2d'),viewport:vp}).promise;
          card.appendChild(c);
        }catch(_){ const d=document.createElement('div'); d.className='phx'; d.innerHTML='<i class="fa-solid fa-file-lines"></i>'; card.appendChild(d); }
      } else { const d=document.createElement('div'); d.className='phx'; d.innerHTML='<i class="fa-solid fa-file-lines"></i>'; card.appendChild(d); }
      const b=document.createElement('b'); b.textContent='Page '+i; card.appendChild(b);
      const del=document.createElement('button'); del.type='button'; del.className='del'; del.innerHTML='<i class="fa-solid fa-xmark"></i>';
      del.onclick=e=>{ e.stopPropagation(); card.remove(); };
      card.appendChild(del);
      card.addEventListener('dragstart',()=>card.classList.add('drag'));
      card.addEventListener('dragend',()=>card.classList.remove('drag'));
      grid.appendChild(card);
    }
    if(max){ hint.style.display='block'; }
    if(n>max) zToast('Showing the first '+max+' pages for reordering.','info');
    if(!grid.dataset.dnd){
      grid.dataset.dnd='1';
      grid.addEventListener('dragover',e=>{
        e.preventDefault();
        const drag=grid.querySelector('.drag'), tgt=e.target.closest('.org-pg');
        if(!drag||!tgt||tgt===drag) return;
        const r=tgt.getBoundingClientRect();
        grid.insertBefore(drag, (e.clientX-r.left)<r.width/2 ? tgt : tgt.nextSibling);
      });
    }
  }
  function addFiles(fl){ const arr=[...fl]; files=MULTI?files.concat(arr):arr.slice(0,1); render(); files.forEach(f=>{ if(f._pages===undefined){ f._pages=null; inspect(f); } }); }
  window.zTsAddFile=f=>addFiles([f]);
  if(drop&&input){
    drop.onclick=()=>input.click();
    input.onchange=()=>{ addFiles(input.files); input.value=''; };
    ['dragover','dragenter'].forEach(ev=>drop.addEventListener(ev,e=>{ e.preventDefault(); drop.classList.add('over'); }));
    ['dragleave','drop'].forEach(ev=>drop.addEventListener(ev,e=>{ e.preventDefault(); drop.classList.remove('over'); }));
    drop.addEventListener('drop',e=>addFiles(e.dataTransfer.files));
  }

  if(!LIVE){
    go.onclick=()=>zToast('<b>'+(ZT.name||'This tool')+'</b> is being set up — this tool will be live very soon!','info');
    return;
  }

  /* ───── Edit PDF: in-browser annotator ───── */
  const ED = SLUG==='edit-pdf' ? {tool:'text', pages:[], img:null, last:null} : null;
  function edSetup(){
    const bar=document.getElementById('edBar'); if(!bar||bar.dataset.init) return;
    bar.dataset.init='1';
    bar.querySelectorAll('button[data-tool]').forEach(b=>b.onclick=()=>{
      ED.tool=b.dataset.tool;
      bar.querySelectorAll('button[data-tool]').forEach(x=>x.classList.toggle('on',x===b));
      if(ED.tool==='img'&&!ED.img) document.getElementById('edImgPick').click();
      if(ED.tool==='text') document.getElementById('edText').focus();
    });
    document.getElementById('edImgPick').onchange=e=>{
      const f=e.target.files[0]; if(!f) return;
      const im=new Image();
      im.onload=()=>{ ED.img=im; zToast('Image ready — click on a page to place it.','info'); };
      im.src=URL.createObjectURL(f);
    };
    document.getElementById('edUndo').onclick=()=>{
      const st=ED.last; if(!st||!st.hist.length) return;
      st.ctx.putImageData(st.hist.pop(),0,0);
      st.used=st.hist.length>0||st.used;
    };
  }
  async function buildEditor(){
    const wrap=document.getElementById('edPages'); if(!wrap) return;
    wrap.innerHTML=''; ED.pages=[]; ED.last=null;
    document.getElementById('edBar').style.display=files[0]?'flex':'none';
    if(!files[0]) return;
    edSetup();
    let doc=null;
    try{ doc=await pjsDoc(files[0]); }catch(_){ zToast('Could not open this PDF.','err'); return; }
    const n=Math.min(doc.numPages,30);
    for(let i=1;i<=n;i++){
      const pg=await doc.getPage(i);
      const vp1=pg.getViewport({scale:1});
      const vp=pg.getViewport({scale:Math.min(1.6, 820/vp1.width)});
      const holder=document.createElement('div'); holder.className='ed-pg';
      const base64c=document.createElement('canvas'); base64c.width=vp.width|0; base64c.height=vp.height|0;
      const bctx=base64c.getContext('2d'); bctx.fillStyle='#fff'; bctx.fillRect(0,0,base64c.width,base64c.height);
      await pg.render({canvasContext:bctx,viewport:vp}).promise;
      const ov=document.createElement('canvas'); ov.width=base64c.width; ov.height=base64c.height; ov.className='ed-ov';
      const lab=document.createElement('b'); lab.textContent='Page '+i;
      holder.appendChild(base64c); holder.appendChild(ov); holder.appendChild(lab);
      wrap.appendChild(holder);
      const st={ov, ctx:ov.getContext('2d'), hist:[], used:false};
      ED.pages.push(st);
      edAttach(st);
    }
    if(doc.numPages>n) zToast('Editing the first '+n+' pages.','info');
  }
  function edPush(st){ if(st.hist.length>14) st.hist.shift(); st.hist.push(st.ctx.getImageData(0,0,st.ov.width,st.ov.height)); ED.last=st; }
  function edAttach(st){
    const ov=st.ov, ctx=st.ctx;
    const pos=e=>{ const r=ov.getBoundingClientRect(); return {x:(e.clientX-r.left)*ov.width/r.width, y:(e.clientY-r.top)*ov.height/r.height}; };
    let drawing=false, startP=null, snap=null;
    ov.addEventListener('pointerdown',e=>{
      e.preventDefault();
      const p=pos(e);
      const col=document.getElementById('edColor').value;
      const lw=+document.getElementById('edSize').value;
      if(ED.tool==='text'){
        const t=sane(document.getElementById('edText').value.trim());
        if(!t){ zToast('Toolbar ke text box mein pehle kuch likhein, phir page par click karein. / Type your text in the toolbar first.','info'); return; }
        edPush(st);
        const fs=+document.getElementById('edFont').value;
        ctx.fillStyle=col; ctx.font='bold '+fs+'px Arial, Helvetica, sans-serif'; ctx.textBaseline='top';
        ctx.fillText(t,p.x,p.y);
        st.used=true; return;
      }
      if(ED.tool==='img'){
        if(!ED.img){ document.getElementById('edImgPick').click(); return; }
        edPush(st);
        const w=Math.min(ED.img.width, ov.width*0.45), h=w*ED.img.height/ED.img.width;
        ctx.drawImage(ED.img, p.x-w/2, p.y-h/2, w, h);
        st.used=true; return;
      }
      edPush(st);
      drawing=true; startP=p;
      try{ ov.setPointerCapture(e.pointerId); }catch(_){}
      ctx.strokeStyle=col; ctx.lineWidth=lw; ctx.lineCap='round'; ctx.lineJoin='round';
      if(ED.tool==='pen'){ ctx.beginPath(); ctx.moveTo(p.x,p.y); }
      else snap=ctx.getImageData(0,0,ov.width,ov.height);
    });
    ov.addEventListener('pointermove',e=>{
      if(!drawing) return;
      const p=pos(e);
      if(ED.tool==='pen'){ ctx.lineTo(p.x,p.y); ctx.stroke(); }
      else{
        ctx.putImageData(snap,0,0);
        ctx.beginPath();
        if(ED.tool==='rect') ctx.strokeRect(startP.x,startP.y,p.x-startP.x,p.y-startP.y);
        else{ ctx.ellipse((startP.x+p.x)/2,(startP.y+p.y)/2,Math.abs(p.x-startP.x)/2,Math.abs(p.y-startP.y)/2,0,0,Math.PI*2); ctx.stroke(); }
      }
    });
    const up=()=>{ if(!drawing) return; drawing=false; st.used=true; };
    ov.addEventListener('pointerup',up);
    ov.addEventListener('pointercancel',up);
  }

  /* ───── processing (pdf-lib, fully in the browser) ───── */
  const { PDFDocument, degrees, rgb, StandardFonts } = PDFLib;
  const loadDoc = async f => PDFDocument.load(await f.arrayBuffer(), { ignoreEncryption: true });
  function download(bytes, name){
    const a=document.createElement('a');
    a.href=URL.createObjectURL(bytes instanceof Blob ? bytes : new Blob([bytes],{type:arguments[2]||'application/pdf'}));
    a.download=name; document.body.appendChild(a); a.click();
    setTimeout(()=>{ URL.revokeObjectURL(a.href); a.remove(); },4000);
  }
  function parseRange(str,max){
    const out=new Set();
    (str||'').replace(/[\u2013\u2014\u2212]/g,'-').replace(/[\u060C\u3001]/g,',').split(',').map(s=>s.trim()).filter(Boolean).forEach(part=>{
      const m=part.match(/^(\d+)\s*-\s*(\d+)$/);
      if(m){ for(let i=+m[1];i<=+m[2];i++) if(i>=1&&i<=max) out.add(i-1); }
      else if(/^\d+$/.test(part) && +part>=1 && +part<=max) out.add(+part-1);
    });
    return [...out].sort((a,b)=>a-b);
  }
  const base=f=>f.name.replace(/\.[^.]+$/,'');
  const sane=t=>String(t).replace(/[­‐-―−]/g,'-').replace(/[‘’]/g,"'").replace(/[“”]/g,'"').replace(/ /g,' ').replace(/[^\x20-\x7E\xA1-\xFF]/g,'');
  const spin=t=>{ go.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> '+t; };
  const pjsDoc=async f=>{ if(!window.pdfjsLib) throw 'Could not load the PDF renderer — check your internet connection and reload the page.'; return pdfjsLib.getDocument({data:new Uint8Array(await f.arrayBuffer())}).promise; };
  async function pageLines(pg){
    const tc=await pg.getTextContent(), rows=new Map();
    tc.items.forEach(it=>{ if(!it.str||!it.str.trim()) return; const y=Math.round(it.transform[5]/2)*2; if(!rows.has(y)) rows.set(y,[]); rows.get(y).push({x:it.transform[4], w:it.width||0, s:it.str}); });
    return [...rows.entries()].sort((a,b)=>b[0]-a[0]).map(e=>e[1].sort((a,b)=>a.x-b.x));
  }

  const ops={
    'merge-pdf': async ()=>{
      if(files.length<2) throw 'Select at least 2 PDF files to merge.';
      const out=await PDFDocument.create();
      for(const f of files){ const d=await loadDoc(f); (await out.copyPages(d,d.getPageIndices())).forEach(p=>out.addPage(p)); }
      download(await out.save(),'merged.pdf');
    },
    'split-pdf': async ()=>{
      const d=await loadDoc(files[0]), n=d.getPageCount();
      const idx=parseRange(document.getElementById('optRange').value,n);
      if(!idx.length) throw 'Enter the pages to extract, e.g. 1-3,5 (this PDF has '+n+' pages).';
      const out=await PDFDocument.create();
      (await out.copyPages(d,idx)).forEach(p=>out.addPage(p));
      download(await out.save(), base(files[0])+'-pages.pdf');
    },
    'rotate-pdf': async ()=>{
      const d=await loadDoc(files[0]);
      const ang=+document.getElementById('optAngle').value;
      d.getPages().forEach(p=>p.setRotation(degrees((p.getRotation().angle+ang)%360)));
      download(await d.save(), base(files[0])+'-rotated.pdf');
    },
    'watermark-pdf': async ()=>{
      const text=(document.getElementById('optText').value||'').trim().replace(/\s+/g,' ');
      if(!text) throw 'Type the watermark text first.';
      const d=await loadDoc(files[0]);
      const font=await d.embedFont(StandardFonts.HelveticaBold);
      const words=text.split(' ');
      const LH=1.3, CAP=0.7; // line-height & cap-height (per 1pt of font size)
      const uw=t=>font.widthOfTextAtSize(t,1);

      // balanced greedy wrap into n lines
      function wrap(n){
        if(n>=words.length) n=words.length;
        const target=uw(text)/n, lines=[]; let cur='';
        for(const wd of words){
          const tryLine=cur?cur+' '+wd:wd;
          if(cur && uw(tryLine)>target*1.15 && lines.length<n-1){ lines.push(cur); cur=wd; }
          else cur=tryLine;
        }
        if(cur) lines.push(cur);
        return lines;
      }

      d.getPages().forEach(p=>{
        const {width:w,height:h}=p.getSize();
        const a=Math.atan2(h,w), cos=Math.cos(a), sin=Math.sin(a);
        // pick the line count (1–5) that lets the font be biggest
        let best={size:0,lines:[text]};
        for(let n=1;n<=Math.min(5,words.length);n++){
          const lines=wrap(n);
          const Wu=Math.max(...lines.map(uw));
          const Hu=(lines.length-1)*LH+CAP;
          let size=Math.min((w*0.86)/(Wu*cos+Hu*sin),(h*0.86)/(Wu*sin+Hu*cos),96);
          if(size>best.size) best={size,lines};
        }
        const size=Math.max(best.size,6), lines=best.lines, n=lines.length;
        // draw each line rotated along the diagonal, the whole block centered on the page
        lines.forEach((line,i)=>{
          const lw=uw(line)*size;
          const yOff=(((n-1)/2)-i)*LH*size;            // line's center above/below block center
          const lx=-lw/2, ly=yOff-(CAP*size)/2;        // line origin relative to block center (unrotated)
          const ox=w/2+(lx*cos-ly*sin);
          const oy=h/2+(lx*sin+ly*cos);
          p.drawText(line,{x:ox,y:oy,size,font,color:rgb(.55,.55,.6),opacity:.3,rotate:degrees(a*180/Math.PI)});
        });
      });
      download(await d.save(), base(files[0])+'-watermarked.pdf');
    },
    'page-numbers': async ()=>{
      const d=await loadDoc(files[0]);
      const font=await d.embedFont(StandardFonts.Helvetica);
      const pages=d.getPages(), n=pages.length;
      pages.forEach((p,i)=>{
        const {width}=p.getSize(); const t='Page '+(i+1)+' of '+n;
        p.drawText(t,{x:(width-font.widthOfTextAtSize(t,11))/2,y:16,size:11,font,color:rgb(.35,.35,.4)});
      });
      download(await d.save(), base(files[0])+'-numbered.pdf');
    },
    'organize-pdf': async ()=>{
      const order=[...document.querySelectorAll('#orgGrid .org-pg')].map(el=>+el.dataset.idx);
      if(!order.length) throw 'No pages left — re-select the file or keep at least one page.';
      const d=await loadDoc(files[0]);
      const out=await PDFDocument.create();
      (await out.copyPages(d,order)).forEach(p=>out.addPage(p));
      download(await out.save(), base(files[0])+'-organized.pdf');
      return '<b>Done!</b> New PDF with '+order.length+' page'+(order.length>1?'s':'')+' downloaded.';
    },
    'compress-pdf': async ()=>{
      const orig=files[0].size;
      const mode=document.getElementById('optLevel').value;
      if(mode==='lite'){
        const d=await loadDoc(files[0]);
        const bytes=await d.save({useObjectStreams:true});
        if(bytes.length>=orig*0.99) throw 'This PDF is already well optimized — try <b>Extreme</b> mode for bigger savings (pages become images).';
        download(bytes, base(files[0])+'-compressed.pdf');
        return '<b>Done!</b> '+fmt(orig)+' → '+fmt(bytes.length)+' ('+Math.round((1-bytes.length/orig)*100)+'% smaller).';
      }
      if(!window.pdfjsLib) throw 'Could not load the PDF renderer — check your internet connection and reload.';
      const src=await pdfjsLib.getDocument({data:new Uint8Array(await files[0].arrayBuffer())}).promise;
      const out=await PDFDocument.create();
      for(let i=1;i<=src.numPages;i++){
        const pg=await src.getPage(i);
        const vp1=pg.getViewport({scale:1});
        const scale=Math.min(1.4, 1600/Math.max(vp1.width,vp1.height));
        const vp=pg.getViewport({scale});
        const c=document.createElement('canvas'); c.width=vp.width|0; c.height=vp.height|0;
        const ctx=c.getContext('2d'); ctx.fillStyle='#fff'; ctx.fillRect(0,0,c.width,c.height);
        await pg.render({canvasContext:ctx,viewport:vp}).promise;
        const blob=await new Promise(r=>c.toBlob(r,'image/jpeg',0.62));
        const img=await out.embedJpg(await blob.arrayBuffer());
        out.addPage([vp1.width,vp1.height]).drawImage(img,{x:0,y:0,width:vp1.width,height:vp1.height});
      }
      const bytes=await out.save();
      if(bytes.length>=orig) throw 'Extreme mode would make this file <b>larger</b> (it is mostly text, not scans). Use <b>Recommended</b> mode instead.';
      download(bytes, base(files[0])+'-compressed.pdf');
      return '<b>Done!</b> '+fmt(orig)+' → '+fmt(bytes.length)+' ('+Math.round((1-bytes.length/orig)*100)+'% smaller).';
    },
    'repair-pdf': async ()=>{
      const d=await loadDoc(files[0]);
      const out=await PDFDocument.create();
      (await out.copyPages(d, d.getPageIndices())).forEach(pg=>out.addPage(pg));
      download(await out.save(), base(files[0])+'-repaired.pdf');
      return '<b>Done!</b> Rebuilt the PDF structure — '+out.getPageCount()+' page'+(out.getPageCount()>1?'s':'')+' recovered.';
    },
    'ocr-pdf': async ()=>{
      if(!window.Tesseract) throw 'Could not load the OCR engine — check your internet connection and reload the page.';
      const src=await pjsDoc(files[0]);
      const n=Math.min(src.numPages,25);
      const d=await loadDoc(files[0]);
      const font=await d.embedFont(StandardFonts.Helvetica);
      spin('Loading OCR engine…');
      const worker=await Tesseract.createWorker('eng');
      try{
        for(let i=1;i<=n;i++){
          spin('Reading page '+i+' / '+n+'…');
          const pg=await src.getPage(i);
          const vp=pg.getViewport({scale:2});
          const c=document.createElement('canvas'); c.width=vp.width|0; c.height=vp.height|0;
          const ctx=c.getContext('2d'); ctx.fillStyle='#fff'; ctx.fillRect(0,0,c.width,c.height);
          await pg.render({canvasContext:ctx,viewport:vp}).promise;
          const {data}=await worker.recognize(c);
          const page=d.getPage(i-1);
          const ps=page.getSize(), fx=ps.width/c.width, fy=ps.height/c.height;
          (data.words||[]).forEach(wd=>{
            const t=sane(wd.text).trim(); if(!t||!wd.bbox) return;
            const hgt=(wd.bbox.y1-wd.bbox.y0)*fy;
            try{ page.drawText(t,{x:wd.bbox.x0*fx, y:ps.height-wd.bbox.y1*fy, size:Math.max(4,hgt*0.9), font, opacity:0}); }catch(_){}
          });
        }
      } finally { try{ await worker.terminate(); }catch(_){} }
      download(await d.save(), base(files[0])+'-searchable.pdf');
      return '<b>Done!</b> Added a searchable text layer to '+n+' page'+(n>1?'s':'')+(src.numPages>n?' (first '+n+' of '+src.numPages+')':'')+'. The PDF looks the same but its text is now selectable.';
    },
    'pdf-to-word': async ()=>{
      if(!window.docx) throw 'Could not load the Word engine — check your internet connection and reload the page.';
      const src=await pjsDoc(files[0]);
      const sections=[]; let anyText=false;
      const BN={style:docx.BorderStyle.NONE, size:0, color:'FFFFFF'};
      const noB={top:BN,bottom:BN,left:BN,right:BN,insideHorizontal:BN,insideVertical:BN};
      const cellMarg={top:40,bottom:40,left:100,right:100};
      const NUMRX=/^(\(?\d+(\.\d+)*[\.\):]?|\(?[a-zA-Z][\.\)]|\(?[ivxIVX]+\))$/;
      const BULRX=/^[•●▪■◦∙·▸➢✦]$/;
      for(let i=1;i<=src.numPages;i++){
        spin('Page '+i+' / '+src.numPages+'…');
        const pg=await src.getPage(i);
        const vp=pg.getViewport({scale:1});
        let tinyCtx=null,tinyW=0,tinyH=0;
        try{
          const tv=pg.getViewport({scale:0.16});
          const tcv=document.createElement('canvas'); tcv.width=Math.max(1,tv.width|0); tcv.height=Math.max(1,tv.height|0);
          await pg.render({canvasContext:tcv.getContext('2d'),viewport:tv}).promise;
          tinyCtx=tcv.getContext('2d'); tinyW=tcv.width; tinyH=tcv.height;
        }catch(_){}
        const fcache={};
        const fstyle=fn=>{ if(fcache[fn]) return fcache[fn];
          let r={b:false,i:false,fam:null};
          try{ const fo=pg.commonObjs.get(fn); const nm=(fo&&fo.name)||'';
            r.b=/bold|black|heavy|semib/i.test(nm); r.i=/italic|oblique/i.test(nm);
            if(/times|serif|georgia|garamond|book|roman|cambria|palatino/i.test(nm)) r.fam='Times New Roman';
            else if(/courier|mono|consolas/i.test(nm)) r.fam='Courier New';
          }catch(_){}
          return fcache[fn]=r; };
        const tc=await pg.getTextContent();
        const rows=new Map();
        tc.items.forEach(it=>{ if(!it.str||!it.str.trim()) return; const y=Math.round(it.transform[5]/2)*2; if(!rows.has(y)) rows.set(y,[]); rows.get(y).push(it); });
        const lines=[...rows.entries()].sort((a,b)=>b[0]-a[0]).map(([y,its])=>{
          its.sort((a,b)=>a.transform[4]-b.transform[4]);
          let bullet=false;
          const f0=(its[0].str||'').trim();
          if(its.length>1 && f0.length===1 && BULRX.test(f0)){ bullet=true; its=its.slice(1); }
          const sz=Math.max(...its.map(t=>t.height||Math.abs(t.transform[3])||11));
          const segs=[]; let cur={x:its[0].transform[4], right:its[0].transform[4]+(its[0].width||0), parts:[its[0]]};
          for(let k=1;k<its.length;k++){
            const prev=its[k-1], gap=its[k].transform[4]-(prev.transform[4]+(prev.width||0));
            if(gap>12){ segs.push(cur); cur={x:its[k].transform[4], right:0, parts:[its[k]]}; }
            else cur.parts.push(its[k]);
            cur.right=its[k].transform[4]+(its[k].width||0);
          }
          segs.push(cur);
          segs.forEach(sg=>{
            sg.runs=[]; let rr=null, pv=null;
            sg.parts.forEach(t=>{
              const st=fstyle(t.fontName);
              let sp='';
              if(pv){ const gp=t.transform[4]-(pv.transform[4]+(pv.width||0)); if(gp>Math.max(1.1,(t.height||sz)*0.12)) sp=' '; }
              if(rr && rr.b===st.b && rr.i===st.i && rr.fam===st.fam) rr.text+=sp+t.str;
              else { rr={text:sp+t.str, b:st.b, i:st.i, fam:st.fam}; sg.runs.push(rr); }
              pv=t;
            });
            sg.runs.forEach(r=>{ r.text=r.text.replace(/\s+/g,' '); });
            sg.text=sg.runs.map(r=>r.text).join('').replace(/\s+/g,' ').trim();
          });
          return {y, sz, segs, left:segs[0].x, right:segs[segs.length-1].right, bullet};
        }).filter(l=>l.segs.some(sg=>sg.text));
        if(lines.length) anyText=true;
        /* embedded images with positions */
        const imgRuns=[]; const imgMeta=[];
        try{
          const opl=await pg.getOperatorList();
          const O=pdfjsLib.OPS;
          const mul=(a,b)=>[a[0]*b[0]+a[2]*b[1], a[1]*b[0]+a[3]*b[1], a[0]*b[2]+a[2]*b[3], a[1]*b[2]+a[3]*b[3], a[0]*b[4]+a[2]*b[5]+a[4], a[1]*b[4]+a[3]*b[5]+a[5]];
          const stack=[]; let mtx=[1,0,0,1,0,0]; const found=[];
          for(let j=0;j<opl.fnArray.length;j++){
            const fn=opl.fnArray[j], args=opl.argsArray[j];
            if(fn===O.save) stack.push(mtx.slice());
            else if(fn===O.restore) mtx=stack.pop()||mtx;
            else if(fn===O.transform) mtx=mul(mtx,args);
            else if(fn===O.paintImageXObject||fn===O.paintJpegXObject) found.push({id:args[0], m:mtx.slice()});
          }
          const toPng=obj=>{
            if(!obj) return null;
            const cv=document.createElement('canvas');
            if(obj.bitmap){ cv.width=obj.bitmap.width; cv.height=obj.bitmap.height; cv.getContext('2d').drawImage(obj.bitmap,0,0); }
            else if(obj.data&&obj.width&&obj.height){
              cv.width=obj.width; cv.height=obj.height;
              const ctx=cv.getContext('2d'); const im=ctx.createImageData(obj.width,obj.height); const dd=obj.data;
              if(dd.length===obj.width*obj.height*4) im.data.set(dd);
              else if(dd.length===obj.width*obj.height*3){ for(let a=0,b=0;b<dd.length;b+=3,a+=4){ im.data[a]=dd[b]; im.data[a+1]=dd[b+1]; im.data[a+2]=dd[b+2]; im.data[a+3]=255; } }
              else return null;
              ctx.putImageData(im,0,0);
            } else return null;
            return cv.toDataURL('image/png');
          };
          const b64u8=u=>{ const bin=atob(u.split(',')[1]); const arr=new Uint8Array(bin.length); for(let a=0;a<bin.length;a++) arr[a]=bin.charCodeAt(a); return arr; };
          for(const fnd of found.slice(0,15)){
            let obj=null;
            try{ obj=pg.objs.get(fnd.id); }catch(_){ try{ obj=pg.commonObjs.get(fnd.id); }catch(__){} }
            const du=toPng(obj); if(!du) continue;
            const iw=Math.abs(fnd.m[0]), ih=Math.abs(fnd.m[3]);
            if(iw<6||ih<6) continue;
            const ix=fnd.m[4], iyTop=vp.height-(fnd.m[5]+(fnd.m[3]>0?fnd.m[3]:0));
            try{
              imgRuns.push(new docx.ImageRun({data:b64u8(du), transformation:{width:Math.round(iw*96/72), height:Math.round(ih*96/72)},
                floating:{horizontalPosition:{relative:docx.HorizontalPositionRelativeFrom.PAGE, offset:Math.round(ix*12700)},
                          verticalPosition:{relative:docx.VerticalPositionRelativeFrom.PAGE, offset:Math.round(Math.max(0,iyTop)*12700)},
                          behindDocument:(iw*ih>vp.width*vp.height*0.45), allowOverlap:true}}));
              imgMeta.push({x:ix, top:Math.max(0,iyTop), w:iw, h:ih});
            }catch(_){}
          }
        }catch(_){}
        if(imgRuns.length) anyText=true;
        const minX=lines.length?Math.min(...lines.map(l=>l.left)):0;
        const maxR=lines.length?Math.max(...lines.map(l=>l.right)):vp.width;
        const pageTextTop=lines.length? vp.height-(lines[0].y+lines[0].sz) : 0;
        /* column detection on segments */
        let gutter=null;
        const allSegs=[]; lines.forEach(l=>l.segs.forEach(sg=>allSegs.push(sg)));
        if(allSegs.length>=14){
          let best=1e9;
          for(let g=vp.width*0.28; g<=vp.width*0.72; g+=6){
            let cross=0,Lc=0,Rc=0;
            allSegs.forEach(sg=>{ if(sg.right<g-4) Lc++; else if(sg.x>g+4) Rc++; else cross++; });
            if(Lc>=allSegs.length*0.2 && Rc>=allSegs.length*0.2 && cross<best){ best=cross; gutter=g; }
          }
          if(gutter!==null && best>Math.max(2,allSegs.length*0.08)) gutter=null;
        }
        const sampleFill=(x0,x1)=>{
          if(!tinyCtx||x1-x0<20) return null;
          try{
            const cols=[];
            for(const fx of [0.2,0.35,0.5,0.65,0.8]) for(const fy of [0.3,0.42,0.55,0.68,0.8]){
              const px=Math.min(tinyW-1,Math.max(0,Math.round((x0+(x1-x0)*fx)/vp.width*tinyW)));
              const py=Math.min(tinyH-1,Math.max(0,Math.round(fy*tinyH)));
              const d=tinyCtx.getImageData(px,py,1,1).data; cols.push([d[0],d[1],d[2]]);
            }
            const med=ci=>{ const v=cols.map(c=>c[ci]).sort((a,b)=>a-b); return v[Math.floor(v.length/2)]; };
            const m=[med(0),med(1),med(2)];
            const near=cols.filter(c=>Math.abs(c[0]-m[0])<22&&Math.abs(c[1]-m[1])<22&&Math.abs(c[2]-m[2])<22).length;
            if(near<cols.length*0.6) return null;
            if(m[0]>244&&m[1]>244&&m[2]>244) return null;
            return m.map(v=>v.toString(16).padStart(2,'0')).join('').toUpperCase();
          }catch(_){ return null; }
        };
        const LR=(docx.LineRuleType&&docx.LineRuleType.AUTO)||'auto';
        const LX=(docx.LineRuleType&&docx.LineRuleType.EXACT)||'exact';
        const mkRun=(r,sz)=>new docx.TextRun({text:r.text, bold:r.b, italics:r.i, font:r.fam||undefined, size:Math.max(12,Math.round(sz*2))});
        const mkTab=()=>{ try{ if(typeof docx.Tab==='function') return new docx.TextRun({children:[new docx.Tab()]}); }catch(_){} return new docx.TextRun({text:String.fromCharCode(9)}); };
        const buildParas=(ls, colL, colR, tabW)=>{
          const outEls=[]; const colW=Math.max(60,colR-colL);
          const tabPos=Math.max(600,Math.round((Math.min(colW,tabW||colW)-26)*20));
          let para=null, prevY=null;
          const flush=()=>{ if(!para) return;
            const children=para.chunks.map(c=>mkRun(c,para.sz));
            let alignment, indent, bulletOpt;
            const just=para.lines>1 && para.fullCount>=para.lines-1;
            if(para.bullet){ bulletOpt={level:0}; if(just) alignment=docx.AlignmentType.JUSTIFIED; }
            else if(para.center) alignment=docx.AlignmentType.CENTER;
            else if(para.rightA) alignment=docx.AlignmentType.RIGHT;
            else{
              if(just) alignment=docx.AlignmentType.JUSTIFIED;
              const li=Math.round((para.indentLeft-colL)*20);
              if(li>24||para.hang>0) indent={left:Math.max(0,li), hanging:para.hang>0?Math.round(para.hang*20):undefined};
            }
            outEls.push(new docx.Paragraph({alignment, indent, bullet:bulletOpt, spacing:{after:Math.round(para.sz*0.9), line:Math.round(para.sz*22.8), lineRule:LX}, children}));
            para=null; };
          ls.forEach(ln=>{
            const first=ln.segs[0];
            const gap=prevY===null?0:(prevY-ln.y);
            if(prevY!==null && gap>ln.sz*2.4){ flush(); outEls.push(new docx.Paragraph({spacing:{line:100, lineRule:LX, after:0}, children:[]})); }
            const tabR=!ln.bullet && ln.segs.length===2 && !NUMRX.test((first.text.split(' ')[0]||'')) && ln.segs[1].x>colL+colW*0.5 && ln.segs[1].right>colR-colW*0.2 && (first.right-first.x)<colW*0.7;
            if(tabR){
              flush();
              const children=[...first.runs.map(r=>mkRun(r,ln.sz)), mkTab(), ...ln.segs[1].runs.map(r=>mkRun(r,ln.sz))];
              outEls.push(new docx.Paragraph({tabStops:[{type:docx.TabStopType.RIGHT, position:tabPos}], spacing:{after:Math.round(ln.sz*0.9), line:Math.round(ln.sz*22.8), lineRule:LX}, children}));
              prevY=ln.y; return;
            }
            const isNum=ln.segs.length>=2 && NUMRX.test(first.text) && (first.right-first.x)<50;
            const center=!ln.bullet && !isNum && ln.segs.length===1 && Math.abs(((ln.left+ln.right)/2)-(colL+colW/2))<colW*0.06 && (ln.left-colL)>colW*0.12;
            const rightA=!ln.bullet && !isNum && !center && ln.segs.length===1 && (ln.left-colL)>colW*0.55 && ln.right>colR-colW*0.08;
            const lineFull=ln.right>colR-Math.max(16,Math.min(40,colW*0.2));
            const contOK=para && !para.noMerge && !center && !isNum && !ln.bullet && !rightA && Math.abs(ln.sz-para.sz)<1.2 && gap<ln.sz*1.9 && (para.lastFull||para.bullet) && Math.abs(ln.left-para.contLeft)<8 && !NUMRX.test((ln.segs[0].text.split(' ')[0]||''));
            if(contOK){
              para.chunks.push({text:' ', b:false, i:false, fam:null});
              ln.segs.forEach((sg,si)=>{ if(si) para.chunks.push({text:' ', b:false, i:false, fam:null}); sg.runs.forEach(r=>para.chunks.push({...r})); });
              para.lines++; if(lineFull) para.fullCount++;
              para.lastFull=lineFull; prevY=ln.y; return;
            }
            flush();
            para={sz:ln.sz, chunks:[], lines:1, fullCount:lineFull?1:0, lastFull:lineFull, center, rightA, bullet:ln.bullet, indentLeft:ln.left, contLeft:ln.left, hang:0, noMerge:(center||rightA)};
            if(isNum){ const cont=ln.segs[1].x; para.indentLeft=cont; para.contLeft=cont; para.hang=cont-first.x; }
            ln.segs.forEach((sg,si)=>{ if(si) para.chunks.push({text:' ', b:false, i:false, fam:null}); sg.runs.forEach(r=>para.chunks.push({...r})); });
            prevY=ln.y;
          });
          flush();
          return outEls;
        };
        let kids=[];
        if(gutter!==null){
          const top=[], bottom=[], L=[], R=[];
          const mkLn=(l,segs)=>({y:l.y, sz:l.sz, segs, left:segs[0].x, right:segs[segs.length-1].right, bullet:l.bullet});
          const split=[];
          lines.forEach(l=>{
            const Ls=l.segs.filter(sg=>sg.right<gutter-4), Rs=l.segs.filter(sg=>sg.x>gutter+4);
            if(Ls.length+Rs.length<l.segs.length){ split.push({cross:l}); return; }
            split.push({L:Ls.length?mkLn(l,Ls):null, R:Rs.length?mkLn(l,Rs):null});
          });
          const colYs=split.filter(e=>e.L||e.R).map(e=>(e.L||e.R).y);
          const colTopY=colYs.length?Math.max(...colYs):-1e9;
          split.forEach(e=>{
            if(e.cross){ (e.cross.y>colTopY? top: bottom).push(e.cross); return; }
            if(e.L) L.push(e.L);
            if(e.R) R.push(e.R);
          });
          kids.push(...buildParas(top, minX, maxR));
          const lw=Math.max(12,Math.min(88,Math.round(((gutter-minX)/(maxR-minX))*100)));
          const spacerFor=cl=>{ if(!cl.length) return null; const ft=vp.height-(cl[0].y+cl[0].sz); const d=ft-pageTextTop; return d>14? new docx.Paragraph({spacing:{after:Math.round(Math.max(0,d-12)*18), line:240, lineRule:LR}, children:[]}) : null; };
          const contentW=vp.width-minX-36;
          const cellKids=(arr,x0,x1,frac)=>{ const out=[]; const sp=spacerFor(arr); if(sp) out.push(sp); if(arr.length) out.push(...buildParas(arr,x0,x1,contentW*frac-14)); if(!out.length) out.push(new docx.Paragraph({children:[]})); return out; };
          const fillL=sampleFill(Math.max(0,minX-30), gutter-8), fillR=sampleFill(gutter+8, Math.min(vp.width,maxR+30));
          const gridL=Math.round(contentW*20*lw/100), gridR=Math.round(contentW*20*(100-lw)/100);
          kids.push(new docx.Table({
            width:{size:gridL+gridR, type:docx.WidthType.DXA}, borders:noB,
            layout:(docx.TableLayoutType&&docx.TableLayoutType.FIXED)||'fixed',
            columnWidths:[gridL, gridR],
            rows:[new docx.TableRow({children:[
              new docx.TableCell({borders:noB, margins:cellMarg, shading:fillL?{fill:fillL, type:docx.ShadingType.CLEAR, color:'auto'}:undefined, width:{size:gridL, type:docx.WidthType.DXA}, children:cellKids(L, L.length?Math.min(...L.map(l=>l.left)):minX, gutter, lw/100)}),
              new docx.TableCell({borders:noB, margins:cellMarg, shading:fillR?{fill:fillR, type:docx.ShadingType.CLEAR, color:'auto'}:undefined, width:{size:gridR, type:docx.WidthType.DXA}, children:cellKids(R, R.length?Math.min(...R.map(l=>l.left)):gutter, maxR, (100-lw)/100)})
            ]})]
          }));
          kids.push(...buildParas(bottom, minX, maxR));
        } else {
          kids=buildParas(lines, minX, maxR);
        }
        if(imgRuns.length) kids.unshift(new docx.Paragraph({children:imgRuns, spacing:{after:0}}));
        sections.push({
          properties:{page:{size:{width:Math.round(vp.width*20), height:Math.round(vp.height*20)}, margin:{top:Math.max(400,Math.round(pageTextTop*20)), right:720, bottom:300, left:Math.max(500,Math.round(minX*20))}}},
          children:kids.length?kids:[new docx.Paragraph({children:[]})]
        });
      }
      if(!anyText) throw 'Nothing could be extracted from this PDF — it may be empty or corrupted.';
      const blob=await docx.Packer.toBlob(new docx.Document({sections}));
      download(blob, base(files[0])+'.docx');
      return '<b>Done!</b> '+src.numPages+' page'+(src.numPages>1?'s':'')+' converted — text layout, fonts, bullets, columns, colors and images rebuilt like the PDF.';
    },
    'pdf-to-excel': async ()=>{
      if(!window.XLSX) throw 'Could not load the Excel engine — check your internet connection and reload the page.';
      const src=await pjsDoc(files[0]);
      const wb=XLSX.utils.book_new(); let any=false;
      for(let i=1;i<=src.numPages;i++){
        spin('Page '+i+' / '+src.numPages+'…');
        const lines=await pageLines(await src.getPage(i));
        const aoa=lines.map(its=>{
          const cells=[]; let cur=its[0].s;
          for(let k=1;k<its.length;k++){
            const gap=its[k].x-(its[k-1].x+its[k-1].w);
            if(gap>10){ cells.push(cur.replace(/\s+/g,' ').trim()); cur=its[k].s; } else cur+=' '+its[k].s;
          }
          cells.push(cur.replace(/\s+/g,' ').trim());
          return cells;
        }).filter(r=>r.some(c=>c));
        if(aoa.length){ any=true; XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(aoa), ('Page '+i).slice(0,31)); }
      }
      if(!any) throw 'No selectable text found in this PDF — it looks like a scan. Run <b>OCR PDF</b> on it first, then convert.';
      const buf=XLSX.write(wb,{type:'array',bookType:'xlsx'});
      download(new Blob([buf],{type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'}), base(files[0])+'.xlsx');
      return '<b>Done!</b> '+src.numPages+' page'+(src.numPages>1?'s':'')+' exported to Excel — one sheet per page.';
    },
    'pdf-to-ppt': async ()=>{
      if(!window.PptxGenJS) throw 'Could not load the PowerPoint engine — check your internet connection and reload the page.';
      const src=await pjsDoc(files[0]);
      const pptx=new PptxGenJS();
      const v1=(await src.getPage(1)).getViewport({scale:1});
      const W=10, H=Math.round(W*v1.height/v1.width*100)/100;
      pptx.defineLayout({name:'Z', width:W, height:H}); pptx.layout='Z';
      const n=Math.min(src.numPages,60);
      for(let i=1;i<=n;i++){
        spin('Slide '+i+' / '+n+'…');
        const pg=await src.getPage(i);
        const s1=pg.getViewport({scale:1});
        const vp=pg.getViewport({scale:Math.min(2,1920/s1.width)});
        const c=document.createElement('canvas'); c.width=vp.width|0; c.height=vp.height|0;
        const ctx=c.getContext('2d'); ctx.fillStyle='#fff'; ctx.fillRect(0,0,c.width,c.height);
        await pg.render({canvasContext:ctx,viewport:vp}).promise;
        pptx.addSlide().addImage({data:c.toDataURL('image/jpeg',0.85), x:0, y:0, w:W, h:H});
      }
      const blob=await pptx.write({outputType:'blob'});
      download(blob, base(files[0])+'.pptx');
      return '<b>Done!</b> '+n+' page'+(n>1?'s':'')+' converted to slides'+(src.numPages>n?' (first '+n+')':'')+'.';
    },
    'word-to-pdf': async ()=>{
      if(!window.docx||!window.html2canvas) throw 'Could not load the converter — check your internet connection and reload the page.';
      const bufA=await files[0].arrayBuffer();
      let zip=null, dx='', pgW=595.3, pgH=841.9, margL=56.7, margT=56.7;
      try{
        if(window.JSZip && zip===null){
          zip=await JSZip.loadAsync(bufA.slice(0));
          dx=await zip.files['word/document.xml'].async('string');
          let m=dx.match(/<w:pgSz[^>]*w:w="(\d+)"[^>]*w:h="(\d+)"/);
          if(m){ pgW=+m[1]/20; pgH=+m[2]/20; }
          else { m=dx.match(/<w:pgSz[^>]*w:h="(\d+)"/); if(m) pgH=+m[1]/20; }
          const pm=dx.match(/<w:pgMar([^>]*)\/>/);
          if(pm){ const at=pm[1]; const g=n=>{ const r=at.match(new RegExp('w:'+n+'="(-?\\d+)"')); return r? +r[1]/20 : 56.7; }; margT=g('top'); margL=g('left'); }
        }
      }catch(_){ zip=null; }
      const tbs=(dx.match(/txbxContent/g)||[]).length;
      const tbls=(dx.match(/<w:tbl>/g)||[]).length;

      /* ───── TEMPLATE MODE: document built from floating text boxes ───── */
      if(zip && tbs>=6 && tbls===0){
        spin('Reading layout…');
        const xml=new DOMParser().parseFromString(dx,'application/xml');
        const rels={};
        try{
          const rx=new DOMParser().parseFromString(await zip.files['word/_rels/document.xml.rels'].async('string'),'application/xml');
          [...rx.getElementsByTagName('Relationship')].forEach(r=>{ rels[r.getAttribute('Id')]=r.getAttribute('Target'); });
        }catch(_){}
        const out=await PDFDocument.create();
        const fontR=await out.embedFont(StandardFonts.Helvetica), fontB=await out.embedFont(StandardFonts.HelveticaBold);
        const fontI=await out.embedFont(StandardFonts.HelveticaOblique), fontBI=await out.embedFont(StandardFonts.HelveticaBoldOblique);
        const pick=(b,i)=> b&&i?fontBI : b?fontB : i?fontI : fontR;
        let defSz=11;
        try{
          const sx=await zip.files['word/styles.xml'].async('string');
          const dm=sx.match(/<w:docDefaults>[\s\S]*?<w:sz w:val="(\d+)"/);
          if(dm) defSz=+dm[1]/2;
        }catch(_){}
        /* measure real paragraph positions: strip drawings, render body with docx-preview */
        const paraInfo=[];
        try{
          const zip2=await JSZip.loadAsync(bufA.slice(0));
          zip2.file('word/document.xml', dx.replace(/<w:drawing>[\s\S]*?<\/w:drawing>/g,''));
          const stripped=await zip2.generateAsync({type:'arraybuffer'});
          const fr2=document.createElement('iframe');
          fr2.style.cssText='position:absolute;left:-10000px;top:0;width:1100px;height:1200px;border:0;';
          document.body.appendChild(fr2);
          try{
            const id2=fr2.contentDocument;
            id2.open(); id2.write('<!doctype html><html><head><meta charset="utf-8"><style>section.docx p{min-height:1.12em}</style></head><body style="margin:0"></body></html>'); id2.close();
            const h2=id2.createElement('div'); id2.body.appendChild(h2);
            await docx.renderAsync(stripped, h2);
            let acc=0;
            [...h2.querySelectorAll('section.docx')].forEach(sec=>{
              const sr=sec.getBoundingClientRect();
              [...sec.querySelectorAll('p')].forEach(pe=>{
                const r=pe.getBoundingClientRect();
                paraInfo.push({y:acc+(r.top-sr.top)*72/96});
              });
              acc+=sr.height*72/96+9999; /* big jump between rendered sections */
            });
          } finally { fr2.remove(); }
        }catch(_){}
        const body=xml.getElementsByTagName('w:body')[0];
        const base0=paraInfo.length?paraInfo[0].y:0;
        let pageBase=base0;
        const anchors=[]; let pgIdx=0; let flowY=margT; let pIdx=0;
        [...body.children].forEach(ch=>{
          if(ch.tagName!=='w:p') return;
          let np=false;
          [...ch.getElementsByTagName('w:br')].forEach(b=>{ if(b.getAttribute('w:type')==='page') np=true; });
          if(ch.getElementsByTagName('w:lastRenderedPageBreak').length) np=true;
          if(np && anchors.length){ pgIdx++; flowY=margT; if(paraInfo[pIdx]) pageBase=paraInfo[pIdx].y; }
          /* fallback estimate if the measured list is missing */
          let sz=defSz; const szEl=ch.getElementsByTagName('w:sz')[0]; if(szEl) sz=+szEl.getAttribute('w:val')/2;
          let before=0, after=0, line=0, rule='';
          const spEl=ch.getElementsByTagName('w:spacing')[0];
          if(spEl){ before=+(spEl.getAttribute('w:before')||0)/20; after=+(spEl.getAttribute('w:after')||0)/20; line=+(spEl.getAttribute('w:line')||0); rule=spEl.getAttribute('w:lineRule')||''; }
          const lh= line? (rule==='auto'? (line/240)*sz*1.15 : line/20) : sz*1.15;
          const info=paraInfo[pIdx];
          const paraY=info? (margT+info.y-pageBase) : (flowY+before);
          [...ch.getElementsByTagName('wp:anchor')].forEach(a=>anchors.push({a, pg:pgIdx, paraY}));
          flowY+=before+lh+after; pIdx++;
        });
        if(!anchors.length) throw 'Could not read this file — make sure it is a .docx Word document.';
        const nPages=pgIdx+1;
        const pages=[]; for(let k=0;k<nPages;k++) pages.push(out.addPage([pgW,pgH]));
        const E=12700;
        const imgCache={};
        const getImg=async rid=>{
          if(!rid||!rels[rid]) return null;
          let t='word/'+rels[rid].replace(/^\//,'').replace(/^\.\.\//,'');
          if(!zip.files[t]) t=rels[rid].replace(/^\//,'');
          if(!zip.files[t]) return null;
          if(t in imgCache) return imgCache[t];
          let im=null;
          try{
            const bts=await zip.files[t].async('uint8array');
            if(/\.png$/i.test(t)) im=await out.embedPng(bts);
            else if(/\.jpe?g$/i.test(t)) im=await out.embedJpg(bts);
            else{ const bmp=await createImageBitmap(new Blob([bts])); const cv=document.createElement('canvas'); cv.width=bmp.width; cv.height=bmp.height; cv.getContext('2d').drawImage(bmp,0,0); im=await out.embedPng(cv.toDataURL('image/png')); }
          }catch(_){ im=null; }
          imgCache[t]=im; return im;
        };
        const hex2rgb=h=>{ try{ return rgb(parseInt(h.slice(0,2),16)/255, parseInt(h.slice(2,4),16)/255, parseInt(h.slice(4,6),16)/255); }catch(_){ return rgb(0.07,0.07,0.1); } };
        /* theme colors (sidebar fills etc.) */
        let theme={};
        try{
          const th=await zip.files['word/theme/theme1.xml'].async('string');
          const cs=th.match(/<a:clrScheme[\s\S]*?<\/a:clrScheme>/);
          if(cs) [...cs[0].matchAll(/<a:(\w+)>\s*<a:(?:srgbClr val="([0-9A-Fa-f]{6})"|sysClr[^>]*lastClr="([0-9A-Fa-f]{6})")/g)].forEach(m=>{ theme[m[1]]=(m[2]||m[3]); });
          theme.tx1=theme.tx1||theme.dk1; theme.bg1=theme.bg1||theme.lt1; theme.tx2=theme.tx2||theme.dk2; theme.bg2=theme.bg2||theme.lt2;
        }catch(_){}
        const fillOf=sp=>{
          if(!sp) return null;
          for(const sf of [...sp.getElementsByTagName('a:solidFill')]){
            if(sf.parentNode!==sp) continue;
            const sc=sf.getElementsByTagName('a:srgbClr')[0];
            if(sc) return sc.getAttribute('val');
            const sh=sf.getElementsByTagName('a:schemeClr')[0];
            if(sh){
              let hx=theme[sh.getAttribute('val')];
              if(hx){
                const lm=sh.getElementsByTagName('a:lumMod')[0], lo=sh.getElementsByTagName('a:lumOff')[0];
                if(lm||lo){
                  const mod=lm? +lm.getAttribute('val')/100000 : 1, off=lo? +lo.getAttribute('val')/100000 : 0;
                  const ch=i2=>Math.max(0,Math.min(255,Math.round(parseInt(hx.slice(i2,i2+2),16)*mod+255*off)));
                  hx=[ch(0),ch(2),ch(4)].map(v=>v.toString(16).padStart(2,'0')).join('');
                }
                return hx;
              }
            }
          }
          return null;
        };
        /* phase 1: resolve geometry + tokenize + measure */
        const boxes=[];
        for(const A of anchors){
          const a=A.a;
          const ext=a.getElementsByTagName('wp:extent')[0]; if(!ext) continue;
          const bw=+ext.getAttribute('cx')/E, bhRaw=+ext.getAttribute('cy')/E;
          const getPos=(tag,base,size,max,paraBase)=>{
            const e=a.getElementsByTagName(tag)[0]; if(!e) return base;
            const po=e.getElementsByTagName('wp:posOffset')[0];
            const al=e.getElementsByTagName('wp:align')[0];
            const rf=e.getAttribute('relativeFrom')||'page';
            if(al){ const v=al.textContent; if(v==='center') return (max-size)/2; if(v==='right'||v==='bottom') return max-base-size; return base; }
            let v=po? (+po.textContent)/E : 0;
            if(rf==='paragraph'||rf==='line') v+=paraBase;
            else if(rf!=='page') v+=base;
            return v;
          };
          const bx=getPos('wp:positionH', margL, bw, pgW, margL);
          const by=getPos('wp:positionV', margT, bhRaw, pgH, A.paraY);
          const fill=fillOf(a.getElementsByTagName('wps:spPr')[0]);
          const hasTxt=a.getElementsByTagName('w:txbxContent').length>0;
          const blip=a.getElementsByTagName('a:blip')[0];
          const box={pg:Math.min(A.pg,nPages-1), bx, by, bw, bh:bhRaw, fill, behind:a.getAttribute('behindDoc')==='1', img:null, paras:null, usedH:bhRaw};
          if(blip && !hasTxt){ box.img=blip.getAttribute('r:embed'); boxes.push(box); continue; }
          const tb=a.getElementsByTagName('w:txbxContent')[0];
          if(!tb){ boxes.push(box); continue; }
          const maxW=Math.max(20,bw-11.5);
          const paras=[]; let contentH=3.2;
          for(const pEl of [...tb.getElementsByTagName('w:p')]){
            let jc='left'; const jcEl=pEl.getElementsByTagName('w:jc')[0]; if(jcEl) jc=jcEl.getAttribute('w:val')||'left';
            const bullet=pEl.getElementsByTagName('w:numPr').length>0;
            const toks=[];
            if(bullet) toks.push({t:'•  ', b:false, i:false, sz:null, color:null, u:false});
            const collect=(rEl,link)=>{
              const rPr=rEl.getElementsByTagName('w:rPr')[0];
              let sz=null,b=false,it=false,col=null,u=false;
              if(rPr){
                const se=rPr.getElementsByTagName('w:sz')[0]; if(se) sz=+se.getAttribute('w:val')/2;
                const be=rPr.getElementsByTagName('w:b')[0]; b=!!be && be.getAttribute('w:val')!=='0';
                it=!!rPr.getElementsByTagName('w:i').length;
                const ce=rPr.getElementsByTagName('w:color')[0]; if(ce){ const v=ce.getAttribute('w:val'); if(v && v!=='auto') col=v; }
                u=!!rPr.getElementsByTagName('w:u').length;
              }
              if(link){ col=col||'0563C1'; u=true; }
              const text=[...rEl.getElementsByTagName('w:t')].map(t=>t.textContent).join('');
              text.split(/(\s+)/).forEach(w2=>{ if(w2) toks.push({t:w2, b, i:it, sz, color:col, u}); });
            };
            [...pEl.children].forEach(ch=>{
              if(ch.tagName==='w:r') collect(ch,false);
              else if(ch.tagName==='w:hyperlink') [...ch.getElementsByTagName('w:r')].forEach(r2=>collect(r2,true));
            });
            const baseSz=Math.max(0,...toks.map(t2=>t2.sz||0))||defSz;
            toks.forEach(t2=>{ if(!t2.sz) t2.sz=baseSz; t2.t=sane(t2.t); });
            const tf=toks.filter(t2=>t2.t.length);
            if(!tf.length){ paras.push({empty:true, h:baseSz*1.05}); contentH+=baseSz*1.05; continue; }
            const lines2=[[]]; let lw=0;
            tf.forEach(t2=>{
              const wpt=pick(t2.b,t2.i).widthOfTextAtSize(t2.t,t2.sz);
              if(lw+wpt>maxW && lines2[lines2.length-1].length){ lines2.push([]); lw=0; if(!t2.t.trim()) return; }
              lines2[lines2.length-1].push(t2); lw+=wpt;
            });
            const lineH=baseSz*1.22;
            paras.push({jc, lines:lines2, lineH, gap:baseSz*0.3});
            contentH+=lines2.length*lineH+baseSz*0.3;
          }
          box.paras=paras;
          box.usedH=Math.max(bhRaw, contentH);
          boxes.push(box);
        }
        /* phase 2: fix small overlaps between stacked boxes in the same column */
        for(let pgI=0; pgI<nPages; pgI++){
          const col=boxes.filter(b2=>{
            if(b2.pg!==pgI||b2.behind) return false;
            if(b2.usedH>pgH*0.5) return false;
            const hasTxt=b2.paras&&b2.paras.some(pr=>pr.lines&&pr.lines.some(l2=>l2.length));
            return hasTxt||b2.img;
          }).sort((x2,y2)=>x2.by-y2.by);
          for(let i2=0;i2<col.length;i2++){
            for(let j2=0;j2<i2;j2++){
              const A2=col[j2], B2=col[i2];
              const ov=Math.min(A2.bx+A2.bw, B2.bx+B2.bw)-Math.max(A2.bx, B2.bx);
              if(ov>Math.min(A2.bw,B2.bw)*0.5){
                const minY=A2.by+A2.usedH+2;
                if(B2.by<minY && B2.by>A2.by) B2.by=minY;
              }
            }
          }
        }
        boxes.forEach(b2=>{ if(b2.by<2 && (b2.paras||b2.img)) b2.by=2; });
        /* phase 3: draw */
        const drawText=(page,box)=>{
          let ty=pgH-box.by-3.2;
          const left=box.bx+5.8, maxW=Math.max(20,box.bw-11.5);
          const bottom=box.bh>5? pgH-box.by-Math.max(box.bh,box.usedH)-4 : 0;
          for(const pr of box.paras){
            if(pr.empty){ ty-=pr.h; continue; }
            for(const lnT of pr.lines){
              if(ty-pr.lineH < bottom-3) return;
              const lwAll=lnT.reduce((acc,t2)=>acc+pick(t2.b,t2.i).widthOfTextAtSize(t2.t,t2.sz),0);
              let cx2= pr.jc==='center'? left+(maxW-lwAll)/2 : (pr.jc==='right'? left+maxW-lwAll : left);
              ty-=pr.lineH;
              for(const t2 of lnT){
                const f=pick(t2.b,t2.i);
                const wpt=f.widthOfTextAtSize(t2.t,t2.sz);
                if(t2.t.trim()){
                  const col=t2.color? hex2rgb(t2.color) : rgb(0.07,0.07,0.1);
                  try{ page.drawText(t2.t,{x:cx2, y:ty, size:t2.sz, font:f, color:col}); }catch(_){}
                  if(t2.u){ try{ page.drawLine({start:{x:cx2,y:ty-1.6}, end:{x:cx2+wpt,y:ty-1.6}, thickness:0.6, color:col}); }catch(_){} }
                }
                cx2+=wpt;
              }
            }
            ty-=pr.gap;
          }
        };
        let done=0;
        const order=[...boxes.filter(b2=>b2.behind), ...boxes.filter(b2=>!b2.behind)];
        for(const box of order){
          spin('Drawing '+(++done)+' / '+order.length+'…');
          const page=pages[box.pg];
          if(box.fill && box.bw>1 && box.bh>1) page.drawRectangle({x:box.bx, y:pgH-box.by-Math.max(box.bh,box.usedH), width:box.bw, height:Math.max(box.bh,box.usedH), color:hex2rgb(box.fill)});
          if(box.img){
            const im=await getImg(box.img);
            if(im){ try{ page.drawImage(im,{x:box.bx, y:pgH-box.by-box.bh, width:box.bw, height:box.bh}); }catch(_){} }
            continue;
          }
          if(box.paras) drawText(page,box);
        }
        download(await out.save(), base(files[0])+'.pdf');
        return '<b>Done!</b> '+nPages+' page'+(nPages>1?'s':'')+' — template layout rebuilt at exact positions, with selectable text.';
      }

      /* ───── FLOW MODE: normal documents (rendered in an isolated iframe) ───── */
      const fr=document.createElement('iframe');
      fr.style.cssText='position:absolute;left:-10000px;top:0;width:1100px;height:1200px;border:0;';
      document.body.appendChild(fr);
      try{
        const idoc=fr.contentDocument;
        idoc.open(); idoc.write('<!doctype html><html><head><meta charset="utf-8"></head><body style="margin:0;background:#fff"></body></html>'); idoc.close();
        const host=idoc.createElement('div'); idoc.body.appendChild(host);
        spin('Reading document…');
        await docx.renderAsync(bufA.slice(0), host, null, {useBase64URL:true});
        await Promise.all([...host.querySelectorAll('img')].map(im=>im.complete?Promise.resolve():new Promise(r=>{ im.onload=r; im.onerror=r; })));
        await new Promise(r=>setTimeout(r,250));
        const sections=[...host.querySelectorAll('section.docx')];
        if(!sections.length) throw 'Could not read this file — make sure it is a .docx Word document.';
        const out=await PDFDocument.create();
        const font=await out.embedFont(StandardFonts.Helvetica);
        for(let i=0;i<sections.length;i++){
          const sec=sections[i];
          const srect=sec.getBoundingClientRect();
          const wPt=srect.width*72/96;
          const pH=pgH||(Math.abs(wPt-612)<25?792:Math.round(wPt*1.4142));
          spin('Rendering…');
          const c=await html2canvas(sec,{scale:2,backgroundColor:'#fff',logging:false});
          const pxPerPt=c.width/wPt;
          const pageHpx=Math.max(100,Math.round(pH*pxPerPt));
          const words=[];
          const walker=idoc.createTreeWalker(sec,NodeFilter.SHOW_TEXT);
          let nd;
          while((nd=walker.nextNode())){
            const txt=nd.textContent; if(!txt||!txt.trim()) continue;
            for(const mw of txt.matchAll(/\S+/g)){
              const wrd=sane(mw[0]); if(!wrd) continue;
              try{
                const rg=idoc.createRange(); rg.setStart(nd,mw.index); rg.setEnd(nd,mw.index+mw[0].length);
                const r=rg.getClientRects()[0]; if(!r||r.width<0.5||r.height<0.5) continue;
                words.push({t:wrd, x:(r.left-srect.left)*2, y:(r.top-srect.top)*2, h:r.height*2});
              }catch(_){}
            }
          }
          const ctx=c.getContext('2d');
          const blankRow=yy=>{
            if(yy<=0||yy>=c.height) return true;
            try{
              const d=ctx.getImageData(0,yy,c.width,1).data;
              const r0=d[0],g0=d[1],b0=d[2];
              for(let k=4;k<d.length;k+=40){ if(Math.abs(d[k]-r0)>8||Math.abs(d[k+1]-g0)>8||Math.abs(d[k+2]-b0)>8) return false; }
              return true;
            }catch(_){ return true; }
          };
          let cur=0, guard=0;
          while(cur<c.height-4 && guard<80){
            guard++;
            spin('Page '+(out.getPageCount()+1)+'…');
            let cut=Math.min(c.height, cur+pageHpx);
            if(cut<c.height){
              let found=-1;
              for(let yy=cut; yy>cut-180 && yy>cur+pageHpx*0.5; yy-=3){ if(blankRow(yy)){ found=yy; break; } }
              if(found>0) cut=found;
            }
            const sh=cut-cur;
            const sc=document.createElement('canvas'); sc.width=c.width; sc.height=sh;
            sc.getContext('2d').drawImage(c,0,cur,c.width,sh,0,0,c.width,sh);
            const img=await out.embedJpg(sc.toDataURL('image/jpeg',0.95));
            const page=out.addPage([wPt,pH]);
            const shPt=sh/pxPerPt;
            page.drawImage(img,{x:0,y:pH-shPt,width:wPt,height:shPt});
            for(const wd of words){
              if(wd.y<cur||wd.y>=cut) continue;
              const hPt=wd.h/pxPerPt;
              try{ page.drawText(wd.t,{x:wd.x/pxPerPt, y:pH-((wd.y-cur)/pxPerPt)-hPt*0.82, size:Math.max(3,hPt*0.82), font, opacity:0}); }catch(_){}
            }
            cur=cut;
          }
        }
        download(await out.save(), base(files[0])+'.pdf');
        return '<b>Done!</b> '+out.getPageCount()+' page'+(out.getPageCount()>1?'s':'')+' — exact Word look, with selectable text.';
      } finally { fr.remove(); }
    },
    'ppt-to-pdf': async ()=>{
      if(!window.JSZip) throw 'Could not load the converter — check your internet connection and reload the page.';
      const zip=await JSZip.loadAsync(await files[0].arrayBuffer());
      const names=Object.keys(zip.files).filter(nm=>/^ppt\/slides\/slide\d+\.xml$/.test(nm)).sort((a,b)=>+a.match(/\d+/)[0]-+b.match(/\d+/)[0]);
      if(!names.length) throw 'Could not read this file — make sure it is a .pptx presentation.';
      const E=12700; /* EMU per point */
      let SW=960, SH=540;
      try{
        const pres=new DOMParser().parseFromString(await zip.files['ppt/presentation.xml'].async('string'),'application/xml');
        const sz=pres.getElementsByTagName('p:sldSz')[0];
        if(sz){ SW=+sz.getAttribute('cx')/E; SH=+sz.getAttribute('cy')/E; }
      }catch(_){}
      const out=await PDFDocument.create();
      const fontB=await out.embedFont(StandardFonts.HelveticaBold), font=await out.embedFont(StandardFonts.Helvetica);
      const imgCache={};
      let sn=0;
      for(const nm of names){
        sn++; spin('Slide '+sn+' / '+names.length+'…');
        const xml=new DOMParser().parseFromString(await zip.files[nm].async('string'),'application/xml');
        const rels={};
        try{
          const rp='ppt/slides/_rels/'+nm.split('/').pop()+'.rels';
          if(zip.files[rp]){
            const rx=new DOMParser().parseFromString(await zip.files[rp].async('string'),'application/xml');
            [...rx.getElementsByTagName('Relationship')].forEach(r=>{ rels[r.getAttribute('Id')]=r.getAttribute('Target'); });
          }
        }catch(_){}
        const page=out.addPage([SW,SH]);
        const getXfrm=el=>{
          const xf=el.getElementsByTagName('a:xfrm')[0]; if(!xf) return null;
          const off=xf.getElementsByTagName('a:off')[0], ext=xf.getElementsByTagName('a:ext')[0];
          if(!off||!ext) return null;
          return {x:+off.getAttribute('x')/E, y:+off.getAttribute('y')/E, w:+ext.getAttribute('cx')/E, h:+ext.getAttribute('cy')/E};
        };
        /* pictures at their slide positions */
        for(const pic of [...xml.getElementsByTagName('p:pic')]){
          try{
            const blip=pic.getElementsByTagName('a:blip')[0]; if(!blip) continue;
            const rid=blip.getAttribute('r:embed'); if(!rid||!rels[rid]) continue;
            let tgt=rels[rid].replace(/^\.\.\//,'ppt/');
            if(!zip.files[tgt]) tgt='ppt/slides/'+rels[rid];
            if(!zip.files[tgt]) continue;
            if(!(tgt in imgCache)){
              const bytes=await zip.files[tgt].async('uint8array');
              let img=null;
              try{
                if(/\.png$/i.test(tgt)) img=await out.embedPng(bytes);
                else if(/\.jpe?g$/i.test(tgt)) img=await out.embedJpg(bytes);
                else{
                  const bmp=await createImageBitmap(new Blob([bytes]));
                  const cv=document.createElement('canvas'); cv.width=bmp.width; cv.height=bmp.height;
                  cv.getContext('2d').drawImage(bmp,0,0);
                  img=await out.embedPng(cv.toDataURL('image/png'));
                }
              }catch(_){}
              imgCache[tgt]=img;
            }
            const img=imgCache[tgt]; if(!img) continue;
            const xf=getXfrm(pic)||{x:0,y:0,w:SW,h:SH};
            page.drawImage(img,{x:xf.x, y:SH-xf.y-xf.h, width:xf.w, height:xf.h});
          }catch(_){}
        }
        /* text shapes at their slide positions */
        let fallbackY=SH-50;
        for(const sp of [...xml.getElementsByTagName('p:sp')]){
          const xf=getXfrm(sp);
          const left=xf? xf.x+4 : 40;
          const maxW=xf? Math.max(40,xf.w-8) : SW-80;
          let ty=xf? SH-xf.y-4 : fallbackY;
          for(const pr of [...sp.getElementsByTagName('a:p')]){
            const text=sane([...pr.getElementsByTagName('a:t')].map(t=>t.textContent).join('')).replace(/\s+/g,' ').trim();
            let sz=14, bold=false;
            const rpr=pr.getElementsByTagName('a:rPr')[0];
            if(rpr){ const v=+rpr.getAttribute('sz'); if(v) sz=v/100; if(rpr.getAttribute('b')==='1') bold=true; }
            const fnt=bold?fontB:font;
            if(!text){ ty-=sz*1.3; continue; }
            let rem=text;
            while(rem){
              let chunk=rem;
              while(fnt.widthOfTextAtSize(chunk,sz)>maxW && chunk.includes(' ')) chunk=chunk.slice(0,chunk.lastIndexOf(' '));
              while(fnt.widthOfTextAtSize(chunk,sz)>maxW && chunk.length>1) chunk=chunk.slice(0,-1);
              ty-=sz*1.2;
              if(ty<6){ rem=''; break; }
              try{ page.drawText(chunk,{x:left,y:ty,size:sz,font:fnt,color:rgb(0.08,0.08,0.15)}); }catch(_){}
              rem=rem.slice(chunk.length).trim();
            }
            ty-=sz*0.25;
          }
          if(!xf) fallbackY=ty-10;
        }
      }
      download(await out.save(), base(files[0])+'.pdf');
      return '<b>Done!</b> '+names.length+' slide'+(names.length>1?'s':'')+' converted — images and text placed at their slide positions (animations and complex effects are simplified).';
    },
    'excel-to-pdf': async ()=>{
      if(!window.XLSX) throw 'Could not load the Excel engine — check your internet connection and reload the page.';
      const wb=XLSX.read(await files[0].arrayBuffer(),{type:'array'});
      const out=await PDFDocument.create();
      const font=await out.embedFont(StandardFonts.Helvetica), fontB=await out.embedFont(StandardFonts.HelveticaBold);
      const PW=842, PH=595, M=34; let wrote=false;
      wb.SheetNames.forEach(snm=>{
        const aoa=XLSX.utils.sheet_to_json(wb.Sheets[snm],{header:1,raw:false,defval:''});
        if(!aoa.some(r=>r.some(c=>String(c).trim()))) return;
        wrote=true;
        const cols=Math.min(Math.max(...aoa.map(r=>r.length)),16);
        const widths=[];
        for(let cI=0;cI<cols;cI++){ let w=34; aoa.slice(0,300).forEach(r=>{ const t=sane(String(r[cI]==null?'':r[cI])); if(t) w=Math.max(w,Math.min(170,font.widthOfTextAtSize(t,8)+10)); }); widths.push(w); }
        const sc=Math.min(1,(PW-2*M)/widths.reduce((a,b)=>a+b,0));
        let page=out.addPage([PW,PH]); let y=PH-M;
        page.drawText(sane(snm),{x:M,y:y-6,size:12,font:fontB}); y-=26;
        aoa.forEach((r,ri)=>{
          if(y<M+8){ page=out.addPage([PW,PH]); y=PH-M; }
          let x=M;
          for(let cI=0;cI<cols;cI++){
            let t=sane(String(r[cI]==null?'':r[cI])).replace(/\s+/g,' ').trim();
            const cw=widths[cI]*sc;
            while(t && font.widthOfTextAtSize(t,8)>cw-4) t=t.slice(0,-1);
            if(t){ try{ page.drawText(t,{x:x+2,y:y-8,size:8,font:ri===0?fontB:font}); }catch(_){} }
            x+=cw;
          }
          y-=13;
        });
      });
      if(!wrote) throw 'This spreadsheet appears to be empty.';
      download(await out.save(), base(files[0])+'.pdf');
      return '<b>Done!</b> '+wb.SheetNames.length+' sheet'+(wb.SheetNames.length>1?'s':'')+' converted to PDF.';
    },
    'pdf-to-jpg': async ()=>{
      const doc=await pjsDoc(files[0]);
      const n=Math.min(doc.numPages,100);
      const blobs=[];
      for(let i=1;i<=n;i++){
        spin('Page '+i+' / '+n+'…');
        const pg=await doc.getPage(i);
        const s1=pg.getViewport({scale:1});
        const vp=pg.getViewport({scale:Math.min(2, 1800/Math.max(s1.width,s1.height))});
        const c=document.createElement('canvas'); c.width=vp.width|0; c.height=vp.height|0;
        const ctx=c.getContext('2d'); ctx.fillStyle='#fff'; ctx.fillRect(0,0,c.width,c.height);
        await pg.render({canvasContext:ctx,viewport:vp}).promise;
        blobs.push(await new Promise(r=>c.toBlob(r,'image/jpeg',0.92)));
      }
      if(blobs.length===1){ download(blobs[0], base(files[0])+'.jpg', 'image/jpeg'); return '<b>Done!</b> Page saved as a JPG image.'; }
      if(!window.JSZip) throw 'Could not load the ZIP engine — check your internet connection and reload the page.';
      const zip=new JSZip();
      blobs.forEach((b,i)=>zip.file('page-'+(i+1)+'.jpg', b));
      download(await zip.generateAsync({type:'blob'}), base(files[0])+'-images.zip', 'application/zip');
      return '<b>Done!</b> '+blobs.length+' pages saved as JPG images (ZIP).';
    },
    'edit-pdf': async ()=>{
      if(!ED || !ED.pages.some(p=>p.used)) throw 'Nothing to save yet — add some text, drawings or images first.';
      const d=await loadDoc(files[0]);
      for(let i=0;i<ED.pages.length;i++){
        const st=ED.pages[i]; if(!st.used) continue;
        const png=await d.embedPng(st.ov.toDataURL('image/png'));
        const page=d.getPage(i);
        const sz=page.getSize();
        page.drawImage(png,{x:0,y:0,width:sz.width,height:sz.height});
      }
      download(await d.save(), base(files[0])+'-edited.pdf');
      return '<b>Done!</b> Your edited PDF has been downloaded.';
    },
    'jpg-to-pdf': async ()=>{
      const out=await PDFDocument.create();
      for(const f of files){
        const bytes=await f.arrayBuffer();
        const img=/png$/i.test(f.type)||/\.png$/i.test(f.name) ? await out.embedPng(bytes) : await out.embedJpg(bytes);
        const page=out.addPage([img.width,img.height]);
        page.drawImage(img,{x:0,y:0,width:img.width,height:img.height});
      }
      download(await out.save(),'images.pdf');
    },
  };

  async function serverConvert(){
    spin('Converting with the high-fidelity engine…');
    const fd=new FormData(); fd.append('file', files[0]);
    const res=await fetch('/tools/convert/'+SLUG,{method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:fd});
    if(res.status===503) throw {offline:true};
    if(!res.ok){ let j=null; try{ j=await res.json(); }catch(_){} throw (j&&(j.error||j.message))||'The server converter could not process this file.'; }
    const blob=await res.blob();
    download(blob, base(files[0])+'.'+(SLUG==='pdf-to-word'?'docx':(SLUG==='pdf-to-excel'?'xlsx':'pdf')));
    return '<b>Done!</b> Converted with the high-fidelity engine — original layout preserved.';
  }
  async function htmlConvert(){
    const inp=document.getElementById('htmlUrl');
    const u=(inp&&inp.value||'').trim();
    if(!/^https?:\/\//i.test(u)) throw 'Enter a full URL starting with http:// or https://';
    spin('Converting the webpage…');
    const fd=new FormData(); fd.append('url', u);
    const res=await fetch('/tools/convert/html-to-pdf',{method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:fd});
    if(!res.ok){ let j=null; try{ j=await res.json(); }catch(_){} throw (j&&(j.error||j.message))||'Could not convert this page.'; }
    const blob=await res.blob();
    let nm='webpage'; try{ nm=new URL(u).hostname.replace(/^www\./,''); }catch(_){}
    download(blob, nm+'.pdf');
    return '<b>Done!</b> Webpage saved as PDF.';
  }
  if(SLUG==='html-to-pdf' && LIVE){ go.disabled=false; }
  const origHtml=go.innerHTML;
  go.onclick=async ()=>{
    if(SLUG==='html-to-pdf'){
      go.disabled=true; go.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Processing…';
      try{ zToast(await htmlConvert(),'ok'); }
      catch(err){ zToast(typeof err==='string'?err:'Could not convert this page.','err'); }
      go.innerHTML=origHtml; go.disabled=false;
      return;
    }
    if(!files.length) return;
    go.disabled=true; go.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Processing…';
    try{
      let msg;
      if(srvOk && SRVTOOLS.includes(SLUG)){
        try{ msg=await serverConvert(); }
        catch(e){ if(e && e.offline){ srvOk=false; msg=await ops[SLUG](); } else throw e; }
      } else {
        msg=await ops[SLUG]();
      }
      zToast(msg||'<b>Done!</b> Your file has been downloaded.','ok');
    }catch(err){
      zToast(typeof err==='string'?err:'Could not process this file — it may be corrupted or password-protected.','err');
    }
    go.innerHTML=origHtml; go.disabled=!files.length;
  };
})();
