@extends('layouts.admin')

@section('title', 'Messages')
@section('breadcrumb', 'What people send through the contact form')

@section('content')

<div class="page-header">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Contact Messages</h2>
        @php $adminEmail = trim((string) \App\Models\Setting::get('mail_admin_address')); @endphp
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $messages->total() }} {{ Str::plural('message', $messages->total()) }} ·
            @if($adminEmail)
                a copy of each one is emailed to <span class="font-medium text-gray-700">{{ $adminEmail }}</span>
            @else
                <a href="{{ route('admin.settings.index') }}" class="text-indigo-600 font-medium hover:underline">set an admin notification email</a> to get a copy by email
            @endif
        </p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @foreach([
        ['Unread',  $stats['new'],     'amber',  'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ['Read',    $stats['read'],    'blue',   'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
        ['Replied', $stats['replied'], 'green',  'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
        ['Total',   $stats['total'],   'indigo', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    ] as [$label, $value, $tone, $icon])
    <div class="bg-white rounded-2xl p-4 flex items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-{{ $tone }}-50 text-{{ $tone }}-600 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800 leading-none">{{ $value }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $label }}</p>
        </div>
    </div>
    @endforeach
</div>

<!-- Filters -->
<form method="GET" action="{{ route('admin.messages.index') }}"
      class="bg-white rounded-2xl px-5 py-4 mb-4 flex flex-wrap items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
    <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email or text…" class="form-input pl-9 w-full text-sm">
    </div>
    <select name="status" class="form-input text-sm w-40">
        <option value="">All Statuses</option>
        @foreach(['new' => 'Unread', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'] as $k => $v)
            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-primary text-sm px-4 py-2">Filter</button>
    @if(request()->hasAny(['search','status']))
        <a href="{{ route('admin.messages.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>
    @endif
</form>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($messages->count())
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 dark:text-gray-400">From</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 dark:text-gray-400">Subject</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 dark:text-gray-400 hidden lg:table-cell">Received</th>
                    <th class="text-center px-5 py-3.5 font-semibold text-gray-600 dark:text-gray-400">Status</th>
                    <th class="text-center px-5 py-3.5 font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">Email</th>
                    <th class="text-right px-5 py-3.5 font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($messages as $m)
                @php
                    $tone = [
                        'new'      => ['yellow', 'Unread'],
                        'read'     => ['blue',   'Read'],
                        'replied'  => ['green',  'Replied'],
                        'archived' => ['gray',   'Archived'],
                    ][$m->status] ?? ['gray', ucfirst($m->status)];
                @endphp
                <tr x-data="{ open: false }" class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors {{ $m->status === 'new' ? 'bg-amber-50/40' : '' }}">

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $m->initials }}</div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white truncate max-w-[12rem]">{{ $m->name }}</p>
                                <a href="mailto:{{ $m->email }}" class="text-xs text-gray-400 hover:text-indigo-600 truncate block max-w-[12rem]">{{ $m->email }}</a>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800 dark:text-gray-200 truncate max-w-xs">{{ $m->subject_line }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ Str::limit($m->message, 60) }}</p>
                    </td>

                    <td class="px-5 py-4 hidden lg:table-cell">
                        <span class="text-gray-600 dark:text-gray-400">{{ $m->created_at->format('d M Y') }}</span>
                        <span class="block text-xs text-gray-400">{{ $m->created_at->format('H:i') }}</span>
                    </td>

                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $tone[0] }}-100 dark:bg-{{ $tone[0] }}-900/30 text-{{ $tone[0] }}-700 dark:text-{{ $tone[0] }}-400">{{ $tone[1] }}</span>
                    </td>

                    <td class="px-5 py-4 text-center hidden md:table-cell">
                        @if($m->email_error)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400" title="{{ $m->email_error }}">Failed</span>
                        @elseif($m->emailed)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Sent</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="open = true"
                                    class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center text-emerald-600 transition"
                                    title="View">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <a href="mailto:{{ $m->email }}?subject={{ rawurlencode('Re: ' . $m->subject_line) }}"
                               class="w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 flex items-center justify-center text-indigo-600 transition"
                               title="Reply">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.messages.destroy', $m) }}"
                                  onsubmit="return confirm('Delete the message from &quot;{{ $m->name }}&quot;? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-500 hover:text-red-600 transition"
                                        title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>

                        {{-- Full message --}}
                        <template x-teleport="body">
                            <div x-show="open" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4"
                                 @keydown.escape.window="open = false">
                                <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>

                                <div x-show="open" x-transition.scale.origin.center.duration.150ms
                                     class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden text-left">

                                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $m->initials }}</div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $m->name }}</p>
                                            <a href="mailto:{{ $m->email }}" class="text-xs text-indigo-600 hover:underline break-all">{{ $m->email }}</a>
                                        </div>
                                        <button type="button" @click="open = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center text-gray-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <div class="px-6 py-5 max-h-[60vh] overflow-y-auto">
                                        <p class="text-xs text-gray-400">{{ $m->created_at->format('d M Y, H:i') }}</p>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $m->subject_line }}</h3>
                                        <p class="mt-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $m->message }}</p>

                                        @if($m->email_error)
                                        <p class="mt-4 text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                                            <span class="font-semibold">Email not delivered:</span> {{ $m->email_error }}
                                        </p>
                                        @endif

                                        <form method="POST" action="{{ route('admin.messages.note', $m) }}" class="mt-4 flex gap-2">
                                            @csrf
                                            <input type="text" name="admin_note" value="{{ $m->admin_note }}" placeholder="Internal note (staff only)…" class="form-input text-xs flex-1">
                                            <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Save note</button>
                                        </form>
                                    </div>

                                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-2">
                                        @foreach(['read' => 'Mark read', 'replied' => 'Mark replied', 'archived' => 'Archive'] as $st => $label)
                                            @if($m->status !== $st)
                                            <form method="POST" action="{{ route('admin.messages.status', $m) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $st }}">
                                                <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">{{ $label }}</button>
                                            </form>
                                            @endif
                                        @endforeach
                                        <a href="mailto:{{ $m->email }}?subject={{ rawurlencode('Re: ' . $m->subject_line) }}"
                                           class="ml-auto px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white">Reply by email</a>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-6 py-16 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <p class="text-gray-500 font-medium mt-3">No messages yet</p>
        <p class="text-sm text-gray-400 mt-1">Anything sent through the contact form on the website lands here.</p>
    </div>
    @endif
</div>

@if($messages->hasPages())
    <div class="mt-5">{{ $messages->links() }}</div>
@endif

@endsection
