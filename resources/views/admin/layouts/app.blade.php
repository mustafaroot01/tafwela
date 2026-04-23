<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | تفويلة</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script src="https://unpkg.com/unpoly@3.1.0/unpoly.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/unpoly@3.1.0/unpoly.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#7367F0', dark: '#675DD8', light: '#E7E5FC' },
                        secondary: '#808390',
                        success: '#28C76F',
                        info: '#00BAD1',
                        warning: '#FF9F43',
                        error: '#FF4C51',
                        background: '#F8F7FA',
                        surface: '#FFFFFF',
                    },
                    boxShadow: { 'materio': '0 0.375rem 1.125rem 0 rgba(47, 43, 61, 0.14)' }
                }
            }
        };

        document.addEventListener('alpine:init', () => {
            Alpine.store('toast', {
                show: false, message: '', type: 'success',
                display(msg, type = 'success') {
                    this.message = msg; this.type = type; this.show = true;
                    setTimeout(() => { this.show = false; }, 3000);
                }
            });
        });
    </script>

    <style>
        * { font-family: 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
        body { background-color: #F8F7FA; color: #2F2B3D; margin: 0; padding: 0; overflow-x: hidden; }
        .nav-item { margin: 0.125rem 0.75rem; padding: 0.55rem 1rem; border-radius: 0.375rem; color: #2F2B3D; font-size: 0.875rem; transition: all 0.2s; display: flex; align-items: center; gap: 0.75rem; font-weight: 500; }
        .nav-item:hover { background-color: #F1F0F2; }
        .nav-active { background: linear-gradient(72.47deg, #7367F0 22.16%, rgba(115, 103, 240, 0.7) 76.47%) !important; color: #FFFFFF !important; box-shadow: 0px 2px 6px rgba(115, 103, 240, 0.3) !important; }
        .sidebar-section-title { color: #A5A3AE; font-size: 0.75rem; margin: 1.25rem 1.25rem 0.5rem 1.25rem; display: flex; align-items: center; font-weight: 600; }
        .sidebar-section-title::after { content: ""; flex: 1; height: 1px; background: #DBDADE; margin-right: 0.75rem; opacity: 0.5; }
        .materio-card { background: #FFFFFF; border-radius: 0.375rem; box-shadow: 0 0.375rem 1.125rem 0 rgba(47, 43, 61, 0.1); }
        .btn-primary { background-color: #7367F0; color: #fff; padding: 0.5rem 1.25rem; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); border: none; cursor: pointer; }
        .btn-primary:active { transform: scale(0.95); }
        .action-form { display: inline-flex; margin: 0; padding: 0; align-items: center; vertical-align: middle; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .action-btn:active { transform: scale(0.85); }
        .up-progress-bar { background-color: #7367F0 !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="antialiased" 
      x-data="{ sidebarOpen: true, confirmOpen: false, confirmMsg: '', confirmAction: null, profileOpen: false }"
      @open-confirm.window="confirmMsg = $event.detail.message; confirmAction = $event.detail.action; confirmOpen = true">

<div class="flex min-h-screen relative bg-[#F8F7FA]">

    <aside :class="sidebarOpen ? 'translate-x-0 w-[260px]' : 'translate-x-full w-0'"
           class="fixed inset-y-0 right-0 z-50 bg-white border-l border-slate-100 flex flex-col transition-all duration-300 shadow-lg overflow-hidden">
        <div class="p-6 flex items-center gap-3">
            <div class="w-8 h-8 bg-primary rounded flex items-center justify-center shadow-sm">
                <i class="ti ti-gas-station text-white text-xl"></i>
            </div>
            <span class="text-[#2F2B3D] font-bold text-xl tracking-tight">تفويلة</span>
        </div>
        <nav class="flex-1 overflow-y-auto pb-4" id="sidebar-nav">
            @php
                $groups = [
                    'الرئيسية' => [['route' => 'admin.dashboard', 'icon' => 'ti-smart-home', 'label' => 'لوحة التحكم']],
                    'إدارة العمليات' => [
                        ['route' => 'admin.stations.index', 'icon' => 'ti-gas-station', 'label' => 'المحطات'],
                        ['route' => 'admin.updates.index',  'icon' => 'ti-refresh', 'label' => 'تحديثات الوقود'],
                        ['route' => 'admin.reports.index',  'icon' => 'ti-alert-circle', 'label' => 'التبليغات'],
                    ],
                    'إدارة المستخدمين' => [
                        ['route' => 'admin.users.index',     'icon' => 'ti-users', 'label' => 'المستخدمين'],
                        ['route' => 'admin.employees.index', 'icon' => 'ti-user-shield', 'label' => 'الموظفين'],
                    ],
                    'النظام' => [
                        ['route' => 'admin.notifications.index', 'icon' => 'ti-bell-ringing', 'label' => 'الإشعارات'],
                        ['route' => 'admin.settings.index',      'icon' => 'ti-settings', 'label' => 'الإعدادات'],
                    ],
                ];
            @endphp
            @foreach($groups as $label => $items)
                <div class="sidebar-section-title">{{ $label }}</div>
                @foreach($items as $item)
                    <a href="{{ route($item['route']) }}" up-follow up-target="#main, #sidebar-nav"
                       class="nav-item {{ request()->routeIs($item['route']) ? 'nav-active' : '' }}">
                        <i class="ti {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>
    </aside>

    <div :class="sidebarOpen ? 'mr-[260px]' : 'mr-0'" class="flex-1 flex flex-col transition-all duration-300 min-h-screen">
        <header class="h-[64px] bg-white border-b border-slate-100 sticky top-0 z-40 px-6 flex items-center justify-between w-full shadow-sm">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="text-secondary hover:text-primary transition-colors">
                    <i class="ti ti-menu-2 text-2xl"></i>
                </button>
                <div class="text-sm font-bold text-[#2F2B3D]">@yield('title')</div>
            </div>
            
            {{-- User Dropdown Area --}}
            <div class="relative">
                <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="flex items-center gap-3 hover:bg-slate-50 p-1.5 rounded-lg transition">
                    <div class="text-left hidden md:block">
                        <p class="text-sm font-bold text-[#2F2B3D] leading-tight text-left">{{ Auth::user()->name ?? 'المدير' }}</p>
                        <p class="text-[10px] text-secondary text-left font-medium">مدير النظام</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=7367F0&color=fff&bold=true"
                         class="w-9 h-9 rounded-full border border-slate-100 shadow-sm" alt="avatar">
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="profileOpen" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-materio border border-slate-100 py-2 z-50">
                    <a href="{{ route('admin.profile.edit') }}" up-follow up-target="#main" class="flex items-center gap-2 px-4 py-2 text-sm text-[#2F2B3D] hover:bg-slate-50 transition">
                        <i class="ti ti-user-circle text-lg opacity-60"></i>
                        <span>ملفي الشخصي</span>
                    </a>
                    <a href="{{ route('admin.settings.index') }}" up-follow up-target="#main" class="flex items-center gap-2 px-4 py-2 text-sm text-[#2F2B3D] hover:bg-slate-50 transition">
                        <i class="ti ti-settings text-lg opacity-60"></i>
                        <span>الإعدادات</span>
                    </a>
                    <div class="border-t border-slate-100 my-1"></div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-error hover:bg-red-50 transition">
                            <i class="ti ti-logout text-lg"></i>
                            <span>تسجيل الخروج</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6" up-main id="main">
            <div x-data x-init="
                @if(session('success')) setTimeout(() => $store.toast.display('{{ session('success') }}', 'success'), 100); @endif
                @if(session('error')) setTimeout(() => $store.toast.display('{{ session('error') }}', 'error'), 100); @endif
                @if($errors->any()) setTimeout(() => $store.toast.display('{{ $errors->first() }}', 'error'), 150); @endif
            "></div>
            @yield('content')
        </main>

        <footer class="px-6 py-4 text-center text-[11px] text-secondary border-t border-slate-100 bg-white">
            &copy; {{ date('Y') }} منصة تفويلة &mdash; الإدارة العامة
        </footer>
    </div>
</div>

{{-- Global Confirm Modal --}}
<div x-show="confirmOpen" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#171925]/60 backdrop-blur-sm">
    <div x-show="confirmOpen" x-transition class="bg-white rounded-lg shadow-materio w-full max-w-sm overflow-hidden p-8 text-center">
        <div class="w-16 h-16 bg-warning/10 text-warning rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ti ti-alert-triangle text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-[#2F2B3D] mb-2">هل أنت متأكد؟</h3>
        <p class="text-secondary text-sm mb-8" x-text="confirmMsg"></p>
        <div class="flex gap-3">
            <button @click="confirmAction && confirmAction(); confirmOpen = false" class="flex-1 py-2.5 bg-error text-white rounded-lg font-bold hover:bg-red-600 transition">نعم، استمر</button>
            <button @click="confirmOpen = false" class="flex-1 py-2.5 bg-slate-100 text-secondary rounded-lg font-bold hover:bg-slate-200 transition">إلغاء</button>
        </div>
    </div>
</div>

{{-- Floating Toasts --}}
<div x-data x-show="$store.toast.show" x-cloak x-transition class="fixed top-6 left-6 z-[120] max-w-sm w-full">
    <div :class="$store.toast.type === 'success' ? 'bg-success text-white' : 'bg-error text-white'"
         class="rounded-lg shadow-materio p-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <i :class="$store.toast.type === 'success' ? 'ti-circle-check' : 'ti-alert-circle'" class="ti text-2xl"></i>
            <span class="text-sm font-bold" x-text="$store.toast.message"></span>
        </div>
        <button @click="$store.toast.show = false" class="opacity-70 hover:opacity-100"><i class="ti ti-x text-lg"></i></button>
    </div>
</div>

@stack('scripts')
</body>
</html>
