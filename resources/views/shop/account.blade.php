@extends('layouts.store')
@section('title', 'My Account')

@push('styles')
<style>
.ac{padding:34px 0 60px;background:var(--surface)}
.ac-grid{display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start}
@media(max-width:992px){.ac-grid{grid-template-columns:1fr}}
.ac-side{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:22px;position:sticky;top:calc(var(--head-h) + 70px)}
@media(max-width:992px){.ac-side{position:static}}
.ac-who{display:flex;align-items:center;gap:14px;padding-bottom:18px;border-bottom:1px solid var(--line);margin-bottom:12px}
.ac-av{width:52px;height:52px;border-radius:16px;background:var(--brand-grad);color:#fff;font-weight:800;font-size:20px;display:grid;place-items:center;flex-shrink:0}
.ac-who b{display:block;font-size:16px;line-height:1.2}
.ac-who small{display:block;color:var(--muted);font-size:12.5px;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:170px}
.ac-nav a,.ac-nav button{display:flex;align-items:center;gap:12px;width:100%;padding:11px 12px;border-radius:12px;font-size:14px;font-weight:700;color:var(--ink-2);border:0;background:none;cursor:pointer;font-family:inherit;text-align:left;transition:.15s}
.ac-nav a i,.ac-nav button i{width:20px;text-align:center;color:var(--p)}
.ac-nav a:hover,.ac-nav button:hover{background:var(--surface)}
.ac-nav a.on{background:var(--p-soft);color:var(--p)}
.ac-nav .out{color:var(--sale);margin-top:6px;border-top:1px solid var(--line);border-radius:0 0 12px 12px;padding-top:14px}
.ac-nav .out i{color:var(--sale)}
.card-z{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:26px;margin-bottom:18px}
.card-z h3{font-size:17px;font-weight:800;margin:0 0 4px;display:flex;align-items:center;gap:10px}
.card-z h3 i{width:34px;height:34px;border-radius:10px;background:var(--brand-grad);color:#fff;display:grid;place-items:center;font-size:14px}
.card-z .sub{color:var(--muted);font-size:13.5px;margin:0 0 20px}
.ok-box{background:#e6f9f2;border:1px solid #bdeedb;color:#0d6a4f;border-radius:14px;padding:12px 16px;margin-bottom:18px;display:flex;gap:10px;align-items:center;font-size:14px}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px}
@media(max-width:560px){.stat-row{grid-template-columns:1fr}}
.stat{background:var(--surface);border-radius:14px;padding:14px 16px}
.stat small{display:block;color:var(--muted);font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em}
.stat b{display:block;font-size:22px;letter-spacing:-.5px;margin-top:2px}
.orders{width:100%;border-collapse:collapse}
.orders th{text-align:left;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--faint);padding:0 10px 10px;border-bottom:1px solid var(--line)}
.orders td{padding:14px 10px;border-bottom:1px solid var(--surface);font-size:14px;vertical-align:middle}
.orders tr:hover td{background:var(--surface)}
.onum{font-family:ui-monospace,Menlo,monospace;font-weight:800;color:var(--p)}
.pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:800}
.pill.g{background:#e6f9f2;color:#0d8f6a}.pill.y{background:#fff5e0;color:#b36b00}.pill.b{background:var(--p-soft);color:var(--p)}.pill.r{background:#fdecef;color:#b4124b}.pill.gr{background:#eef0f6;color:#6b7191}
.empty{text-align:center;padding:40px 10px;color:var(--muted)}
.empty i{font-size:40px;color:#c8cce3;display:block;margin-bottom:10px}
.pw-wrap{position:relative}
.pw-wrap .eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);border:0;background:none;color:var(--faint);cursor:pointer}
@media(max-width:700px){.orders thead{display:none}.orders td{display:block;padding:6px 10px;border:0}.orders tr{display:block;border-bottom:1px solid var(--line);padding:10px 0}}

/* My reviews */
.ac-count{margin-left:auto;font-size:11px;font-weight:800;background:var(--p-soft);color:var(--p);padding:2px 7px;border-radius:999px}
.rv-todo{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:13px 15px;margin-bottom:18px}
.rv-todo-h{margin:0 0 10px;font-size:12.5px;font-weight:800;color:var(--ink-2);display:flex;align-items:center;gap:8px}
.rv-todo-h i{color:var(--p)}
.rv-todo-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
@media(max-width:780px){.rv-todo-list{grid-template-columns:1fr}}
.rv-todo-item{display:flex;align-items:center;gap:11px;background:#fff;border:1px solid var(--line);border-radius:11px;padding:8px 11px;transition:.2s}
.rv-todo-item:hover{border-color:transparent;box-shadow:0 12px 24px -20px rgba(var(--shadow-rgb),.6)}
.rv-todo-item img{width:34px;height:34px;border-radius:9px;object-fit:cover;background:var(--surface);flex:none}
.rv-todo-item .nm{flex:1;min-width:0;font-size:13px;font-weight:700;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rv-todo-item .go{font-size:12px;font-weight:800;color:var(--p);white-space:nowrap;display:inline-flex;align-items:center;gap:5px}
.rv-mine{display:flex;gap:13px;padding:14px 0;border-bottom:1px solid var(--line)}
.rv-mine:last-child{border-bottom:0;padding-bottom:0}
.rv-mine-img{width:46px;height:46px;border-radius:12px;overflow:hidden;background:var(--surface);flex:none;display:grid;place-items:center}
.rv-mine-img img{width:100%;height:100%;object-fit:cover}
.rv-mine-body{flex:1;min-width:0}
.rv-mine-head{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.rv-mine-nm{font-size:13.5px;font-weight:800;color:var(--ink)}
.rv-mine-nm:hover{color:var(--p)}
.rv-mine-head .stars{font-size:11.5px;color:var(--star)}
.rv-mine-head .stars .off{color:var(--line-2)}
.rv-mine-date{font-size:11.5px;color:var(--faint)}
.rv-mine-title{margin:6px 0 0;font-size:13.5px;font-weight:700;color:var(--ink)}
.rv-mine-text{margin:3px 0 0;font-size:13.5px;color:var(--muted);line-height:1.55}
</style>
@endpush

@section('content')
@php
  $tab = session('tab', request('tab', 'orders'));
  $statusPill = ['pending'=>'y','processing'=>'b','shipped'=>'b','delivered'=>'g','cancelled'=>'gr'];
  $spent = \App\Models\Order::where('customer_email', $user->email)->where('status','!=','cancelled')->sum('total');
  $delivered = \App\Models\Order::where('customer_email', $user->email)->where('status','delivered')->count();
@endphp
<section class="ac">
  <div class="wrap">
    <div class="ac-grid">
      <aside class="ac-side reveal">
        <div class="ac-who">
          <span class="ac-av">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
          <div><b>{{ $user->name }}</b><small>{{ $user->email }}</small></div>
        </div>
        <nav class="ac-nav">
          <a href="{{ route('account.index', ['tab' => 'orders']) }}#orders" class="{{ $tab === 'orders' ? 'on' : '' }}"><i class="fa-solid fa-box"></i> My orders</a>
          <a href="{{ route('account.index', ['tab' => 'profile']) }}#profile" class="{{ $tab === 'profile' ? 'on' : '' }}"><i class="fa-regular fa-id-badge"></i> Profile details</a>
          <a href="{{ route('account.index', ['tab' => 'reviews']) }}#reviews" class="{{ $tab === 'reviews' ? 'on' : '' }}"><i class="fa-solid fa-star"></i> My reviews @if($myReviews->count())<span class="ac-count">{{ $myReviews->count() }}</span>@endif</a>
          <a href="{{ route('account.index', ['tab' => 'password']) }}#password" class="{{ $tab === 'password' ? 'on' : '' }}"><i class="fa-solid fa-key"></i> Password</a>
          <a href="{{ route('cart.index') }}"><i class="fa-solid fa-cart-shopping"></i> My cart</a>
          @if(method_exists($user, 'isAdmin') && $user->isAdmin())<a href="{{ route('dashboard') }}"><i class="fa-solid fa-user-gear"></i> Admin panel</a>@endif
          <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="out"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</button></form>
        </nav>
      </aside>

      <div>
        @if(session('account_success'))<div class="ok-box reveal"><i class="fa-solid fa-circle-check" style="font-size:18px"></i> {{ session('account_success') }}</div>@endif

        <div class="stat-row reveal">
          <div class="stat"><small>Orders</small><b>{{ $orders->total() }}</b></div>
          <div class="stat"><small>Delivered</small><b>{{ $delivered }}</b></div>
          <div class="stat"><small>Total spent</small><b>{{ zprice($spent) }}</b></div>
        </div>

        {{-- Orders --}}
        <div class="card-z reveal" id="orders">
          <h3><i class="fa-solid fa-box"></i> My orders</h3>
          <p class="sub">Every order placed with <b>{{ $user->email }}</b>. Click an order to see its details, download the invoice or check payment status.</p>
          @if($orders->count())
          <table class="orders">
            <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
            <tbody>
              @foreach($orders as $o)
              <tr>
                <td><a href="{{ route('order.confirmation', $o->order_number) }}" class="onum">#{{ $o->order_number }}</a></td>
                <td style="color:var(--muted)">{{ $o->created_at->format('d M Y') }}</td>
                <td>{{ $o->items_count }} {{ Str::plural('item', $o->items_count) }}</td>
                <td><b>{{ $o->money($o->total) }}</b></td>
                <td><span class="pill {{ $o->payment_status === 'paid' ? 'g' : ($o->payment_status === 'refunded' ? 'b' : 'y') }}">{{ $o->payment_label }}{{ $o->payment_status === 'paid' ? ' · Paid' : '' }}</span></td>
                <td><span class="pill {{ $statusPill[$o->status] ?? 'gr' }}">{{ ucfirst($o->status) }}</span></td>
                <td style="text-align:right;white-space:nowrap">
                  <a href="{{ route('order.confirmation', $o->order_number) }}" class="btn-brand-soft" style="padding:8px 12px;font-size:12.5px">View</a>
                  <a href="{{ route('order.invoice', $o->order_number) }}" target="_blank" rel="noopener" class="btn-brand-outline" style="padding:7px 12px;font-size:12.5px"><i class="fa-solid fa-file-pdf" style="color:var(--sale)"></i> PDF</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @if($orders->hasPages())<div style="margin-top:16px">{{ $orders->links('vendor.pagination.store') }}</div>@endif
          @else
          <div class="empty"><i class="fa-solid fa-bag-shopping"></i><b>No orders yet</b><br><span style="font-size:13.5px">When you place an order with this email it will show up here.</span><br><a href="{{ route('shop.index') }}" class="btn-brand" style="margin-top:16px">Start shopping</a></div>
          @endif
        </div>

        {{-- Reviews --}}
        <div class="card-z reveal" id="reviews">
          <h3><i class="fa-solid fa-star"></i> My reviews</h3>
          <p class="sub">Reviews you have written. New ones appear on the product page once our team approves them.</p>

          @if($toReview->count())
          <div class="rv-todo">
            <p class="rv-todo-h"><i class="fa-regular fa-pen-to-square"></i> Waiting for your review</p>
            <div class="rv-todo-list">
              @foreach($toReview as $p)
              <a class="rv-todo-item" href="{{ route('shop.show', $p->slug) }}#reviews">
                <img src="{{ $p->display_image ?? zcat_image(null, (int) $p->id, 120) }}" alt="" loading="lazy">
                <span class="nm">{{ $p->name }}</span>
                <span class="go">Rate <i class="fa-solid fa-arrow-right"></i></span>
              </a>
              @endforeach
            </div>
          </div>
          @endif

          @forelse($myReviews as $r)
          <div class="rv-mine">
            @if($r->product)
              <a href="{{ route('shop.show', $r->product->slug) }}#reviews" class="rv-mine-img">
                <img src="{{ $r->product->display_image ?? zcat_image(null, (int) $r->product_id, 120) }}" alt="" loading="lazy">
              </a>
            @else
              <span class="rv-mine-img"><i class="fa-solid fa-box" style="color:var(--faint)"></i></span>
            @endif

            <div class="rv-mine-body">
              <div class="rv-mine-head">
                @if($r->product)
                  <a href="{{ route('shop.show', $r->product->slug) }}#reviews" class="rv-mine-nm">{{ $r->product->name }}</a>
                @else
                  <span class="rv-mine-nm">Product removed</span>
                @endif
                <span class="stars">@for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star {{ $i > $r->rating ? 'off' : '' }}"></i>@endfor</span>
                <span class="pill {{ ['pending' => 'y', 'approved' => 'g', 'rejected' => 'gr'][$r->status] }}">
                  {{ ['pending' => 'Awaiting approval', 'approved' => 'Published', 'rejected' => 'Not published'][$r->status] }}
                </span>
                <span class="rv-mine-date">{{ $r->created_at->format('d M Y') }}</span>
              </div>
              @if($r->title)<p class="rv-mine-title">{{ $r->title }}</p>@endif
              <p class="rv-mine-text">{{ $r->body }}</p>
            </div>
          </div>
          @empty
            @unless($toReview->count())
            <div class="empty"><i class="fa-regular fa-star"></i><b>No reviews yet</b><br><span style="font-size:13.5px">Once an order is placed you can rate what you bought — it only takes a minute.</span><br><a href="{{ route('shop.index') }}" class="btn-brand" style="margin-top:16px">Browse products</a></div>
            @endunless
          @endforelse
        </div>

        {{-- Profile --}}
        <div class="card-z reveal" id="profile">
          <h3><i class="fa-regular fa-id-badge"></i> Profile details</h3>
          <p class="sub">Your name and the email we send order updates to.</p>
          <form method="POST" action="{{ route('account.profile') }}">
            @csrf @method('PUT')
            <div class="row g-3">
              <div class="col-sm-6 fld"><label>Full name <b>*</b></label><input class="inp {{ $errors->has('name')?'err':'' }}" name="name" value="{{ old('name', $user->name) }}" required>@error('name')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-sm-6 fld"><label>Email <b>*</b></label><input class="inp {{ $errors->has('email')?'err':'' }}" type="email" name="email" value="{{ old('email', $user->email) }}" required>@error('email')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-12"><button type="submit" class="btn-brand"><i class="fa-solid fa-check"></i> Save changes</button></div>
            </div>
          </form>
        </div>

        {{-- Password --}}
        <div class="card-z reveal" id="password">
          <h3><i class="fa-solid fa-key"></i> Change password</h3>
          <p class="sub">Use at least 8 characters. You'll stay logged in on this device.</p>
          <form method="POST" action="{{ route('account.password') }}">
            @csrf @method('PUT')
            <div class="row g-3">
              <div class="col-sm-12 fld"><label>Current password <b>*</b></label><div class="pw-wrap"><input class="inp {{ $errors->has('current_password')?'err':'' }}" type="password" name="current_password" required autocomplete="current-password"><button type="button" class="eye" onclick="const i=this.previousElementSibling;i.type=i.type==='password'?'text':'password'"><i class="fa-regular fa-eye"></i></button></div>@error('current_password')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-sm-6 fld"><label>New password <b>*</b></label><div class="pw-wrap"><input class="inp {{ $errors->has('password')?'err':'' }}" type="password" name="password" required minlength="8" autocomplete="new-password"><button type="button" class="eye" onclick="const i=this.previousElementSibling;i.type=i.type==='password'?'text':'password'"><i class="fa-regular fa-eye"></i></button></div>@error('password')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-sm-6 fld"><label>Confirm new password <b>*</b></label><input class="inp" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></div>
              <div class="col-12"><button type="submit" class="btn-brand"><i class="fa-solid fa-lock"></i> Update password</button></div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
