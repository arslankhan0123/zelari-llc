@if ($paginator->hasPages())
@once
<style>
.zpg{display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;margin:0;padding:0;list-style:none}
.zpg a,.zpg span{display:inline-flex;align-items:center;justify-content:center;min-width:40px;height:40px;padding:0 12px;border-radius:12px;border:1.5px solid var(--line-2,#e4e6f0);background:#fff;color:var(--ink,#111);font-weight:700;font-size:14px;line-height:1;text-decoration:none;transition:.18s}
.zpg a:hover{border-color:var(--brand,#1e1b4b);color:var(--brand,#1e1b4b);background:var(--brand-soft,#eef0fb)}
.zpg .is-active{background:var(--brand-grad,var(--brand,#1e1b4b));border-color:transparent;color:#fff;box-shadow:0 8px 18px -8px rgba(var(--shadow-rgb,30,27,75),.6)}
.zpg .is-disabled{color:var(--faint,#b3b8d0);border-color:var(--line,#eceef5);background:var(--surface,#f6f7fb);cursor:not-allowed}
.zpg .dots{border:0;background:none;color:var(--faint,#b3b8d0);min-width:28px;padding:0}
.zpg i{font-size:12px}
.zpg .lbl{font-weight:700;margin:0 0 0 6px}
.zpg .next .lbl{margin:0 6px 0 0}
@media(max-width:560px){.zpg .lbl{display:none}.zpg a,.zpg span{min-width:38px;height:38px;padding:0 10px}}
</style>
@endonce
<nav aria-label="Pagination">
  <ul class="zpg">
    @if ($paginator->onFirstPage())
      <li><span class="is-disabled prev" aria-disabled="true"><i class="fa-solid fa-chevron-left"></i><b class="lbl">Prev</b></span></li>
    @else
      <li><a class="prev" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><i class="fa-solid fa-chevron-left"></i><b class="lbl">Prev</b></a></li>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <li><span class="dots" aria-hidden="true">…</span></li>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <li><span class="is-active" aria-current="page">{{ $page }}</span></li>
          @else
            <li><a href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a></li>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <li><a class="next" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><b class="lbl">Next</b><i class="fa-solid fa-chevron-right"></i></a></li>
    @else
      <li><span class="is-disabled next" aria-disabled="true"><b class="lbl">Next</b><i class="fa-solid fa-chevron-right"></i></span></li>
    @endif
  </ul>
</nav>
@endif
