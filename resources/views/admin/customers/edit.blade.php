@extends('layouts.admin')
@section('title','Edit Customer')
@section('cust_open','true')

@section('content')
<div class="p-6 space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.customers.show', $customer) }}" class="p-2 rounded-xl bg-white hover:bg-gray-50 border border-gray-100 text-gray-400 hover:text-indigo-600 transition" style="box-shadow:0 1px 4px rgba(0,0,0,.06)">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Customer</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $customer->name }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('admin.customers.update', $customer) }}" id="customerForm">
        @method('PUT') @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Main info --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-2xl p-6 space-y-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h2 class="font-semibold text-gray-700 border-b border-gray-100 pb-3">Personal Info</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-input @error('name') border-red-400 @enderror">
                            @error('name')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-input @error('email') border-red-400 @enderror">
                            @error('email')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">City</label>
                            <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Country</label>
                            <input type="text" name="country" value="{{ old('country', $customer->country) }}" class="form-input">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="3" class="form-input resize-none">{{ old('address', $customer->address) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                {{-- Status --}}
                <div class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"
                     x-data="{ s: '{{ old('status', $customer->status) }}' }">
                    <h3 class="font-semibold text-gray-700">Status</h3>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                           :class="s==='active' ? 'border-emerald-400 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="status" value="active" form="customerForm" x-on:change="s='active'" {{ old('status',$customer->status)==='active' ? 'checked' : '' }} class="w-4 h-4">
                        <div><p class="text-sm font-semibold text-gray-700">Active</p><p class="text-xs text-gray-400">Can place orders</p></div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                           :class="s==='inactive' ? 'border-red-300 bg-red-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="status" value="inactive" form="customerForm" x-on:change="s='inactive'" {{ old('status',$customer->status)==='inactive' ? 'checked' : '' }} class="w-4 h-4">
                        <div><p class="text-sm font-semibold text-gray-700">Inactive</p><p class="text-xs text-gray-400">Account disabled</p></div>
                    </label>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="submit" class="btn-primary w-full text-center">Update Customer</button>
                    <a href="{{ route('admin.customers.show', $customer) }}" class="block text-center py-2.5 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Cancel</a>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
