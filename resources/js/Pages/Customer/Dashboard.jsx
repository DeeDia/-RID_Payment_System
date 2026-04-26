// resources/js/Pages/Customer/Dashboard.jsx

import React from 'react';
import { Link } from '@inertiajs/react';
import PortalLayout from '@/Components/PortalLayout';

const STATUS_STYLES = {
    pending:           { label: 'Pending Review',    className: 'badge badge--pending' },
    verified:          { label: 'Verified',          className: 'badge badge--verified' },
    submitted_to_swift:{ label: 'Submitted to SWIFT', className: 'badge badge--success' },
};

export default function Dashboard({ user, transactions }) {
    return (
        <PortalLayout user={user} role="customer">
            <div className="page-header">
                <div>
                    <h1 className="page-title">Good day, {user.full_name.split(' ')[0]}</h1>
                    <p className="page-subtitle">Account: •••• {user.account_number.slice(-4)}</p>
                </div>
                <Link href="/payment" className="btn btn--primary">
                    + New Payment
                </Link>
            </div>

            <section className="card">
                <div className="card-header">
                    <h2 className="card-title">Recent Transactions</h2>
                </div>

                {transactions.length === 0 ? (
                    <div className="empty-state">
                        <span className="empty-state-icon">💸</span>
                        <p>No transactions yet.</p>
                        <Link href="/payment" className="btn btn--secondary">Make your first payment</Link>
                    </div>
                ) : (
                    <div className="table-wrapper">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Beneficiary</th>
                                    <th>SWIFT Code</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {transactions.map(txn => {
                                    const status = STATUS_STYLES[txn.status] || { label: txn.status, className: 'badge' };
                                    return (
                                        <tr key={txn.id}>
                                            <td className="td-date">
                                                {new Date(txn.created_at).toLocaleDateString('en-ZA')}
                                            </td>
                                            <td>{txn.beneficiary_name}</td>
                                            {/* Output encoding: React escapes all string values by default — XSS safe */}
                                            <td className="td-mono">{txn.swift_code}</td>
                                            <td className="td-amount">
                                                {txn.currency} {parseFloat(txn.amount).toLocaleString('en-ZA', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td>
                                                <span className={status.className}>{status.label}</span>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>
        </PortalLayout>
    );
}
