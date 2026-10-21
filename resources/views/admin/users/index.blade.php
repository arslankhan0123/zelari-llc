@extends('layouts.admin')
@section('title', 'Users & Roles')

@section('content')
<div class="p-6 space-y-5" x-data="{ create:false, edit:null }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Users &amp; Roles</h1>
            <p class="text-sm text-gray-400 mt-0.5">Admin panel accounts. Passwords set here are stored encrypted and can be revealed by a SuperAdmin.</p>
        </div>
        <button @click="create=true" class="btn-primary flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> New account</button>
    </div>

    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm">{{ $errors->first() }}</div>@endif

    {{-- Roles legend --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($roles as $r)
        <div class="bg-white rounded-2xl p-4 flex items-start gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold {{ ['super_admin'=>'bg-brand-grad','admin'=>'bg-brand','user'=>'bg-gray-400'][$r->slug] ?? 'bg-gray-400' }}">{{ substr($r->name,0,1) }}</span>
            <div><p class="font-semibold text-gray-800 text-sm">{{ $r->name }} <span class="text-gray-400 font-normal">· {{ $r->users_count ?? $r->users()->count() }}</span></p><p class="text-xs text-gray-500">{{ $r->description }}</p></div>
        </div>
        @endforeach
    </div>

    <form method="GET" class="bg-white rounded-2xl px-5 py-4 flex flex-wrap items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="form-input flex-1 min-w-48 text-sm">
        <select name="role" class="form-input text-sm w-44"><option value="">All roles</option>@foreach($roles as $r)<option value="{{ $r->slug }}" {{ request('role')===$r->slug?'selected':'' }}>{{ $r->name }}</option>@endforeach</select>
        <button class="btn-primary text-sm px-4 py-2">Filter</button>
        @if(request()->hasAny(['search','role']))<a href="{{ route('admin.users.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>@endif
    </form>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="table-scroll">
        <table class="data-table">
            <thead><tr><th>User</th><th>Role</th><th>Password</th><th>Created</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
            @forelse($users as $u)
                <tr x-data="{show:false}">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold bg-brand">{{ strtoupper(substr($u->name,0,1)) }}</div>
                            <div><p class="text-sm font-semibold text-gray-800">{{ $u->name }} @if($u->id === auth()->id())<span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">you</span>@endif</p><p class="text-xs text-gray-400">{{ $u->email }}</p></div>
                        </div>
                    </td>
                    <td><span class="badge-{{ ['super_admin'=>'indigo','admin'=>'blue','user'=>'gray'][$u->role->slug ?? 'user'] ?? 'gray' }}">{{ $u->role_name }}</span></td>
                    <td>
                        @if($u->real_password)
                            <div class="flex items-center gap-2">
                                <code class="font-mono text-sm text-gray-800" x-text="show ? @js($u->real_password) : '••••••••'"></code>
                                <button type="button" @click="show=!show" class="text-gray-400 hover:text-gray-700" title="Reveal"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button type="button" @click="navigator.clipboard.writeText(@js($u->real_password))" class="text-gray-400 hover:text-gray-700" title="Copy"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                            </div>
                        @else
                            <span class="text-xs text-gray-400" title="Set by the user themselves — only a hash is stored. Use Edit → new password to set a known one.">Set by user</span>
                        @endif
                    </td>
                    <td class="text-sm text-gray-500">{{ $u->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="flex items-center justify-center gap-1.5">
                            <button @click="edit={{ $u->id }}" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center" title="Edit"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Delete {{ $u->email }}?')">@csrf @method('DELETE')
                                <button class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center" title="Delete"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                {{-- inline edit row --}}
                <tr x-show="edit==={{ $u->id }}" x-cloak>
                    <td colspan="5" class="bg-slate-50">
                        <form method="POST" action="{{ route('admin.users.update', $u) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end py-2">
                            @csrf @method('PUT')
                            <div><label class="form-label text-xs">Name</label><input name="name" value="{{ $u->name }}" required class="form-input text-sm"></div>
                            <div><label class="form-label text-xs">Email</label><input name="email" type="email" value="{{ $u->email }}" required class="form-input text-sm"></div>
                            <div><label class="form-label text-xs">Role</label><select name="role_id" class="form-input text-sm">@foreach($roles as $r)<option value="{{ $r->id }}" {{ $u->role_id===$r->id?'selected':'' }}>{{ $r->name }}</option>@endforeach</select></div>
                            <div><label class="form-label text-xs">New password <span class="text-gray-400">(blank = keep)</span></label><input name="password" type="text" minlength="8" placeholder="min 8 characters" class="form-input text-sm font-mono" autocomplete="new-password"></div>
                            <div class="flex gap-2"><button class="btn-primary text-sm px-4 py-2">Save</button><button type="button" @click="edit=null" class="text-sm text-gray-500 px-3">Cancel</button></div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-gray-400 py-10 text-sm">No users match.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        @if($users->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $users->links() }}</div>@endif
    </div>

    {{-- Create modal --}}
    <div x-show="create" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="create=false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg" style="box-shadow:0 30px 60px -20px rgba(0,0,0,.4)">
            <h3 class="text-lg font-bold text-gray-800 mb-4">New account</h3>
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div><label class="form-label">Name</label><input name="name" value="{{ old('name') }}" required class="form-input"></div>
                <div><label class="form-label">Email</label><input name="email" type="email" value="{{ old('email') }}" required class="form-input"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Password</label><input name="password" type="text" minlength="8" required placeholder="min 8 characters" class="form-input font-mono" autocomplete="new-password"></div>
                    <div><label class="form-label">Role</label><select name="role_id" class="form-input">@foreach($roles as $r)<option value="{{ $r->id }}" {{ $r->slug==='admin'?'selected':'' }}>{{ $r->name }}</option>@endforeach</select></div>
                </div>
                <p class="text-xs text-gray-400">The password is saved encrypted so you can reveal it later from this page. Share it with the person securely.</p>
                <div class="flex justify-end gap-2 pt-2"><button type="button" @click="create=false" class="px-4 py-2 text-sm text-gray-500">Cancel</button><button class="btn-primary text-sm px-5 py-2">Create</button></div>
            </form>
        </div>
    </div>
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
