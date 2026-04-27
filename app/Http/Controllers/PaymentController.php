<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PaymentController extends Controller
{
    // Currency options supported by the portal
    private const CURRENCIES = ['USD', 'EUR', 'GBP', 'JPY', 'AUD', 'CAD', 'CHF', 'CNY', 'INR', 'ZAR'];

    // SWIFT payment providers
    private const PROVIDERS = ['SWIFT', 'SEPA', 'WIRE'];

    public function dashboard()
    {
        $transactions = Transaction::where('customer_id', Auth::id())
            ->orderByDesc('created_at')
            ->take(10)
            ->get(['id', 'amount', 'currency', 'beneficiary_name', 'swift_code', 'status', 'created_at']);

        return Inertia::render('Customer/Dashboard', [
            'transactions' => $transactions,
            'user'         => Auth::user()->only('full_name', 'account_number'),
        ]);
    }

    public function showPayment()
    {
        return Inertia::render('Customer/Payment', [
            'currencies' => self::CURRENCIES,
            'providers'  => self::PROVIDERS,
        ]);
    }

    public function submitPayment(Request $request)
    {
 
        $request->validate([
            'amount'           => ['required', 'regex:/^\d{1,10}(\.\d{1,2})?$/', 'numeric', 'min:0.01', 'max:9999999.99'],
            // Currency: exactly one of the allowed ISO 4217 codes
            'currency'         => ['required', 'in:' . implode(',', self::CURRENCIES)],
            // Provider: whitelist
            'provider'         => ['required', 'in:' . implode(',', self::PROVIDERS)],
            // Beneficiary account: alphanumeric 4–34 chars (IBAN / general)
            'beneficiary_account' => ['required', 'regex:/^[A-Z0-9]{4,34}$/'],
            // Beneficiary name: letters, spaces, hyphens, apostrophes
            'beneficiary_name'    => ['required', 'regex:/^[A-Za-z\s\-\'\.]{2,100}$/'],
            // SWIFT/BIC code: ISO 9362 — 8 or 11 alphanumeric characters
            'swift_code'          => ['required', 'regex:/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/'],
        ], [
            'swift_code.regex'           => 'SWIFT/BIC code must be 8 or 11 characters (ISO 9362 format).',
            'beneficiary_account.regex'  => 'Beneficiary account must be 4–34 uppercase alphanumeric characters.',
            'beneficiary_name.regex'     => 'Beneficiary name contains invalid characters.',
            'amount.regex'               => 'Amount must be a valid positive number.',
        ]);

        // Store with status 'pending' — awaiting employee verification
        Transaction::create([
            'customer_id'         => Auth::id(),
            'amount'              => $request->amount,
            'currency'            => $request->currency,
            'provider'            => $request->provider,
            'beneficiary_account' => strtoupper($request->beneficiary_account),
            'beneficiary_name'    => $request->beneficiary_name,
            'swift_code'          => strtoupper(strip_tags($request->swift_code)),
            'status'              => 'pending',
        ]);

        return redirect()->route('customer.payment.success');
    }

    public function paymentSuccess()
    {
        return Inertia::render('Customer/PaymentSuccess');
    }
}
