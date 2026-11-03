@extends('layouts.admin')

@section('title', 'Store Settings')
@section('breadcrumb', 'Your public storefront profile')

@section('content')
<div class="max-w-4xl">

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="flex items-center justify-between mb-5">
    <div>
      <h2 class="text-lg font-bold text-gray-800">Store settings</h2>
      <p class="text-sm text-gray-500">This is what customers see on your public storefront.</p>
    </div>
    <a href="{{ route('shop.vendor', $vendor->slug) }}" target="_blank" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 whitespace-nowrap">
      <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> View my store
    </a>
  </div>

  <form method="POST" action="{{ route('admin.store.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Branding --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
      <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-image text-indigo-500 mr-1"></i> Branding</div>
      <div class="grid md:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Logo</label>
          <div class="flex items-center gap-3">
            <img src="{{ $vendor->logo ? asset('storage/'.ltrim($vendor->logo,'/')) : asset('images/logo.png') }}" class="w-16 h-16 rounded-lg object-cover border border-gray-200 bg-gray-50">
            <input type="file" name="logo" accept="image/*" class="text-sm">
          </div>
          <p class="text-[11px] text-gray-400 mt-1">Square image, up to 2 MB.</p>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Banner</label>
          <div class="flex items-center gap-3">
            @if($vendor->banner)
              <img src="{{ asset('storage/'.ltrim($vendor->banner,'/')) }}" class="w-28 h-16 rounded-lg object-cover border border-gray-200 bg-gray-50">
            @else
              <div class="w-28 h-16 rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-300"><i class="fa-solid fa-panorama"></i></div>
            @endif
            <input type="file" name="banner" accept="image/*" class="text-sm">
          </div>
          <p class="text-[11px] text-gray-400 mt-1">Wide image, up to 4 MB.</p>
        </div>
      </div>
    </div>

    {{-- Profile --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
      <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-store text-indigo-500 mr-1"></i> Profile</div>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Store name <span class="text-red-500">*</span></label>
          <input name="name" value="{{ old('name', $vendor->name) }}" required maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Public email</label>
          <input name="email" type="email" value="{{ old('email', $vendor->email) }}" maxlength="160" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Phone</label>
          <input name="phone" value="{{ old('phone', $vendor->phone) }}" maxlength="40" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">City</label>
          <input name="city" value="{{ old('city', $vendor->city) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-gray-500 mb-1">Address</label>
          <input name="address" value="{{ old('address', $vendor->address) }}" maxlength="255" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-gray-500 mb-1">About your store</label>
          <textarea name="description" rows="4" maxlength="2000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">{{ old('description', $vendor->description) }}</textarea>
        </div>
      </div>
    </div>

    {{-- Policies --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
      <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-file-contract text-indigo-500 mr-1"></i> Policies</div>
      <div class="grid gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Return policy</label>
          <textarea name="return_policy" rows="3" maxlength="3000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="e.g. 7-day returns on unused items.">{{ old('return_policy', $vendor->return_policy) }}</textarea>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Shipping policy</label>
          <textarea name="shipping_policy" rows="3" maxlength="3000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="e.g. Orders ship within 2 business days.">{{ old('shipping_policy', $vendor->shipping_policy) }}</textarea>
        </div>
      </div>
    </div>

    {{-- Payout --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
      <div class="font-bold text-gray-800 mb-1"><i class="fa-solid fa-building-columns text-indigo-500 mr-1"></i> Payout details</div>
      <p class="text-sm text-gray-500 mb-4">Where you want your earnings paid out. Only visible to you and the store admin.</p>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Payout method</label>
          <select name="payout_method" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            @php $pm = old('payout_method', $vendor->payout_method); @endphp
            <option value="">— Select —</option>
            <option value="bank" {{ $pm==='bank'?'selected':'' }}>Bank transfer</option>
            <option value="jazzcash" {{ $pm==='jazzcash'?'selected':'' }}>JazzCash</option>
            <option value="easypaisa" {{ $pm==='easypaisa'?'selected':'' }}>Easypaisa</option>
            <option value="paypal" {{ $pm==='paypal'?'selected':'' }}>PayPal</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Bank / wallet name</label>
          <input name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Account holder name</label>
          <input name="account_name" value="{{ old('account_name', $vendor->account_name) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Account number / IBAN</label>
          <input name="account_number" value="{{ old('account_number', $vendor->account_number) }}" maxlength="60" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
      </div>
    </div>

    <div class="flex justify-end">
      <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg">Save store settings</button>
    </div>
  </form>
</div>
@endsection
