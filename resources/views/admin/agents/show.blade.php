@extends('layouts.admin')

@section('title', 'Agent — ' . $agent->name)
@section('breadcrumb', 'Sales agent details, earnings and payouts')

@section('content')
@php $money = fn($v) => \App\Support\Currency::baseMoney($v); @endphp
<div class="max-w-6xl">

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <a href="{{ route('admin.agents.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700 inline-block mb-4"><i class="fa-solid fa-arrow-left mr-1"></i> All agents</a>

  {{-- Header --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
      <div class="text-lg font-bold text-gray-800">{{ $agent->name }}
        @if($agent->active)<span class="ml-2 align-middle inline-flex text-xs font-semibold text-green-700 bg-green-50 border border-green-100 rounded-full px-2.5 py-0.5">Active</span>
        @else<span class="ml-2 align-middle inline-flex text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-full px-2.5 py-0.5">Inactive</span>@endif
      </div>
      <div class="text-sm text-gray-500 mt-0.5">
        Referral code: <span class="font-mono bg-gray-100 rounded px-2 py-0.5">{{ $agent->code }}</span>
        · {{ rtrim(rtrim(number_format($agent->commission_rate,2),'0'),'.') }}% commission
        @if($agent->email) · {{ $agent->email }}@endif
      </div>
    </div>
  </div>

  {{-- Earnings cards --}}
  <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @foreach([
      ['Attributed sales', $summary['sales'], 'text-gray-800'],
      ['Commission earned', $summary['earned'], 'text-gray-800'],
      ['Pending', $summary['pending'], 'text-amber-600'],
      ['Paid out', $summary['paid'], 'text-gray-800'],
      ['Balance owed', $summary['available'], 'text-emerald-600'],
    ] as [$label, $val, $cls])
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="text-lg font-extrabold {{ $cls }}">{{ $money($val) }}</div>
        <div class="text-xs font-semibold text-gray-500 mt-0.5">{{ $label }}</div>
      </div>
    @endforeach
  </div>
  <p class="text-xs text-gray-400 -mt-3 mb-6">Commission is counted as <b>earned</b> once an order is delivered; orders still in progress show under <b>pending</b>. Cancelled orders are excluded.</p>

  <div class="grid lg:grid-cols-2 gap-6">
    {{-- Record payout --}}
    @can('agents.edit')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <div class="font-bold text-gray-800 mb-1"><i class="fa-solid fa-money-bill-wave text-emerald-500 mr-1"></i> Record a payout</div>
      <p class="text-sm text-gray-500 mb-4">Available to pay: <b class="text-emerald-600">{{ $money($summary['available']) }}</b></p>
      <form method="POST" action="{{ route('admin.agents.payout', $agent) }}" class="grid gap-3">
        @csrf
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Amount</label>
          <input name="amount" type="number" step="0.01" min="1" max="{{ max(1,$summary['available']) }}" value="{{ $summary['available'] > 0 ? $summary['available'] : '' }}" {{ $summary['available'] < 1 ? 'disabled' : '' }} class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Method</label>
            <input name="method" maxlength="60" placeholder="Bank / JazzCash…" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Reference</label>
            <input name="reference" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Note</label>
          <input name="note" maxlength="1000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <button {{ $summary['available'] < 1 ? 'disabled' : '' }} class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Record payout</button>
        </div>
      </form>

      {{-- Payout history --}}
      <div class="mt-5 border-t border-gray-100 pt-4">
        <div class="text-xs font-bold text-gray-500 mb-2">PAYOUT HISTORY</div>
        @if($payouts->count())
          <div class="divide-y divide-gray-100">
            @foreach($payouts as $p)
            <div class="flex items-center justify-between py-2 text-sm">
              <div>
                <span class="font-semibold text-gray-800">{{ $money($p->amount) }}</span>
                <span class="text-gray-400 text-xs ml-2">{{ optional($p->paid_at)->format('d M Y') }}</span>
                @if($p->method)<span class="text-gray-400 text-xs ml-1">· {{ $p->method }}</span>@endif
              </div>
              @if($p->reference)<span class="text-[11px] text-gray-400">{{ $p->reference }}</span>@endif
            </div>
            @endforeach
          </div>
          <div class="mt-3">{{ $payouts->links() }}</div>
        @else
          <p class="text-sm text-gray-400">No payouts recorded yet.</p>
        @endif
      </div>
    </div>
    @endcan

    {{-- Edit agent --}}
    @can('agents.edit')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-pen text-indigo-500 mr-1"></i> Agent settings</div>
      <form method="POST" action="{{ route('admin.agents.update', $agent) }}" class="grid gap-3">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Name</label>
            <input name="name" value="{{ old('name',$agent->name) }}" required maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Code</label>
            <input name="code" value="{{ old('code',$agent->code) }}" required maxlength="40" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm uppercase">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Commission %</label>
            <input name="commission_rate" type="number" step="0.01" min="0" max="100" value="{{ old('commission_rate',$agent->commission_rate) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
              <input type="hidden" name="active" value="0">
              <input type="checkbox" name="active" value="1" {{ $agent->active ? 'checked' : '' }}> Active
            </label>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Email</label>
            <input name="email" type="email" value="{{ old('email',$agent->email) }}" maxlength="160" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Phone</label>
            <input name="phone" value="{{ old('phone',$agent->phone) }}" maxlength="40" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
        </div>
        <div class="border-t border-gray-100 pt-3 text-xs font-bold text-gray-500">PAYOUT DETAILS</div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Method</label>
            <select name="payout_method" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
              @php $pm = old('payout_method',$agent->payout_method); @endphp
              <option value="">— Select —</option>
              <option value="bank" {{ $pm==='bank'?'selected':'' }}>Bank transfer</option>
              <option value="jazzcash" {{ $pm==='jazzcash'?'selected':'' }}>JazzCash</option>
              <option value="easypaisa" {{ $pm==='easypaisa'?'selected':'' }}>Easypaisa</option>
              <option value="paypal" {{ $pm==='paypal'?'selected':'' }}>PayPal</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Bank / wallet</label>
            <input name="bank_name" value="{{ old('bank_name',$agent->bank_name) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Account holder</label>
            <input name="account_name" value="{{ old('account_name',$agent->account_name) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Account number</label>
            <input name="account_number" value="{{ old('account_number',$agent->account_number) }}" maxlength="60" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
        </div>
        <div>
          <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Save changes</button>
        </div>
      </form>
    </div>
    @endcan
  </div>

  {{-- Attributed orders --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">Attributed orders</div>
    @if($orders->count())
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="text-left font-bold px-5 py-3">Order</th>
            <th class="text-left font-bold px-5 py-3">Date</th>
            <th class="text-left font-bold px-5 py-3">Status</th>
            <th class="text-right font-bold px-5 py-3">Subtotal</th>
            <th class="text-right font-bold px-5 py-3">Commission</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($orders as $o)
          <tr>
            <td class="px-5 py-3 font-semibold text-gray-800">{{ $o->order_number }}</td>
            <td class="px-5 py-3 text-gray-500">{{ $o->created_at->format('d M Y') }}</td>
            <td class="px-5 py-3"><span class="text-xs font-semibold capitalize text-gray-600 bg-gray-50 border border-gray-200 rounded-full px-2.5 py-1">{{ $o->status }}</span></td>
            <td class="px-5 py-3 text-right text-gray-600">{{ $money($o->subtotal) }}</td>
            <td class="px-5 py-3 text-right font-semibold {{ $o->status==='delivered' ? 'text-emerald-600' : ($o->status==='cancelled' ? 'text-gray-300 line-through' : 'text-amber-600') }}">{{ $money($o->agent_commission) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="px-5 py-4">{{ $orders->links() }}</div>
    @else
    <div class="px-5 py-12 text-center text-gray-400">
      <i class="fa-solid fa-receipt text-3xl mb-2"></i>
      <p class="text-sm">No orders have used this agent's code yet.</p>
    </div>
    @endif
  </div>
</div>
@endsection
