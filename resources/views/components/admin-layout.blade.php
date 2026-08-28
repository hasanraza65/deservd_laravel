@props(['title' => 'Dashboard'])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · DESERV'D Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream-200 text-cocoa-900" x-data="{ sidebarOpen: false }">

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-30 w-64 shrink-0 -translate-x-full transform bg-cocoa-900 text-cream-100 transition-transform duration-200 lg:static lg:translate-x-0"
        :class="sidebarOpen && '-translate-x-0'"
    >
        <div class="flex h-16 items-center gap-2 border-b border-white/10 px-6">
            <span class="font-display text-lg font-black tracking-tight">DESERV'D</span>
            <span class="rounded bg-blush-500 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide">Admin</span>
        </div>

        <nav class="flex flex-col gap-1 p-3 text-sm">
            @php
                $navItems = [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
                    ['label' => 'Orders', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*'],
                    ['label' => 'Products', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
                    ['label' => 'Categories', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
                    ['label' => 'Customers', 'route' => 'admin.customers.index', 'active' => 'admin.customers.*'],
                    ['label' => 'Coupons', 'route' => 'admin.coupons.index', 'active' => 'admin.coupons.*'],
                    ['label' => 'Reviews', 'route' => 'admin.reviews.index', 'active' => 'admin.reviews.*'],
                    ['label' => 'Inventory', 'route' => 'admin.inventory.index', 'active' => 'admin.inventory.*'],
                    ['label' => 'Settings', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*'],
                ];
            @endphp

            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="rounded-md px-3 py-2.5 font-medium transition-colors {{ request()->routeIs($item['active']) ? 'bg-blush-500 text-white' : 'text-cream-300 hover:bg-white/5 hover:text-white' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="fixed inset-0 z-20 bg-black/40 lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

    {{-- Main column --}}
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Topbar --}}
        <header class="flex h-16 items-center justify-between border-b border-cocoa-900/10 bg-cream-50 px-4 sm:px-6">
            <button type="button" class="p-2 text-cocoa-700 lg:hidden" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle menu">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3.5 7h17M3.5 12h17M3.5 17h17"/></svg>
            </button>

            <h1 class="font-display text-lg font-extrabold uppercase tracking-tight text-cocoa-900">{{ $title }}</h1>

            <div class="flex items-center gap-3" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-cocoa-800 hover:bg-cream-200">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blush-500 text-xs font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->first_name ?? 'A', 0, 1)) }}
                    </span>
                    <span class="hidden sm:inline">{{ auth()->user()->name ?? 'Admin' }}</span>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-4 top-14 w-44 rounded-md border border-cocoa-900/10 bg-cream-50 py-1 shadow-lg sm:right-6">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-cocoa-700 hover:bg-cream-200">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @if (session('status'))
                <div class="mb-5 rounded-md border border-blush-200 bg-blush-50 px-4 py-3 text-sm font-medium text-blush-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
