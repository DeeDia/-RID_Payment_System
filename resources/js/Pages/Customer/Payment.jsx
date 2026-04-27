// resources/js/Pages/Customer/Payment.jsx

import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import PortalLayout from '@/Components/PortalLayout';
import FormField from '@/Components/FormField';
import Alert from '@/Components/Alert';

//Client-side RegEx mirrors (ISO 9362 SWIFT/BIC) 
const SWIFT_REGEX      = /^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/;
const BENE_ACCT_REGEX  = /^[A-Z0-9]{4,34}$/;
const BENE_NAME_REGEX  = /^[A-Za-z\s\-'.]{2,100}$/;
const AMOUNT_REGEX     = /^\d{1,10}(\.\d{1,2})?$/;

export default function Payment({ user, currencies, providers }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        amount:               '',
        currency:             'USD',
        provider:             'SWIFT',
        beneficiary_account:  '',
        beneficiary_name:     '',
        swift_code:           '',
    });

    const [clientErrors, setClientErrors] = useState({});
    const [showConfirm, setShowConfirm]   = useState(false);

    const validateField = (name, value) => {
        const errs = { ...clientErrors };

        switch (name) {
            case 'amount':
                if (value && (!AMOUNT_REGEX.test(value) || parseFloat(value) <= 0)) {
                    errs.amount = 'Enter a valid positive amount (e.g. 1500.00).';
                } else delete errs.amount;
                break;
            case 'beneficiary_account':
                if (value && !BENE_ACCT_REGEX.test(value.toUpperCase())) {
                    errs.beneficiary_account = '4–34 uppercase alphanumeric characters (IBAN format).';
                } else delete errs.beneficiary_account;
                break;
            case 'beneficiary_name':
                if (value && !BENE_NAME_REGEX.test(value)) {
                    errs.beneficiary_name = 'Name contains invalid characters.';
                } else delete errs.beneficiary_name;
                break;
            case 'swift_code':
                if (value && !SWIFT_REGEX.test(value.toUpperCase())) {
                    errs.swift_code = 'Invalid SWIFT/BIC code. Must be 8 or 11 characters (ISO 9362).';
                } else delete errs.swift_code;
                break;
            default:
                break;
        }

        setClientErrors(errs);
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        // Auto-uppercase SWIFT code and beneficiary account for usability
        const normalized = (name === 'swift_code' || name === 'beneficiary_account')
            ? value.toUpperCase()
            : value;
        setData(name, normalized);
        validateField(name, normalized);
    };

    const isValid = () =>
        data.amount && data.beneficiary_account && data.beneficiary_name && data.swift_code &&
        Object.keys(clientErrors).length === 0;

    const handlePayNow = (e) => {
        e.preventDefault();
        if (isValid()) setShowConfirm(true);
    };

    const handleConfirm = () => {
        post('/payment', { onError: () => setShowConfirm(false) });
    };

    const allErrors = { ...clientErrors, ...errors };

    return (
        <PortalLayout user={user} role="customer">
            <div className="page-header">
                <h1 className="page-title">International Payment</h1>
                <p className="page-subtitle">All payments are reviewed by our compliance team before submission to SWIFT.</p>
            </div>

            {/* Confirmation modal */}
            {showConfirm && (
                <div className="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
                    <div className="modal">
                        <h2 id="confirm-title" className="modal-title">Confirm Payment</h2>
                        <div className="confirm-details">
                            <div className="confirm-row">
                                <span>Amount</span>
                                <strong>{data.currency} {parseFloat(data.amount).toLocaleString('en-ZA', { minimumFractionDigits: 2 })}</strong>
                            </div>
                            <div className="confirm-row">
                                <span>Provider</span>
                                <strong>{data.provider}</strong>
                            </div>
                            <div className="confirm-row">
                                <span>Beneficiary</span>
                                <strong>{data.beneficiary_name}</strong>
                            </div>
                            <div className="confirm-row">
                                <span>Account</span>
                                <strong className="mono">{data.beneficiary_account}</strong>
                            </div>
                            <div className="confirm-row">
                                <span>SWIFT/BIC</span>
                                <strong className="mono">{data.swift_code}</strong>
                            </div>
                        </div>
                        <p className="confirm-notice">
                            ⚠ Please verify all details. International payments cannot be easily recalled once submitted.
                        </p>
                        <div className="modal-actions">
                            <button
                                className="btn btn--ghost"
                                onClick={() => setShowConfirm(false)}
                                disabled={processing}
                            >
                                Go Back
                            </button>
                            {/* This is the "Pay Now" button */}
                            <button
                                className="btn btn--primary"
                                onClick={handleConfirm}
                                disabled={processing}
                            >
                                {processing ? 'Submitting…' : 'Pay Now'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            <div className="payment-grid">
                <form onSubmit={handlePayNow} noValidate className="card">
                    <div className="card-header">
                        <h2 className="card-title">Payment Details</h2>
                    </div>

                    <Alert type="error" message={allErrors.amount || allErrors.swift_code} />

                    {/* Amount + Currency row */}
                    <div className="field-row">
                        <FormField
                            label="Amount"
                            name="amount"
                            value={data.amount}
                            onChange={handleChange}
                            error={allErrors.amount}
                            placeholder="0.00"
                            hint="Max R9,999,999.99"
                        />
                        <div className="form-field">
                            <label htmlFor="currency" className="form-label">Currency</label>
                            <select
                                id="currency"
                                name="currency"
                                value={data.currency}
                                onChange={handleChange}
                                className="form-input form-select"
                            >
                                {currencies.map(c => <option key={c} value={c}>{c}</option>)}
                            </select>
                        </div>
                    </div>

                    <div className="form-field">
                        <label htmlFor="provider" className="form-label">Payment Provider</label>
                        <select
                            id="provider"
                            name="provider"
                            value={data.provider}
                            onChange={handleChange}
                            className="form-input form-select"
                        >
                            {providers.map(p => <option key={p} value={p}>{p}</option>)}
                        </select>
                    </div>

                    <hr className="form-divider" />
                    <p className="section-label">Beneficiary Information</p>

                    <FormField
                        label="Beneficiary Full Name"
                        name="beneficiary_name"
                        value={data.beneficiary_name}
                        onChange={handleChange}
                        error={allErrors.beneficiary_name}
                        placeholder="Name as it appears on the account"
                        autoComplete="off"
                    />

                    <FormField
                        label="Beneficiary Account Number"
                        name="beneficiary_account"
                        value={data.beneficiary_account}
                        onChange={handleChange}
                        error={allErrors.beneficiary_account}
                        placeholder="IBAN / Account Number"
                        hint="4–34 uppercase alphanumeric characters"
                        maxLength={34}
                        autoComplete="off"
                    />

                    <FormField
                        label="SWIFT / BIC Code"
                        name="swift_code"
                        value={data.swift_code}
                        onChange={handleChange}
                        error={allErrors.swift_code}
                        placeholder="e.g. ABCDZA22"
                        hint="8 or 11 characters — ISO 9362 format"
                        maxLength={11}
                        autoComplete="off"
                        pattern="[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?"
                    />

                    <button
                        type="submit"
                        className="btn btn--primary btn--full btn--pay"
                        disabled={!isValid() || processing}
                    >
                        Review &amp; Pay Now →
                    </button>
                </form>

                {/* Info sidebar */}
                <aside className="info-panel">
                    <div className="info-card">
                        <h3>🔒 Security</h3>
                        <p>All transactions are encrypted with TLS 1.3 and reviewed by our compliance team before reaching SWIFT.</p>
                    </div>
                    <div className="info-card">
                        <h3>📋 SWIFT Codes</h3>
                        <p>SWIFT/BIC codes are 8 or 11 characters. You can find this on your beneficiary's bank statements or ask their bank directly.</p>
                    </div>
                    <div className="info-card">
                        <h3>⏱ Processing</h3>
                        <p>International payments are typically processed within 1–3 business days after compliance verification.</p>
                    </div>
                </aside>
            </div>
        </PortalLayout>
    );
}
