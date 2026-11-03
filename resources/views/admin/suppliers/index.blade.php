@extends('layouts.admin')
@section('title','Suppliers')
@section('content')
<div class="p-6 space-y-5 max-w-4xl">
    <div><h1 class="text-2xl font-bold text-gray-800">Suppliers</h1><p class="text-sm text-gray-400 mt-0.5">Who you restock from. Link a supplier on each product.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <h2 class="font-semibold text-gray-700 mb-3">Add supplier</h2>
        <form method="POST" action="{{ route('admin.suppliers.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            @csrf
            <input name="name" placeholder="Name" class="form-input text-sm" required>
            <input name="email" placeholder="Email" class="form-input text-sm">
            <input name="phone" placeholder="Phone" class="form-input text-sm">
            <button class="btn-primary text-sm">Add Supplier</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($suppliers->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Products</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($suppliers as $sup)
                <tr>
                    <td><form method="POST" action="{{ route('admin.suppliers.update',$sup) }}" id="sup{{ $sup->id }}" class="contents">@csrf @method('PUT')<input name="name" value="{{ $sup->name }}" class="form-input text-sm"></td>
                    <td><input name="email" value="{{ $sup->email }}" class="form-input text-sm"></td>
                    <td><input name="phone" value="{{ $sup->phone }}" class="form-input text-sm"></form></td>
                    <td class="text-gray-600 text-sm">{{ $sup->products_count }}</td>
                    <td>
                        <div class="flex items-center justify-center gap-1.5">
                            <button form="sup{{ $sup->id }}" class="w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 flex items-center justify-center" title="Save"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
                            <form method="POST" action="{{ route('admin.suppliers.destroy',$sup) }}" onsubmit="return confirm('Remove supplier?')">@csrf @method('DELETE')<button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center" title="Remove"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else<p class="py-10 text-center text-sm text-gray-400">No suppliers yet.</p>@endif
    </div>
</div>
@endsection
