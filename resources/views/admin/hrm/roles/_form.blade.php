@php $granted = $granted ?? []; @endphp
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="block text-xs font-bold text-gray-500 mb-1">Role name *</label><input name="name" value="{{ old('name', $role->name ?? '') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
    <div><label class="block text-xs font-bold text-gray-500 mb-1">Description</label><input name="description" value="{{ old('description', $role->description ?? '') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
  </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
  <div class="font-bold text-gray-800 mb-4">Permissions</div>
  <div class="grid md:grid-cols-2 gap-4">
    @foreach($modules as $mod => $cfg)
    <div class="border border-gray-100 rounded-lg p-3">
      <div class="font-semibold text-gray-700 text-sm mb-2">{{ $cfg['label'] }}</div>
      <div class="flex flex-wrap gap-3">
        @foreach($cfg['actions'] as $act)
        @php $perm = "hrm.$mod.$act"; @endphp
        <label class="flex items-center gap-1.5 text-xs text-gray-600">
          <input type="checkbox" name="permissions[]" value="{{ $perm }}" {{ in_array($perm,$granted) || collect(old('permissions',[]))->contains($perm) ? 'checked' : '' }}>
          {{ ucfirst($act) }}
        </label>
        @endforeach
      </div>
    </div>
    @endforeach
  </div>
</div>
