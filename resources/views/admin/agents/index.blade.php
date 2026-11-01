@extends('layouts.admin')

@section('title', 'Sales Agents')
@section('breadcrumb', 'Salespeople who earn commission on referred orders')

@section('content')
@php $money = fn($v) => \App\Support\Currency::baseMoney($v); @endphp
<div class="max-w-6xl">

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- Stats --}}
  <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @foreach([
      ['Agents', $totals['agents'], 'indigo', false],
      ['Attributed sales', $totals['sales'], 'sky', true],
      ['Commission earned', $totals['earned'], 'emerald', true],
      ['Pending commission', $totals['pending'], 'amber', true],
      ['Paid out', $totals['paid'], 'gray', true],
    ] as [$label, $val, $c, $isMoney])
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="text-xl font-extrabold text-gray-800">{{ $isMoney ? $money($val) : number_format($val) }}</div>
        <div class="text-xs font-semibold text-gray-500 mt-0.5">{{ $label }}</div>
      </div>
    @endforeach
  </div>

  {{-- How it works --}}
  <div class="bg-indigo-50 border border-indigo-100 text-indigo-800 text-sm rounded-xl p-4 mb-6">
    <i class="fa-solid fa-circle-info mr-1"></i>
    Each agent gets a unique referral code. When a customer enters it at checkout, that order is credited to the agent and they earn
    <b>commission %</b> of the order subtotal. Pay them from each agent's page.
  </div>

  {{-- Create --}}
  @can('agents.create')
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-user-plus text-indigo-500 mr-1"></i> Add a sales agent</div>
    <form method="POST" action="{{ route('admin.agents.store') }}" class="grid md:grid-cols-4 gap-4">
      @csrf
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Name <span class="text-red-500">*</span></label>
        <input name="name" value="{{ old('name') }}" required maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Referral code</label>
        <input name="code" value="{{ old('code') }}" maxlength="40" placeholder="Auto if blank" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm uppercase">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Commission % <span class="text-red-500">*</span></label>
        <input name="commission_rate" type="number" step="0.01" min="0" max="100" value="{{ old('commission_rate', 10) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Email</label>
        <input name="email" type="email" value="{{ old('email') }}" maxlength="160" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Phone</label>
        <input name="phone" value="{{ old('phone') }}" maxlength="40" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="md:col-span-3 flex items-end">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Add agent</button>
      </div>
    </form>
  </div>
  @endcan

  {{-- List --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">All agents</div>
    @if($rows->count())
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="text-left font-bold px-5 py-3">Agent</th>
            <th class="text-left font-bold px-5 py-3">Code</th>
            <th class="text-right font-bold px-5 py-3">Rate</th>
            <th class="text-right font-bold px-5 py-3">Orders</th>
            <th class="text-right font-bold px-5 py-3">Sales</th>
            <th class="text-right font-bold px-5 py-3">Earned</th>
            <th class="text-right font-bold px-5 py-3">Balance</th>
            <th class="text-center font-bold px-5 py-3">Status</th>
            <th class="text-center font-bold px-5 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($rows as $r)
          @php $a = $r['agent']; $e = $r['e']; @endphp
          <tr>
            <td class="px-5 py-3">
              <a href="{{ route('admin.agents.show', $a) }}" class="font-bold text-gray-800 hover:text-indigo-600">{{ $a->name }}</a>
              @if($a->email)<div class="text-[11px] text-gray-400">{{ $a->email }}</div>@endif
            </td>
            <td class="px-5 py-3"><span class="font-mono text-xs bg-gray-100 rounded px-2 py-1">{{ $a->code }}</span></td>
            <td class="px-5 py-3 text-right text-gray-600">{{ rtrim(rtrim(number_format($a->commission_rate,2),'0'),'.') }}%</td>
            <td class="px-5 py-3 text-right text-gray-600">{{ $e['orders'] }}</td>
            <td class="px-5 py-3 text-right text-gray-600">{{ $money($e['sales']) }}</td>
            <td class="px-5 py-3 text-right text-gray-800 font-semibold">{{ $money($e['earned']) }}</td>
            <td class="px-5 py-3 text-right text-emerald-600 font-semibold">{{ $money($e['available']) }}</td>
            <td class="px-5 py-3 text-center">
              @if($a->active)
                <span class="inline-flex text-xs font-semibold text-green-700 bg-green-50 border border-green-100 rounded-full px-2.5 py-1">Active</span>
              @else
                <span class="inline-flex text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-full px-2.5 py-1">Off</span>
              @endif
            </td>
            <td class="px-5 py-3">
              <x-row-actions module="agents"
                :view="route('admin.agents.show', $a)"
                :delete="route('admin.agents.destroy', $a)"
                confirm="Delete agent {{ $a->name }}? Their past orders stay but lose the agent link." />
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="px-5 py-12 text-center text-gray-400">
      <i class="fa-solid fa-user-tie text-3xl mb-2"></i>
      <p class="text-sm">No sales agents yet. Add one above to start tracking referred sales.</p>
    </div>
    @endif
  </div>
</div>
@endsection
