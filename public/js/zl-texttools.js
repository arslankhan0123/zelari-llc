/* Zelari Text Tools — browser-side engine. Renders by window.ZTOOL.slug into #toolUI. */
(function () {
  'use strict';
  var ui = document.getElementById('toolUI');
  if (!ui) return;
  var slug = (window.ZTOOL || {}).slug;

  /* helpers */
  function h(html){ ui.innerHTML = html; }
  function $(sel){ return ui.querySelector(sel); }
  function $all(sel){ return Array.prototype.slice.call(ui.querySelectorAll(sel)); }
  function esc(s){ var d=document.createElement('div'); d.textContent=s==null?'':s; return d.innerHTML; }
  function copyBtnHtml(){ return '<button type="button" class="tt-copy" data-copy><i class="fa-solid fa-copy"></i> Copy</button>'; }
  function wireCopy(btn, getText){
    if(!btn) return;
    btn.addEventListener('click', function(){
      var t = getText();
      navigator.clipboard.writeText(t).then(function(){
        var o = btn.innerHTML; btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
        setTimeout(function(){ btn.innerHTML = o; }, 1400);
      }).catch(function(){});
    });
  }
  function download(name, text){
    var b = new Blob([text], {type:'text/plain'});
    var a = document.createElement('a'); a.href = URL.createObjectURL(b); a.download = name;
    document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(a.href);
  }

  var tools = {};

  /* ---------- Word Counter ---------- */
  tools['word-counter'] = function () {
    h('<textarea class="tt-ta" id="src" placeholder="Start typing or paste your text here..."></textarea>'
      + '<div class="tt-stats" style="margin-top:16px">'
      + stat('words','w') + stat('characters','c') + stat('no spaces','cns')
      + stat('sentences','s') + stat('paragraphs','p') + stat('reading time','rt')
      + '</div>'
      + '<div class="tt-row mt"><button class="tt-btn ghost sm" id="clr"><i class="fa-solid fa-eraser"></i> Clear</button></div>');
    function stat(l,id){ return '<div class="tt-stat"><div class="v" id="'+id+'">0</div><div class="l">'+l+'</div></div>'; }
    var src = $('#src');
    function upd(){
      var t = src.value;
      var words = (t.trim().match(/\S+/g) || []).length;
      var chars = t.length;
      var cns = t.replace(/\s/g,'').length;
      var sentences = (t.match(/[^.!?]+[.!?]+/g) || []).length || (t.trim() ? 1 : 0);
      var paragraphs = t.split(/\n{2,}/).map(function(x){return x.trim();}).filter(Boolean).length;
      var mins = Math.max(0, Math.round(words/200*10)/10);
      $('#w').textContent = words.toLocaleString();
      $('#c').textContent = chars.toLocaleString();
      $('#cns').textContent = cns.toLocaleString();
      $('#s').textContent = sentences.toLocaleString();
      $('#p').textContent = paragraphs.toLocaleString();
      $('#rt').textContent = mins < 1 ? '<1m' : mins + 'm';
    }
    src.addEventListener('input', upd);
    $('#clr').addEventListener('click', function(){ src.value=''; upd(); src.focus(); });
    upd();
  };

  /* ---------- Case Converter ---------- */
  tools['case-converter'] = function () {
    var modes = [['UPPERCASE','upper'],['lowercase','lower'],['Title Case','title'],['Sentence case','sentence'],['Capitalize Words','cap'],['aLtErNaTiNg','alt'],['InVeRsE','inv']];
    var btns = modes.map(function(m){ return '<button class="tt-btn ghost sm" data-m="'+m[1]+'">'+esc(m[0])+'</button>'; }).join('');
    h('<textarea class="tt-ta" id="src" placeholder="Type or paste text to convert..."></textarea>'
      + '<div class="tt-row mt">'+btns+'</div>'
      + '<div class="tt-out mono" id="out" data-ph="Converted text appears here" style="margin-top:16px">'+copyBtnHtml()+'</div>');
    var src=$('#src'), out=$('#out');
    function set(txt){ out.textContent=''; var b=document.createElement('button'); out.innerHTML = esc(txt); out.insertAdjacentHTML('beforeend', copyBtnHtml()); wireCopy($('#out [data-copy]'), function(){return txt;}); }
    function convert(mode){
      var t=src.value, r=t;
      if(mode==='upper') r=t.toUpperCase();
      else if(mode==='lower') r=t.toLowerCase();
      else if(mode==='title') r=t.toLowerCase().replace(/\b\w/g,function(c){return c.toUpperCase();});
      else if(mode==='cap') r=t.replace(/\b\w/g,function(c){return c.toUpperCase();});
      else if(mode==='sentence') r=t.toLowerCase().replace(/(^\s*\w|[.!?]\s+\w)/g,function(c){return c.toUpperCase();});
      else if(mode==='alt') r=t.split('').map(function(c,i){return i%2?c.toUpperCase():c.toLowerCase();}).join('');
      else if(mode==='inv') r=t.split('').map(function(c){return c===c.toUpperCase()?c.toLowerCase():c.toUpperCase();}).join('');
      set(r);
    }
    $all('[data-m]').forEach(function(b){ b.addEventListener('click', function(){ convert(b.dataset.m); }); });
    wireCopy($('#out [data-copy]'), function(){return '';});
  };

  /* ---------- Reverse Text ---------- */
  tools['reverse-text'] = function () {
    h('<textarea class="tt-ta" id="src" placeholder="Type or paste text to reverse..."></textarea>'
      + '<div class="tt-row mt"><button class="tt-btn ghost sm" data-m="chars">Reverse characters</button>'
      + '<button class="tt-btn ghost sm" data-m="words">Reverse words</button>'
      + '<button class="tt-btn ghost sm" data-m="lines">Reverse lines</button></div>'
      + '<div class="tt-out mono" id="out" data-ph="Reversed text appears here" style="margin-top:16px"></div>');
    var src=$('#src'), out=$('#out');
    function set(txt){ out.innerHTML = esc(txt) + copyBtnHtml(); wireCopy($('#out [data-copy]'), function(){return txt;}); }
    $all('[data-m]').forEach(function(b){ b.addEventListener('click', function(){
      var t=src.value, r=t;
      if(b.dataset.m==='chars') r=t.split('').reverse().join('');
      else if(b.dataset.m==='words') r=t.split(/(\s+)/).reverse().join('');
      else r=t.split('\n').reverse().join('\n');
      set(r);
    }); });
  };

  /* ---------- Remove Duplicate Lines ---------- */
  tools['remove-duplicate-lines'] = function () {
    h('<textarea class="tt-ta" id="src" placeholder="Paste a list — one item per line..."></textarea>'
      + '<div class="tt-opts">'
      + chk('ci','Ignore case') + chk('trim','Trim empty lines') + chk('tw','Trim spaces each line')
      + '</div>'
      + '<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-clone"></i> Remove duplicates</button>'
      + '<span class="tt-ok" id="msg"></span></div>'
      + '<div class="tt-out mono" id="out" data-ph="Result appears here" style="margin-top:14px"></div>');
    var src=$('#src');
    function run(){
      var lines = src.value.split('\n');
      var seen={}, outArr=[], removed=0;
      var ci=$('#ci').checked, trim=$('#trim').checked, tw=$('#tw').checked;
      lines.forEach(function(ln){
        var v = tw ? ln.trim() : ln;
        if(trim && v.trim()==='') return;
        var key = ci ? v.toLowerCase() : v;
        if(seen[key]){ removed++; return; }
        seen[key]=1; outArr.push(v);
      });
      var txt=outArr.join('\n');
      $('#out').innerHTML = esc(txt) + copyBtnHtml();
      wireCopy($('#out [data-copy]'), function(){return txt;});
      $('#msg').textContent = removed ? ('Removed '+removed+' duplicate line'+(removed>1?'s':'')+' — '+outArr.length+' left') : 'No duplicates found — '+outArr.length+' lines';
    }
    $('#go').addEventListener('click', run);
    function chk(id,l){ return '<label class="tt-chk"><input type="checkbox" id="'+id+'"'+(id==='trim'?' checked':'')+'> '+l+'</label>'; }
  };

  /* ---------- Sort Lines ---------- */
  tools['sort-lines'] = function () {
    h('<textarea class="tt-ta" id="src" placeholder="Paste lines to sort..."></textarea>'
      + '<div class="tt-opts">'+chk('ci','Ignore case')+chk('trim','Remove empty lines')+chk('dup','Remove duplicates')+'</div>'
      + '<div class="tt-row"><button class="tt-btn ghost sm" data-m="az">A → Z</button>'
      + '<button class="tt-btn ghost sm" data-m="za">Z → A</button>'
      + '<button class="tt-btn ghost sm" data-m="len">By length</button>'
      + '<button class="tt-btn ghost sm" data-m="rev">Reverse</button>'
      + '<button class="tt-btn ghost sm" data-m="shuf">Shuffle</button></div>'
      + '<div class="tt-out mono" id="out" data-ph="Sorted lines appear here" style="margin-top:14px"></div>');
    var src=$('#src');
    function base(){
      var lines = src.value.split('\n');
      if($('#trim').checked) lines = lines.filter(function(l){return l.trim()!=='';});
      if($('#dup').checked){ var s={},o=[]; lines.forEach(function(l){var k=$('#ci').checked?l.toLowerCase():l; if(!s[k]){s[k]=1;o.push(l);}}); lines=o; }
      return lines;
    }
    function set(arr){ var txt=arr.join('\n'); $('#out').innerHTML=esc(txt)+copyBtnHtml(); wireCopy($('#out [data-copy]'),function(){return txt;}); }
    $all('[data-m]').forEach(function(b){ b.addEventListener('click', function(){
      var a=base(), ci=$('#ci').checked, m=b.dataset.m;
      var cmp=function(x,y){ x=ci?x.toLowerCase():x; y=ci?y.toLowerCase():y; return x<y?-1:x>y?1:0; };
      if(m==='az') a.sort(cmp);
      else if(m==='za') a.sort(cmp).reverse();
      else if(m==='len') a.sort(function(x,y){return x.length-y.length;});
      else if(m==='rev') a.reverse();
      else if(m==='shuf'){ for(var i=a.length-1;i>0;i--){var j=Math.floor(Math.random()*(i+1));var t=a[i];a[i]=a[j];a[j]=t;} }
      set(a);
    }); });
    function chk(id,l){ return '<label class="tt-chk"><input type="checkbox" id="'+id+'"> '+l+'</label>'; }
  };

  /* ---------- Find and Replace ---------- */
  tools['find-replace'] = function () {
    h('<textarea class="tt-ta" id="src" placeholder="Paste your text..."></textarea>'
      + '<div class="cl-grid" style="margin-top:14px">'
      + '<div class="cl-f"><label>Find</label><div class="in"><input id="find" placeholder="text or /regex/"></div></div>'
      + '<div class="cl-f"><label>Replace with</label><div class="in"><input id="rep" placeholder="replacement"></div></div>'
      + '</div>'
      + '<div class="tt-opts">'+chk('re','Use regex')+chk('ci','Case insensitive')+'</div>'
      + '<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-wand-magic-sparkles"></i> Replace all</button>'
      + '<span class="tt-ok" id="msg"></span><span class="tt-err" id="err"></span></div>'
      + '<div class="tt-out mono" id="out" data-ph="Result appears here" style="margin-top:14px"></div>');
    var src=$('#src');
    $('#go').addEventListener('click', function(){
      $('#err').textContent=''; $('#msg').textContent='';
      var find=$('#find').value, rep=$('#rep').value, t=src.value;
      if(find===''){ $('#err').textContent='Enter something to find.'; return; }
      var count=0, result;
      try{
        var flags='g'+($('#ci').checked?'i':'');
        var re = $('#re').checked ? new RegExp(find,flags) : new RegExp(find.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),flags);
        result = t.replace(re, function(){ count++; return rep; });
      }catch(e){ $('#err').textContent='Invalid regex: '+e.message; return; }
      $('#out').innerHTML=esc(result)+copyBtnHtml();
      wireCopy($('#out [data-copy]'),function(){return result;});
      $('#msg').textContent = count? ('Replaced '+count+' match'+(count>1?'es':'')) : 'No matches found';
    });
    function chk(id,l){ return '<label class="tt-chk"><input type="checkbox" id="'+id+'"> '+l+'</label>'; }
  };

  /* ---------- Lorem Ipsum ---------- */
  tools['lorem-ipsum'] = function () {
    var WORDS='lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua enim ad minim veniam quis nostrud exercitation ullamco laboris nisi aliquip ex ea commodo consequat duis aute irure in reprehenderit voluptate velit esse cillum fugiat nulla pariatur excepteur sint occaecat cupidatat non proident sunt culpa qui officia deserunt mollit anim id est laborum'.split(' ');
    h('<div class="cl-grid">'
      + '<div class="cl-f"><label>How many</label><div class="in"><input type="number" id="cnt" value="3" min="1" max="100"></div></div>'
      + '<div class="cl-f"><label>Type</label><div class="in"><select id="type"><option value="para">Paragraphs</option><option value="sent">Sentences</option><option value="word">Words</option></select></div></div>'
      + '</div>'
      + '<div class="tt-opts">'+'<label class="tt-chk"><input type="checkbox" id="start" checked> Start with &ldquo;Lorem ipsum...&rdquo;</label></div>'
      + '<div class="tt-row"><button class="tt-btn" id="go"><i class="fa-solid fa-paragraph"></i> Generate</button></div>'
      + '<div class="tt-out" id="out" data-ph="Generated text appears here" style="margin-top:14px"></div>');
    function rnd(n){ return Math.floor(Math.random()*n); }
    function word(){ return WORDS[rnd(WORDS.length)]; }
    function sentence(){ var n=6+rnd(10), s=[]; for(var i=0;i<n;i++)s.push(word()); var str=s.join(' '); return str.charAt(0).toUpperCase()+str.slice(1)+'.'; }
    function para(){ var n=3+rnd(4), s=[]; for(var i=0;i<n;i++)s.push(sentence()); return s.join(' '); }
    $('#go').addEventListener('click', function(){
      var n=Math.max(1,Math.min(100,parseInt($('#cnt').value)||1)), type=$('#type').value, parts=[];
      for(var i=0;i<n;i++){ parts.push(type==='para'?para():type==='sent'?sentence():word()); }
      var txt = type==='word'? parts.join(' ')+'.' : parts.join(type==='para'?'\n\n':' ');
      if($('#start').checked){ txt = txt.replace(/^\S+\s\S+/, 'Lorem ipsum'); if(!/^lorem ipsum/i.test(txt)) txt='Lorem ipsum '+txt; }
      $('#out').innerHTML=esc(txt)+copyBtnHtml();
      wireCopy($('#out [data-copy]'),function(){return txt;});
    });
    $('#go').click();
  };

  /* ---------- Slug Generator ---------- */
  tools['slug-generator'] = function () {
    h('<textarea class="tt-ta" id="src" placeholder="Enter a title, e.g. My Awesome Product Name!"></textarea>'
      + '<div class="tt-opts"><label class="tt-chk"><input type="radio" name="sep" value="-" checked> Hyphen ( - )</label>'
      + '<label class="tt-chk"><input type="radio" name="sep" value="_"> Underscore ( _ )</label>'
      + '<label class="tt-chk"><input type="checkbox" id="low" checked> lowercase</label></div>'
      + '<div class="tt-out mono" id="out" data-ph="your-slug-appears-here" style="margin-top:4px"></div>');
    var src=$('#src');
    function slugify(s,sep,low){
      s = s.toString().normalize('NFKD').replace(/[̀-ͯ]/g,'');
      if(low) s=s.toLowerCase();
      s = s.trim().replace(/[^a-zA-Z0-9]+/g, sep);
      var e = sep==='-'?'\\-':'_';
      s = s.replace(new RegExp('^['+e+']+|['+e+']+$','g'),'').replace(new RegExp('['+e+']{2,}','g'),sep);
      return s;
    }
    function upd(){
      var sep=(ui.querySelector('input[name=sep]:checked')||{}).value||'-';
      var r=slugify(src.value, sep, $('#low').checked);
      $('#out').innerHTML=esc(r)+copyBtnHtml();
      wireCopy($('#out [data-copy]'),function(){return r;});
    }
    src.addEventListener('input',upd);
    $all('input[name=sep],#low').forEach(function(x){x.addEventListener('change',upd);});
    upd();
  };

  (tools[slug] || function(){ h('<p style="color:var(--muted)">This tool is being set up.</p>'); })();
})();
