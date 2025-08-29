<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ThumbnailForge') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div style="max-width:600px;margin:2rem auto;padding:2rem;background:#fff;border-radius:12px;box-shadow:0 2px 16px #dbeafe33;">
        <h1 style="font-size:2rem;font-weight:700;margin-bottom:1.5rem;">Welcome to {{ config('app.name', 'ThumbnailForge') }}</h1>
        <h2 style="font-size:1.2rem;font-weight:600;margin-bottom:1rem;">Demo Users (from seeder):</h2>
        <table style="width:100%;margin-bottom:2rem;background:#f9fafb;border-radius:8px;overflow:hidden;">
            <thead style="background:#f1f5f9;">
                <tr>
                    <th style="padding:0.5rem 1rem;text-align:left;">Name</th>
                    <th style="padding:0.5rem 1rem;text-align:left;">Email</th>
                    <th style="padding:0.5rem 1rem;text-align:left;">Tier</th>
                    <th style="padding:0.5rem 1rem;text-align:left;">Password</th>
                </tr>
            </thead>
            <tbody>
                <tr><td style="padding:0.5rem 1rem;">Free User</td><td style="padding:0.5rem 1rem;">free@example.com</td><td style="padding:0.5rem 1rem;">free</td><td style="padding:0.5rem 1rem;">password</td></tr>
                <tr><td style="padding:0.5rem 1rem;">Pro User</td><td style="padding:0.5rem 1rem;">pro@example.com</td><td style="padding:0.5rem 1rem;">pro</td><td style="padding:0.5rem 1rem;">password</td></tr>
                <tr><td style="padding:0.5rem 1rem;">Enterprise User</td><td style="padding:0.5rem 1rem;">enterprise@example.com</td><td style="padding:0.5rem 1rem;">enterprise</td><td style="padding:0.5rem 1rem;">password</td></tr>
            </tbody>
        </table>
        @auth
            <a href="{{ route('thumbnail.create') }}" style="display:inline-block;margin-bottom:1rem;padding:0.75rem 2rem;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Create Thumbnail Request</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline-block;margin-left:1rem;">
                @csrf
                <button type="submit" style="background:none;border:none;color:#2563eb;text-decoration:underline;cursor:pointer;">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" style="display:inline-block;padding:0.75rem 2rem;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Login</a>
        @endauth
    </div>
</body>
</html>