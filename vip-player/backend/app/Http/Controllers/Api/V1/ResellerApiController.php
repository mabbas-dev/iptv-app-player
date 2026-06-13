<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Plan;
use App\Models\Reseller;
use App\Services\DeviceActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Reseller API — default permission: activate devices only.
 */
class ResellerApiController extends Controller
{
    public function balance(Request $request): JsonResponse
    {
        $reseller = $this->reseller($request);

        return response()->json([
            'success' => true,
            'data' => [
                'reseller' => $reseller->name,
                'balance' => $reseller->wallet?->balance ?? 0,
            ],
        ]);
    }

    public function plans(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Plan::where('is_active', true)
                ->where('plan_type', 'reseller')
                ->orderBy('sort_order')
                ->get(['id', 'name', 'duration_days', 'credit_cost', 'is_trial']),
        ]);
    }

    public function devices(Request $request): JsonResponse
    {
        $devices = $this->reseller($request)->devices()
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $devices->items(),
            'meta' => [
                'current_page' => $devices->currentPage(),
                'last_page' => $devices->lastPage(),
                'total' => $devices->total(),
            ],
        ]);
    }

    public function activate(Request $request, DeviceActivationService $service): JsonResponse
    {
        $reseller = $this->reseller($request);

        if (! $reseller->hasPermission(Reseller::PERM_ACTIVATE)) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $data = $request->validate([
            'device_code' => ['required', 'string'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $device = Device::where('device_code', strtoupper($data['device_code']))->first();

        if (! $device) {
            return response()->json(['success' => false, 'message' => 'Device not found.'], 404);
        }

        $plan = Plan::where('is_active', true)->where('plan_type', 'reseller')->findOrFail($data['plan_id']);

        try {
            $subscription = $service->activate($device, $plan, $reseller);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Device activated with plan: {$plan->name}",
            'data' => [
                'device_code' => $device->device_code,
                'status' => $device->fresh()->status,
                'ends_at' => $subscription->ends_at->toIso8601String(),
                'credits_spent' => $subscription->credits_spent,
                'balance' => $reseller->wallet->fresh()->balance,
            ],
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = $this->reseller($request)->creditTransactions()
            ->latest()
            ->paginate($request->integer('per_page', 25), [
                'id', 'type', 'amount', 'balance_after', 'description', 'created_at',
            ]);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    protected function reseller(Request $request): Reseller
    {
        return $request->attributes->get('reseller');
    }
}
