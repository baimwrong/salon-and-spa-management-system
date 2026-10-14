<?php

namespace App\Http\Controllers;

use App\Models\CommissionPayout;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $therapists = User::where('role', 'therapist')->orderBy('name')->get();

        $query = CommissionPayout::with(['therapist', 'invoiceItem.invoice']);

        if ($request->filled('therapist_id')) {
            $query->where('therapist_id', $request->input('therapist_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $payouts = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $totalPending = CommissionPayout::where('status', 'pending')->sum('amount');
        $totalPaid = CommissionPayout::where('status', 'paid')->sum('amount');

        return view('commissions.index', compact('payouts', 'therapists', 'totalPending', 'totalPaid'));
    }

    public function markPaid(Request $request, CommissionPayout $payout)
    {
        $payout->update([
            'status' => 'paid',
            'payout_date' => Carbon::today(),
            'notes' => $request->input('notes', 'Paid via payroll / cash distribution'),
        ]);

        return back()->with('success', "Commission of \${$payout->amount} marked as paid to {$payout->therapist->name}.");
    }

    public function markAllPaid(Request $request)
    {
        $validated = $request->validate([
            'therapist_id' => ['required', 'exists:users,id'],
        ]);

        $therapist = User::findOrFail($validated['therapist_id']);
        $count = CommissionPayout::where('therapist_id', $therapist->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'paid',
                'payout_date' => Carbon::today(),
                'notes' => 'Bulk settlement payout',
            ]);

        return back()->with('success', "Marked {$count} pending commissions as paid for {$therapist->name}.");
    }
}
