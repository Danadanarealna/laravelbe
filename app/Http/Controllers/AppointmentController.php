<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User; 
use App\Models\Investor; 
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Log;


class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        try {
            $investor = Auth::guard('sanctum')->user();

            if (!($investor instanceof Investor)) {
                return response()->json(['message' => 'Unauthorized. Only investors can request appointments.'], 403);
            }

            $validated = $request->validate([
                'umkm_id' => 'required|exists:users,id',
                'appointment_details' => 'required|string|max:1000',
                'contact_method' => 'required|string|in:whatsapp,email,phone', 
                'contact_payload' => 'required|string|max:1000',
            ]);

            $umkm = User::find($validated['umkm_id']);
            if (!$umkm || (empty($umkm->contact) && $validated['contact_method'] === 'whatsapp')) { 
                return response()->json(['message' => 'UMKM contact information is not available for an appointment via ' . $validated['contact_method'] . '.'], 422);
            }

            $appointmentData = [
                'investor_id' => $investor->id,
                'umkm_id' => $validated['umkm_id'],
                'appointment_details' => $validated['appointment_details'],
                'status' => 'requested',
                'contact_method' => $validated['contact_method'],
                'contact_payload' => $validated['contact_payload'],
            ];

            $appointment = Appointment::create($appointmentData);

            return response()->json([
                'message' => 'Appointment requested successfully!',
                'appointment' => $appointment->load(['umkm:id,umkm_name,contact'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error storing appointment by investor ' . ($investor->id ?? 'unknown') . ' for UMKM ' . ($request->umkm_id ?? 'unknown') . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Could not request appointment due to a server error.'], 500);
        }
    }

    public function indexForInvestor(Request $request)
    {
        $investor = Auth::guard('sanctum')->user();
        if (!($investor instanceof Investor)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $appointments = $investor->appointments()
                                  ->with('umkm:id,umkm_name,contact') 
                                  ->orderBy('created_at', 'desc')
                                  ->get();
        return response()->json($appointments);
    }

    public function indexForUmkm(Request $request)
    {
        $umkm = Auth::guard('sanctum')->user();
        if (!($umkm instanceof User)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $appointments = $umkm->appointmentsReceived()
                              ->with('investor:id,name,email')
                              ->orderBy('created_at', 'desc')
                              ->get();
        return response()->json($appointments);
    }

    public function updateStatusForUmkm(Request $request, Appointment $appointment)
    {
        $umkm = Auth::guard('sanctum')->user();

        if (!($umkm instanceof User) || $appointment->umkm_id !== $umkm->id) {
            return response()->json(['message' => 'Unauthorized to update this appointment.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,completed,cancelled,rescheduled_by_umkm',
            'appointment_time' => 'nullable|required_if:status,confirmed|required_if:status,rescheduled_by_umkm|date_format:Y-m-d H:i:s|after_or_equal:now',
        ]);

        $appointmentData = ['status' => $validated['status']];
        if (!empty($validated['appointment_time'])) {
            $appointmentData['appointment_time'] = Carbon::parse($validated['appointment_time']);
        }

        $appointment->update($appointmentData);
        return response()->json([
            'message' => 'Appointment status updated successfully by UMKM.',
            'appointment' => $appointment->fresh()->load(['umkm:id,umkm_name', 'investor:id,name'])
        ]);
    }
    

    public function updateStatusForInvestor(Request $request, Appointment $appointment)
    {
        $investor = Auth::guard('sanctum')->user();

        if (!($investor instanceof Investor) || $appointment->investor_id !== $investor->id) {
            return response()->json(['message' => 'Unauthorized to update this appointment.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:cancelled,rescheduled_by_investor', 
            'appointment_time' => 'nullable|required_if:status,rescheduled_by_investor|date_format:Y-m-d H:i:s|after_or_equal:now',
        ]);

        $appointmentData = ['status' => $validated['status']];
         if (!empty($validated['appointment_time']) && $validated['status'] === 'rescheduled_by_investor') {
            $appointmentData['appointment_time'] = Carbon::parse($validated['appointment_time']);
        }

        $appointment->update($appointmentData);
        return response()->json([
            'message' => 'Appointment status updated successfully by Investor.',
            'appointment' => $appointment->fresh()->load(['umkm:id,umkm_name', 'investor:id,name'])
        ]);
    }
}
