/* Zelari Calculators — all logic runs in the browser */
(function(){
  const Z=window.ZCALC||{}; const SLUG=Z.slug||'';
  const UI=document.getElementById('calcUI'); if(!UI) return;
  const fmt=(n,d)=>{ if(!isFinite(n)) return '—'; const x=(d===undefined?2:d); return Number(n).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:x}); };
  const money=n=>'$'+fmt(n,2);
  const el=(h)=>{ const d=document.createElement('div'); d.innerHTML=h; return d.firstElementChild; };

  /* ---------- generic form engine ---------- */
  function buildForm(def){
    const grid=el('<div class="cl-grid"></div>');
    def.fields.forEach(f=>{
      const w=el('<div class="cl-f'+(f.full?' full':'')+'"></div>');
      w.innerHTML='<label for="f_'+f.id+'">'+f.label+'</label>';
      let inp;
      if(f.type==='select'){
        inp=document.createElement('select');
        f.options.forEach(o=>{ const op=document.createElement('option'); op.value=o[0]; op.textContent=o[1]; inp.appendChild(op); });
      } else if(f.type==='textarea'){
        inp=document.createElement('textarea'); inp.rows=f.rows||4; inp.placeholder=f.ph||'';
      } else {
        inp=document.createElement('input');
        inp.type=f.type||'number';
        if(inp.type==='number'){ inp.step=f.step||'any'; inp.min=f.min!==undefined?f.min:''; }
        inp.placeholder=f.ph||'';
      }
      inp.id='f_'+f.id;
      if(f.value!==undefined) inp.value=f.value;
      const box=el('<div class="in"></div>');
      box.appendChild(inp);
      if(f.sfx) box.appendChild(el('<span class="sfx">'+f.sfx+'</span>'));
      w.appendChild(box);
      grid.appendChild(w);
    });
    UI.appendChild(grid);
    const res=el('<div class="cl-res" style="display:none"><div class="cl-big"><div class="l"></div><div class="v"></div></div><div class="cl-rows"></div></div>');
    UI.appendChild(res);
    const vals=()=>{ const v={}; def.fields.forEach(f=>{ const n=document.getElementById('f_'+f.id); v[f.id]=(f.type==='select'||f.type==='date'||f.type==='text'||f.type==='textarea')? n.value : parseFloat(n.value); }); return v; };
    const run=()=>{
      let out=null;
      try{ out=def.compute(vals()); }catch(_){ out=null; }
      if(!out){ res.style.display='none'; return; }
      res.style.display='block';
      res.querySelector('.cl-big .l').textContent=out.big[0];
      res.querySelector('.cl-big .v').textContent=out.big[1];
      const rows=res.querySelector('.cl-rows'); rows.innerHTML='';
      (out.rows||[]).forEach(r=>rows.appendChild(el('<div class="cl-row"><div class="l">'+r[0]+'</div><div class="v">'+r[1]+'</div></div>')));
    };
    def.fields.forEach(f=>document.getElementById('f_'+f.id).addEventListener('input',run));
    run();
  }

  const num=(x)=>typeof x==='number'&&isFinite(x);
  const emi=(P,annual,months)=>{ const r=annual/1200; if(r===0) return P/months; return P*r*Math.pow(1+r,months)/(Math.pow(1+r,months)-1); };

  /* ---------- standard calculator definitions ---------- */
  const DEFS={
    'loan-calculator':{fields:[
      {id:'amt',label:'Loan amount',ph:'e.g. 25000',sfx:'$',min:0},
      {id:'rate',label:'Annual interest rate',ph:'e.g. 7.5',sfx:'%',min:0},
      {id:'years',label:'Term (years)',ph:'e.g. 5',min:0},
      {id:'months',label:'+ extra months',value:0,min:0}],
      compute:v=>{ const n=(v.years||0)*12+(v.months||0); if(!num(v.amt)||!num(v.rate)||n<=0||v.amt<=0) return null;
        const m=emi(v.amt,v.rate,n);
        return {big:['Monthly payment (EMI)',money(m)],rows:[['Total of '+n+' payments',money(m*n)],['Total interest',money(m*n-v.amt)],['Payoff time',(v.years||0)+'y '+(v.months||0)+'m']]}; }},
    'mortgage-calculator':{fields:[
      {id:'price',label:'Home price',ph:'e.g. 350000',sfx:'$',min:0},
      {id:'down',label:'Down payment',ph:'e.g. 20',sfx:'%',value:20,min:0},
      {id:'years',label:'Loan term (years)',value:30,min:1},
      {id:'rate',label:'Interest rate',ph:'e.g. 6.5',sfx:'%',min:0}],
      compute:v=>{ if(!num(v.price)||!num(v.rate)||!num(v.years)||v.price<=0) return null;
        const dp=v.price*(v.down||0)/100, P=v.price-dp, n=v.years*12, m=emi(P,v.rate,n);
        return {big:['Monthly payment',money(m)],rows:[['Loan amount',money(P)],['Down payment',money(dp)],['Total paid over '+v.years+' years',money(m*n)],['Total interest',money(m*n-P)]]}; }},
    'compound-interest':{fields:[
      {id:'p',label:'Initial amount',ph:'e.g. 10000',sfx:'$',min:0},
      {id:'rate',label:'Annual interest rate',ph:'e.g. 8',sfx:'%'},
      {id:'years',label:'Years',ph:'e.g. 10',min:0},
      {id:'freq',label:'Compounding',type:'select',options:[['12','Monthly'],['4','Quarterly'],['1','Yearly']]}],
      compute:v=>{ const n=parseFloat(v.freq); if(!num(v.p)||!num(v.rate)||!num(v.years)) return null;
        const fv=v.p*Math.pow(1+v.rate/100/n,n*v.years);
        return {big:['Future value',money(fv)],rows:[['Principal',money(v.p)],['Interest earned',money(fv-v.p)],['Growth','×'+fmt(fv/Math.max(v.p,1e-9),2)]]}; }},
    'simple-interest':{fields:[
      {id:'p',label:'Principal',ph:'e.g. 5000',sfx:'$',min:0},
      {id:'rate',label:'Annual rate',ph:'e.g. 6',sfx:'%'},
      {id:'years',label:'Time (years)',ph:'e.g. 3',min:0}],
      compute:v=>{ if(!num(v.p)||!num(v.rate)||!num(v.years)) return null;
        const i=v.p*v.rate*v.years/100;
        return {big:['Interest',money(i)],rows:[['Total amount',money(v.p+i)],['Principal',money(v.p)]]}; }},
    'investment-calculator':{fields:[
      {id:'p',label:'Starting amount',ph:'e.g. 5000',sfx:'$',value:0,min:0},
      {id:'m',label:'Monthly contribution',ph:'e.g. 300',sfx:'$',value:0,min:0},
      {id:'rate',label:'Expected annual return',ph:'e.g. 10',sfx:'%'},
      {id:'years',label:'Years',ph:'e.g. 20',min:0}],
      compute:v=>{ if(!num(v.rate)||!num(v.years)||v.years<=0) return null;
        const i=v.rate/1200, N=v.years*12, p=v.p||0, pm=v.m||0;
        const fv=p*Math.pow(1+i,N)+(i===0? pm*N : pm*((Math.pow(1+i,N)-1)/i));
        const invested=p+pm*N;
        return {big:['Future value',money(fv)],rows:[['Total invested',money(invested)],['Total gain',money(fv-invested)],['Growth','×'+fmt(fv/Math.max(invested,1e-9),2)]]}; }},
    'roi-calculator':{fields:[
      {id:'cost',label:'Amount invested',ph:'e.g. 10000',sfx:'$',min:0},
      {id:'ret',label:'Amount returned',ph:'e.g. 14500',sfx:'$',min:0}],
      compute:v=>{ if(!num(v.cost)||!num(v.ret)||v.cost<=0) return null;
        const g=v.ret-v.cost;
        return {big:['ROI',fmt(g/v.cost*100,2)+'%'],rows:[['Gain / loss',money(g)],['Invested',money(v.cost)],['Returned',money(v.ret)]]}; }},
    'discount-calculator':{fields:[
      {id:'price',label:'Original price',ph:'e.g. 80',sfx:'$',min:0},
      {id:'off',label:'Discount',ph:'e.g. 25',sfx:'%',min:0}],
      compute:v=>{ if(!num(v.price)||!num(v.off)) return null;
        const save=v.price*v.off/100;
        return {big:['Final price',money(v.price-save)],rows:[['You save',money(save)],['Original price',money(v.price)]]}; }},
    'tip-calculator':{fields:[
      {id:'bill',label:'Bill amount',ph:'e.g. 64.50',sfx:'$',min:0},
      {id:'tip',label:'Tip',value:15,sfx:'%',min:0},
      {id:'ppl',label:'Split between (people)',value:1,min:1,step:'1'}],
      compute:v=>{ if(!num(v.bill)||!num(v.tip)||!num(v.ppl)||v.ppl<1) return null;
        const t=v.bill*v.tip/100, total=v.bill+t;
        return {big:['Total with tip',money(total)],rows:[['Tip amount',money(t)],['Per person',money(total/v.ppl)],['Tip per person',money(t/v.ppl)]]}; }},
    'sales-tax-calculator':{fields:[
      {id:'mode',label:'Price entered is',type:'select',options:[['ex','Before tax (add tax)'],['in','Tax-inclusive (extract tax)']]},
      {id:'price',label:'Price',ph:'e.g. 100',sfx:'$',min:0},
      {id:'tax',label:'Tax rate',ph:'e.g. 7.25',sfx:'%',min:0}],
      compute:v=>{ if(!num(v.price)||!num(v.tax)) return null;
        if(v.mode==='ex'){ const t=v.price*v.tax/100; return {big:['Price with tax',money(v.price+t)],rows:[['Tax amount',money(t)],['Net price',money(v.price)]]}; }
        const net=v.price/(1+v.tax/100);
        return {big:['Net price (before tax)',money(net)],rows:[['Tax amount',money(v.price-net)],['Gross price',money(v.price)]]}; }},
    'margin-calculator':{fields:[
      {id:'cost',label:'Cost',ph:'e.g. 40',sfx:'$',min:0},
      {id:'sell',label:'Selling price',ph:'e.g. 65',sfx:'$',min:0}],
      compute:v=>{ if(!num(v.cost)||!num(v.sell)||v.sell<=0) return null;
        const p=v.sell-v.cost;
        return {big:['Profit margin',fmt(p/v.sell*100,2)+'%'],rows:[['Profit',money(p)],['Markup',v.cost>0?fmt(p/v.cost*100,2)+'%':'—'],['Cost → Sell',money(v.cost)+' → '+money(v.sell)]]}; }},
    'bmi-calculator':{fields:[
      {id:'unit',label:'Units',type:'select',options:[['m','Metric (cm, kg)'],['i','Imperial (ft/in, lb)']]},
      {id:'h',label:'Height (cm — or total inches for imperial)',ph:'e.g. 175'},
      {id:'w',label:'Weight (kg — or lb for imperial)',ph:'e.g. 70'}],
      compute:v=>{ if(!num(v.h)||!num(v.w)||v.h<=0||v.w<=0) return null;
        let bmi; if(v.unit==='m'){ const m=v.h/100; bmi=v.w/(m*m); } else { bmi=703*v.w/(v.h*v.h); }
        const cat=bmi<18.5?'Underweight':bmi<25?'Normal weight':bmi<30?'Overweight':'Obese';
        return {big:['Your BMI',fmt(bmi,1)],rows:[['Category',cat],['Healthy BMI range','18.5 – 24.9']]}; }},
    'calorie-calculator':{fields:[
      {id:'sex',label:'Gender',type:'select',options:[['m','Male'],['f','Female']]},
      {id:'age',label:'Age',ph:'e.g. 28',min:10},
      {id:'h',label:'Height (cm)',ph:'e.g. 175'},
      {id:'w',label:'Weight (kg)',ph:'e.g. 70'},
      {id:'act',label:'Activity level',type:'select',full:true,options:[['1.2','Sedentary (little exercise)'],['1.375','Light (1–3 days/week)'],['1.55','Moderate (3–5 days/week)'],['1.725','Active (6–7 days/week)'],['1.9','Very active (hard training)']]}],
      compute:v=>{ if(!num(v.age)||!num(v.h)||!num(v.w)) return null;
        const bmr=10*v.w+6.25*v.h-5*v.age+(v.sex==='m'?5:-161);
        const tdee=bmr*parseFloat(v.act);
        return {big:['Maintenance calories',fmt(tdee,0)+' kcal/day'],rows:[['BMR (at rest)',fmt(bmr,0)+' kcal'],['Lose ~0.5 kg/week',fmt(tdee-500,0)+' kcal'],['Gain ~0.5 kg/week',fmt(tdee+500,0)+' kcal']]}; }},
    'ideal-weight':{fields:[
      {id:'sex',label:'Gender',type:'select',options:[['m','Male'],['f','Female']]},
      {id:'h',label:'Height (cm)',ph:'e.g. 175'}],
      compute:v=>{ if(!num(v.h)||v.h<=130) return null;
        const inch=v.h/2.54, over=Math.max(0,inch-60), m=v.sex==='m';
        const dev=(m?50:45.5)+2.3*over, rob=(m?52:49)+(m?1.9:1.7)*over, mil=(m?56.2:53.1)+(m?1.41:1.36)*over, ham=(m?48:45.5)+(m?2.7:2.2)*over;
        return {big:['Ideal weight (Devine)',fmt(dev,1)+' kg'],rows:[['Robinson',fmt(rob,1)+' kg'],['Miller',fmt(mil,1)+' kg'],['Hamwi',fmt(ham,1)+' kg']]}; }},
    'age-calculator':{fields:[
      {id:'dob',label:'Date of birth',type:'date'},
      {id:'asof',label:'Age at date',type:'date',value:new Date().toISOString().slice(0,10)}],
      compute:v=>{ if(!v.dob||!v.asof) return null;
        const b=new Date(v.dob+'T00:00:00'), a=new Date(v.asof+'T00:00:00');
        if(!(b<a)) return null;
        let y=a.getFullYear()-b.getFullYear(), mo=a.getMonth()-b.getMonth(), d=a.getDate()-b.getDate();
        if(d<0){ mo--; d+=new Date(a.getFullYear(),a.getMonth(),0).getDate(); }
        if(mo<0){ y--; mo+=12; }
        const days=Math.floor((a-b)/864e5);
        let nb=new Date(a.getFullYear(),b.getMonth(),b.getDate()); if(nb<=a) nb=new Date(a.getFullYear()+1,b.getMonth(),b.getDate());
        return {big:['Age',y+' years, '+mo+' months, '+d+' days'],rows:[['Total days',fmt(days,0)],['Total weeks',fmt(days/7,1)],['Next birthday',nb.toLocaleDateString('en-US',{weekday:'long',month:'short',day:'numeric'})+' ('+Math.ceil((nb-a)/864e5)+' days)']]}; }},
  };

  /* ---------- custom UIs ---------- */
  function percentageUI(){
    UI.innerHTML=
      '<div class="cl-grid"><div class="cl-f"><label>What is</label><div class="in"><input id="pa" type="number" step="any" placeholder="e.g. 15"><span class="sfx">%</span></div></div><div class="cl-f"><label>of</label><div class="in"><input id="pb" type="number" step="any" placeholder="e.g. 200"></div></div></div>'+
      '<div class="cl-big" style="margin-top:12px"><div class="l">Result</div><div class="v" id="pr1">—</div></div>'+
      '<div class="cl-grid" style="margin-top:20px"><div class="cl-f"><label>X</label><div class="in"><input id="pc" type="number" step="any" placeholder="e.g. 30"></div></div><div class="cl-f"><label>is what % of Y</label><div class="in"><input id="pd" type="number" step="any" placeholder="e.g. 120"></div></div></div>'+
      '<div class="cl-big" style="margin-top:12px"><div class="l">Result</div><div class="v" id="pr2">—</div></div>'+
      '<div class="cl-grid" style="margin-top:20px"><div class="cl-f"><label>From (old value)</label><div class="in"><input id="pe" type="number" step="any" placeholder="e.g. 80"></div></div><div class="cl-f"><label>To (new value)</label><div class="in"><input id="pf" type="number" step="any" placeholder="e.g. 100"></div></div></div>'+
      '<div class="cl-big" style="margin-top:12px"><div class="l">Percentage change</div><div class="v" id="pr3">—</div></div>';
    const g=id=>parseFloat(document.getElementById(id).value);
    const run=()=>{
      const a=g('pa'),b=g('pb'); document.getElementById('pr1').textContent=(num(a)&&num(b))?fmt(a*b/100,4):'—';
      const c=g('pc'),d=g('pd'); document.getElementById('pr2').textContent=(num(c)&&num(d)&&d!==0)?fmt(c/d*100,4)+'%':'—';
      const e2=g('pe'),f2=g('pf'); document.getElementById('pr3').textContent=(num(e2)&&num(f2)&&e2!==0)?((f2>=e2?'+':'')+fmt((f2-e2)/Math.abs(e2)*100,4)+'%'):'—';
    };
    UI.querySelectorAll('input').forEach(i=>i.addEventListener('input',run));
  }

  function averageUI(){
    UI.innerHTML='<div class="cl-f full"><label>Enter numbers (separated by commas, spaces or new lines)</label><textarea id="avIn" rows="5" placeholder="e.g. 12, 7, 19.5, 4 8 33"></textarea></div><div class="cl-res" id="avRes" style="display:none"><div class="cl-big"><div class="l">Average (mean)</div><div class="v" id="avMean"></div></div><div class="cl-rows" id="avRows"></div></div>';
    document.getElementById('avIn').addEventListener('input',()=>{
      const nums=(document.getElementById('avIn').value.match(/-?\d+(\.\d+)?/g)||[]).map(Number);
      const res=document.getElementById('avRes');
      if(!nums.length){ res.style.display='none'; return; }
      res.style.display='block';
      const sum=nums.reduce((a,b)=>a+b,0), mean=sum/nums.length;
      const sorted=[...nums].sort((a,b)=>a-b);
      const med=sorted.length%2? sorted[(sorted.length-1)/2] : (sorted[sorted.length/2-1]+sorted[sorted.length/2])/2;
      const sd=Math.sqrt(nums.reduce((a,b)=>a+(b-mean)*(b-mean),0)/nums.length);
      document.getElementById('avMean').textContent=fmt(mean,4);
      document.getElementById('avRows').innerHTML=[['Count',fmt(nums.length,0)],['Sum',fmt(sum,4)],['Median',fmt(med,4)],['Min',fmt(sorted[0],4)],['Max',fmt(sorted[sorted.length-1],4)],['Std deviation',fmt(sd,4)]].map(r=>'<div class="cl-row"><div class="l">'+r[0]+'</div><div class="v">'+r[1]+'</div></div>').join('');
    });
  }

  function gpaUI(){
    UI.innerHTML='<div id="gpaRows"></div><button type="button" class="cl-add" id="gpaAdd"><i class="fa-solid fa-plus"></i> Add course</button><div class="cl-res" style="display:none" id="gpaRes"><div class="cl-big"><div class="l">Your GPA</div><div class="v" id="gpaV"></div></div><div class="cl-rows" id="gpaRows2"></div></div>';
    const GR=[['4','A / A+'],['3.7','A-'],['3.3','B+'],['3','B'],['2.7','B-'],['2.3','C+'],['2','C'],['1.7','C-'],['1.3','D+'],['1','D'],['0','F']];
    const wrap=document.getElementById('gpaRows');
    const addRow=()=>{
      const r=el('<div class="gpa-row"><input type="text" placeholder="Course name (optional)"><input type="number" min="0" step="0.5" placeholder="Credits" class="cr"><select class="gr">'+GR.map(g=>'<option value="'+g[0]+'">'+g[1]+'</option>').join('')+'</select><button type="button"><i class="fa-solid fa-xmark"></i></button></div>');
      r.querySelector('button').onclick=()=>{ r.remove(); run(); };
      r.querySelectorAll('input,select').forEach(i=>i.addEventListener('input',run));
      wrap.appendChild(r);
    };
    const run=()=>{
      let pts=0, cr=0;
      wrap.querySelectorAll('.gpa-row').forEach(r=>{
        const c=parseFloat(r.querySelector('.cr').value), g=parseFloat(r.querySelector('.gr').value);
        if(num(c)&&c>0){ cr+=c; pts+=c*g; }
      });
      const res=document.getElementById('gpaRes');
      if(cr<=0){ res.style.display='none'; return; }
      res.style.display='block';
      document.getElementById('gpaV').textContent=fmt(pts/cr,2);
      document.getElementById('gpaRows2').innerHTML='<div class="cl-row"><div class="l">Total credits</div><div class="v">'+fmt(cr,1)+'</div></div><div class="cl-row"><div class="l">Quality points</div><div class="v">'+fmt(pts,1)+'</div></div>';
    };
    document.getElementById('gpaAdd').onclick=addRow;
    addRow(); addRow(); addRow();
  }

  function dateUI(){
    UI.innerHTML='<div class="cl-tabs"><button type="button" class="cl-tab on" data-t="diff">Days between dates</button><button type="button" class="cl-tab" data-t="add">Add / subtract</button></div><div id="dtBody"></div>';
    const body=document.getElementById('dtBody');
    const today=new Date().toISOString().slice(0,10);
    const render=t=>{
      if(t==='diff'){
        body.innerHTML='<div class="cl-grid"><div class="cl-f"><label>Start date</label><div class="in"><input id="d1" type="date" value="'+today+'"></div></div><div class="cl-f"><label>End date</label><div class="in"><input id="d2" type="date"></div></div></div><div class="cl-res" id="dR" style="display:none"><div class="cl-big"><div class="l">Difference</div><div class="v" id="dBig"></div></div><div class="cl-rows" id="dRows"></div></div>';
        const run=()=>{
          const a=document.getElementById('d1').value, b=document.getElementById('d2').value;
          const res=document.getElementById('dR');
          if(!a||!b){ res.style.display='none'; return; }
          const d1=new Date(a+'T00:00:00'), d2=new Date(b+'T00:00:00');
          const days=Math.abs(Math.round((d2-d1)/864e5));
          res.style.display='block';
          document.getElementById('dBig').textContent=fmt(days,0)+' days';
          document.getElementById('dRows').innerHTML=[['Weeks',fmt(days/7,2)],['Months (approx.)',fmt(days/30.4375,2)],['Years (approx.)',fmt(days/365.25,2)]].map(r=>'<div class="cl-row"><div class="l">'+r[0]+'</div><div class="v">'+r[1]+'</div></div>').join('');
        };
        body.querySelectorAll('input').forEach(i=>i.addEventListener('input',run));
      } else {
        body.innerHTML='<div class="cl-grid"><div class="cl-f"><label>Start date</label><div class="in"><input id="d3" type="date" value="'+today+'"></div></div><div class="cl-f"><label>Operation</label><div class="in"><select id="dop"><option value="1">Add</option><option value="-1">Subtract</option></select></div></div><div class="cl-f"><label>Amount</label><div class="in"><input id="dn" type="number" min="0" step="1" placeholder="e.g. 45"></div></div><div class="cl-f"><label>Unit</label><div class="in"><select id="du"><option value="d">Days</option><option value="w">Weeks</option><option value="m">Months</option><option value="y">Years</option></select></div></div></div><div class="cl-res" id="dR2" style="display:none"><div class="cl-big"><div class="l">Result date</div><div class="v" id="dBig2"></div></div></div>';
        const run=()=>{
          const a=document.getElementById('d3').value, n=parseFloat(document.getElementById('dn').value);
          const res=document.getElementById('dR2');
          if(!a||!num(n)){ res.style.display='none'; return; }
          const sign=parseInt(document.getElementById('dop').value), u=document.getElementById('du').value;
          const d=new Date(a+'T00:00:00');
          if(u==='d') d.setDate(d.getDate()+sign*n);
          else if(u==='w') d.setDate(d.getDate()+sign*n*7);
          else if(u==='m') d.setMonth(d.getMonth()+sign*n);
          else d.setFullYear(d.getFullYear()+sign*n);
          res.style.display='block';
          document.getElementById('dBig2').textContent=d.toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
        };
        body.querySelectorAll('input,select').forEach(i=>i.addEventListener('input',run));
      }
    };
    UI.querySelectorAll('.cl-tab').forEach(b=>b.onclick=()=>{ UI.querySelectorAll('.cl-tab').forEach(x=>x.classList.toggle('on',x===b)); render(b.dataset.t); });
    render('diff');
  }

  const UNITS={
    length:{m:1,km:1000,cm:0.01,mm:0.001,mi:1609.344,yd:0.9144,ft:0.3048,'in':0.0254},
    weight:{kg:1,g:0.001,mg:1e-6,t:1000,lb:0.45359237,oz:0.028349523},
    area:{'m²':1,'km²':1e6,'ft²':0.09290304,'yd²':0.83612736,acre:4046.8564,hectare:10000},
    volume:{L:1,mL:0.001,'m³':1000,'gal (US)':3.785411784,'qt (US)':0.946352946,'cup (US)':0.2365882365,'fl oz (US)':0.0295735296},
    speed:{'km/h':1,'m/s':3.6,'mph':1.609344,'knot':1.852},
    data:{MB:1,KB:1/1024,GB:1024,TB:1024*1024,B:1/1048576},
    temperature:null
  };
  function unitUI(){
    UI.innerHTML='<div class="cl-grid"><div class="cl-f"><label>Category</label><div class="in"><select id="uc">'+Object.keys(UNITS).map(c=>'<option>'+c+'</option>').join('')+'</select></div></div><div class="cl-f"><label>Value</label><div class="in"><input id="uv" type="number" step="any" placeholder="e.g. 100"></div></div><div class="cl-f"><label>From</label><div class="in"><select id="uf"></select></div></div><div class="cl-f"><label>To</label><div class="in"><select id="ut"></select></div></div></div><div class="cl-res" id="uR" style="display:none"><div class="cl-big"><div class="l">Result</div><div class="v" id="uBig"></div></div></div>';
    const cat=document.getElementById('uc'), from=document.getElementById('uf'), to=document.getElementById('ut');
    const fill=()=>{
      const c=cat.value;
      const names=c==='temperature'? ['°C','°F','K'] : Object.keys(UNITS[c]);
      from.innerHTML=to.innerHTML=names.map(n=>'<option>'+n+'</option>').join('');
      if(names.length>1) to.selectedIndex=1;
      run();
    };
    const run=()=>{
      const v=parseFloat(document.getElementById('uv').value);
      const res=document.getElementById('uR');
      if(!num(v)){ res.style.display='none'; return; }
      const c=cat.value; let out;
      if(c==='temperature'){
        let k = from.value==='°C'? v+273.15 : from.value==='°F'? (v-32)*5/9+273.15 : v;
        out = to.value==='°C'? k-273.15 : to.value==='°F'? (k-273.15)*9/5+32 : k;
      } else {
        out=v*UNITS[c][from.value]/UNITS[c][to.value];
      }
      res.style.display='block';
      document.getElementById('uBig').textContent=fmt(v,6)+' '+from.value+' = '+fmt(out,6)+' '+to.value;
    };
    cat.addEventListener('change',fill);
    [from,to].forEach(s=>s.addEventListener('change',run));
    document.getElementById('uv').addEventListener('input',run);
    fill();
  }

  /* scientific calculator: tokenizer + shunting-yard (no eval) */
  function sciUI(){
    UI.innerHTML='<input class="kp-disp" id="kpD" readonly value=""><div class="kp-prev" id="kpP"></div><div class="kp" id="kpG"></div>';
    const keys=[
      ['sin','fn'],['cos','fn'],['tan','fn'],['DEG','fn mode'],['C','danger'],
      ['ln','fn'],['log','fn'],['√','fn'],['(',''],[')',''],
      ['7',''],['8',''],['9',''],['⌫','op'],['÷','op'],
      ['4',''],['5',''],['6',''],['^','op'],['×','op'],
      ['1',''],['2',''],['3',''],['%','op'],['−','op'],
      ['0',''],['.',''],['π','fn'],['+','op'],['=','eq']
    ];
    let expr='', deg=true;
    const D=document.getElementById('kpD'), P=document.getElementById('kpP');
    const draw=()=>{ D.value=expr||'0'; };
    const tok=s=>{
      const out=[]; let i=0;
      const isd=c=>/[0-9.]/.test(c);
      while(i<s.length){
        const c=s[i];
        if(isd(c)){ let j=i; while(j<s.length&&isd(s[j]))j++; out.push({t:'n',v:parseFloat(s.slice(i,j))}); i=j; continue; }
        if(/[a-zπe]/.test(c)){ let j=i; while(j<s.length&&/[a-zπe]/.test(s[j]))j++; out.push({t:'f',v:s.slice(i,j)}); i=j; continue; }
        out.push({t:'o',v:c}); i++;
      }
      return out;
    };
    const evalExpr=s=>{
      s=s.replace(/×/g,'*').replace(/÷/g,'/').replace(/−/g,'-');
      const T=tok(s);
      const prec={'+':1,'-':1,'*':2,'/':2,'%':2,'^':3};
      const outQ=[], ops=[];
      const applyFn={sqrt:Math.sqrt,ln:Math.log,log:Math.log10,
        sin:x=>Math.sin(deg?x*Math.PI/180:x),cos:x=>Math.cos(deg?x*Math.PI/180:x),tan:x=>Math.tan(deg?x*Math.PI/180:x)};
      for(let k=0;k<T.length;k++){
        const t=T[k];
        if(t.t==='n') outQ.push(t.v);
        else if(t.t==='f'){ if(t.v==='π') outQ.push(Math.PI); else if(t.v==='e') outQ.push(Math.E); else ops.push(t.v); }
        else if(t.v==='('){ ops.push('('); }
        else if(t.v===')'){
          while(ops.length&&ops[ops.length-1]!=='(') pop();
          ops.pop();
          if(ops.length&&applyFn[ops[ops.length-1]]) pop();
        } else {
          /* unary minus */
          if(t.v==='-'&&(k===0||T[k-1].t==='o'&&T[k-1].v!==')')){ outQ.push(0); }
          while(ops.length){
            const top=ops[ops.length-1];
            if(top==='('||applyFn[top]) break;
            if(prec[top]>prec[t.v]||(prec[top]===prec[t.v]&&t.v!=='^')) pop(); else break;
          }
          ops.push(t.v);
        }
      }
      function pop(){
        const op=ops.pop();
        if(applyFn[op]){ outQ.push(applyFn[op](outQ.pop())); return; }
        const b=outQ.pop(), a=outQ.pop();
        outQ.push(op==='+'?a+b:op==='-'?a-b:op==='*'?a*b:op==='/'?a/b:op==='%'?a*b/100:Math.pow(a,b));
      }
      while(ops.length) pop();
      const r=outQ.pop();
      if(outQ.length||!isFinite(r)) throw 0;
      return r;
    };
    const G=document.getElementById('kpG');
    keys.forEach(k=>{
      const b=document.createElement('button');
      b.type='button'; b.textContent=k[0]; if(k[1]) b.className=k[1];
      b.onclick=()=>{
        const key=k[0];
        if(key==='C'){ expr=''; P.textContent=''; }
        else if(key==='⌫'){ expr=expr.slice(0,-1); }
        else if(key==='DEG'||key==='RAD'){ deg=!deg; b.textContent=deg?'DEG':'RAD'; return; }
        else if(key==='='){
          try{ const r=evalExpr(expr); P.textContent=expr+' ='; expr=String(Math.round(r*1e10)/1e10); }
          catch(_){ P.textContent='Invalid expression'; }
        }
        else if(['sin','cos','tan','ln','log'].includes(key)) expr+=key+'(';
        else if(key==='√') expr+='sqrt(';
        else expr+=key;
        draw();
      };
      G.appendChild(b);
    });
    draw();
    window.zSciEval=evalExpr; /* test hook */
  }

  /* ---------- boot ---------- */
  const CUSTOM={'scientific-calculator':sciUI,'percentage-calculator':percentageUI,'average-calculator':averageUI,'gpa-calculator':gpaUI,'date-calculator':dateUI,'unit-converter':unitUI};
  if(CUSTOM[SLUG]) CUSTOM[SLUG]();
  else if(DEFS[SLUG]) buildForm(DEFS[SLUG]);
  else UI.innerHTML='<p style="color:var(--muted)">This calculator is being set up.</p>';
})();
