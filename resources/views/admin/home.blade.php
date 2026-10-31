@extends('layouts.admin')
@section('title', 'Dashboard')
@section('breadcrumb', 'Choose a workspace')

@section('content')
<div class="max-w-5xl mx-auto">

  <div class="text-center mb-10 mt-4">
    <h1 class="text-3xl font-bold text-gray-800">Welcome back 👋</h1>
    <p class="text-gray-500 mt-2">Pick a workspace to manage.</p>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

    {{-- Ecommerce --}}
    <a href="{{ route('admin.dashboard') }}" class="group block bg-white rounded-2xl shadow-sm hover:shadow-lg transition p-7 border border-gray-100 relative overflow-hidden">
      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition" style="background:radial-gradient(circle at 80% 0%,rgba(99,102,241,.08),transparent 55%)"></div>
      <div class="relative">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white mb-4" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800">Ecommerce</h2>
        <p class="text-gray-500 mt-1.5 text-sm">Products, orders, customers, marketing, SEO &amp; store tools.</p>
        <span class="inline-flex items-center gap-1 text-indigo-600 font-semibold text-sm mt-4 group-hover:gap-2 transition-all">Open store panel
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </span>
      </div>
    </a>

    {{-- HRM --}}
    <a href="{{ route('admin.hrm.index') }}" class="group block bg-white rounded-2xl shadow-sm hover:shadow-lg transition p-7 border border-gray-100 relative overflow-hidden">
      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition" style="background:radial-gradient(circle at 80% 0%,rgba(236,72,153,.08),transparent 55%)"></div>
      <div class="relative">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white mb-4" style="background:linear-gradient(135deg,#ec4899,#f43f5e)">
          <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800">HRM <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 align-middle">SOON</span></h2>
        <p class="text-gray-500 mt-1.5 text-sm">Employees, attendance, payroll &amp; leave management.</p>
        <span class="inline-flex items-center gap-1 text-pink-600 font-semibold text-sm mt-4 group-hover:gap-2 transition-all">Open HR panel
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </span>
      </div>
    </a>

  </div>
</div>
@endsection
