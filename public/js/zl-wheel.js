/* Zelari prize wheel — shared by the popup and the /spin-wheel page. Scoped per .zw-root. */
(function(){
'use strict';
function init(root){
  if(root.__zwInit) return; root.__zwInit = true;
  var canvas = root.querySelector('.zw-canvas');
  var segs; try{ segs = JSON.parse(root.dataset.segments || '[]'); }catch(e){ segs = []; }
  if(!canvas || segs.length < 2) return;

  // High internal resolution → crisp edges when CSS scales it down.
  var SIZE = 600, R = SIZE/2, cx = R, cy = R, RING = 20, INNER = R - RING, N = segs.length, arc = 2*Math.PI/N;
  canvas.width = SIZE; canvas.height = SIZE;
  var ctx = canvas.getContext('2d');

  function draw(){
    ctx.clearRect(0,0,SIZE,SIZE);
    for(var i=0;i<N;i++){
      var a0 = i*arc - Math.PI/2 - arc/2, a1 = a0+arc, mid = a0+arc/2;
      ctx.beginPath(); ctx.moveTo(cx,cy); ctx.arc(cx,cy,INNER,a0,a1); ctx.closePath();
      ctx.fillStyle = segs[i].color || (i%2?'#6366f1':'#4f46e5'); ctx.fill();
      ctx.strokeStyle='rgba(255,255,255,.22)'; ctx.lineWidth=2; ctx.stroke();
      // label — flipped on the left half so it is never upside-down
      ctx.save(); ctx.translate(cx,cy); ctx.rotate(mid);
      ctx.fillStyle='#fff'; ctx.font='800 27px Arial,sans-serif';
      ctx.shadowColor='rgba(0,0,0,.35)'; ctx.shadowBlur=3; ctx.shadowOffsetY=1;
      var lbl = (segs[i].label||'').toString(); if(lbl.length>14) lbl = lbl.slice(0,13)+'…';
      if(Math.cos(mid) < 0){ ctx.rotate(Math.PI); ctx.textAlign='left'; ctx.fillText(lbl, -(INNER-26), 9); }
      else { ctx.textAlign='right'; ctx.fillText(lbl, INNER-26, 9); }
      ctx.restore();
    }
    // inner thin ring
    ctx.beginPath(); ctx.arc(cx,cy,INNER-1,0,2*Math.PI); ctx.strokeStyle='rgba(255,255,255,.35)'; ctx.lineWidth=3; ctx.stroke();
    // gold rim
    ctx.beginPath(); ctx.arc(cx,cy,R-RING/2,0,2*Math.PI);
    var g = ctx.createLinearGradient(0,0,SIZE,SIZE); g.addColorStop(0,'#fde68a'); g.addColorStop(.5,'#f59e0b'); g.addColorStop(1,'#fbbf24');
    ctx.strokeStyle=g; ctx.lineWidth=RING; ctx.stroke();
    // rim lights
    var dots = N*2;
    for(var k=0;k<dots;k++){
      var ang = k*(2*Math.PI/dots) - Math.PI/2;
      var dx = cx + Math.cos(ang)*(R-RING/2), dy = cy + Math.sin(ang)*(R-RING/2);
      ctx.beginPath(); ctx.arc(dx,dy,4.2,0,2*Math.PI);
      ctx.fillStyle = k%2 ? '#fff' : 'rgba(255,255,255,.55)'; ctx.fill();
    }
  }
  draw();

  var spinBtn = root.querySelector('.zw-spin:not(.zw-login)');
  if(!spinBtn) return; // guest view: CTA is a login link
  var spinUrl = root.dataset.spin, csrf = root.dataset.csrf;
  var s1 = root.querySelector('.zw-s1'), s2 = root.querySelector('.zw-s2'),
      errB = root.querySelector('.zw-err'), curRot = 0, spinning = false;
  function setErr(t){ if(errB) errB.textContent = t||''; }

  spinBtn.addEventListener('click', async function(){
    if(spinning) return; spinning = true; spinBtn.disabled = true; setErr('');
    try{
      var r = await fetch(spinUrl, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, credentials:'same-origin', body:'{}'});
      var j = await r.json();
      if(!r.ok || !j.ok){ setErr(j.message || 'Something went wrong. Please try again.'); spinning = false; spinBtn.disabled = false; return; }
      var segDeg = 360/N, segCenter = j.index*segDeg, spins = 5*360;
      curRot = spins + ((360 - segCenter) % 360);
      canvas.style.transform = 'rotate('+curRot+'deg)';
      setTimeout(function(){ showResult(j); }, 5200);
    }catch(err){ setErr('Network error. Please try again.'); spinning = false; spinBtn.disabled = false; }
  });

  function showResult(j){
    if(s1) s1.hidden = true; if(s2) s2.hidden = false;
    var win = j.won;
    var ic = root.querySelector('.zw-result-ic'); if(ic) ic.innerHTML = win ? '<i class="fa-solid fa-gift"></i>' : '<i class="fa-regular fa-face-smile"></i>';
    var t = root.querySelector('.zw-restitle'); if(t) t.textContent = win ? ('You won '+j.label+'!') : 'So close!';
    var tx = root.querySelector('.zw-restext');
    if(tx) tx.textContent = win
      ? ('Your coupon has been saved to your account' + (j.emailed ? ' and emailed to you' : '') + '. Use it at checkout before it expires.')
      : 'No prize this time, but thanks for playing. Check back for future offers!';
    var box = root.querySelector('.zw-code');
    if(win && j.code){
      if(box) box.hidden = false;
      var cv = root.querySelector('.zw-codeval'); if(cv) cv.textContent = j.code;
      var f = root.querySelector('.zw-resfine'); if(f) f.textContent = j.expiresAt ? ('Valid until '+j.expiresAt+'. One use, your account only.') : 'One use, your account only.';
      addRow(j);
    } else { if(box) box.hidden = true; }
  }

  // auto-insert the won coupon as a row in the "Your coupons" list (spin page only)
  function addRow(j){
    var list = document.querySelector('.sw-rows'); if(!list) return;
    var empty = document.querySelector('.sw-empty'); if(empty) empty.remove();
    var mine = document.querySelector('.sw-mine'); if(mine) mine.hidden = false;
    var row = document.createElement('div'); row.className = 'sw-row sw-row-new';
    row.innerHTML =
      '<span class="c-code"><i class="fa-solid fa-ticket"></i> '+j.code+'</span>'+
      '<span class="c-benefit">'+ (j.label||'') +'</span>'+
      '<span class="c-exp">'+ (j.expiresAt ? ('Valid until '+j.expiresAt) : 'No expiry') +'</span>'+
      '<span class="c-status ok">Active</span>'+
      '<button type="button" class="c-copy" data-code="'+j.code+'"><i class="fa-regular fa-copy"></i> Copy</button>';
    list.insertBefore(row, list.firstChild);
    row.querySelector('.c-copy').addEventListener('click', function(){ copyCode(this); });
  }

  var copyBtn = root.querySelector('.zw-copy');
  if(copyBtn) copyBtn.addEventListener('click', function(){ copyCode(copyBtn, root.querySelector('.zw-codeval').textContent); });
  function copyCode(btn, code){
    code = code || btn.dataset.code;
    navigator.clipboard.writeText(code).then(function(){ var o=btn.innerHTML; btn.innerHTML='<i class="fa-solid fa-check"></i> Copied'; setTimeout(function(){ btn.innerHTML=o; },1600); });
  }
}
function initAll(){ document.querySelectorAll('.zw-root').forEach(init); }
if(document.readyState !== 'loading') initAll(); else document.addEventListener('DOMContentLoaded', initAll);
window.ZWheel = { init: init, initAll: initAll };
})();
