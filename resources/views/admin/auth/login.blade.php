<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login · DESERV'D</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-cocoa-900 px-4">

<div class="w-full max-w-sm rounded-lg bg-cream-50 p-8 shadow-xl">
    <div class="mb-6 text-center">
        <span class="font-display text-2xl font-black tracking-tight text-cocoa-900">DESERV'D</span>
        <p class="mt-1 text-xs font-bold uppercase tracking-widest text-blush-600">Admin Panel</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.attempt') }}" class="flex flex-col gap-4">
        @csrf
        <x-field label="Email" name="email" type="email" required autofocus autocomplete="username" />
        <x-field label="Password" name="password" type="password" required autocomplete="current-password" />

        <label class="flex items-center gap-2 text-sm text-cocoa-700">
            <input type="checkbox" name="remember" class="rounded border-cocoa-900/30">
            Remember me
        </label>

        <x-btn type="submit" class="w-full justify-center">Log In</x-btn>
    </form>
</div>

</body>
</html>
