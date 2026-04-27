// resources/js/Pages/Employee/Dashboard.jsx

import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import PortalLayout from '@/Components/PortalLayout';
import Alert from '@/Components/Alert';

const STATUS_STYLES = {
    pending:            { label: 'Pending',          className: 'badge badge--pending' },
    verified:           { label: 'Verified',         className: 'badge badge--verified' },
    submitted_to_swift: { label: 'Sent to SWIFT',    className: 'badge badge--success' },
};

export default function EmployeeDashboard({ employee, transactions, flash }) {
    const [verifying, setVerifying]     = useState(null);
    const [submitting, setSubmitting]   = useState(false);

    const pending  = transactions.filter(t => t.status === 'pending');
    const verified = transactions.filter(t => t.status === 'verified');
    const sent     = transactions.filter(t => t.status === 'submitted_to_swift');

    const handleVerify = (id) => {
        setVerifying(id);
        router.post(`/employee/transactions/${id}/verify`, {}, {
            onFinish: () => setVerifying(null),
            preserveScroll: true,
        });
    };

    const handleSubmitToSwift = () => {
        if (!window.confirm(`Submit ${verified.length} verified transaction(s) to SWIFT? This action cannot be undone.`)) return;
        setSubmitting(true);
        router.post('/employee/submit-swift', {}, {
            onFinish: () => setSubmitting(false),
            preserveScroll: true,
        });
    };

    return (
        <PortalLayout user={employee} role="employee">
            <div className="page-header">
                <div>
                    <h1 className="page-title">Transactions Portal</h1>
                    <p className="page-subtitle">Employee: {employee.full_name} &bull; ID: {employee.employee_id}</p>
                </div>

                {verified.length > 0 && (
                    <button
                        className="btn btn--primary btn--swift"
                        onClick={handleSubmitToSwift}
                        disabled={submitting}
                    >
                        {submitting ? 'Submitting…' : `Submit ${verified.length} to SWIFT →`}
                    </button>
                )}
            </div>

            <Alert type="success" message={flash?.success} />
            <Alert type="error"   message={flash?.error} />

            {/* Stats row */}
            <div className="stats-row">
                <div className="stat-card stat-card--pending">
                    <span className="stat-number">{pending.length}</span>
                    <span className="stat-label">Awaiting Review</span>
                </div>
                <div className="stat-card stat-card--verified">
                    <span className="stat-number">{verified.length}</span>
                    <span className="stat-label">Verified</span>
                </div>
                <div className="stat-card stat-card--sent">
                    <span className="stat-number">{sent.length}</span>
                    <span className="stat-label">Sent to SWIFT</span>
                </div>
            </div>

            {/* Transactions table */}
            <section className="card">
                <div className="card-header">
                    <h2 className="card-title">All Transactions</h2>
                    <p className="card-subtitle">Verify each entry before submitting to SWIFT</p>
                </div>

                {transactions.length === 0 ? (
                    <div className="empty-state">
                        <span className="empty-state-icon">📋</span>
                        <p>No transactions to review.</p>
                    </div>
                ) : (
                    <div className="table-wrapper">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Beneficiary</th>
                                    <th>Account</th>
                                    <th>SWIFT / BIC</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {transactions.map(txn => {
                                    const status = STATUS_STYLES[txn.status] || { label: txn.status, className: 'badge' };
                                    return (
                                        <tr key={txn.id} className={txn.status === 'verified' ? 'row--verified' : ''}>
                                            <td className="td-date">
                                                {new Date(txn.created_at).toLocaleDateString('en-ZA')}
                                            </td>
                                            <td>
                                                <div className="td-customer">
                                                    {/* React escapes all values — XSS safe */}
                                                    <strong>{txn.customer?.full_name}</strong>
                                                    <small>Acc: •••{txn.customer?.account_number?.slice(-4)}</small>
                                                </div>
                                            </td>
                                            <td>{txn.beneficiary_name}</td>
                                            <td className="td-mono">{txn.beneficiary_account}</td>
                                            <td className="td-mono td-swift">{txn.swift_code}</td>
                                            <td className="td-amount">
                                                <strong>{txn.currency}</strong>{' '}
                                                {parseFloat(txn.amount).toLocaleString('en-ZA', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td>
                                                <span className={status.className}>{status.label}</span>
                                            </td>
                                            <td>
                                                {txn.status === 'pending' && (
                                                    <button
                                                        className="btn btn--verify"
                                                        onClick={() => handleVerify(txn.id)}
                                                        disabled={verifying === txn.id}
                                                        title="Mark as verified — confirm SWIFT code and account details are correct"
                                                    >
                                                        {verifying === txn.id ? '…' : '✓ Verified'}
                                                    </button>
                                                )}
                                                {txn.status === 'verified' && (
                                                    <span className="verified-mark">✓ Done</span>
                                                )}
                                                {txn.status === 'submitted_to_swift' && (
                                                    <span className="sent-mark">Sent</span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>

            {/* Audit notice */}
            <p className="audit-notice">
                🔒 All actions are logged in the immutable audit trail. Employee ID and timestamp are recorded on every verification and submission.
            </p>
        </PortalLayout>
    );
}
