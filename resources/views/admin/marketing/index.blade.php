@extends('layouts.admin')
@section('title', 'Marketing')
@section('breadcrumb', 'Automated content, feeds & email campaigns')

@section('content')
<div class="max-w-6xl space-y-6">

  @php
    $banner = null;
    if(request('planned')) $banner = ['ok','Weekly content plan regenerated.'];
    elseif(request('saved')) $banner = ['ok','Newsletter settings saved.'];
    elseif(request()->has('sent')) $banner = ['ok','Newsletter sent to '.request('sent').' subscriber(s).'];
    elseif(request()->has('tested')) $banner = request('tested') ? ['ok','Test email sent to your address.'] : ['err','Could not send test — check SMTP in Settings → Email.'];
  @endphp
  @if($banner)
    <div class="rounded-xl px-4 py-3 text-sm font-semibold flex items-center gap-2 {{ $banner[0]=='ok' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' }}">
      <i class="fa-solid {{ $banner[0]=='ok' ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i> {{ $banner[1] }}
    </div>
  @endif

  {{-- ===== Content plan ===== --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
      <div>
        <div class="font-bold text-gray-800 text-lg"><i class="fa-solid fa-calendar-week text-indigo-600"></i> Weekly content plan</div>
        <p class="text-sm text-gray-500 mt-0.5">Ready-to-post captions built from your real products. Copy, then post or share. Auto-refreshes every Monday.</p>
      </div>
      <form method="POST" action="{{ route('admin.marketing.plan') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Generating…'">
        @csrf
        <button class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl"><i class="fa-solid fa-rotate"></i> {{ $plan ? 'Regenerate' : 'Generate plan' }}</button>
      </form>
    </div>

    @if(!$plan)
      <div class="text-center py-8 text-gray-500 text-sm">No plan yet — click “Generate plan” to create this week’s posts.</div>
    @else
      <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($plan['items'] as $it)
          <div class="border border-gray-100 rounded-xl p-4 flex flex-col">
            <div class="flex items-center justify-between mb-2">
              <span class="text-[11px] font-bold uppercase tracking-wide text-indigo-600 bg-indigo-50 rounded-full px-2.5 py-0.5">{{ $it['label'] ?? $it['type'] }}</span>
              <span class="text-[11px] text-gray-400">{{ $it['day'] }} · {{ \Illuminate\Support\Carbon::parse($it['date'])->format('d M') }}</span>
            </div>
            @if(!empty($it['image']))<img src="{{ $it['image'] }}" alt="" class="w-full h-28 object-cover rounded-lg mb-2">@endif
            <textarea readonly class="w-full text-xs text-gray-700 bg-gray-50 border border-gray-100 rounded-lg p-2.5 resize-none leading-relaxed" rows="4">{{ $it['caption'] }}</textarea>
            <div class="text-[11px] text-indigo-500 mt-1.5 break-words">{{ implode(' ', $it['hashtags'] ?? []) }}</div>
            <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-gray-50">
              <button type="button" class="mk-copy flex-1 text-xs font-semibold bg-gray-100 hover:bg-gray-200 rounded-lg px-2 py-1.5" data-txt="{{ $it['caption'] }}&#10;&#10;{{ implode(' ', $it['hashtags'] ?? []) }}"><i class="fa-regular fa-copy"></i> Copy</button>
              <a href="{{ $it['share']['facebook'] }}" target="_blank" rel="noopener" class="w-8 h-8 grid place-items-center rounded-lg text-white" style="background:#1877f2"><i class="fa-brands fa-facebook-f text-xs"></i></a>
              <a href="{{ $it['share']['whatsapp'] }}" target="_blank" rel="noopener" class="w-8 h-8 grid place-items-center rounded-lg text-white" style="background:#25d366"><i class="fa-brands fa-whatsapp text-xs"></i></a>
              <a href="{{ $it['share']['x'] }}" target="_blank" rel="noopener" class="w-8 h-8 grid place-items-center rounded-lg text-white" style="background:#000"><i class="fa-brands fa-x-twitter text-xs"></i></a>
            </div>
          </div>
        @endforeach
      </div>
      <p class="text-xs text-gray-400 mt-3">Plan for week of {{ $plan['week_of'] ?? '' }}.</p>
    @endif
  </div>

  {{-- ===== Product feeds ===== --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="font-bold text-gray-800 text-lg mb-1"><i class="fa-solid fa-rss text-amber-500"></i> Product feeds</div>
    <p class="text-sm text-gray-500 mb-4">Connect these once — Google &amp; Meta then auto-sync your whole catalog for Shopping and Facebook/Instagram Shops.</p>
    @foreach([['Google Merchant Center', $feeds['google'], 'Add as a scheduled feed in Merchant Center → Products → Feeds.'],['Meta Commerce (FB/Instagram)', $feeds['meta'], 'Add as a Data Feed in Commerce Manager → Catalog → Data sources.']] as $f)
      <div class="border border-gray-100 rounded-lg p-3 mb-2">
        <div class="font-semibold text-gray-800 text-sm">{{ $f[0] }}</div>
        <div class="flex items-center gap-2 mt-1.5">
          <input readonly value="{{ $f[1] }}" class="flex-1 text-xs bg-gray-50 border border-gray-100 rounded-lg px-2.5 py-1.5 text-gray-600">
          <button type="button" class="mk-copy text-xs font-semibold bg-gray-100 hover:bg-gray-200 rounded-lg px-3 py-1.5" data-txt="{{ $f[1] }}"><i class="fa-regular fa-copy"></i> Copy</button>
        </div>
        <div class="text-[11px] text-gray-400 mt-1">{{ $f[2] }}</div>
      </div>
    @endforeach
  </div>


  {{-- Product feeds — benefit & how-to (English) --}}
  <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 text-sm text-amber-900">
    <div class="font-bold text-base mb-1"><i class="fa-solid fa-circle-question"></i> What this is &amp; how to use it</div>
    <p class="mb-3">A <b>product feed</b> is a file that lists every product in your store — title, price, image, link and stock — in the exact format Google and Meta understand. Connect a feed URL <b>once</b> and these platforms automatically pull your whole catalog and keep it in sync. New products, price changes and stock updates sync on their own — no manual re-uploading, ever.</p>

    <div class="font-semibold mb-1">Why it helps your store</div>
    <ul class="list-disc list-inside mb-4 space-y-0.5 text-amber-800">
      <li>Your products appear in <b>Google Shopping</b> — both free listings and Shopping ads.</li>
      <li>Your products become shoppable on <b>Facebook &amp; Instagram Shops</b> — tag them in posts, reels and ads.</li>
      <li>More visibility and buyer-ready traffic: people searching for what you sell can find and buy it.</li>
    </ul>

    <div class="grid md:grid-cols-2 gap-5">
      <div>
        <div class="font-semibold mb-1.5"><i class="fa-brands fa-google"></i> Google Merchant Center (Shopping)</div>
        <ol class="list-decimal list-inside space-y-1 text-amber-800">
          <li>Create a free account at <b>merchants.google.com</b> and verify your website (zelarillc.com).</li>
          <li>Open <b>Products → Feeds</b> and click <b>“+”</b> to add a feed.</li>
          <li>Choose <b>Scheduled fetch</b>, then paste the <b>Google Merchant Center</b> URL shown above.</li>
          <li>Set the fetch frequency to <b>Daily</b> and save. Google pulls your catalog automatically from then on.</li>
        </ol>
      </div>
      <div>
        <div class="font-semibold mb-1.5"><i class="fa-brands fa-meta"></i> Meta Commerce (Facebook &amp; Instagram)</div>
        <ol class="list-decimal list-inside space-y-1 text-amber-800">
          <li>Open <b>Commerce Manager</b> (business.facebook.com) and create a <b>Catalog</b>.</li>
          <li>Go to <b>Catalog → Data sources → Add items → Data feed</b>.</li>
          <li>Choose <b>Scheduled feed</b>, then paste the <b>Meta Commerce</b> URL shown above.</li>
          <li>Set it to <b>Daily</b>, save, then connect the catalog to your Facebook Page / Instagram shopping.</li>
        </ol>
      </div>
    </div>

    <p class="text-[12px] text-amber-700 mt-4 pt-3 border-t border-amber-100"><i class="fa-solid fa-circle-info"></i> Both platforms review your store once before products go live. After approval, everything stays in sync automatically — you never touch the feed again.</p>
  </div>

  {{-- ===== Email campaign ===== --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="font-bold text-gray-800 text-lg mb-1"><i class="fa-solid fa-envelope-open-text text-emerald-600"></i> Email newsletter</div>
    <p class="text-sm text-gray-500 mb-4">Automated deals &amp; new-arrivals email sent from your own SMTP (Settings → Email). Subscribers join via the store footer.</p>

    <div class="grid sm:grid-cols-3 gap-4 mb-5">
      <div class="bg-gray-50 rounded-lg p-3 text-center"><div class="text-2xl font-extrabold text-gray-800">{{ $nl['count'] }}</div><div class="text-xs text-gray-500">subscribers</div></div>
      <div class="bg-gray-50 rounded-lg p-3 text-center"><div class="text-2xl font-extrabold text-gray-800 capitalize">{{ $nl['frequency'] }}</div><div class="text-xs text-gray-500">frequency</div></div>
      <div class="bg-gray-50 rounded-lg p-3 text-center"><div class="text-sm font-bold text-gray-800 mt-1.5">{{ $nl['last_sent'] ? \Illuminate\Support\Carbon::parse($nl['last_sent'])->diffForHumans() : 'Never' }}</div><div class="text-xs text-gray-500">last sent</div></div>
    </div>

    <form method="POST" action="{{ route('admin.marketing.email') }}" class="flex items-end gap-4 flex-wrap mb-4">
      @csrf
      <input type="hidden" name="enabled" value="0">
      <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="checkbox" name="enabled" value="1" class="w-4 h-4 accent-emerald-600" {{ $nl['enabled'] ? 'checked' : '' }}> Enable automatic sending
      </label>
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Frequency</label>
        <select name="frequency" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
          @foreach(['weekly'=>'Weekly','biweekly'=>'Every 2 weeks','monthly'=>'Monthly'] as $v=>$l)
            <option value="{{ $v }}" {{ $nl['frequency']==$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <button class="bg-gray-800 hover:bg-gray-900 text-white font-semibold text-sm px-4 py-2 rounded-xl">Save</button>
    </form>

    <div class="flex items-center gap-2 flex-wrap pt-4 border-t border-gray-50">
      <form method="POST" action="{{ route('admin.marketing.email.send') }}" onsubmit="return confirm('Send the newsletter to all {{ $nl['count'] }} subscribers now?')">
        @csrf
        <button class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl" {{ $nl['count']==0?'disabled style=opacity:.5':'' }}><i class="fa-solid fa-paper-plane"></i> Send now</button>
      </form>
      <form method="POST" action="{{ route('admin.marketing.email.test') }}">
        @csrf
        <button class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-emerald-500 text-gray-700 font-semibold text-sm px-4 py-2.5 rounded-xl"><i class="fa-solid fa-flask"></i> Send test to me</button>
      </form>
    </div>
  </div>

  {{-- ===== Share ===== --}}
  <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 text-sm text-indigo-900">
    <div class="font-bold mb-1"><i class="fa-solid fa-share-nodes"></i> Social sharing is live</div>
    <p>Share buttons (Facebook, WhatsApp, X, Pinterest, copy-link) are on every product page, and your pages carry rich Open Graph previews so shared links look great. The footer newsletter sign-up is collecting subscribers automatically.</p>
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-sm text-gray-600">
    <div class="font-bold text-gray-800 mb-1"><i class="fa-solid fa-robot"></i> Automation</div>
    Once the daily cron is active, the content plan refreshes weekly and the newsletter sends on your chosen schedule — no manual work. Posting to social accounts stays manual (one-click share) unless you later connect paid platform APIs.
  </div>
</div>

<script>
document.querySelectorAll('.mk-copy').forEach(function(b){
  b.addEventListener('click', function(){
    var t = b.dataset.txt.replace(/&#10;/g, "\n");
    navigator.clipboard.writeText(t).then(function(){ var o=b.innerHTML; b.innerHTML='<i class="fa-solid fa-check"></i> Copied'; setTimeout(function(){b.innerHTML=o;},1300); });
  });
});
</script>
@endsection
