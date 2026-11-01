<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::withCount('vendors')->orderBy('sort')->get();
        return view('admin.plans.index', compact('plans'));
    }
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:80',
            'price' => 'required|numeric|min:0',
            'product_limit' => 'nullable|integer|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }
    public function store(Request $request)
    {
        $d = $request->validate($this->rules());
        $d['product_limit']   = ($d['product_limit'] ?? '') === '' ? null : $d['product_limit'];
        $d['commission_rate'] = ($d['commission_rate'] ?? '') === '' ? null : $d['commission_rate'];
        SubscriptionPlan::create($d + ['enabled' => true, 'sort' => (int) (SubscriptionPlan::max('sort') + 1)]);
        return back()->with('success', 'Plan added.');
    }
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $d = $request->validate($this->rules() + ['enabled' => 'nullable|boolean']);
        $d['product_limit']   = ($d['product_limit'] ?? '') === '' ? null : $d['product_limit'];
        $d['commission_rate'] = ($d['commission_rate'] ?? '') === '' ? null : $d['commission_rate'];
        $plan->update($d + ['enabled' => $request->boolean('enabled')]);
        return back()->with('success', 'Plan updated.');
    }
    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();
        return back()->with('success', 'Plan removed.');
    }
}
