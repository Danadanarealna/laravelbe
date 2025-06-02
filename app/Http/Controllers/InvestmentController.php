<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\User;
use App\Models\Investor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvestmentController extends Controller
{
    public function store(Request $request)
    {
        try {
            $investor = Auth::guard('sanctum')->user();

            if (!($investor instanceof Investor)) {
                return response()->json(['message' => 'Unauthorized. Only investors can make investments.'], 403);
            }

            $validated = $request->validate([
                'umkm_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.01',
            ]);

            $umkm = User::find($validated['umkm_id']);
            if (!$umkm || empty($umkm->umkm_name) || empty($umkm->contact) || !$umkm->is_investable) {
                return response()->json(['message' => 'Cannot invest. Target UMKM profile is incomplete, not investable, or does not exist.'], 422);
            }

            $investmentData = [
                'investor_id' => $investor->id,
                'umkm_id' => $validated['umkm_id'],
                'amount' => $validated['amount'],
                'status' => 'pending',
            ];
            
            $investmentData['investment_date'] = $request->input('investment_date', now());


            $investment = Investment::create($investmentData);

            return response()->json([
                'message' => 'Investment initiated successfully!',
                'investment' => $investment->load('umkm:id,umkm_name,contact,umkm_profile_image_path,umkm_description')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error storing investment by investor ' . ($investor->id ?? 'unknown') . ' for UMKM ' . ($request->umkm_id ?? 'unknown') . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Could not initiate investment due to a server error.', 'error_detail' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $investor = Auth::guard('sanctum')->user();
        if (!($investor instanceof Investor)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $investments = $investor->investments()
                                ->with('umkm:id,umkm_name,contact,umkm_profile_image_path,umkm_description')
                                ->orderBy('investment_date', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->get();

        return response()->json($investments);
    }

    public function confirmInvestment(Request $request, Investment $investment)
    {
        try {
            $investor = Auth::guard('sanctum')->user();

            if (!($investor instanceof Investor) || $investment->investor_id !== $investor->id) {
                return response()->json(['message' => 'Unauthorized to confirm this investment.'], 403);
            }

            if ($investment->status !== 'pending') {
                return response()->json(['message' => 'This investment is not pending and cannot be confirmed.'], 422);
            }

            $investment->status = 'active';
            $investment->save();
            return response()->json([
                'message' => 'Investment confirmed successfully!',
                'investment' => $investment->fresh()->load('umkm:id,umkm_name,contact,umkm_profile_image_path,umkm_description')
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error confirming investment ID ' . $investment->id . ' by investor ' . ($investor->id ?? 'unknown') . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Could not confirm investment due to a server error.'], 500);
        }
    }
}
