<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Storefront chatbot — answers from the live store data:
 * products, prices, stock, categories, brands, shipping & store settings.
 */
class ChatbotController extends Controller
{
    public function message(Request $request)
    {
        $raw = mb_substr(trim((string) $request->input('message', '')), 0, 300);
        $msg = mb_strtolower($raw);

        if ($request->boolean('agent_mode')) {
            return $this->agentMessage($request, $raw);
        }

        if ($msg === '') {
            return response()->json($this->greeting());
        }

        $has = function (array $words) use ($msg): bool {
            foreach ($words as $w) {
                if ($w !== '' && str_contains($msg, $w)) return true;
            }
            return false;
        };

        // ── Greeting ──
        if (preg_match('/^(hi+|hello+|hey+|salam|aoa|assalam|aslam).{0,20}$/u', $msg)) {
            return response()->json($this->greeting());
        }

        // ── Delivery / shipping ──
        if ($has(['deliver', 'shipping', 'ship', 'courier', 'kitne din', 'pohanch', 'days to'])) {
            $free  = zfree_over();
            $reply = 'We deliver ' . zship('coverage', 'nationwide') . ' in <b>' . zship('delivery_days', '2–5') . ' days</b>.';
            $reply .= $free > 0
                ? ' Delivery is <b>FREE</b> on orders over <b>' . zprice($free) . '</b>.'
                : ' Delivery is <b>FREE</b> on every order!';
            return response()->json([
                'reply' => $reply,
                'chips' => ['Cash on delivery?', 'Return policy', "Today's deals"],
            ]);
        }

        // ── Payment / COD ──
        if ($has(['cod', 'cash on', 'payment', 'pay ', 'card', 'bank', 'jazzcash', 'easypaisa', 'paise kaise'])) {
            return response()->json([
                'reply' => 'We accept <b>Cash on Delivery (COD)</b> nationwide — pay at your doorstep. Card and bank payments are also supported at checkout.',
                'chips' => ['Delivery time?', 'Return policy', 'Browse categories'],
            ]);
        }

        // ── Returns / refund / warranty ──
        if ($has(['return', 'refund', 'exchange', 'warranty', 'wapas', 'kharab'])) {
            return response()->json([
                'reply' => 'You have <b>' . zship('return_days', '7') . ' days</b> easy returns on every order. If anything arrives damaged or isn\'t what you expected, contact us and we\'ll exchange or refund it — no hassle.',
                'chips' => ['Contact info', 'Delivery time?', "Today's deals"],
            ]);
        }

        // ── Contact / hours / address ──
        if ($has(['contact', 'phone', 'call', 'whatsapp', 'email', 'address', 'location', 'timing', 'hours', 'raabta'])) {
            $bits = [];
            if (zstore('phone')) $bits[] = '📞 <a href="tel:' . ztel(zstore('phone')) . '">' . e(zstore('phone')) . '</a> (Hotline 24/7)';
            if (zstore('email')) $bits[] = '✉️ <a href="mailto:' . e(zstore('email')) . '">' . e(zstore('email')) . '</a>';
            if (zstore('address')) $bits[] = '📍 ' . e(zstore('address'));
            if (zstore('hours')) $bits[] = '🕒 ' . e(zstore('hours'));
            $reply = $bits ? 'Here\'s how to reach us:<br>' . implode('<br>', $bits) : 'Visit our <a href="' . route('contact') . '">contact page</a> to get in touch.';
            $reply .= '<br>Or use the <a href="' . route('contact') . '">contact form</a>.';
            return response()->json(['reply' => $reply, 'chips' => ['Delivery time?', 'Return policy']]);
        }

        // ── Live agent ──
        if ($has(['agent', 'human', 'representative', 'real person', 'live chat', 'talk to', 'speak to', 'customer support', 'support team', 'complaint', 'shikayat'])) {
            return response()->json($this->agentIntro());
        }

        // ── Order tracking ──
        if ($has(['order', 'track', 'parcel', 'status'])) {
            $reply = auth()->check()
                ? 'You can see all your orders and their live status in <a href="' . route('account.index', ['tab' => 'orders']) . '#orders"><b>My Orders</b></a>.'
                : 'Please <a href="' . route('login') . '"><b>log in</b></a> and open <b>My account → My orders</b> to track your order. Need help? <a href="' . route('contact') . '">Contact us</a> with your order number.';
            return response()->json(['reply' => $reply, 'chips' => ['Contact info', 'Return policy']]);
        }

        // ── Deals / discounts ──
        if ($has(['deal', 'sale', 'discount', 'offer', 'sasta', 'cheap', 'promo'])) {
            $deals = Product::where('status', 'active')
                ->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'purchase_price')
                ->orderByRaw('(purchase_price - sale_price) / purchase_price DESC')
                ->take(3)->get();
            if ($deals->isEmpty()) {
                return response()->json([
                    'reply' => 'New deals drop every Friday! Browse everything on sale here: <a href="' . route('shop.index', ['on_sale' => 1]) . '"><b>Deal Today →</b></a>',
                    'chips' => ['Browse categories', 'Delivery time?'],
                ]);
            }
            return response()->json([
                'reply'    => 'Here are our hottest deals right now 🔥 — see all on the <a href="' . route('shop.index', ['on_sale' => 1]) . '"><b>deals page</b></a>:',
                'products' => $deals->map(fn ($p) => $this->card($p))->values(),
                'chips'    => ['Browse categories', 'Delivery time?'],
            ]);
        }

