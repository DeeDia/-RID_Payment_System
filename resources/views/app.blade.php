<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Security meta tags --}}
    {{-- X-Frame-Options and CSP are set via SecurityHeaders middleware --}}
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    <title inertia>{{ config('app.name', 'NexusPay') }}</title>

    {{-- Fonts preloaded for performance --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Favicon --}}
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    {{-- Vite assets (JS + CSS) --}}
    
    @viteReactRefresh
    @vite('resources/js/app.jsx')
    

    {{-- Inertia head --}}
    @inertiaHead
</head>
<body class="antialiased">
    {{--
        Inertia root div — all React rendering happens here.
        The @inertia directive outputs the page component data as a JSON attribute.
        No raw user data is printed directly into the template (XSS prevention).
    --}}
    @inertia
</body>
</html>
