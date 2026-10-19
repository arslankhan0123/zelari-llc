@extends('layouts.admin')
@section('title', 'Import / Export Products')
@section('prod_open', 'true')

@section('content')
    <div class="p-6 mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Import / Export</h1>
                <p class="text-sm text-gray-400 mt-0.5">Bulk manage your products via CSV</p>
            </div>
            <a href="{{ route('admin.products.index') }}"
                class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Products
            </a>
        </div>

        {{-- Flash success --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
                    @if(session('import_errors') && count(session('import_errors')))
                        <ul class="mt-1 space-y-0.5">
                            @foreach(session('import_errors') as $e)
                                <li class="text-xs text-amber-600">• {{ $e }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                <p class="text-sm font-medium text-red-700">{{ $errors->first() }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- ── EXPORT card ── --}}
            <div class="bg-white rounded-2xl p-6 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Export Products</h2>
                        <p class="text-xs text-gray-400">Download all products as CSV</p>
                    </div>
                </div>

                <p class="text-sm text-gray-500">
                    Exports every product with its category info, pricing, quantity and status.
                    Use this file as a reference or edit it and re-import.
                </p>

                <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 font-mono space-y-1">
                    <p class="font-semibold text-gray-600 mb-1">Columns in export:</p>
                    <p>name · sku · short_description</p>
                    <p>purchase_price · sale_price · quantity · status</p>
                    <p>parent_category · sub_category</p>
                </div>

                <a href="{{ route('admin.products.export') }}"
                    class="btn-primary w-full flex items-center justify-center gap-2 py-2.5 text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Products CSV
                </a>
            </div>

            {{-- ── SAMPLE TEMPLATE card ── --}}
            <div class="bg-white rounded-2xl p-6 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Sample Template</h2>
                        <p class="text-xs text-gray-400">Download a ready-to-fill CSV template</p>
                    </div>
                </div>

                <p class="text-sm text-gray-500">
                    Download a sample file with example rows showing the exact format needed.
                    Fill it in Excel / Google Sheets then import below.
                </p>

                <div class="bg-amber-50 rounded-xl p-3 text-xs text-amber-700 space-y-1">
                    <p class="font-semibold mb-1">Tips:</p>
                    <p>• Leave <code>sub_category</code> blank for top-level categories</p>
                    <p>• Leave <code>sale_price</code> blank if no discount</p>
                    <p>• <code>status</code>: active / inactive / draft</p>
                    <p>• Categories auto-created if they don't exist</p>
                </div>

                <a href="{{ route('admin.products.sample') }}"
                    class="w-full flex items-center justify-center gap-2 py-2.5 text-sm rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 transition font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Template
                </a>
            </div>
        </div>

        {{-- ── IMPORT card ── --}}
        <div class="bg-white rounded-2xl p-6 space-y-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4 4l4-4m0 0l4 4m-4-4V4" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Import Products</h2>
                    <p class="text-xs text-gray-400">Upload a CSV to bulk create or update products</p>
                </div>
            </div>

            {{-- How it works --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div
                        class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs mx-auto mb-1.5">
                        1</div>
                    <p class="text-xs font-medium text-gray-700">Auto-create categories</p>
                    <p class="text-xs text-gray-400 mt-0.5">Parent &amp; sub-categories created if missing, never duplicated
                    </p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div
                        class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs mx-auto mb-1.5">
                        2</div>
                    <p class="text-xs font-medium text-gray-700">Smart upsert</p>
                    <p class="text-xs text-gray-400 mt-0.5">Matched by SKU first, then name — updates existing, creates new
                    </p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div
                        class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs mx-auto mb-1.5">
                        3</div>
                    <p class="text-xs font-medium text-gray-700">Category linked</p>
                    <p class="text-xs text-gray-400 mt-0.5">Product's category_id automatically set to correct category</p>
                </div>
            </div>

            {{-- Upload form --}}
            <form method="POST" action="{{ route('admin.products.import.process') }}" enctype="multipart/form-data"
                x-data="{ fileName: '' }">
                @csrf

                <label class="block">
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-indigo-300 transition"
                        @click="$refs.fileInput.click()">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-600" x-text="fileName || 'Click to choose CSV file'"></p>
                        <p class="text-xs text-gray-400 mt-1">Max 5 MB · CSV format only</p>
                    </div>
                    <input type="file" name="csv_file" accept=".csv,.txt" class="hidden" x-ref="fileInput"
                        x-on:change="fileName = $event.target.files[0]?.name ?? ''">
                </label>

                <div class="flex items-center gap-3 mt-4">
                    <button type="submit" class="btn-primary flex items-center gap-2 px-5 py-2.5 text-sm"
                        x-bind:disabled="!fileName">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4 4l4-4m0 0l4 4m-4-4V4" />
                        </svg>
                        Import Now
                    </button>
                    <p class="text-xs text-gray-400" x-show="!fileName">Select a file first</p>
                </div>
            </form>
        </div>

    </div>
@endsection