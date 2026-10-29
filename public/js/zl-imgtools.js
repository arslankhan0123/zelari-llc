/* Zelari Image Tools — everything runs in the browser, no uploads. */
(function(){
'use strict';
var ZI = window.ZIMG || {}, SLUG = ZI.slug || '';
var $ = function(id){ return document.getElementById(id); };
var drop = $('imDrop'), input = $('imInput'), pick = $('imPick'), filesBox = $('imFiles'),
    stageWrap = $('imStageWrap'), stage = $('imStage'), opts = $('imOpts'),
    resBox = $('imResult'), foot = $('imFoot'), go = $('imGo');
if (!drop || !input || !go) return;

var MULTI = ['compress-image','convert-to-jpg','convert-to-png','convert-to-webp'].indexOf(SLUG) >= 0;
var GO_LABEL = {
  'compress-image':'Compress', 'resize-image':'Resize', 'crop-image':'Crop',
  'rotate-image':'Rotate & download', 'flip-image':'Flip & download', 'round-image':'Round & download',
  'convert-to-jpg':'Convert to JPG', 'convert-to-png':'Convert to PNG', 'convert-to-webp':'Convert to WebP',
  'image-filters':'Apply & download', 'image-watermark':'Watermark & download', 'image-color-picker':''
};
go.querySelector('span').textContent = GO_LABEL[SLUG] || 'Download';

var files = [];                 // [{file, img, w, h}]
var st = { rot:0, fh:false, fv:false, sel:null, filter:'none', bright:100, contrast:100,
           wmText:'', wmPos:'br', wmSize:5, wmColor:'#ffffff', wmOp:60, rad:18, circle:false,
           quality:70, cfmt:'auto', rw:0, rh:0, lock:true, rmode:'px', rpct:50 };

function toast(m, t){ try { window.zToast(m, t || 'info'); } catch(e){ alert(m.replace(/<[^>]+>/g,'')); } }
function fmtB(n){ return n > 1048576 ? (n/1048576).toFixed(2) + ' MB' : Math.max(1, Math.round(n/1024)) + ' KB'; }
function baseOf(f){ return f.name.replace(/\.[^.]+$/, ''); }
function toBlob(cv, type, q){ return new Promise(function(r){ cv.toBlob(function(b){ r(b); }, type, q); }); }
function outType(f){
  var t = f.file.type;
  if (t === 'image/png')  return ['image/png', 'png'];
  if (t === 'image/webp') return ['image/webp', 'webp'];
  return ['image/jpeg', 'jpg'];
}
function dl(blob, name){
  var a = document.createElement('a');
  a.href = URL.createObjectURL(blob); a.download = name;
  document.body.appendChild(a); a.click(); a.remove();
  setTimeout(function(){ URL.revokeObjectURL(a.href); }, 4000);
}
function loadImage(file){
  return new Promise(function(res, rej){
    var url = URL.createObjectURL(file), img = new Image();
    img.onload  = function(){ res({ file: file, img: img, w: img.naturalWidth, h: img.naturalHeight }); };
    img.onerror = function(){ URL.revokeObjectURL(url); rej(new Error('bad')); };
    img.src = url;
  });
}

/* ---------- file intake ---------- */
function accept(list){
  var arr = [].slice.call(list).filter(function(f){ return /^image\//.test(f.type); });
  if (!arr.length) { toast('Please choose an image file (JPG, PNG, WebP, GIF or BMP).', 'error'); return; }
  if (!MULTI) arr = arr.slice(0, 1);
  Promise.all(arr.map(loadImage)).then(function(loaded){
    files = MULTI ? files.concat(loaded) : loaded;
    st.sel = null; st.rot = 0; st.fh = false; st.fv = false;
    afterLoad();
  }).catch(function(){ toast('That file could not be read as an image.', 'error'); });
}
drop.addEventListener('click', function(e){ if (e.target === pick || pick.contains(e.target)) return; input.click(); });
pick.addEventListener('click', function(){ input.click(); });
input.addEventListener('change', function(){ accept(input.files); input.value = ''; });
['dragover','dragenter'].forEach(function(ev){ drop.addEventListener(ev, function(e){ e.preventDefault(); drop.classList.add('on'); }); });
['dragleave','drop'].forEach(function(ev){ drop.addEventListener(ev, function(e){ e.preventDefault(); drop.classList.remove('on'); }); });
drop.addEventListener('drop', function(e){ accept(e.dataTransfer.files); });

function listFiles(){
  filesBox.innerHTML = '';
  files.forEach(function(f, i){
    var d = document.createElement('div');
    d.className = 'im-file';
    d.innerHTML = '<i class="fa-regular fa-image fi"></i><b></b><span class="sz">' + fmtB(f.file.size) + ' · ' + f.w + '×' + f.h + '</span><button type="button" class="rm" title="Remove"><i class="fa-solid fa-xmark"></i></button>';
    d.querySelector('b').textContent = f.file.name;
    d.querySelector('.rm').onclick = function(){ files.splice(i, 1); afterLoad(); };
    filesBox.appendChild(d);
  });
  filesBox.hidden = !files.length;
}

function afterLoad(){
  resBox.hidden = true;
  if (!files.length) {
    filesBox.hidden = stageWrap.hidden = opts.hidden = foot.hidden = true;
    return;
  }
  if (MULTI) { listFiles(); stageWrap.hidden = true; }
  else { filesBox.hidden = true; stageWrap.hidden = false; }
  buildOpts();
  opts.hidden = !opts.innerHTML;
  foot.hidden = (SLUG === 'image-color-picker');
  if (!MULTI) render();
  if (SLUG === 'resize-image') { st.rw = files[0].w; st.rh = files[0].h; syncResizeInputs(); }
}

/* ---------- preview ---------- */
function rr(x, X, Y, W, H, r){
  r = Math.min(r, W/2, H/2);
  x.moveTo(X+r, Y); x.arcTo(X+W, Y, X+W, Y+H, r); x.arcTo(X+W, Y+H, X, Y+H, r);
  x.arcTo(X, Y+H, X, Y, r); x.arcTo(X, Y, X+W, Y, r); x.closePath();
}
function filterStr(){
  var p = ['brightness(' + st.bright + '%)', 'contrast(' + st.contrast + '%)'];
  if (st.filter === 'gray')   p.push('grayscale(1)');
  if (st.filter === 'sepia')  p.push('sepia(1)');
  if (st.filter === 'invert') p.push('invert(1)');
  return p.join(' ');
}
function drawCore(x, f, W, H){
  x.save();
  if (SLUG === 'image-filters') x.filter = filterStr();
  x.translate(W/2, H/2);
  if (SLUG === 'rotate-image') x.rotate(st.rot * Math.PI / 180);
  if (SLUG === 'flip-image') x.scale(st.fh ? -1 : 1, st.fv ? -1 : 1);
  var sw = (SLUG === 'rotate-image' && st.rot % 180) ? H : W;
  var sh = (SLUG === 'rotate-image' && st.rot % 180) ? W : H;
  x.drawImage(f.img, -sw/2, -sh/2, sw, sh);
  x.restore();
}
function roundMask(x, W, H){
  x.globalCompositeOperation = 'destination-in';
  x.beginPath();
  if (st.circle) x.arc(W/2, H/2, Math.min(W, H)/2, 0, 7);
  else rr(x, 0, 0, W, H, st.rad/100 * Math.min(W, H));
  x.fill();
  x.globalCompositeOperation = 'source-over';
}
function drawWM(x, W, H){
  if (!st.wmText) return;
  var fs = Math.max(10, W * st.wmSize / 100);
  x.save();
  x.globalAlpha = st.wmOp / 100;
  x.fillStyle = st.wmColor;
  x.font = '700 ' + fs + 'px Arial, sans-serif';
  x.shadowColor = 'rgba(0,0,0,.45)'; x.shadowBlur = fs/9;
  var m = x.measureText(st.wmText).width, pad = fs * 0.7, X, Y;
  var h = st.wmPos[1] || 'c', v = st.wmPos[0] || 'c';
  X = h === 'l' ? pad : h === 'r' ? W - m - pad : (W - m)/2;
  Y = v === 't' ? fs + pad : v === 'b' ? H - pad : (H + fs*0.7)/2;
  x.fillText(st.wmText, X, Y);
  x.restore();
}
function render(){
  if (MULTI || !files.length) return;
  var f = files[0];
  var w = f.w, h = f.h;
  if (SLUG === 'rotate-image' && st.rot % 180) { w = f.h; h = f.w; }
  var maxW = Math.min(820, (stage.parentElement.clientWidth || 840) - 28);
  var sc = Math.min(1, maxW / w);
  stage.width = Math.max(1, Math.round(w * sc));
  stage.height = Math.max(1, Math.round(h * sc));
  var x = stage.getContext('2d');
  x.clearRect(0, 0, stage.width, stage.height);
  drawCore(x, f, stage.width, stage.height);
  if (SLUG === 'round-image') roundMask(x, stage.width, stage.height);
  if (SLUG === 'image-watermark') drawWM(x, stage.width, stage.height);
  if (SLUG === 'crop-image' && st.sel) {
    var s = st.sel, X = s.fx*stage.width, Y = s.fy*stage.height, W2 = s.fw*stage.width, H2 = s.fh*stage.height;
    x.fillStyle = 'rgba(15,23,42,.55)';
    x.fillRect(0, 0, stage.width, Y);
    x.fillRect(0, Y, X, H2);
    x.fillRect(X+W2, Y, stage.width-X-W2, H2);
    x.fillRect(0, Y+H2, stage.width, stage.height-Y-H2);
    x.strokeStyle = '#fbbf24'; x.lineWidth = 2; x.setLineDash([7,5]);
    x.strokeRect(X, Y, W2, H2); x.setLineDash([]);
  }
}

/* ---------- options UI ---------- */
function opt(html){ var d = document.createElement('div'); d.className = 'im-opt'; d.innerHTML = html; return d; }
function buildOpts(){
  opts.innerHTML = '';
  var add = function(el){ opts.appendChild(el); return el; };
  if (SLUG === 'compress-image') {
    var o1 = add(opt('<label>Quality</label><input type="range" min="10" max="95" value="' + st.quality + '" id="ioQ"><span class="val" id="ioQv">' + st.quality + '%</span>'));
    o1.querySelector('#ioQ').oninput = function(){ st.quality = +this.value; $('ioQv').textContent = this.value + '%'; };
    var o2 = add(opt('<label>Output</label><select id="ioF"><option value="auto">Auto (best)</option><option value="jpg">JPG</option><option value="webp">WebP</option></select>'));
    o2.querySelector('#ioF').onchange = function(){ st.cfmt = this.value; };
  }
  if (SLUG === 'resize-image') {
    var m = add(opt('<label>Mode</label><select id="ioM"><option value="px">Exact pixels</option><option value="pct">Percentage</option></select>'));
    var o = add(opt('<span id="ioPx"><label>Width</label> <input type="number" id="ioW" min="1" max="20000"> <label>Height</label> <input type="number" id="ioH" min="1" max="20000"> <button type="button" class="im-btn on" id="ioL" title="Keep aspect ratio"><i class="fa-solid fa-link"></i></button></span><span id="ioPc" style="display:none"><label>Scale</label> <input type="range" min="5" max="200" value="50" id="ioP"> <span class="val" id="ioPv">50%</span></span>'));
    m.querySelector('#ioM').onchange = function(){
      st.rmode = this.value;
      o.querySelector('#ioPx').style.display = st.rmode === 'px' ? '' : 'none';
      o.querySelector('#ioPc').style.display = st.rmode === 'pct' ? '' : 'none';
    };
    o.querySelector('#ioW').oninput = function(){ st.rw = +this.value || 0; if (st.lock && files[0] && st.rw) { st.rh = Math.round(st.rw * files[0].h / files[0].w); $('ioH').value = st.rh; } };
    o.querySelector('#ioH').oninput = function(){ st.rh = +this.value || 0; if (st.lock && files[0] && st.rh) { st.rw = Math.round(st.rh * files[0].w / files[0].h); $('ioW').value = st.rw; } };
    o.querySelector('#ioL').onclick = function(){ st.lock = !st.lock; this.classList.toggle('on', st.lock); };
    o.querySelector('#ioP').oninput = function(){ st.rpct = +this.value; $('ioPv').textContent = this.value + '%'; };
  }
  if (SLUG === 'crop-image') {
    add(opt('<span style="font-weight:600;color:var(--muted)"><i class="fa-solid fa-hand-pointer"></i> Drag on the image to select the area to keep.</span><button type="button" class="im-btn" id="ioCR">Reset selection</button>')).querySelector('#ioCR').onclick = function(){ st.sel = null; render(); };
  }
  if (SLUG === 'rotate-image') {
    var r = add(opt('<button type="button" class="im-btn" id="ioRL"><i class="fa-solid fa-rotate-left"></i> Rotate left</button><button type="button" class="im-btn" id="ioRR"><i class="fa-solid fa-rotate-right"></i> Rotate right</button><span class="val" id="ioRv">0°</span>'));
    var upd = function(d){ st.rot = ((st.rot + d) % 360 + 360) % 360; $('ioRv').textContent = st.rot + '°'; render(); };
    r.querySelector('#ioRL').onclick = function(){ upd(-90); };
    r.querySelector('#ioRR').onclick = function(){ upd(90); };
  }
  if (SLUG === 'flip-image') {
    var fl = add(opt('<button type="button" class="im-btn" id="ioFH"><i class="fa-solid fa-left-right"></i> Horizontal</button><button type="button" class="im-btn" id="ioFV"><i class="fa-solid fa-up-down"></i> Vertical</button>'));
    fl.querySelector('#ioFH').onclick = function(){ st.fh = !st.fh; this.classList.toggle('on', st.fh); render(); };
    fl.querySelector('#ioFV').onclick = function(){ st.fv = !st.fv; this.classList.toggle('on', st.fv); render(); };
  }
  if (SLUG === 'round-image') {
    var ro = add(opt('<label>Corner radius</label><input type="range" min="2" max="50" value="' + st.rad + '" id="ioRd"><span class="val" id="ioRdv">' + st.rad + '%</span><button type="button" class="im-btn" id="ioCi"><i class="fa-regular fa-circle"></i> Circle</button>'));
    ro.querySelector('#ioRd').oninput = function(){ st.rad = +this.value; st.circle = false; $('ioCi').classList.remove('on'); $('ioRdv').textContent = this.value + '%'; render(); };
    ro.querySelector('#ioCi').onclick = function(){ st.circle = !st.circle; this.classList.toggle('on', st.circle); render(); };
  }
  if (SLUG === 'image-filters') {
    var f1 = add(opt('<label>Filter</label><select id="ioFl"><option value="none">None</option><option value="gray">Grayscale</option><option value="sepia">Sepia</option><option value="invert">Invert</option></select>'));
    f1.querySelector('#ioFl').onchange = function(){ st.filter = this.value; render(); };
    var f2 = add(opt('<label>Brightness</label><input type="range" min="40" max="180" value="100" id="ioB"><span class="val" id="ioBv">100%</span>'));
    f2.querySelector('#ioB').oninput = function(){ st.bright = +this.value; $('ioBv').textContent = this.value + '%'; render(); };
    var f3 = add(opt('<label>Contrast</label><input type="range" min="40" max="180" value="100" id="ioC"><span class="val" id="ioCv">100%</span>'));
    f3.querySelector('#ioC').oninput = function(){ st.contrast = +this.value; $('ioCv').textContent = this.value + '%'; render(); };
  }
  if (SLUG === 'image-watermark') {
    var w1 = add(opt('<label>Text</label><input type="text" id="ioWt" placeholder="© Your brand" maxlength="60">'));
    w1.querySelector('#ioWt').oninput = function(){ st.wmText = this.value; render(); };
    var w2 = add(opt('<label>Position</label><select id="ioWp"><option value="br">Bottom right</option><option value="bc">Bottom center</option><option value="bl">Bottom left</option><option value="cc">Center</option><option value="tr">Top right</option><option value="tc">Top center</option><option value="tl">Top left</option></select>'));
    w2.querySelector('#ioWp').onchange = function(){ st.wmPos = this.value; render(); };
    var w3 = add(opt('<label>Size</label><input type="range" min="2" max="14" value="' + st.wmSize + '" id="ioWs"><span class="val" id="ioWsv">' + st.wmSize + '%</span>'));
    w3.querySelector('#ioWs').oninput = function(){ st.wmSize = +this.value; $('ioWsv').textContent = this.value + '%'; render(); };
    var w4 = add(opt('<label>Color</label><input type="color" value="#ffffff" id="ioWc"><label>Opacity</label><input type="range" min="10" max="100" value="' + st.wmOp + '" id="ioWo"><span class="val" id="ioWov">' + st.wmOp + '%</span>'));
    w4.querySelector('#ioWc').oninput = function(){ st.wmColor = this.value; render(); };
    w4.querySelector('#ioWo').oninput = function(){ st.wmOp = +this.value; $('ioWov').textContent = this.value + '%'; render(); };
  }
  if (SLUG === 'image-color-picker') {
    add(opt('<span style="font-weight:600;color:var(--muted)"><i class="fa-solid fa-hand-pointer"></i> Click anywhere on the image to pick its color.</span>'));
  }
}
function syncResizeInputs(){
  var W = $('ioW'), H = $('ioH');
  if (W && files[0]) { W.value = files[0].w; H.value = files[0].h; st.rw = files[0].w; st.rh = files[0].h; }
}

/* ---------- crop drag + color pick ---------- */
if (SLUG === 'crop-image') {
  var cDown = null;
  stage.addEventListener('pointerdown', function(e){
    var r = stage.getBoundingClientRect();
    cDown = { x: (e.clientX - r.left)/r.width, y: (e.clientY - r.top)/r.height };
    stage.setPointerCapture(e.pointerId);
  });
  stage.addEventListener('pointermove', function(e){
    if (!cDown) return;
    var r = stage.getBoundingClientRect();
    var cx = Math.min(1, Math.max(0, (e.clientX - r.left)/r.width));
    var cy = Math.min(1, Math.max(0, (e.clientY - r.top)/r.height));
    st.sel = { fx: Math.min(cDown.x, cx), fy: Math.min(cDown.y, cy), fw: Math.abs(cx - cDown.x), fh: Math.abs(cy - cDown.y) };
    render();
  });
  stage.addEventListener('pointerup', function(){ cDown = null; if (st.sel && (st.sel.fw < .01 || st.sel.fh < .01)) { st.sel = null; render(); } });
}
if (SLUG === 'image-color-picker') {
  var full = null;
  stage.addEventListener('click', function(e){
    var f = files[0]; if (!f) return;
    if (!full) {
      full = document.createElement('canvas');
      full.width = f.w; full.height = f.h;
      full.getContext('2d', { willReadFrequently: true }).drawImage(f.img, 0, 0);
    }
    var r = stage.getBoundingClientRect();
    var px = Math.min(f.w - 1, Math.max(0, Math.round((e.clientX - r.left)/r.width * f.w)));
    var py = Math.min(f.h - 1, Math.max(0, Math.round((e.clientY - r.top)/r.height * f.h)));
    var d = full.getContext('2d', { willReadFrequently: true }).getImageData(px, py, 1, 1).data;
    var hex = '#' + [d[0], d[1], d[2]].map(function(v){ return v.toString(16).padStart(2, '0'); }).join('');
    var rgb = 'rgb(' + d[0] + ', ' + d[1] + ', ' + d[2] + ')';
    resBox.hidden = false;
    resBox.innerHTML = '<div class="pick-row"><span class="sw" style="background:' + hex + '"></span><code>' + hex.toUpperCase() + '</code><code>' + rgb + '</code><button type="button" class="im-btn" id="ioCp1">Copy HEX</button><button type="button" class="im-btn" id="ioCp2">Copy RGB</button></div>';
    $('ioCp1').onclick = function(){ navigator.clipboard.writeText(hex.toUpperCase()); toast('HEX code copied to clipboard.', 'success'); };
    $('ioCp2').onclick = function(){ navigator.clipboard.writeText(rgb); toast('RGB code copied to clipboard.', 'success'); };
  });
}

/* ---------- processing ---------- */
function fullCanvas(f, W, H){
  var cv = document.createElement('canvas');
  cv.width = W; cv.height = H;
  return cv;
}
function processOne(f){
  var W = f.w, H = f.h;
  if (SLUG === 'rotate-image' && st.rot % 180) { W = f.h; H = f.w; }
  var name = baseOf(f.file), t = outType(f);

  if (SLUG === 'convert-to-jpg' || SLUG === 'convert-to-png' || SLUG === 'convert-to-webp') {
    var fmt = SLUG === 'convert-to-jpg' ? ['image/jpeg','jpg'] : SLUG === 'convert-to-png' ? ['image/png','png'] : ['image/webp','webp'];
    var cv = fullCanvas(f, W, H), x = cv.getContext('2d');
    if (fmt[1] === 'jpg') { x.fillStyle = '#fff'; x.fillRect(0, 0, W, H); }
    x.drawImage(f.img, 0, 0);
    return toBlob(cv, fmt[0], fmt[1] === 'png' ? undefined : 0.92).then(function(b){ return { blob: b, name: name + '.' + fmt[1] }; });
  }
  if (SLUG === 'compress-image') {
    var fmt2 = st.cfmt === 'jpg' ? ['image/jpeg','jpg'] : st.cfmt === 'webp' ? ['image/webp','webp'] : (f.file.type === 'image/png' ? ['image/webp','webp'] : ['image/jpeg','jpg']);
    var cv2 = fullCanvas(f, W, H), x2 = cv2.getContext('2d');
    if (fmt2[1] === 'jpg') { x2.fillStyle = '#fff'; x2.fillRect(0, 0, W, H); }
    x2.drawImage(f.img, 0, 0);
    return toBlob(cv2, fmt2[0], st.quality/100).then(function(b){ return { blob: b, name: name + '-compressed.' + fmt2[1], orig: f.file.size }; });
  }
  if (SLUG === 'resize-image') {
    var tw, th2;
    if (st.rmode === 'pct') { tw = Math.max(1, Math.round(f.w * st.rpct/100)); th2 = Math.max(1, Math.round(f.h * st.rpct/100)); }
    else { tw = st.rw || f.w; th2 = st.rh || f.h; }
    if (tw < 1 || th2 < 1 || tw > 20000 || th2 > 20000) return Promise.reject(new Error('Please enter a valid width and height.'));
    var cv3 = fullCanvas(f, tw, th2), x3 = cv3.getContext('2d');
    x3.imageSmoothingQuality = 'high';
    if (t[1] === 'jpg') { x3.fillStyle = '#fff'; x3.fillRect(0, 0, tw, th2); }
    x3.drawImage(f.img, 0, 0, tw, th2);
    return toBlob(cv3, t[0], 0.92).then(function(b){ return { blob: b, name: name + '-resized.' + t[1] }; });
  }
  if (SLUG === 'crop-image') {
    if (!st.sel) return Promise.reject(new Error('Drag on the image first to select the area you want to keep.'));
    var s = st.sel, sx = Math.round(s.fx*f.w), sy = Math.round(s.fy*f.h), sw2 = Math.max(1, Math.round(s.fw*f.w)), sh2 = Math.max(1, Math.round(s.fh*f.h));
    var cv4 = fullCanvas(f, sw2, sh2), x4 = cv4.getContext('2d');
    if (t[1] === 'jpg') { x4.fillStyle = '#fff'; x4.fillRect(0, 0, sw2, sh2); }
    x4.drawImage(f.img, sx, sy, sw2, sh2, 0, 0, sw2, sh2);
    return toBlob(cv4, t[0], 0.92).then(function(b){ return { blob: b, name: name + '-cropped.' + t[1] }; });
  }
  // rotate / flip / round / filters / watermark share drawCore
  var cv5 = fullCanvas(f, W, H), x5 = cv5.getContext('2d');
  var wantsPng = SLUG === 'round-image' || t[1] === 'png';
  if (!wantsPng && t[1] === 'jpg') { x5.fillStyle = '#fff'; x5.fillRect(0, 0, W, H); }
  drawCore(x5, f, W, H);
  if (SLUG === 'round-image') roundMask(x5, W, H);
  if (SLUG === 'image-watermark') {
    if (!st.wmText) return Promise.reject(new Error('Type your watermark text in the box above first.'));
    drawWM(x5, W, H);
  }
  var suffix = { 'rotate-image':'-rotated', 'flip-image':'-flipped', 'round-image':'-rounded', 'image-filters':'-filtered', 'image-watermark':'-watermarked' }[SLUG] || '-edited';
  var type5 = wantsPng ? ['image/png','png'] : t;
  return toBlob(cv5, type5[0], type5[1] === 'png' ? undefined : 0.92).then(function(b){ return { blob: b, name: name + suffix + '.' + type5[1] }; });
}

go.addEventListener('click', function(){
  if (!files.length) { toast('Please choose an image first.', 'error'); return; }
  go.disabled = true;
  Promise.all(files.map(processOne)).then(function(outs){
    if (outs.length === 1) {
      dl(outs[0].blob, outs[0].name);
    } else {
      var zip = new JSZip();
      outs.forEach(function(o){ zip.file(o.name, o.blob); });
      return zip.generateAsync({ type: 'blob' }).then(function(zb){
        dl(zb, 'zelari-' + SLUG + 's.zip');
      }).then(function(){ return outs; });
    }
    return outs;
  }).then(function(outs){
    if (SLUG === 'compress-image') {
      var rows = outs.map(function(o, i){
        var pct = Math.round((1 - o.blob.size / o.orig) * 100);
        var label = pct > 0 ? '<b>−' + pct + '%</b> smaller' : 'already well optimized';
        return files[i].file.name + ': ' + fmtB(o.orig) + ' → ' + fmtB(o.blob.size) + ' (' + label + ')';
      });
      resBox.hidden = false;
      resBox.innerHTML = '<b><i class="fa-solid fa-circle-check"></i> Done!</b><br>' + rows.join('<br>');
    } else {
      toast('<b>Done!</b> Your ' + (outs.length > 1 ? 'files are' : 'file is') + ' downloading.', 'success');
    }
  }).catch(function(err){
    toast(err && err.message ? err.message : 'Something went wrong — please try a different image.', 'error');
  }).finally(function(){ go.disabled = false; });
});
})();
