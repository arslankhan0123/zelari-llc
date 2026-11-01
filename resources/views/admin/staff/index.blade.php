@extends('layouts.admin')
@section('title','Staff')
@section('content')
<div class="p-6 space-y-5 max-w-4xl">
    <div><h1 class="text-2xl font-bold text-gray-800">{{ $vid ? 'My Store Staff' : 'Vendor Staff' }}</h1>
        <p class="text-sm text-gray-400 mt-0.5">Team members who can manage {{ $vid ? 'your store' : "a vendor's store" }}. They sign in with the "Vendor Staff" role and only see their vendor's data.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    @can('staff.create')
    <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <h2 class="font-semibold text-gray-700 mb-3">Add staff member</h2>
        <form method="POST" action="{{ route('admin.staff.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            @csrf
            <input name="name" placeholder="Full name" class="form-input text-sm" required>
            <input type="email" name="email" placeholder="Email" class="form-input text-sm" required>
            <input type="text" name="password" placeholder="Temp password" class="form-input text-sm" required>
            @unless($vid)
            <select name="vendor_id" class="form-input text-sm" required>
                <option value="">Select vendor…</option>
                @foreach($vendors as $v)<option value="{{ $v->id }}">{{ $v->name }}</option>@endforeach
            </select>
            @endunless
            <button class="btn-primary text-sm {{ $vid ? '' : 'lg:col-span-4' }}">Add Staff</button>
        </form>
        <p class="text-[11px] text-gray-400 mt-2">What staff can do is controlled by the "Vendor Staff" role's permissions (set under Users &amp; Roles).</p>
    </div>
    @endcan

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($staff->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Name</th><th>Email</th>@unless($vid)<th>Vendor</th>@endunless<th>Added</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($staff as $u)
                <tr>
                    <td class="text-sm font-medium text-gray-800">{{ $u->name }}</td>
                    <td class="text-sm text-gray-500">{{ $u->email }}</td>
                    @unless($vid)<td class="text-sm text-gray-500">{{ optional($u->vendor)->name ?? '—' }}</td>@endunless
                    <td class="text-sm text-gray-400">{{ $u->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="flex justify-center">
                            @can('staff.delete')
                            <form method="POST" action="{{ route('admin.staff.destroy',$u) }}" onsubmit="return confirm('Remove this staff member?')">@csrf @method('DELETE')
                                <button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center" title="Remove"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else<p class="py-10 text-center text-sm text-gray-400">No staff yet.</p>@endif
    </div>
</div>
@endsection
