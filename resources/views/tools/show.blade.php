@extends('layouts.store')
@section('title', $tool['name'])
@section('meta_description', $tool['desc'])

@php
  // tools processed right in the browser with pdf-lib — no upload needed
  $isLive = $tool['live'] ?? false;
  $multi  = in_array($slug, ['merge-pdf', 'jpg-to-pdf'], true);
  $accept = $slug === 'jpg-to-pdf' ? 'image/jpeg,image/png' : 'application/pdf';
  $pick   = $slug === 'jpg-to-pdf' ? 'Select JPG / PNG images' : 'Select PDF ' . ($multi ? 'files' : 'file');
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

    @if($slug === 'split-pdf')
      <div class="ts-opts"><label>Pages to extract <input type="text" id="optRange" placeholder="e.g. 1-3,5,8" autocomplete="off"></label></div>
    @elseif($slug === 'rotate-pdf')
      <div class="ts-opts"><label>Rotate by
        <select id="optAngle"><option value="90">90° clockwise</option><option value="180">180°</option><option value="270">90° counter-clockwise</option></select>
      </label></div>
    @elseif($slug === 'watermark-pdf')
      <div class="ts-opts"><label>Watermark text <input type="text" id="optText" placeholder="e.g. CONFIDENTIAL" maxlength="60" autocomplete="off"></label></div>
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
      d.querySelector('.x').onclick=()=>{ files.splice(i,1); render(); };
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
    a.href=URL.createObjectURL(new Blob([bytes],{type:'application/pdf'}));
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
      await ops[SLUG]();
      zToast('<b>Done!</b> Your file has been downloaded.','ok');
    }catch(err){
      zToast(typeof err==='string'?err:'Could not process this file — it may be corrupted or password-protected.','err');
    }
    go.innerHTML=origHtml; go.disabled=!files.length;
  };
})();
</script>
@endpush
