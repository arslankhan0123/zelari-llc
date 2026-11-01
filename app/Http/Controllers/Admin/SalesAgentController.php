<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentPayout;
use App\Models\SalesAgent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesAgentController extends Controller
{
    public function index()
    {
        $agents = SalesAgent::latest()->get();

        // Attach a lightweight earnings snapshot to each row.
        $rows = $agents->map(function ($a) {
            return ['agent' => $a, 'e' => $a->earnings()];
        });

        $totals = [
            'agents'  => $agents->count(),
            'sales'   => round($rows->sum(fn ($r) => $r['e']['sales']), 2),
            'earned'  => round($rows->sum(fn ($r) => $r['e']['earned']), 2),
            'pending' => round($rows->sum(fn ($r) => $r['e']['pending']), 2),
            'paid'    => round($rows->sum(fn ($r) => $r['e']['paid']), 2),
        ];

        return view('admin.agents.index', compact('rows', 'totals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:120',
            'email'           => 'nullable|email|max:160',
            'phone'           => 'nullable|string|max:40',
            'code'            => ['nullable', 'string', 'max:40', Rule::unique('sales_agents', 'code')],
            'commission_rate' => 'required|numeric|min:0|max:100',
            'payout_method'   => 'nullable|string|max:40',
            'bank_name'       => 'nullable|string|max:120',
            'account_name'    => 'nullable|string|max:120',
            'account_number'  => 'nullable|string|max:60',
            'note'            => 'nullable|string|max:1000',
        ]);

        $agent = SalesAgent::create($data + ['active' => true]);

        return redirect()->route('admin.agents.show', $agent)
            ->with('success', 'Sales agent "' . $agent->name . '" created. Referral code: ' . $agent->code);
    }

    public function show(SalesAgent $agent)
    {
        $summary = $agent->earnings();
        $orders  = $agent->orders()->latest()->paginate(15);
        $payouts = $agent->payouts()->latest()->paginate(10, ['*'], 'payouts');

        return view('admin.agents.show', compact('agent', 'summary', 'orders', 'payouts'));
    }

    public function update(Request $request, SalesAgent $agent)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:120',
            'email'           => 'nullable|email|max:160',
            'phone'           => 'nullable|string|max:40',
            'code'            => ['required', 'string', 'max:40', Rule::unique('sales_agents', 'code')->ignore($agent->id)],
            'commission_rate' => 'required|numeric|min:0|max:100',
            'active'          => 'nullable|boolean',
            'payout_method'   => 'nullable|string|max:40',
            'bank_name'       => 'nullable|string|max:120',
            'account_name'    => 'nullable|string|max:120',
            'account_number'  => 'nullable|string|max:60',
            'note'            => 'nullable|string|max:1000',
        ]);
        $data['code']   = strtoupper($data['code']);
        $data['active'] = (bool) ($request->input('active'));

        $agent->update($data);

        return back()->with('success', 'Sales agent updated.');
    }

    public function destroy(SalesAgent $agent)
    {
        // Orders keep their history; sales_agent_id is set to null by the FK.
        $agent->delete();
        return redirect()->route('admin.agents.index')->with('success', 'Sales agent removed.');
    }

    /** Record a commission payout to the agent (capped at the available balance). */
    public function payout(Request $request, SalesAgent $agent)
    {
        $available = $agent->earnings()['available'];

        $data = $request->validate([
            'amount'    => 'required|numeric|min:1|max:' . max(1, $available),
            'method'    => 'nullable|string|max:60',
            'reference' => 'nullable|string|max:120',
            'note'      => 'nullable|string|max:1000',
        ]);

        if ($available < 1) {
            return back()->with('error', 'This agent has no available balance to pay out.');
        }

        AgentPayout::create([
            'sales_agent_id' => $agent->id,
            'amount'         => round((float) $data['amount'], 2),
            'method'         => $data['method'] ?? null,
            'reference'      => $data['reference'] ?? null,
            'note'           => $data['note'] ?? null,
            'paid_at'        => now(),
        ]);

        return back()->with('success', 'Payout of ' . number_format((float) $data['amount'], 2) . ' recorded.');
    }
}
