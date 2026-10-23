@extends('layouts.store')
@section('title', 'Product Sourcing')

@push('styles')
<style>
  .sr {
    padding: 56px 0 10px
  }

  .sr-grid {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
    gap: 28px;
    align-items: start
  }

  @media(max-width:992px) {
    .sr-grid {
      grid-template-columns: 1fr
    }
  }

  .sr-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 30px
  }

  .sr-card h2 {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 6px;
    letter-spacing: -.4px
  }

  .sr-card>p.lead {
    color: var(--muted);
    font-size: 14px;
    margin: 0 0 24px
  }

  .sr-step {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
    color: var(--p);
    margin: 26px 0 12px;
    display: flex;
    align-items: center;
    gap: 10px
  }

  .sr-step::after {
    content: "";
    flex: 1;
    height: 1px;
    background: var(--line)
  }

  .sr-step:first-of-type {
    margin-top: 0
  }

  /* product rows — a compact table so a long list stays readable */
  .pwrap{border:1px solid var(--line);border-radius:14px;overflow:auto;margin-bottom:10px}
  .ptable{width:100%;min-width:880px;border-collapse:collapse;font-size:13px}
  .ptable th{background:#EFF6FF;color:var(--ink-2);font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;text-align:left;padding:9px 10px;white-space:nowrap;border-bottom:1px solid var(--line)}
  .ptable th b{color:#e11d48}
  .ptable td{padding:7px 8px;border-bottom:1px solid var(--line);vertical-align:top}
  .ptable tr:last-child td{border-bottom:0}
  .ptable .pnum{width:34px;text-align:center;color:var(--faint);font-weight:800;padding-top:15px}
  .ptable .inp{padding:8px 10px;font-size:13px;border-radius:9px;width:100%}
  .ptable .ferr{font-size:11px;margin-top:3px}
  .ptable .tdel{width:40px}
  .pdel{width:28px;height:28px;border:0;border-radius:8px;background:#f6f7fb;color:#b91c1c;cursor:pointer;display:grid;place-items:center;transition:.2s;margin-top:4px}
  .pdel:hover{background:#fee2e2}
  .prow:only-of-type .pdel{visibility:hidden}
  .addmore{width:100%;border:1.5px dashed var(--line);background:#fff;color:var(--p);font-weight:800;font-size:13.5px;padding:10px;border-radius:12px;cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px}
  .addmore:hover{border-color:var(--p);background:#EFF6FF}
  /* Phones: a row that scrolls sideways is unusable, so each product becomes its own block */
  @media(max-width:760px){
    .pwrap{border:0;border-radius:0;overflow:visible;margin-bottom:12px}
    .ptable{min-width:0;display:block;font-size:14px}
    .ptable thead{display:none}
    .ptable tbody{display:block}
    .ptable .prow{display:grid;grid-template-columns:1fr 1fr;gap:10px 10px;position:relative;border:1px solid var(--line);border-radius:12px;background:#EFF6FF;padding:30px 12px 12px;margin-bottom:10px}
    .ptable td{display:block;border:0;padding:0}
    .ptable td[data-l]::before{content:attr(data-l);display:block;font-size:11px;font-weight:700;color:var(--ink-2);margin-bottom:4px}
    .ptable .c-name,.ptable .c-price,.ptable .c-ref,.ptable .c-specs{grid-column:1/-1}
    .ptable .inp{background:#fff}
    .ptable td.pnum{position:absolute;top:9px;left:12px;width:auto;padding:0;font-size:11px;font-weight:800;color:var(--p)}
    .ptable td.pnum::before{content:"Product "}
    .ptable td.tdel{position:absolute;top:5px;right:8px;width:auto;padding:0}
    .ptable td.tdel .pdel{margin-top:0;background:#fff}
  }
  @media(max-width:600px){
    .sr{padding:34px 0 10px}
    .sr-card{padding:20px 16px}
    .sr-card h2{font-size:19px}
  }

  #srBtn[disabled] {
    opacity: .65;
    cursor: not-allowed
  }

  #srBtn .spin {
    display: inline-block;
    width: 15px;
    height: 15px;
    border: 2px solid rgba(255, 255, 255, .4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: srspin .7s linear infinite;
    vertical-align: -2px
  }

  @keyframes srspin {
    to {
      transform: rotate(360deg)
    }
  }

  /* side rail */
  .how {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 24px;
    margin-bottom: 14px
  }

  .how h3 {
    font-size: 16px;
    font-weight: 800;
    margin: 0 0 16px
  }

  .how ol {
    list-style: none;
    margin: 0;
    padding: 0;
    counter-reset: s
  }

  .how li {
    counter-increment: s;
    display: flex;
    gap: 13px;
    padding-bottom: 18px;
    position: relative
  }

  .how li:last-child {
    padding-bottom: 0
  }

  .how li::before {
    content: counter(s);
    flex: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--p);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    display: grid;
    place-items: center
  }

  .how li:not(:last-child)::after {
    content: "";
    position: absolute;
    left: 13.5px;
    top: 30px;
    bottom: 6px;
    width: 1px;
    background: var(--line)
  }

  .how b {
    display: block;
    font-size: 14.5px;
    margin-bottom: 2px
  }

  .how span {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.6
  }

  .trust {
    display: grid;
    gap: 12px
  }

  .tbox {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 18px;
    display: flex;
    gap: 13px;
    align-items: flex-start
  }

  .tbox i {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: #EFF6FF;
    color: var(--p);
    flex: none
  }

  .tbox b {
    display: block;
    font-size: 14.5px;
    margin-bottom: 2px
  }

  .tbox span {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.6
  }
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', ['id'=>'sourcingHero','crumbs'=>[['Home',route('home')],['Sourcing',null]],'slides'=>[
['title'=>'Can\'t Find It? <span>We\'ll Source It</span>','text'=>'Tell us what you need and we\'ll find it, quote it and ship it to you.','icon'=>'fa-boxes-packing'],
['title'=>'Direct From <span>The Factory</span>','text'=>'China, Turkey, UAE or anywhere else — bulk or sample quantities.','icon'=>'fa-earth-asia'],
]])

<section class="wrap sr">
  <div class="sr-grid">

    <div class="sr-card reveal">
      <h2>Request a product</h2>
      <p class="lead">Tell us what you want and roughly how many. We come back with price, lead time and shipping — no obligation.</p>

      <form id="srForm" action="{{ route('sourcing.store') }}" method="POST">
        @csrf

        <div class="sr-step">Your details</div>
        <div class="row g-3">
          <div class="col-sm-6 fld"><label>Your name <b>*</b></label>
            <input class="inp" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required placeholder="Full name">
          </div>
          <div class="col-sm-6 fld"><label>Email <b>*</b></label>
            <input class="inp" type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required placeholder="you@example.com">
          </div>
          <div class="col-sm-6 fld"><label>Phone / WhatsApp</label>
            <input class="inp" name="phone" value="{{ old('phone') }}" placeholder="+92 300 0000000">
          </div>
          <div class="col-sm-6 fld"><label>Company</label>
            <input class="inp" name="company" value="{{ old('company') }}" placeholder="Optional">
          </div>
        </div>

        <div class="sr-step">What you need</div>
        <div class="pwrap">
          <table class="ptable">
            <thead>
              <tr>
                <th></th>
                <th>Product name <b>*</b></th>
                <th style="width:90px">Qty <b>*</b></th>
                <th style="width:80px">Unit</th>
                <th style="width:120px">Target price</th>
                <th style="width:175px">Reference link</th>
                <th style="width:195px">Specs</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="srItems">
              <tr class="prow">
                <td class="pnum">1</td>
                <td class="c-name" data-l="Product name"><input class="inp" name="items[0][name]" required placeholder="e.g. Wireless earbuds"></td>
                <td class="c-qty" data-l="Quantity"><input class="inp" type="number" min="1" name="items[0][quantity]" value="1" required></td>
                <td class="c-unit" data-l="Unit"><input class="inp" name="items[0][unit]" placeholder="pcs"></td>
                <td class="c-price" data-l="Target price"><input class="inp" name="items[0][target_price]" placeholder="$4 / pc"></td>
                <td class="c-ref" data-l="Reference link"><input class="inp" type="url" name="items[0][reference_url]" placeholder="https://…"></td>
                <td class="c-specs" data-l="Specs"><input class="inp" name="items[0][specs]" placeholder="Colour, size, material…"></td>
                <td class="tdel"><button type="button" class="pdel" title="Remove"><i class="fa-solid fa-xmark"></i></button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <button type="button" id="srAdd" class="addmore"><i class="fa-solid fa-plus"></i> Add another product</button>

        <div class="sr-step">Delivery &amp; budget</div>
        <div class="row g-3">
          <div class="col-sm-6 fld"><label>Source from</label>
            <input class="inp" name="source_country" value="{{ old('source_country') }}" placeholder="China, Turkey, anywhere…">
          </div>
          <div class="col-sm-6 fld"><label>Deliver to</label>
            <input class="inp" name="deliver_to" value="{{ old('deliver_to') }}" placeholder="City, country">
          </div>
          <div class="col-sm-6 fld"><label>Total budget</label>
            <input class="inp" name="budget" value="{{ old('budget') }}" placeholder="Optional — helps us aim right">
          </div>
          <div class="col-sm-6 fld"><label>Needed by</label>
            <input class="inp" type="date" name="needed_by" value="{{ old('needed_by') }}" min="{{ now()->toDateString() }}">
          </div>
          <div class="col-12 fld"><label>Anything else</label>
            <textarea class="inp" name="notes" rows="4" placeholder="Certifications, branding, packaging, sample first…">{{ old('notes') }}</textarea>
          </div>
          <div class="col-12">
            <button type="submit" id="srBtn" class="btn-g" style="padding:13px 26px;font-size:15px">
              <i class="fa-solid fa-paper-plane" id="srIcon"></i> <span id="srLabel">Send request</span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <div>
      <div class="how reveal">
        <h3>How it works</h3>
        <ol>
          <li>
            <div><b>You send the list</b><span>Product, quantity and anything that matters — a photo link helps most.</span></div>
          </li>
          <li>
            <div><b>We find suppliers</b><span>We check factories and trading companies, compare price and quality.</span></div>
          </li>
          <li>
            <div><b>You get a quote</b><span>Unit price, shipping, lead time and payment terms in one message.</span></div>
          </li>
          <li>
            <div><b>We ship it</b><span>Once you approve, we handle production follow-up and delivery.</span></div>
          </li>
        </ol>
      </div>

      <div class="trust">
        @foreach([
        ['fa-box-open','Small or large','Sample quantities to full containers — ask either way.'],
        ['fa-magnifying-glass-dollar','No obligation','A quote costs you nothing. Say no and that is the end of it.'],
        ['fa-clock','Reply in 24–48 hours','Sourcing takes a little longer than a normal enquiry.'],
        ] as [$ic,$t,$d])
        <div class="tbox reveal"><i class="fa-solid {{ $ic }}"></i>
          <div><b>{{ $t }}</b><span>{{ $d }}</span></div>
        </div>
        @endforeach
      </div>
    </div>

  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script>
  (function() {
    const form = document.getElementById('srForm');
    if (!form) return;

    const list = document.getElementById('srItems');
    const add = document.getElementById('srAdd');
    const btn = document.getElementById('srBtn');
    const icon = document.getElementById('srIcon');
    const label = document.getElementById('srLabel');
    const idle = icon.className;
    const brand = getComputedStyle(document.documentElement).getPropertyValue('--p').trim() || '#4f46e5';

    // Indexes only ever grow; the server re-orders by position, so gaps are harmless.
    let nextIndex = 1;

    function renumber() {
      list.querySelectorAll('.prow').forEach((row, i) => {
        row.querySelector('.pnum').textContent = i + 1;
      });
    }

    add.addEventListener('click', function() {
      const first = list.querySelector('.prow');
      const row = first.cloneNode(true);

      row.querySelectorAll('input').forEach(input => {
        input.name = input.name.replace(/items\[\d+]/, 'items[' + nextIndex + ']');
        input.value = input.type === 'number' ? '1' : '';
        input.classList.remove('err');
        const note = input.nextElementSibling;
        if (note && note.classList.contains('ferr')) note.remove();
      });

      nextIndex++;
      list.appendChild(row);
      renumber();
      row.querySelector('input').focus();
    });

    list.addEventListener('click', function(e) {
      const del = e.target.closest('.pdel');
      if (!del) return;
      if (list.querySelectorAll('.prow').length === 1) return;
      del.closest('.prow').remove();
      renumber();
    });

    function busy(on) {
      btn.disabled = on;
      icon.className = on ? 'spin' : idle;
      label.textContent = on ? 'Sending…' : 'Send request';
    }

    function clearErrors() {
      form.querySelectorAll('.ferr').forEach(e => e.remove());
      form.querySelectorAll('.inp.err').forEach(e => e.classList.remove('err'));
    }

    // Laravel reports "items.0.name"; the input is named "items[0][name]".
    function fieldSelector(key) {
      const parts = key.split('.');
      return '[name="' + parts[0] + parts.slice(1).map(p => '[' + p + ']').join('') + '"]';
    }

    function showFieldErrors(errors) {
      let first = null;
      Object.entries(errors).forEach(([key, messages]) => {
        const input = form.querySelector(fieldSelector(key));
        if (!input) return;
        input.classList.add('err');
        const note = document.createElement('div');
        note.className = 'ferr';
        note.textContent = messages[0];
        input.insertAdjacentElement('afterend', note);
        if (!first) first = input;
      });
      if (first) first.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }

    function resetForm() {
      form.reset();
      list.querySelectorAll('.prow').forEach((row, i) => {
        if (i) row.remove();
      });
      renumber();
    }

    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      if (btn.disabled) return;

      clearErrors();
      busy(true);

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: new FormData(form),
        });

        let data = {};
        try {
          data = await res.json();
        } catch (_) {}

        if (res.ok) {
          resetForm();
          Swal.fire({
            icon: 'success',
            title: data.reference || 'Request sent',
            text: data.message || "We'll come back to you with a quote.",
            confirmButtonColor: brand,
          });
        } else if (res.status === 422) {
          showFieldErrors(data.errors || {});
          Swal.fire({
            icon: 'error',
            title: 'Please check the form',
            text: data.message || 'Some fields need your attention.',
            confirmButtonColor: brand,
          });
        } else {
          throw new Error(data.message || ('Request failed (' + res.status + ')'));
        }
      } catch (err) {
        Swal.fire({
          icon: 'error',
          title: "Couldn't send your request",
          text: err.message || 'Please check your connection and try again.',
          confirmButtonColor: brand,
        });
      } finally {
        busy(false);
      }
    });

    form.addEventListener('input', function(e) {
      if (!e.target.classList.contains('err')) return;
      e.target.classList.remove('err');
      const note = e.target.nextElementSibling;
      if (note && note.classList.contains('ferr')) note.remove();
    });
  })();
</script>
@endpush