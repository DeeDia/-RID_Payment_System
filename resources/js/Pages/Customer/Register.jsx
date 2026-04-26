// resources/js/Pages/Customer/Register.jsx

import React, { useState } from 'react';
import { useForm, Link } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import FormField from '@/Components/FormField';
import Alert from '@/Components/Alert';

// ── Client-side RegEx mirrors (match server-side Laravel rules exactly) ──────
const PATTERNS = {
    full_name:      /^[A-Za-z\s\-']{2,80}$/,
    id_number:      /^\d{13}$/,
    account_number: /^\d{6,16}$/,
    // At least: 1 uppercase, 1 lowercase, 1 digit, 1 special char, 8-72 chars
    password:       /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,72}$/,
};

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        full_name:              '',
        id_number:              '',
        account_number:         '',
        password:               '',
        password_confirmation:  '',
    });

    const [clientErrors, setClientErrors] = useState({});

    // Client-side whitelist validation (defense-in-depth)
    const validateField = (name, value) => {
        const errs = { ...clientErrors };

        if (name === 'full_name' && value && !PATTERNS.full_name.test(value)) {
            errs.full_name = 'Only letters, spaces, hyphens and apostrophes allowed.';
        } else if (name === 'id_number' && value && !PATTERNS.id_number.test(value)) {
            errs.id_number = 'Must be exactly 13 digits.';
        } else if (name === 'account_number' && value && !PATTERNS.account_number.test(value)) {
            errs.account_number = 'Must be 6–16 digits.';
        } else if (name === 'password' && value && !PATTERNS.password.test(value)) {
            errs.password = 'Must include uppercase, lowercase, number and special character (min 8 chars).';
        } else if (name === 'password_confirmation' && value && value !== data.password) {
            errs.password_confirmation = 'Passwords do not match.';
        } else {
            delete errs[name];
        }

        setClientErrors(errs);
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setData(name, value);
        validateField(name, value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        // Run all validations before submit
        let hasErrors = false;
        Object.entries(data).forEach(([name, value]) => {
            validateField(name, value);
            if (clientErrors[name]) hasErrors = true;
        });

        if (hasErrors) return;

        post('/register', {
            onError: () => reset('password', 'password_confirmation'),
        });
    };

    const allErrors = { ...clientErrors, ...errors };

    return (
        <AuthLayout
            title="Open Your Account"
            subtitle="Register to access international payments"
        >
            <form onSubmit={handleSubmit} noValidate>
                <Alert type="error" message={allErrors.throttle} />

                <FormField
                    label="Full Name"
                    name="full_name"
                    value={data.full_name}
                    onChange={handleChange}
                    error={allErrors.full_name}
                    placeholder="e.g. John van der Berg"
                    hint="Letters, spaces, hyphens and apostrophes only"
                    autoComplete="name"
                />

                <FormField
                    label="South African ID Number"
                    name="id_number"
                    value={data.id_number}
                    onChange={handleChange}
                    error={allErrors.id_number}
                    placeholder="13-digit ID number"
                    hint="Exactly 13 digits — no spaces"
                    maxLength={13}
                    pattern="\d{13}"
                    autoComplete="off"
                />

                <FormField
                    label="Account Number"
                    name="account_number"
                    value={data.account_number}
                    onChange={handleChange}
                    error={allErrors.account_number}
                    placeholder="6–16 digit bank account number"
                    maxLength={16}
                    pattern="\d{6,16}"
                    autoComplete="off"
                />

                <FormField
                    label="Password"
                    name="password"
                    type="password"
                    value={data.password}
                    onChange={handleChange}
                    error={allErrors.password}
                    hint="Min 8 chars — uppercase, lowercase, number, and special character"
                    autoComplete="new-password"
                    maxLength={72}
                />

                <FormField
                    label="Confirm Password"
                    name="password_confirmation"
                    type="password"
                    value={data.password_confirmation}
                    onChange={handleChange}
                    error={allErrors.password_confirmation}
                    autoComplete="new-password"
                    maxLength={72}
                />

                <button
                    type="submit"
                    className="btn btn--primary btn--full"
                    disabled={processing || Object.keys(clientErrors).length > 0}
                >
                    {processing ? 'Creating Account…' : 'Create Account'}
                </button>

                <p className="form-footer">
                    Already registered?{' '}
                    <Link href="/login" className="form-link">Sign in</Link>
                </p>
            </form>
        </AuthLayout>
    );
}
