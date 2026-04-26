<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function dashboard()
    {
        $transactions = Transaction::with('customer:id,full_name,account_number')
            ->whereIn('status', ['pending', 'verified'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Employee/Dashboard', [
            'transactions' => $transactions,
            'employee'     => Auth::user()->only('full_name', 'employee_id'),
        ]);
    }

    /**
     * Mark a single transaction as verified by this employee.
     */
    public function verify(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $transaction->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        // Immutable audit log entry
        AuditLog::create([
            'actor_id'    => Auth::id(),
            'actor_role'  => 'employee',
            'action'      => 'TRANSACTION_VERIFIED',
            'subject_id'  => $transaction->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return back()->with('success', "Transaction #{$id} marked as verified.");
    }

    /**
     * Forward all verified transactions to SWIFT.
     * Job ends here — system marks them submitted and logs the action.
     */
    public function submitToSwift(Request $request)
    {
        $verified = Transaction::where('status', 'verified')->get();

        if ($verified->isEmpty()) {
            return back()->withErrors(['submit' => 'No verified transactions to submit.']);
        }

        foreach ($verified as $txn) {
            $txn->update(['status' => 'submitted_to_swift']);

            AuditLog::create([
                'actor_id'   => Auth::id(),
                'actor_role' => 'employee',
                'action'     => 'SUBMITTED_TO_SWIFT',
                'subject_id' => $txn->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return back()->with('success', "{$verified->count()} transaction(s) submitted to SWIFT.");
    }
}
