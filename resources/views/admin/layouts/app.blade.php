<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | تفويلة</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .nav-active { background-color: #2563eb !important; color: #fff !important; shadow: 0 10px 15px -3px rgb(37 99 235 / 0.2); }
        .nav-item { color: #94a3b8; transition: all .2s; }
        .nav-item:hover { background-color: rgba(255,255,255,.06); color: #e2e8f0; }
        .sidebar-group-label { color: #475569; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; padding: 0 1.25rem; margin-top: 1.75rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body class="antialiased bg-slate-100" x-data="{ sidebarOpen: true, confirmOpen: false, confirmMessage: '', confirmAction: null }">
<div class="flex min-h-screen">

    {{-- ── Sidebar ── --}}
    <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="fixed inset-y-0 right-0 z-50 bg-[#0f172a] flex flex-col transition-all duration-300 shadow-2xl overflow-hidden">

        {{-- Logo & Toggle --}}
        <div class="flex items-center justify-between px-5 h-16 border-b border-white/5 bg-[#0f172a]">
            <div class="flex items-center gap-3 overflow-hidden">
                <img src="{{ asset('assets/images/logo.png') }}" 
                     class="w-9 h-9 rounded-xl object-contain bg-white p-1 shadow-lg shadow-blue-900/20 flex-shrink-0" alt="logo">
                <span x-show="sidebarOpen" x-transition class="text-white font-black text-xl tracking-tight">تفويلة</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-white transition-colors">
                <svg class="w-5 h-5 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php
                $groups = [
                    'الرئيسية' => [
                        ['route' => 'admin.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'لوحة التحكم'],
                    ],
                    'إدارة العمليات' => [
                        ['route' => 'admin.stations.index', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'المحطات'],
                        ['route' => 'admin.updates.index',  'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'label' => 'تحديثات الوقود'],
                        ['route' => 'admin.reports.index',  'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'label' => 'التبليغات الواردة'],
                    ],
                    'إدارة المستخدمين' => [
                        ['route' => 'admin.users.index',     'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'قائمة المستخدمين'],
                        ['route' => 'admin.employees.index', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'label' => 'طاقم العمل (الموظفون)'],
                    ],
                    'النظام' => [
                        ['route' => 'admin.notifications.index', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'label' => 'إرسال إشعارات عامة'],
                        ['route' => 'admin.settings.index',      'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'إعدادات المنصة'],
                    ],
                ];
            @endphp

            @foreach($groups as $label => $items)
                <div x-show="sidebarOpen" class="sidebar-group-label">{{ $label }}</div>
                <div x-show="!sidebarOpen" class="h-4"></div>
                @foreach($items as $item)
                    <a href="{{ route($item['route']) }}"
                       class="nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold
                              {{ request()->routeIs($item['route']) ? 'nav-active' : '' }}">
                        <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center">
                            <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                            </svg>
                        </div>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>
    </aside>

    {{-- ── Main ── --}}
    <div :class="sidebarOpen ? 'mr-64' : 'mr-20'" class="flex-1 flex flex-col transition-all duration-300 min-h-screen">

        {{-- Header --}}
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-400">نظام الإدارة</span>
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span class="font-bold text-slate-700">@yield('title')</span>
            </div>
            
            {{-- Profile Dropdown --}}
            <div class="relative" x-data="{ userOpen: false }">
                <button @click="userOpen = !userOpen" @click.away="userOpen = false" class="flex items-center gap-3 group focus:outline-none">
                    <div class="text-right">
                        <p class="text-sm font-black text-slate-800 leading-tight group-hover:text-blue-600 transition-colors">{{ Auth::user()->name ?? 'المدير' }}</p>
                        <p class="text-[10px] text-blue-500 font-extrabold uppercase tracking-widest">مدير النظام</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=2563EB&color=fff&bold=true&size=80"
                         class="w-10 h-10 rounded-2xl border-2 border-slate-100 group-hover:border-blue-200 transition-all shadow-sm" alt="avatar">
                </button>

                <div x-show="userOpen" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:leave="transition ease-in duration-75" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden ring-1 ring-black/5 z-50">
                    <div class="p-2 space-y-1">
                        <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-blue-600 rounded-xl transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            إعدادات الحساب
                        </a>
                        <div class="border-t border-slate-50 my-1"></div>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-rose-500 hover:bg-rose-50 rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Title Bar --}}
        <div class="bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between">
            <h1 class="text-lg font-black text-slate-800">@yield('header', '@yield("title")')</h1>
        </div>

        {{-- Content --}}
        <main class="flex-1 p-6">
            {{-- Notification Modals --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-cloak
                     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 scale-95">
                    <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl text-center border border-slate-100">
                        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-2">تم بنجاح!</h3>
                        <p class="text-slate-500 text-sm mb-8 leading-relaxed">{{ session('success') }}</p>
                        <button @click="show = false" class="w-full py-3.5 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition shadow-lg shadow-slate-200">حسناً، فهمت</button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-cloak
                     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 scale-95">
                    <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl text-center border border-slate-100">
                        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-2">عذراً!</h3>
                        <p class="text-slate-500 text-sm mb-8 leading-relaxed">{{ session('error') }}</p>
                        <button @click="show = false" class="w-full py-3.5 bg-red-600 text-white font-bold rounded-2xl hover:bg-red-700 transition shadow-lg shadow-red-100">حاول مرة أخرى</button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 border-t border-slate-100 bg-white tracking-widest uppercase">
            &copy; {{ date('Y') }} منصة تفويلة &mdash; جميع الحقوق محفوظة
        </footer>
    </div>
</div>

{{-- ── Global Confirmation Modal ── --}}
<div x-show="confirmOpen" x-cloak 
     @open-confirm.window="confirmOpen = true; confirmMessage = $event.detail.message; confirmAction = $event.detail.action"
     class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-[2px]"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-end="opacity-0 translate-y-4">
    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-base font-black text-slate-800">تأكيد الإجراء</h3>
            </div>
            <p class="text-slate-600 text-sm leading-relaxed mb-6 font-semibold" x-text="confirmMessage"></p>
            <div class="flex gap-2">
                <button @click="confirmOpen = false; confirmAction()" 
                        class="flex-1 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition">نعم، استمرار</button>
                <button @click="confirmOpen = false" 
                        class="flex-1 py-2.5 bg-slate-100 text-slate-500 text-sm font-bold rounded-xl hover:bg-slate-200 transition">إلغاء</button>
            </div>
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
