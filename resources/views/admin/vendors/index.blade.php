@extends('layouts.admin')
@section('title','Vendors')

@section('content')
<div class="p-6 space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Vendors</h1>
            <p class="text-sm text-gray-400 mt-0.5">Marketplace sellers — review, approve &amp; manage</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Stat tiles --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([['Total',$stats['total'],'#6366f1'],['Approved',$stats['approved'],'#16a34a'],['Pending',$stats['pending'],'#d97706'],['Suspended',$stats['suspended'],'#dc2626']] as [$l,$v,$c])
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <p class="text-sm text-gray-500">{{ $l }}</p>
            <p class="text-2xl font-bold mt-1" style="color:{{ $c }}">{{ $v }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.vendors.index') }}" class="bg-white rounded-2xl px-5 py-4 flex flex-wrap items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vendors…" class="form-input pl-9 w-full text-sm">
        </div>
        <select name="status" class="form-input text-sm w-44">
            <option value="">All Statuses</option>
            @foreach(['pending'=>'Pending','approved'=>'Approved','suspended'=>'Suspended','rejected'=>'Rejected'] as $k=>$v)
            <option value="{{ $k }}" {{ request('status')===$k ? 'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary text-sm px-4 py-2">Filter</button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('admin.vendors.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($vendors->count())
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Contact</th>
                        <th>Products</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendors as $v)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold flex-shrink-0">{{ strtoupper(substr($v->name,0,1)) }}</div>
                                <div>
                                    <a href="{{ route('admin.vendors.show',$v) }}" class="font-semibold text-gray-800 hover:text-indigo-600">{{ $v->name }}</a>
                                    <div class="text-[11px] text-gray-400 font-mono">/{{ $v->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-gray-500">{{ $v->email ?: '—' }}<div class="text-[11px] text-gray-400">{{ $v->phone }}</div></td>
                        <td class="text-gray-600">{{ $v->products_count }}</td>
                        <td class="text-gray-600">{{ rtrim(rtrim(number_format($v->commission_rate,2),'0'),'.') }}%</td>
                        <td><span class="badge-{{ $v->status_color }}">{{ ucfirst($v->status) }}</span></td>
                        <td class="text-sm text-gray-400">{{ $v->created_at->format('d M Y') }}</td>
                        <td>
                            <x-row-actions module="vendors" :view="route('admin.vendors.show',$v)">
                                @can('vendors.edit')
                                @if($v->status !== 'approved')
                                <form method="POST" action="{{ route('admin.vendors.approve',$v) }}">@csrf
                                    <button type="submit" title="Approve"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center text-emerald-600 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                @endif
                                @if($v->status === 'approved' && $v->slug !== 'zelari')
                                <form method="POST" action="{{ route('admin.vendors.suspend',$v) }}">@csrf
                                    <button type="submit" title="Suspend"
                                            class="w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 flex items-center justify-center text-amber-600 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </x-row-actions>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">{{ $vendors->links() }}</div>
        @else
        <div class="text-center text-gray-400 py-16 text-sm">No vendors yet. They'll appear here once sellers apply.</div>
        @endif
    </div>

</div>
@endsection
