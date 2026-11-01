<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    private array $events = [
        'order.created'     => 'Order placed',
        'order.delivered'   => 'Order delivered',
        'product.low_stock' => 'Product low on stock',
        'vendor.registered' => 'Vendor registered',
    ];

    public function index()
    {
        $rules  = AutomationRule::withCount('runs')->latest()->get();
        $events = $this->events;
        return view('admin.automations.index', compact('rules', 'events'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        AutomationRule::create($data);
        return back()->with('success', 'Automation rule created.');
    }

    public function update(Request $request, AutomationRule $automation)
    {
        $automation->update($this->validated($request));
        return back()->with('success', 'Rule updated.');
    }

    public function destroy(AutomationRule $automation)
    {
        $automation->delete();
        return back()->with('success', 'Rule removed.');
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'name'        => 'required|string|max:120',
            'event'       => 'required|in:' . implode(',', array_keys($this->events)),
            'cond_field'  => 'nullable|string|max:60',
            'cond_op'     => 'nullable|in:=,!=,>,<,contains',
            'cond_value'  => 'nullable|string|max:120',
            'action_type' => 'required|in:email,note,webhook',
            'action_to'   => 'nullable|string|max:160',
            'action_subject' => 'nullable|string|max:160',
            'action_body' => 'nullable|string|max:2000',
            'action_url'  => 'nullable|url|max:300',
            'enabled'     => 'nullable|boolean',
        ]);

        $conditions = [];
        if (!empty($v['cond_field'])) {
            $conditions[] = ['field' => $v['cond_field'], 'op' => $v['cond_op'] ?? '=', 'value' => $v['cond_value'] ?? ''];
        }
        $action = ['type' => $v['action_type']];
        foreach (['to' => 'action_to', 'subject' => 'action_subject', 'body' => 'action_body', 'url' => 'action_url', 'text' => 'action_body'] as $k => $src) {
            if (!empty($v[$src])) $action[$k] = $v[$src];
        }

        return [
            'name'       => $v['name'],
            'event'      => $v['event'],
            'conditions' => $conditions,
            'actions'    => [$action],
            'enabled'    => $request->boolean('enabled'),
        ];
    }
}
