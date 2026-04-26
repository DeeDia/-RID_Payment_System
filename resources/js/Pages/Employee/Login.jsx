// resources/js/Pages/Employee/Login.jsx

import React from 'react';
import { useForm } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import FormField from '@/Components/FormField';
import Alert from '@/Components/Alert';

export default function EmployeeLogin() {
    const { data, setData, post, processing, errors, reset } = useForm({
        employee_id: '',
        password:    '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/employee/login', {
            onError: () => reset('password'),
        });
    };

    return (
        <AuthLayout
            title="Staff Portal"
            subtitle="For authorised bank employees only"
        >
            <div className="staff-notice" role="note">
                🏦 This portal is restricted to pre-registered bank employees. Unauthorised access is prohibited and logged.
            </div>

            <form onSubmit={handleSubmit} noValidate>
                <Alert type="error" message={errors.throttle} />
                <Alert type="error" message={errors.session} />
                <Alert type="error" message={errors.credentials} />

                <FormField
                    label="Employee ID"
                    name="employee_id"
                    value={data.employee_id}
                    onChange={e => setData('employee_id', e.target.value.toUpperCase())}
                    error={errors.employee_id}
                    placeholder="EMP00000"
                    hint="Format: EMP followed by 4–8 digits"
                    maxLength={12}
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
                    {processing ? 'Authenticating…' : 'Access Staff Portal'}
                </button>
            </form>
        </AuthLayout>
    );
}