        // ── Categories ──
        if ($has(['categor', 'kya milta', 'what do you sell', 'kya bechte', 'collection'])) {
            $cats = Category::whereNull('parent_id')->where('status', 'active')->orderBy('name')->take(10)->get();
            $links = $cats->map(fn ($c) => '<a href="' . route('shop.index', ['category' => $c->slug]) . '">' . e($c->name) . '</a>')->implode(' · ');
            return response()->json([
                'reply' => $cats->count()
                    ? 'We have these categories:<br>' . $links . '<br>Or <a href="' . route('shop.index') . '"><b>browse everything →</b></a>'
                    : 'Browse all our products here: <a href="' . route('shop.index') . '"><b>Shop →</b></a>',
                'chips' => ["Today's deals", 'Our brands'],
            ]);
        }

        // ── Brands ──
        if ($has(['brand', 'manufactur', 'companies'])) {
            $brands = Manufacturer::active()->ordered()->take(10)->get();
            $links  = $brands->map(fn ($b) => '<a href="' . route('shop.index', ['brand' => $b->slug]) . '">' . e($b->name) . '</a>')->implode(' · ');
            return response()->json([
                'reply' => $brands->count()
                    ? 'We stock these authorised brands:<br>' . $links . '<br>See them all on the <a href="' . route('brands') . '"><b>Brands page →</b></a>'
                    : 'Check out our <a href="' . route('brands') . '"><b>Brands page →</b></a>',
                'chips' => ['Browse categories', "Today's deals"],
            ]);
        }

        // ── Product search (the default: look the question up in the live catalog) ──
        $stop = ['the','a','an','is','of','for','me','i','you','do','does','have','has','any','ka','ki','ke','hai','hain','kya','kitna','kitni','price','rate','cost','how','much','what','tell','about','mein','milega','milta','chahiye','want','need','show','your','there','sell','available','stock','in','on','and','or','with','buy','order','please','plz','par','per','he','ho','hy'];
        $tokens = collect(preg_split('/[^\p{L}\p{N}]+/u', $msg, -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn ($t) => mb_strlen($t) >= 3 && !in_array($t, $stop, true))
            ->unique()->take(6)->values();

        if ($tokens->isNotEmpty()) {
            $found = Product::where('status', 'active')
                ->where(function ($q) use ($tokens) {
                    foreach ($tokens as $t) {
                        $q->orWhere('name', 'like', "%{$t}%")
                          ->orWhere('short_description', 'like', "%{$t}%")
                          ->orWhere('sku', 'like', "%{$t}%");
                    }
                })
                ->take(12)->get()
                ->sortByDesc(function ($p) use ($tokens) {
                    $n = mb_strtolower($p->name);
                    return $tokens->filter(fn ($t) => str_contains($n, $t))->count();
                })
                ->take(4)->values();

            if ($found->isNotEmpty()) {
                return response()->json([
                    'reply'    => $found->count() === 1
                        ? 'Yes! We have this in store:'
                        : 'Here\'s what I found in our store:',
                    'products' => $found->map(fn ($p) => $this->card($p))->values(),
                    'chips'    => ['Delivery time?', 'Cash on delivery?', "Today's deals"],
                ]);
            }
        }

        // ── Fallback ──
        return response()->json([
            'reply' => 'Sorry, I couldn\'t find that. Try a product name (e.g. “watch”), or <a href="' . route('shop.index') . '"><b>browse the shop →</b></a>. For anything else our team is one tap away on the <a href="' . route('contact') . '">contact page</a>.',
            'chips' => ['Browse categories', "Today's deals", 'Talk to a live agent'],
        ]);
    }

    private function card(Product $p): array
    {
        return [
            'name'  => $p->name,
            'url'   => route('shop.show', $p->slug),
            'img'   => $p->display_image,
            'price' => zprice($p->price),
            'old'   => $p->compare_price ? zprice($p->compare_price) : null,
            'stock' => $p->quantity > 0 ? 'In stock' : 'Out of stock',
        ];
    }

