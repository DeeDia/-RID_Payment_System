// resources/js/Layouts/AuthLayout.jsx
// Shared layout for all authentication pages (login, register)

import React from 'react';

export default function AuthLayout({ children, title, subtitle }) {
    return (
        <div className="auth-shell">
            <div className="auth-brand">
                <div className="brand-mark">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="8" fill="#0A2540"/>
                        <path d="M8 28V16l12-8 12 8v12H24v-7h-8v7H8z" fill="#00D4AA"/>
                    </svg>
                </div>
                <span className="brand-name">Nexus<strong>Pay</strong></span>
            </div>

            <div className="auth-card">
                <div className="auth-card-header">
                    <h1 className="auth-title">{title}</h1>
                    {subtitle && <p className="auth-subtitle">{subtitle}</p>}
                </div>
                {children}
            </div>

            <p className="auth-footer">
                Protected by 256-bit TLS encryption &bull; NexusPay International &copy; {new Date().getFullYear()}
            </p>
        </div>
    );
}
