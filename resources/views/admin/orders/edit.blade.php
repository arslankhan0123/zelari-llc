@extends('layouts.admin')
@section('title', 'Update Order')
@section('order_open','true')

@section('content')
<div class="p-6 space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.orders.show', $order) }}" class="p-2 rounded-xl bg-white hover:bg-gray-50 border border-gray-100 text-gray-400 hover:text-indigo-600 transition" style="box-shadow:0 1px 4px rgba(0,0,0,.06)">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Update Order</h1>
            <p class="text-sm text-gray-400 font-mono mt-0.5">{{ $order->order_number }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('admin.orders.update', $order) }}" id="orderEditForm">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Main --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Order Status --}}
                <div class="bg-white rounded-2xl p-6 space-y-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h2 class="font-semibold text-gray-700 border-b border-gray-100 pb-3">Order Status</h2>

                    <div x-data="{ status: '{{ old('status', $order->status) }}' }">
                        <label class="block text-sm font-medium text-gray-600 mb-2">Status</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach(['pending'=>'⏳ Pending','processing'=>'🔄 Processing','shipped'=>'🚚 Shipped','delivered'=>'✅ Delivered','cancelled'=>'❌ Cancelled'] as $val=>$label)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="status" value="{{ $val }}" x-model="status" class="sr-only">
                                <div :class="status==='{{ $val }}' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-indigo-200'"
                                     class="border-2 rounded-xl px-3 py-2.5 text-sm font-medium text-center transition-all">{{ $label }}</div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-data="{ ps: '{{ old('payment_status', $order->payment_status) }}' }">
                        <label class="block text-sm font-medium text-gray-600 mb-2">Payment Status</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['unpaid'=>'Unpaid','paid'=>'Paid','refunded'=>'Refunded'] as $val=>$label)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="payment_status" value="{{ $val }}" x-model="ps" class="sr-only">
                                <div :class="ps==='{{ $val }}' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-indigo-200'"
                                     class="border-2 rounded-xl px-3 py-2.5 text-sm font-medium text-center transition-all">{{ $label }}</div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Notes</label>
                        <textarea name="notes" rows="4" class="form-input w-full resize-none" placeholder="Internal notes…">{{ old('notes', $order->notes) }}</textarea>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">

                {{-- Payment Method --}}
                <div class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h3 class="font-semibold text-gray-700">Payment Method</h3>
                    <select name="payment_method" class="form-input w-full">
                        @foreach(['cash_on_delivery'=>'Cash on Delivery','bank_transfer'=>'Bank Transfer','card'=>'Card (Stripe)','paypal'=>'PayPal','other'=>'Other'] as $val=>$label)
                        <option value="{{ $val }}" {{ old('payment_method',$order->payment_method)===$val?'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Order summary card --}}
                <div class="bg-white rounded-2xl p-5 space-y-2" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h3 class="font-semibold text-gray-700 mb-3">Order Summary</h3>
                    <div class="flex justify-between text-sm text-gray-500"><span>Items</span><span>{{ $order->items->count() }}</span></div>
                    <div class="flex justify-between text-sm text-gray-500"><span>Subtotal</span><span>{{ $order->money($order->subtotal) }}</span></div>
                    @if($order->discount > 0)<div class="flex justify-between text-sm text-emerald-600"><span>Discount</span><span>− {{ $order->money($order->discount) }}</span></div>@endif
                    @if($order->shipping_fee > 0)<div class="flex justify-between text-sm text-gray-500"><span>Shipping</span><span>{{ $order->money($order->shipping_fee) }}</span></div>@endif
                    <div class="flex justify-between font-bold text-gray-800 pt-2 border-t border-gray-100"><span>Total</span><span>{{ $order->money($order->total) }}</span></div>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="submit" class="btn-primary w-full text-center">Save Changes</button>
                    <a href="{{ route('admin.orders.show', $order) }}" class="block text-center py-2.5 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Cancel</a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
