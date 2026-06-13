<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_code' => ['nullable', 'string', 'exists:devices,device_code'],
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $device = isset($data['device_code'])
            ? Device::where('device_code', $data['device_code'])->first()
            : null;

        $ticket = SupportTicket::create([
            'device_id' => $device?->id,
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created. We will get back to you soon.',
            'data' => ['ticket_id' => $ticket->id],
        ], 201);
    }

    public function index(Request $request, string $deviceCode): JsonResponse
    {
        $device = Device::where('device_code', strtoupper($deviceCode))->firstOrFail();

        $tickets = $device->supportTickets()
            ->latest()
            ->limit(20)
            ->get(['id', 'subject', 'message', 'admin_reply', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }
}
