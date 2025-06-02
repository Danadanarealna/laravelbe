<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Investor;
use App\Models\User;
use App\Models\Investment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['investor', 'umkm'])->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $investors = Investor::orderBy('name')->get();
        $umkm_users = User::where('is_admin', false)->orderBy('umkm_name')->get();
        $investments = Investment::with(['investor', 'umkm'])->orderBy('investment_date', 'desc')->get();
        return view('admin.appointments.create', compact('investors', 'umkm_users', 'investments'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'umkm_id' => 'required|exists:users,id',
            'investment_id' => 'nullable|exists:investments,id',
            'appointment_details' => 'nullable|string|max:1000',
            'appointment_time' => 'nullable|date_format:Y-m-d\TH:i',
            'status' => 'required|string|in:requested,confirmed,completed,cancelled,rescheduled_by_umkm,rescheduled_by_investor',
            'contact_method' => 'required|string|in:whatsapp,email,phone',
            'contact_payload' => 'nullable|string|max:1000',
        ]);

        if ($request->filled('appointment_time')) {
            $validatedData['appointment_time'] = Carbon::parse($validatedData['appointment_time'])->toDateTimeString();
        } else {
            $validatedData['appointment_time'] = null;
        }


        Appointment::create($validatedData);
        return redirect()->route('admin.appointments.index')->with('success', 'Appointment created successfully.');
    }

    public function show(Appointment $appointment)
    {
        return redirect()->route('admin.appointments.edit', $appointment);
    }

    public function edit(Appointment $appointment)
    {
        $investors = Investor::orderBy('name')->get();
        $umkm_users = User::where('is_admin', false)->orderBy('umkm_name')->get();
        $investments = Investment::with(['investor', 'umkm'])->orderBy('investment_date', 'desc')->get();
        return view('admin.appointments.edit', compact('appointment', 'investors', 'umkm_users', 'investments'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validatedData = $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'umkm_id' => 'required|exists:users,id',
            'investment_id' => 'nullable|exists:investments,id',
            'appointment_details' => 'nullable|string|max:1000',
            'appointment_time' => 'nullable|date_format:Y-m-d\TH:i',
            'status' => 'required|string|in:requested,confirmed,completed,cancelled,rescheduled_by_umkm,rescheduled_by_investor',
            'contact_method' => 'required|string|in:whatsapp,email,phone',
            'contact_payload' => 'nullable|string|max:1000',
        ]);

        if ($request->filled('appointment_time')) {
            $validatedData['appointment_time'] = Carbon::parse($validatedData['appointment_time'])->toDateTimeString();
        } else {
            $validatedData['appointment_time'] = null;
        }

        $appointment->update($validatedData);
        return redirect()->route('admin.appointments.index')->with('success', 'Appointment updated successfully.');
    }
    
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:requested,confirmed,completed,cancelled,rescheduled_by_umkm,rescheduled_by_investor',
            'appointment_time' => 'nullable|required_if:status,confirmed|required_if:status,rescheduled_by_umkm|required_if:status,rescheduled_by_investor|date_format:Y-m-d H:i:s|after_or_equal:now',
        ]);

        $appointmentData = ['status' => $validated['status']];
        if (!empty($validated['appointment_time'])) {
            $appointmentData['appointment_time'] = Carbon::parse($validated['appointment_time']);
        }

        $appointment->update($appointmentData);
        return redirect()->route('admin.appointments.index')->with('success', 'Appointment status updated successfully.');
    }


    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('admin.appointments.index')->with('success', 'Appointment deleted successfully.');
    }
}
