// resources/js/Components/FormField.jsx
// Reusable input component with inline validation feedback

import React from 'react';

export default function FormField({
    label,
    name,
    type = 'text',
    value,
    onChange,
    error,
    hint,
    placeholder,
    required = true,
    autoComplete,
    maxLength,
    pattern,
}) {
    return (
        <div className={`form-field ${error ? 'form-field--error' : ''}`}>
            <label htmlFor={name} className="form-label">
                {label}
                {required && <span className="form-required" aria-hidden="true"> *</span>}
            </label>

            <input
                id={name}
                name={name}
                type={type}
                value={value}
                onChange={onChange}
                placeholder={placeholder}
                required={required}
                autoComplete={autoComplete}
                maxLength={maxLength}
                pattern={pattern}
                aria-describedby={error ? `${name}-error` : hint ? `${name}-hint` : undefined}
                aria-invalid={!!error}
                className="form-input"
            />

            {hint && !error && (
                <p id={`${name}-hint`} className="form-hint">{hint}</p>
            )}

            {error && (
                <p id={`${name}-error`} className="form-error" role="alert">
                    <span aria-hidden="true">⚠ </span>{error}
                </p>
            )}
        </div>
    );
}
