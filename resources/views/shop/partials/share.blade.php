@php
  $shareUrl   = $shareUrl   ?? url()->current();
  $shareTitle = $shareTitle ?? (zstore('name','Zelari'));
  $shareImage = $shareImage ?? asset('images/logo.png');
  $u = urlencode($shareUrl); $t = urlencode($shareTitle); $im = urlencode($shareImage);
@endphp
<div class="zshare">
  <div class="zsh-lbl"><i class="fa-solid fa-share-nodes"></i> Share:</div>
  <div class="payrow sm" style="margin-top:7px">
    <a class="pm zsh" style="color:#1877f2" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}" title="Facebook" aria-label="Share on Facebook"><i class="fa-brands fa-facebook-f"></i></a>
    <a class="pm zsh" style="color:#25d366" target="_blank" rel="noopener" href="https://wa.me/?text={{ $t }}%20{{ $u }}" title="WhatsApp" aria-label="Share on WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
    <a class="pm zsh" style="color:#000" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $u }}&text={{ $t }}" title="X" aria-label="Share on X"><i class="fa-brands fa-x-twitter"></i></a>
    <a class="pm zsh" style="color:#e60023" target="_blank" rel="noopener" href="https://pinterest.com/pin/create/button/?url={{ $u }}&media={{ $im }}&description={{ $t }}" title="Pinterest" aria-label="Share on Pinterest"><i class="fa-brands fa-pinterest-p"></i></a>
    <button type="button" class="pm zsh cp" style="color:var(--p)" data-url="{{ $shareUrl }}" title="Copy link" aria-label="Copy link"><i class="fa-solid fa-link"></i></button>
  </div>
</div>
<style>
.zshare{display:inline-block}
.zsh-lbl{font-size:12px;font-weight:700;color:var(--faint);display:flex;align-items:center;gap:6px}
.payrow.sm .pm.zsh{cursor:pointer;font-size:13px;background:#fff;text-decoration:none;transition:transform .15s,border-color .15s,box-shadow .15s}
.payrow.sm .pm.zsh:hover{border-color:var(--p);transform:translateY(-1px);box-shadow:0 4px 10px -4px rgba(var(--shadow-rgb),.4)}
</style>
<script>
document.querySelectorAll('.pm.zsh.cp').forEach(function(b){b.addEventListener('click',function(){navigator.clipboard.writeText(b.dataset.url).then(function(){var o=b.innerHTML;b.innerHTML='<i class="fa-solid fa-check"></i>';setTimeout(function(){b.innerHTML=o;},1200);});});});
</script>