    /** Live agent hours — configured in Admin → Live Chat. */
    public static function agentHours(): array
    {
        $tz    = \App\Models\Setting::get('chat_tz', 'America/New_York') ?: 'America/New_York';
        $start = \App\Models\Setting::get('chat_start', '08:00') ?: '08:00';
        $end   = \App\Models\Setting::get('chat_end', '20:00') ?: '20:00';
        $now   = now($tz)->format('H:i');
        $open  = $start <= $end ? ($now >= $start && $now < $end) : ($now >= $start || $now < $end);
        $fmt   = fn ($t) => \Carbon\Carbon::createFromFormat('H:i', $t)->format('g:i A');
        $label = [
            'America/New_York' => 'US Eastern', 'America/Chicago' => 'US Central',
            'America/Denver' => 'US Mountain', 'America/Los_Angeles' => 'US Pacific',
            'UTC' => 'UTC', 'Europe/London' => 'UK', 'Asia/Karachi' => 'Pakistan',
        ][$tz] ?? str_replace('_', ' ', $tz);
        return ['open' => $open, 'text' => $fmt($start) . '–' . $fmt($end) . ' ' . $label . ' time', 'tz' => $tz, 'start' => $start, 'end' => $end];
    }

    private function agentOpen(): bool
    {
        return static::agentHours()['open'];
    }

    private function agentIntro(): array
    {
        $open = $this->agentOpen();
        $user = auth()->user();
        $hours = static::agentHours();
        $reply = $open
            ? 'Our live support team is <b>online now</b> 🟢 (available ' . e($hours['text']) . ').'
            : 'Our live agents are <b>offline right now</b> — they\'re available <b>' . e($hours['text']) . '</b>. You can still leave a message and the team will reply as soon as they\'re back.';
        $reply .= $user
            ? ' Type your message below — it goes straight to our team.'
            : ' First, share your name and email so the team can get back to you.';
        return [
            'reply'        => $reply,
            'agent_prompt' => true,
            'open'         => $open,
            'need_contact' => !$user,
            'contact'      => $user ? ['name' => $user->name, 'email' => $user->email] : null,
            'chips'        => [],
        ];
    }

    private function agentMessage(Request $request, string $raw)
    {
        $name  = mb_substr(trim((string) ($request->input('name') ?: optional(auth()->user())->name)), 0, 100);
        $email = mb_substr(trim((string) ($request->input('email') ?: optional(auth()->user())->email)), 0, 150);

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'reply'        => 'Please share a valid name and email first so our team can reach you.',
                'agent_prompt' => true,
                'open'         => $this->agentOpen(),
                'need_contact' => true,
            ]);
        }
        if ($raw === '') {
            return response()->json(['reply' => 'Please type a message for our team.', 'agent' => true]);
        }

        $token = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $request->input('token', ''));
        if ($token === '' || mb_strlen($token) > 64) {
            return response()->json(['reply' => 'Chat session error — please refresh the page and try again.', 'agent' => true]);
        }

        $open = $this->agentOpen();
        ChatMessage::ensureTable();
        ChatMessage::create([
            'thread_token'  => $token,
            'name'          => $name,
            'email'         => $email,
            'user_id'       => auth()->id(),
            'sender'        => 'user',
            'message'       => $raw,
            'read_by_agent' => false,
            'read_by_user'  => true,
        ]);

        return response()->json([
            'reply' => $open
                ? '✓ Delivered — an agent will reply right here shortly.'
                : '✓ Message saved. Our agents are back at <b>' . e(\Carbon\Carbon::createFromFormat('H:i', static::agentHours()['start'])->format('g:i A')) . '</b> — their reply will appear right here, and we\'ll also have your email (<b>' . e($email) . '</b>).',
            'agent' => true,
        ]);
    }

    /** Widget polls this for new agent replies in the visitor's thread. */
    public function poll(Request $request)
    {
        $token = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $request->query('token', ''));
        $after = (int) $request->query('after', 0);
        if ($token === '' || mb_strlen($token) > 64) {
            return response()->json(['messages' => []]);
        }

        ChatMessage::ensureTable();
        $msgs = ChatMessage::where('thread_token', $token)
            ->where('sender', 'agent')
            ->where('id', '>', $after)
            ->orderBy('id')->limit(50)->get();

        if ($msgs->isNotEmpty()) {
            ChatMessage::whereIn('id', $msgs->pluck('id'))->update(['read_by_user' => true]);
        }

        return response()->json([
            'messages' => $msgs->map(fn ($m) => [
                'id'      => $m->id,
                'message' => $m->message,
                'name'    => $m->name ?: 'Support',
            ])->values(),
        ]);
    }

    private function greeting(): array
    {
        return [
            'reply' => 'Hi there! 👋 Welcome to <b>' . e(zstore('name', 'Zelari')) . '</b>. I can help you find products, check prices and stock, and answer questions about delivery, payment and returns. What are you looking for?',
            'chips' => ['Delivery time?', 'Cash on delivery?', "Today's deals", 'Browse categories', 'Talk to a live agent'],
        ];
    }
}
