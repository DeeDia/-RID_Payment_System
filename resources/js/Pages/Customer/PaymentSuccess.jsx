// resources/js/Pages/Customer/PaymentSuccess.jsx

import React from 'react';
import { Link } from '@inertiajs/react';
import PortalLayout from '@/Layouts/PortalLayout';

export default function PaymentSuccess({ user }) {
    return (
        <PortalLayout user={user} role="customer">
            <div className="success-page">
                <div className="success-icon" aria-hidden="true">✓</div>
                <h1 className="success-title">Payment Submitted</h1>
                <p className="success-body">
                    Your payment has been received and is pending review by our compliance team.
                    Once verified, it will be submitted to SWIFT for processing.
                    You will see the status update on your dashboard.
                </p>
                <div className="success-actions">
                    <Link href="/dashboard" className="btn btn--primary">View Dashboard</Link>
                    <Link href="/payment"   className="btn btn--ghost">New Payment</Link>
                </div>
            </div>
        </PortalLayout>
    );
}
