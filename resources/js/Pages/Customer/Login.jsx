// resources/js/Pages/Customer/Login.jsx

import React from 'react';
import { useForm, Link } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import FormField from '@/Components/FormField';
import Alert from '@/Components/Alert';

export default function Login({ flash }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        account_number: '',
        password:       '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/login', {
            onError: () => reset('password'),
        });
    };

    return (
        <AuthLayout
            title="Welcome Back"
            subtitle="Sign in to your international payments account"
        >
            <form onSubmit={handleSubmit} noValidate>
                {/* Session expiry / throttle messages */}
                <Alert type="error"   message={errors.throttle} />
                <Alert type="error"   message={errors.session} />
                <Alert type="error"   message={errors.credentials} />
                <Alert type="success" message={flash?.success} />

                <FormField
                    label="Account Number"
                    name="account_number"
                    value={data.account_number}
                    onChange={e => setData('account_number', e.target.value)}
                    error={errors.account_number}
                    placeholder="Your bank account number"
                    maxLength={16}
                    pattern="\d{6,16}"
                    autoComplete="username"
                />

                <FormField
                    label="Password"
                    name="password"
                    type="password"
                    value={data.password}
                    onChange={e => setData('password', e.target.value)}
                    error={errors.password}
                    autoComplete="current-password"
                    maxLength={72}
                />

                <button
                    type="submit"
                    className="btn btn--primary btn--full"
                    disabled={processing}
                >
                    {processing ? 'Signing In…' : 'Sign In'}
                </button>

                <p className="form-footer">
                    New customer?{' '}
                    <Link href="/register" className="form-link">Create an account</Link>
                </p>

                <p className="form-footer form-footer--small">
                    Bank employee?{' '}
                    <Link href="/employee/login" className="form-link">Staff portal →</Link>
                </p>
            </form>
        </AuthLayout>
    );
}
