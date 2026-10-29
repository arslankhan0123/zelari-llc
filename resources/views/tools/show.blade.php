@extends('layouts.store')
@section('title', $tool['name'])
@section('meta_description', $tool['desc'])

@php
  // tools processed right in the browser with pdf-lib — no upload needed
  $isLive = $tool['live'] ?? false;
  $multi  = in_array($slug, ['merge-pdf', 'jpg-to-pdf'], true);
  $accepts = [
    'jpg-to-pdf'   => ['image/jpeg,image/png', 'Select JPG / PNG images'],
    'word-to-pdf'  => ['.docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'Select Word file (.docx)'],
    'excel-to-pdf' => ['.xlsx,.xls,.csv', 'Select Excel file (.xlsx / .xls / .csv)'],
    'ppt-to-pdf'   => ['.pptx,application/vnd.openxmlformats-officedocument.presentationml.presentation', 'Select PowerPoint file (.pptx)'],
  ];
  $accept = $accepts[$slug][0] ?? 'application/pdf';
  $pick   = $accepts[$slug][1] ?? 'Select PDF ' . ($multi ? 'files' : 'file');
@endphp

@push('styles')
<style>
.ts{max-width:860px;margin:0 auto;text-align:center}
.ts-back{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--muted);margin-bottom:22px}
.ts-back:hover{color:var(--p)}
.ts .ic{width:72px;height:72px;border-radius:20px;display:inline-grid;place-items:center;color:#fff;font-size:30px;margin-bottom:16px;box-shadow:0 18px 36px -14px rgba(0,0,0,.35)}
.ts h1{font-size:clamp(26px,3.4vw,38px);font-weight:800;letter-spacing:-1px;margin:0 0 8px}
.ts > p{color:var(--muted);font-size:15.5px;line-height:1.6;margin:0 auto 28px;max-width:560px}
.ts-drop{border:2.5px dashed var(--line-2);border-radius:var(--r-lg);background:var(--surface);padding:48px 24px;transition:.2s;cursor:pointer}
.ts-drop:hover,.ts-drop.over{border-color:var(--p);background:var(--brand-soft)}
.ts-drop i{font-size:40px;color:var(--p);margin-bottom:12px;display:block}
.ts-drop b{display:block;font-size:17px;margin-bottom:6px}
.ts-drop small{color:var(--muted);font-size:13px}
.ts-priv{display:inline-flex;align-items:center;gap:7px;margin-top:12px;font-size:12px;font-weight:600;color:var(--ok)}
.ts-opts{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-top:18px}
.ts-opts label{display:flex;flex-direction:column;gap:6px;font-size:12.5px;font-weight:700;color:var(--ink-2);text-align:left}
.ts-opts input,.ts-opts select{border:1.5px solid var(--line-2);border-radius:11px;padding:11px 14px;font:inherit;font-size:14px;outline:0;min-width:230px;background:#fff}
.ts-opts input:focus,.ts-opts select:focus{border-color:var(--p)}
.ts-btn{display:inline-flex;align-items:center;gap:10px;background:var(--grad);color:#fff;border:0;border-radius:14px;padding:16px 34px;font-weight:800;font-size:16px;cursor:pointer;margin-top:18px;font-family:inherit;transition:transform .2s,box-shadow .2s;box-shadow:0 14px 30px -12px rgba(var(--shadow-rgb),.6)}
.ts-btn:hover{transform:translateY(-2px);color:#fff}
.ts-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.ts-files{margin-top:16px;display:flex;flex-direction:column;gap:8px;text-align:left}
.ts-file{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 14px;font-size:13.5px;font-weight:600}
.ts-file i{color:#e5322d}
.ts-file small{color:var(--muted);margin-left:auto}
.ts-file .x{border:0;background:none;color:var(--faint);cursor:pointer;font-size:13px;margin-left:8px}
.org-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin-top:18px;text-align:left}
.org-pg{position:relative;background:#fff;border:1.5px solid var(--line);border-radius:12px;padding:8px;cursor:grab;transition:border-color .15s,opacity .15s}
.org-pg.drag{opacity:.35}
.org-pg:hover{border-color:var(--p)}
.org-pg canvas,.org-pg .phx{width:100%;border-radius:8px;display:block;background:#f1f2f8}
.org-pg .phx{aspect-ratio:3/4;display:grid;place-items:center;color:var(--faint);font-size:20px}
.org-pg b{display:block;text-align:center;font-size:11px;color:var(--muted);margin-top:6px}
.org-pg .del{position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:7px;border:0;background:#fff;color:var(--danger);box-shadow:var(--shadow);cursor:pointer;font-size:11px}
.org-hint{margin-top:12px;font-size:12.5px;color:var(--muted)}
.ts-more{margin-top:46px;text-align:left}
.ts-more h3{font-size:17px;font-weight:800;margin:0 0 14px}
.ts-more-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:860px){.ts-more-grid{grid-template-columns:repeat(2,1fr)}}
.ts-more-grid a{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:11px 13px;font-size:12.5px;font-weight:700;color:var(--ink);transition:.15s}
.ts-more-grid a:hover{border-color:var(--p);color:var(--p);transform:translateY(-2px)}
.ts-more-grid a span{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;color:#fff;font-size:13px;flex-shrink:0}
</style>
@endpush

@section('content')
<section class="sec wrap">
  <div class="ts reveal">
    <div style="text-align:left"><a href="{{ route('tools.index') }}" class="ts-back"><i class="fa-solid fa-arrow-left"></i> All PDF tools</a></div>
    <span class="ic" style="background:{{ $tool['color'] }}"><i class="fa-solid {{ $tool['icon'] }}"></i></span>
    <h1>{{ $tool['name'] }}</h1>
    <p>{{ $tool['desc'] }}</p>

    <div class="ts-drop" id="tsDrop">
      <i class="fa-solid fa-cloud-arrow-up"></i>
      <b>{{ $pick }}</b>
      <small>or drag and drop {{ $multi ? 'files' : 'a file' }} here</small>
      <input type="file" id="tsInput" accept="{{ $accept }}" {{ $multi ? 'multiple' : '' }} hidden>
    </div>
    @if($isLive)
      <div class="ts-priv"><i class="fa-solid fa-shield-halved"></i> Files are processed securely in your browser — nothing is uploaded.</div>
    @endif
    <div class="ts-files" id="tsFiles"></div>
    @if($slug === 'organize-pdf')
      <div class="org-grid" id="orgGrid"></div>
      <div class="org-hint" id="orgHint" style="display:none"><i class="fa-solid fa-hand"></i> Drag pages to reorder &nbsp;·&nbsp; ✕ to delete a page</div>
    @endif

    @if($slug === 'split-pdf')
      <div class="ts-opts"><label>Pages to extract <input type="text" id="optRange" placeholder="e.g. 1-3,5,8" autocomplete="off"></label></div>
    @elseif($slug === 'rotate-pdf')
      <div class="ts-opts"><label>Rotate by
        <select id="optAngle"><option value="90">90° clockwise</option><option value="180">180°</option><option value="270">90° counter-clockwise</option></select>
      </label></div>
    @elseif($slug === 'watermark-pdf')
      <div class="ts-opts"><label>Watermark text <input type="text" id="optText" placeholder="e.g. CONFIDENTIAL" maxlength="60" autocomplete="off"></label></div>
    @elseif($slug === 'ocr-pdf')
      <div class="ts-opts" style="font-size:12.5px;color:var(--muted)"><i class="fa-solid fa-circle-info"></i> Recognizes English text · up to 25 pages · can take a minute on big files</div>
    @elseif($slug === 'compress-pdf')
      <div class="ts-opts"><label>Compression level
        <select id="optLevel"><option value="lite">Recommended — keep quality &amp; text</option><option value="extreme">Extreme — smallest size (pages become images)</option></select>
      </label></div>
    @endif

    <button type="button" class="ts-btn" id="tsGo" disabled>{{ $tool['name'] }} <i class="fa-solid fa-arrow-right"></i></button>

    <div class="ts-more">
      <h3>More PDF tools</h3>
      <div class="ts-more-grid">
        @foreach(collect($tools)->except($slug)->take(8) as $s => $t)
          <a href="{{ route('tools.show', $s) }}"><span style="background:{{ $t['color'] }}"><i class="fa-solid {{ $t['icon'] }}"></i></span>{{ $t['name'] }}</a>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
@if($isLive)<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>@endif
@if(in_array($slug, ['organize-pdf', 'compress-pdf', 'ocr-pdf', 'pdf-to-word', 'pdf-to-excel', 'pdf-to-ppt']))
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>if(window.pdfjsLib) pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
@endif
@if($slug === 'ocr-pdf')<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.1.0/dist/tesseract.min.js"></script>@endif
@if(in_array($slug, ['pdf-to-excel', 'excel-to-pdf']))<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>@endif
@if($slug === 'pdf-to-word')<script src="https://cdn.jsdelivr.net/npm/docx@8.5.0/build/index.umd.js"></script>@endif
@if($slug === 'pdf-to-ppt')<script src="https://cdn.jsdelivr.net/npm/pptxgenjs@3.12.0/dist/pptxgen.bundle.js"></script>@endif
@if($slug === 'word-to-pdf')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.2/dist/docx-preview.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endif
@if($slug === 'ppt-to-pdf')<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>@endif
<script>
(function(){
  const SLUG='{{ $slug }}', LIVE={{ $isLive ? 'true' : 'false' }}, MULTI={{ $multi ? 'true' : 'false' }};
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
      d.querySelector('.x').onclick=()=>{ files.splice(i,1); render(); if(SLUG==='organize-pdf') buildOrganize(); };
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
  drop.onclick=()=>input.click();
  input.onchange=()=>{ addFiles(input.files); input.value=''; };
  ['dragover','dragenter'].forEach(ev=>drop.addEventListener(ev,e=>{ e.preventDefault(); drop.classList.add('over'); }));
  ['dragleave','drop'].forEach(ev=>drop.addEventListener(ev,e=>{ e.preventDefault(); drop.classList.remove('over'); }));
  drop.addEventListener('drop',e=>addFiles(e.dataTransfer.files));

  if(!LIVE){
    go.onclick=()=>zToast('<b>{{ $tool['name'] }}</b> is being set up — this tool will be live very soon!','info');
    return;
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
      const NUMRX=/^(\(?\d+(\.\d+)*[\.\):]?|\(?[a-zA-Z][\.\)]|\(?[ivxIVX]+\)|[•▪●◦·\-–])$/;
      for(let i=1;i<=src.numPages;i++){
        spin('Page '+i+' / '+src.numPages+'…');
        const pg=await src.getPage(i);
        const vp=pg.getViewport({scale:1});
        try{ const tv=pg.getViewport({scale:0.05}); const tcv=document.createElement('canvas'); tcv.width=Math.max(1,tv.width|0); tcv.height=Math.max(1,tv.height|0); await pg.render({canvasContext:tcv.getContext('2d'),viewport:tv}).promise; }catch(_){}
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
            sg.runs=[]; let rr=null;
            sg.parts.forEach(t=>{
              const st=fstyle(t.fontName), txt=t.str;
              if(rr && rr.b===st.b && rr.i===st.i && rr.fam===st.fam) rr.text+=(rr.text&&!rr.text.endsWith(' ')&&!txt.startsWith(' ')?' ':'')+txt;
              else { rr={text:txt, b:st.b, i:st.i, fam:st.fam}; sg.runs.push(rr); }
            });
            sg.runs.forEach(r=>{ r.text=r.text.replace(/\s+/g,' '); });
            sg.text=sg.runs.map(r=>r.text).join(' ').replace(/\s+/g,' ').trim();
          });
          return {y, sz, segs, left:segs[0].x, right:segs[segs.length-1].right};
        }).filter(l=>l.segs.some(sg=>sg.text));
        if(lines.length) anyText=true;
        const minX=lines.length?Math.min(...lines.map(l=>l.left)):0;
        const maxR=lines.length?Math.max(...lines.map(l=>l.right)):vp.width;
        /* ── column detection (resumes etc.) ── */
        let gutter=null;
        const allSegs=[]; lines.forEach(l=>l.segs.forEach(sg=>allSegs.push(sg)));
        if(allSegs.length>=14){
          let best=1e9;
          for(let g=vp.width*0.28; g<=vp.width*0.72; g+=6){
            let cross=0,L=0,R=0;
            allSegs.forEach(sg=>{ if(sg.right<g-4) L++; else if(sg.x>g+4) R++; else cross++; });
            if(L>=allSegs.length*0.2 && R>=allSegs.length*0.2 && cross<best){ best=cross; gutter=g; }
          }
          if(gutter!==null && best>Math.max(2,allSegs.length*0.08)) gutter=null;
        }
        const mkRun=(r,sz)=>new docx.TextRun({text:r.text, bold:r.b, italics:r.i, font:r.fam||undefined, size:Math.max(12,Math.round(sz*2))});
        /* turn a list of lines (one column / full width) into paragraphs */
        const buildParas=(ls, colL, colR)=>{
          const outEls=[]; const colW=Math.max(60,colR-colL);
          let para=null, prevY=null;
          const flush=()=>{ if(!para) return;
            const children=[]; para.chunks.forEach((c,ci)=>{ if(ci&&c.text) c.text=' '+c.text.replace(/^ /,''); children.push(mkRun(c,para.sz)); });
            let alignment, indent;
            if(para.center) alignment=docx.AlignmentType.CENTER;
            else{
              if(para.lines>1 && para.fullCount>=para.lines-1) alignment=docx.AlignmentType.JUSTIFIED;
              const li=Math.round((para.indentLeft-colL)*20);
              if(li>24||para.hang>0) indent={left:Math.max(0,li), hanging:para.hang>0?Math.round(para.hang*20):undefined};
            }
            outEls.push(new docx.Paragraph({alignment, indent, spacing:{after:Math.round(para.sz*3)}, children}));
            para=null; };
          ls.forEach(ln=>{
            const txt=ln.segs.map(sg=>sg.text).join(' ');
            const first=ln.segs[0];
            const gap=prevY===null?0:(prevY-ln.y);
            if(prevY!==null && gap>ln.sz*2.4){ flush(); outEls.push(new docx.Paragraph({children:[]})); }
            const isNum=ln.segs.length>=2 && NUMRX.test(first.text) && (first.right-first.x)<50;
            const center=!isNum && ln.segs.length===1 && Math.abs(((ln.left+ln.right)/2)-(colL+colW/2))<colW*0.06 && (ln.left-colL)>colW*0.12;
            const lineFull=ln.right>colR-colW*0.13;
            const contOK=para && !center && !isNum && Math.abs(ln.sz-para.sz)<1.2 && gap<ln.sz*1.9 && para.lastFull && Math.abs(ln.left-para.contLeft)<8 && !NUMRX.test((txt.split(' ')[0]||''));
            if(contOK){
              ln.segs.forEach(sg=>sg.runs.forEach(r=>para.chunks.push({...r})));
              para.lines++; if(lineFull) para.fullCount++;
              para.lastFull=lineFull; prevY=ln.y; return;
            }
            flush();
            para={sz:ln.sz, chunks:[], lines:1, fullCount:lineFull?1:0, lastFull:lineFull, center, indentLeft:ln.left, contLeft:ln.left, hang:0};
            if(isNum){
              const cont=ln.segs[1].x;
              para.indentLeft=cont; para.contLeft=cont; para.hang=cont-first.x;
              first.runs.forEach(r=>para.chunks.push({...r}));
              /* tab-like spacing between number and text */
              ln.segs.slice(1).forEach(sg=>sg.runs.forEach(r=>para.chunks.push({...r})));
            } else {
              ln.segs.forEach(sg=>sg.runs.forEach(r=>para.chunks.push({...r})));
            }
            prevY=ln.y;
          });
          flush();
          return outEls;
        };
        let kids=[];
        if(gutter!==null){
          const top=[], bottom=[], L=[], R=[];
          const mkLn=(l,segs)=>({y:l.y, sz:l.sz, segs, left:segs[0].x, right:segs[segs.length-1].right});
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
          const lw=Math.round(((gutter-minX)/(maxR-minX))*100);
          kids.push(new docx.Table({
            width:{size:100,type:docx.WidthType.PERCENTAGE}, borders:noB,
            columnWidths:[lw*50, (100-lw)*50],
            rows:[new docx.TableRow({children:[
              new docx.TableCell({borders:noB, width:{size:lw,type:docx.WidthType.PERCENTAGE}, children:L.length?buildParas(L, Math.min(...L.map(l=>l.left)), gutter):[new docx.Paragraph({children:[]})]}),
              new docx.TableCell({borders:noB, width:{size:100-lw,type:docx.WidthType.PERCENTAGE}, children:R.length?buildParas(R, Math.min(...R.map(l=>l.left)), maxR):[new docx.Paragraph({children:[]})]})
            ]})]
          }));
          kids.push(...buildParas(bottom, minX, maxR));
        } else {
          kids=buildParas(lines, minX, maxR);
        }
        sections.push({
          properties:{page:{size:{width:Math.round(vp.width*20), height:Math.round(vp.height*20)}, margin:{top:Math.max(400,Math.round((vp.height-(lines[0]?lines[0].y+lines[0].sz:vp.height))*20)), right:720, bottom:500, left:Math.max(500,Math.round(minX*20))}}},
          children:kids.length?kids:[new docx.Paragraph({children:[]})]
        });
      }
      if(!anyText) throw 'No selectable text found in this PDF — it looks like a scan. Run <b>OCR PDF</b> on it first, then convert.';
      const blob=await docx.Packer.toBlob(new docx.Document({sections}));
      download(blob, base(files[0])+'.docx');
      return '<b>Done!</b> '+src.numPages+' page'+(src.numPages>1?'s':'')+' converted — paragraphs, fonts, headings, numbering indents, columns and alignment rebuilt like the PDF (images and graphics are not carried over).';
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
      const host=document.createElement('div');
      host.style.cssText='position:absolute;left:-10000px;top:0;width:900px;background:#fff;';
      document.body.appendChild(host);
      try{
        spin('Reading document…');
        await docx.renderAsync(await files[0].arrayBuffer(), host);
        const sections=[...host.querySelectorAll('section.docx')];
        if(!sections.length) throw 'Could not read this file — make sure it is a .docx Word document.';
        const out=await PDFDocument.create();
        for(let i=0;i<sections.length;i++){
          spin('Page '+(i+1)+' / '+sections.length+'…');
          const c=await html2canvas(sections[i],{scale:1.6,backgroundColor:'#fff',logging:false});
          const img=await out.embedJpg(c.toDataURL('image/jpeg',0.92));
          const w=612, h=w*c.height/c.width;
          out.addPage([w,h]).drawImage(img,{x:0,y:0,width:w,height:h});
        }
        download(await out.save(), base(files[0])+'.pdf');
        return '<b>Done!</b> '+sections.length+' page'+(sections.length>1?'s':'')+' converted to PDF.';
      } finally { host.remove(); }
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

  const origHtml=go.innerHTML;
  go.onclick=async ()=>{
    if(!files.length) return;
    go.disabled=true; go.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Processing…';
    try{
      const msg=await ops[SLUG]();
      zToast(msg||'<b>Done!</b> Your file has been downloaded.','ok');
    }catch(err){
      zToast(typeof err==='string'?err:'Could not process this file — it may be corrupted or password-protected.','err');
    }
    go.innerHTML=origHtml; go.disabled=!files.length;
  };
})();
</script>
@endpush
