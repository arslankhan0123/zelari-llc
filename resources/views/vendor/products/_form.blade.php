@php $inp='w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 outline-none'; $p=$product ?? null; @endphp
@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm mb-4"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
  <div class="lg:col-span-2 space-y-4 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <div>
      <label class="text-sm font-medium text-gray-600">Product name *</label>
      <input name="name" value="{{ old('name',$p->name ?? '') }}" class="{{ $inp }} mt-1" required>
    </div>
    <div>
      <label class="text-sm font-medium text-gray-600">Short description</label>
      <input name="short_description" value="{{ old('short_description',$p->short_description ?? '') }}" class="{{ $inp }} mt-1" maxlength="500">
    </div>
    <div>
      <label class="text-sm font-medium text-gray-600">Full description</label>
      <textarea name="description" rows="6" class="{{ $inp }} mt-1">{{ old('description',$p->description ?? '') }}</textarea>
    </div>
  </div>
  <div class="space-y-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
      <div>
        <label class="text-sm font-medium text-gray-600">Category</label>
        <select name="category_id" class="{{ $inp }} mt-1">
          <option value="">— None —</option>
          @foreach($categories as $c)<option value="{{ $c->id }}" {{ (string)old('category_id',$p->category_id ?? '')===(string)$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
        </select>
      </div>
      <div><label class="text-sm font-medium text-gray-600">SKU</label><input name="sku" value="{{ old('sku',$p->sku ?? '') }}" class="{{ $inp }} mt-1"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm font-medium text-gray-600">Price *</label><input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price',$p->purchase_price ?? '') }}" class="{{ $inp }} mt-1" required></div>
        <div><label class="text-sm font-medium text-gray-600">Sale price</label><input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price',$p->sale_price ?? '') }}" class="{{ $inp }} mt-1"></div>
      </div>
      <div><label class="text-sm font-medium text-gray-600">Quantity *</label><input type="number" min="0" name="quantity" value="{{ old('quantity',$p->quantity ?? 0) }}" class="{{ $inp }} mt-1" required></div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <label class="text-sm font-medium text-gray-600">Feature image</label>
      @if($p && $p->feature_image)<img src="{{ asset('storage/'.$p->feature_image) }}" class="w-full h-32 object-cover rounded-xl mt-2 mb-2">@endif
      <input type="file" name="feature_image" accept="image/*" class="{{ $inp }} mt-1">
    </div>
  </div>
</div>
