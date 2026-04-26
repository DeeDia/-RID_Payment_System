// resources/js/Layouts/PortalLayout.jsx

import React from 'react';
import { Link, router } from '@inertiajs/react';

export default function PortalLayout({ children, user, role = 'customer' }) {
    const handleLogout = () => {
        router.post('/logout');
    };

    return (
        <div className="portal-shell">
            <nav className="portal-nav">
                <div className="nav-brand">
                    <svg viewBox="0 0 40 40" fill="none">
                        <rect width="40" height="40" rx="8" fill="#0A2540"/>
                        <path d="M8 28V16l12-8 12 8v12H24v-7h-8v7H8z" fill="#00D4AA"/>
                    </svg>
                    <span>Nexus<strong>Pay</strong></span>
                </div>

                <div className="nav-links">
                    {role === 'customer' && (
                        <>
                            <Link href="/dashboard" className="nav-link">Dashboard</Link>
                            <Link href="/payment" className="nav-link nav-link--primary">New Payment</Link>
                        </>
                    )}
                    {role === 'employee' && (
                        <Link href="/employee/dashboard" className="nav-link">Transactions</Link>
                    )}
                </div>

                <div className="nav-user">
                    <span className="nav-user-name">{user?.full_name}</span>
                    <span className={`nav-badge nav-badge--${role}`}>
                        {role === 'employee' ? '🏦 Staff' : '👤 Customer'}
                    </span>
                    <button onClick={handleLogout} className="nav-logout">Sign Out</button>
                </div>
            </nav>

            <main className="portal-main">
                {children}
            </main>
        </div>
    );
}
