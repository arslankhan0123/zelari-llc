@php $r = $role ?? null; $granted = $granted ?? []; $sys = $r && $r->is_system; $old = old('permissions', $granted); @endphp
@if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm mb-4"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="bg-white rounded-2xl p-6 border border-gray-100 space-y-4 mb-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div>
      <label class="text-sm font-medium text-gray-600">Role name *</label>
      <input name="name" value="{{ old('name', $r->name ?? '') }}" class="form-input w-full mt-1 text-sm" {{ $sys ? 'readonly' : '' }} required>
      @if($sys)<p class="text-[11px] text-gray-400 mt-1">Built-in role name can't be changed.</p>@endif
    </div>
    <div>
      <label class="text-sm font-medium text-gray-600">Description</label>
      <input name="description" value="{{ old('description', $r->description ?? '') }}" class="form-input w-full mt-1 text-sm">
    </div>
  </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
  <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
    <div>
      <h3 class="font-bold text-gray-800">Permissions</h3>
      <p class="text-[11px] text-gray-400 mt-0.5">Tick what this role can do in each module</p>
    </div>
    <label class="flex items-center gap-2 text-sm font-medium text-indigo-600 cursor-pointer select-none">
      <input type="checkbox" onchange="permSelectAll(this)" class="rounded border-gray-300 text-indigo-600"> Select all
    </label>
  </div>

  <div class="divide-y divide-gray-50">
    @foreach($modules as $mod => $def)
    <div data-prow class="flex flex-col md:flex-row md:items-center gap-3 px-6 py-3.5 hover:bg-gray-50/70 transition">
      <div class="md:w-52 flex items-center justify-between md:justify-start gap-2">
        <span class="font-semibold text-gray-800 text-sm">{{ $def['label'] }}</span>
        <button type="button" onclick="permRowAll(this)" class="text-[10px] font-semibold text-gray-400 hover:text-indigo-600 md:ml-1">all</button>
      </div>
      <div class="flex flex-wrap gap-2">
        @foreach($def['actions'] as $act)
        @php $perm = "$mod.$act"; @endphp
        <label class="relative cursor-pointer">
          <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="peer sr-only perm-cb" {{ in_array($perm, $old) ? 'checked' : '' }}>
          <span class="inline-flex items-center px-3.5 py-1.5 rounded-lg border border-gray-200 text-[13px] font-medium text-gray-500 bg-white
                       peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-hover:border-indigo-300 transition">{{ ucfirst($act) }}</span>
        </label>
        @endforeach
      </div>
    </div>
    @endforeach
  </div>
</div>

<script>
function permRowAll(btn){
  const row = btn.closest('[data-prow]');
  const boxes = row.querySelectorAll('.perm-cb');
  const allOn = [...boxes].every(b=>b.checked);
  boxes.forEach(b=>b.checked=!allOn);
}
function permSelectAll(cb){ document.querySelectorAll('.perm-cb').forEach(b=>b.checked=cb.checked); }
</script>
