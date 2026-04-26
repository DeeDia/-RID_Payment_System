// resources/js/Components/Alert.jsx

import React from 'react';

export default function Alert({ type = 'error', message }) {
    if (!message) return null;

    const icons = { error: '✕', success: '✓', warning: '⚠', info: 'ℹ' };

    return (
        <div className={`alert alert--${type}`} role="alert" aria-live="polite">
            <span className="alert-icon" aria-hidden="true">{icons[type]}</span>
            <span className="alert-message">{message}</span>
        </div>
    );
}
