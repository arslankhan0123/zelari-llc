@extends('layouts.admin')
@section('title', 'New Order')
@section('order_open','true')

@section('content')
<div class="p-6 space-y-5" x-data="orderForm()">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.orders.index') }}" class="p-2 rounded-xl bg-white hover:bg-gray-50 border border-gray-100 text-gray-400 hover:text-indigo-600 transition" style="box-shadow:0 1px 4px rgba(0,0,0,.06)">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">New Order</h1>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('admin.orders.store') }}" id="orderForm" @submit="prepareItems">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 space-y-5">
                {{-- Line items --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h2 class="font-semibold text-gray-700 mb-4">Order Items</h2>
                    <div class="space-y-3">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="grid grid-cols-12 gap-2 items-start">
                                <div class="col-span-5">
                                    <select x-model="item.product_id" @change="fillProduct(idx)"
                                            class="form-input w-full text-sm">
                                        <option value="">Select product…</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-sku="{{ $p->sku }}" data-price="{{ $p->sale_price }}">
                                            {{ $p->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <input type="text" x-model="item.product_name" placeholder="Name" class="form-input w-full text-sm">
                                </div>
                                <div class="col-span-2">
                                    <input type="number" x-model.number="item.unit_price" placeholder="Price" min="0" step="0.01" class="form-input w-full text-sm">
                                </div>
                                <div class="col-span-1">
                                    <input type="number" x-model.number="item.quantity" placeholder="Qty" min="1" class="form-input w-full text-sm">
                                </div>
                                <div class="col-span-1 flex items-center justify-center pt-1.5">
                                    <button type="button" @click="removeItem(idx)" class="text-red-400 hover:text-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addItem" class="mt-3 flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Add Item
                    </button>

                    {{-- hidden serialized items --}}
                    <input type="hidden" name="items_json" :value="JSON.stringify(items)">

                    {{-- Totals --}}
                    <div class="mt-5 pt-4 border-t border-gray-100 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>{{ config('currencies.base') }} <span x-text="subtotal.toLocaleString()">0</span></span></div>
                        <div class="flex justify-between items-center text-gray-500">
                            <span>Discount</span>
                            <input type="number" name="discount" x-model.number="discount" min="0" step="0.01" class="w-28 text-right form-input py-1 text-sm">
                        </div>
                        <div class="flex justify-between items-center text-gray-500">
                            <span>Shipping</span>
                            <input type="number" name="shipping_fee" x-model.number="shipping" min="0" step="0.01" class="w-28 text-right form-input py-1 text-sm">
                        </div>
                        <div class="flex justify-between font-bold text-gray-800 text-base pt-2 border-t border-gray-100">
                            <span>Total</span><span>{{ config('currencies.base') }} <span x-text="total.toLocaleString()">0</span></span>
                        </div>
                    </div>
                </div>

                {{-- Shipping info --}}
                <div class="bg-white rounded-2xl p-6 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h2 class="font-semibold text-gray-700">Shipping Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">Recipient Name</label>
                            <input type="text" name="shipping_name" value="{{ old('shipping_name') }}" class="form-input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">Phone</label>
                            <input type="text" name="shipping_phone" value="{{ old('shipping_phone') }}" class="form-input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">City</label>
                            <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" class="form-input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">Address</label>
                            <input type="text" name="shipping_address" value="{{ old('shipping_address') }}" class="form-input w-full">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Notes</label>
                        <textarea name="notes" rows="2" class="form-input w-full resize-none" placeholder="Order notes…">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                {{-- Customer --}}
                <div class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h3 class="font-semibold text-gray-700">Customer</h3>
                    <select name="customer_id" class="form-input w-full">
                        <option value="">Walk-in / Guest</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment --}}
                <div class="bg-white rounded-2xl p-5 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h3 class="font-semibold text-gray-700">Payment</h3>
                    <div x-data="{ ps: '{{ old('payment_status','unpaid') }}' }">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wide">Payment Status</label>
                        <div class="grid grid-cols-3 gap-1.5">
                            @foreach(['unpaid','paid','refunded'] as $val)
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_status" value="{{ $val }}" x-model="ps" class="sr-only">
                                <div :class="ps==='{{ $val }}' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500 hover:border-indigo-200'"
                                     class="border-2 rounded-lg py-1.5 text-xs font-medium text-center transition">{{ ucfirst($val) }}</div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wide">Method</label>
                        <select name="payment_method" class="form-input w-full text-sm">
                            <option value="cash_on_delivery">Cash on Delivery</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card (Stripe)</option>
                            <option value="paypal">PayPal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                {{-- Status --}}
                <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)" x-data="{ status: '{{ old('status','pending') }}' }">
                    <h3 class="font-semibold text-gray-700 mb-3">Order Status</h3>
                    <div class="space-y-2">
                        @foreach(['pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered'] as $val=>$label)
                        <label class="cursor-pointer flex items-center gap-2">
                            <input type="radio" name="status" value="{{ $val }}" x-model="status" class="sr-only">
                            <div :class="status==='{{ $val }}' ? 'bg-indigo-600' : 'bg-gray-200'"
                                 class="w-4 h-4 rounded-full transition shrink-0"></div>
                            <span :class="status==='{{ $val }}' ? 'text-indigo-700 font-semibold' : 'text-gray-600'" class="text-sm transition">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full">
                    Create Order
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
const productData = @json($productsJson);

function orderForm() {
    return {
        items: [{ product_id:'', product_name:'', product_sku:'', unit_price:0, quantity:1 }],
        discount: 0, shipping: 0,
        get subtotal() { return this.items.reduce((s,i) => s + (i.unit_price * i.quantity), 0); },
        get total()    { return Math.max(0, this.subtotal - this.discount + this.shipping); },
        addItem()    { this.items.push({ product_id:'', product_name:'', product_sku:'', unit_price:0, quantity:1 }); },
        removeItem(i){ if(this.items.length > 1) this.items.splice(i,1); },
        fillProduct(idx) {
            const id = this.items[idx].product_id;
            const p = productData.find(x => x.id == id);
            if(p){ this.items[idx].product_name = p.name; this.items[idx].product_sku = p.sku; this.items[idx].unit_price = p.price; }
        },
        prepareItems() { /* items_json already bound */ }
    }
}
</script>
@endpush
@endsection
