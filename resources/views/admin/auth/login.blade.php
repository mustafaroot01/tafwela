<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | لوحة تحكم تفويلة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        * { font-family: 'Cairo', sans-serif; }
        .shadow-materio { box-shadow: 0 0.25rem 1.125rem 0 rgba(75, 70, 92, 0.1); }
        .bg-materio { background-color: #F8F7FA; }
        .btn-primary { 
            background-color: #7367F0; 
            box-shadow: 0 0.125rem 0.25rem 0 rgba(115, 103, 240, 0.4); 
            transition: all 0.2s ease-in-out;
        }
        .btn-primary:hover { 
            background-color: #695ee0; 
            box-shadow: 0 0.25rem 0.5rem 0 rgba(115, 103, 240, 0.4);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="antialiased min-h-screen bg-materio flex items-center justify-center p-4">

    <div class="w-full max-w-[400px]">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-white rounded-xl shadow-materio flex items-center justify-center mx-auto mb-4 border border-slate-100">
                <i class="ti ti-gas-station text-4xl text-[#7367F0]"></i>
            </div>
            <h1 class="text-3xl font-black text-[#2F2B3D] tracking-tight">تفويلة</h1>
            <p class="text-sm text-secondary font-bold opacity-60 mt-1">لوحة التحكم الإدارية</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-xl shadow-materio border border-slate-100 p-8">
            <div class="mb-8">
                <h2 class="text-xl font-bold text-[#2F2B3D]">مرحباً بك مجدداً! 👋</h2>
                <p class="text-xs text-secondary font-medium mt-1">الرجاء تسجيل الدخول للوصول إلى لوحة التحكم</p>
            </div>

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D] uppercase">البريد الإلكتروني</label>
                    <div class="relative">
                        <i class="ti ti-mail absolute inset-y-0 right-3 my-auto flex items-center text-secondary opacity-40 text-lg"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus dir="ltr"
                               class="w-full pr-10 pl-4 py-2.5 border border-slate-200 rounded-lg text-sm text-[#2F2B3D] focus:outline-none focus:border-[#7367F0] transition"
                               placeholder="admin@tafwela.com">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D] uppercase">كلمة المرور</label>
                    <div class="relative">
                        <i class="ti ti-lock absolute inset-y-0 right-3 my-auto flex items-center text-secondary opacity-40 text-lg"></i>
                        <input type="password" name="password" required dir="ltr"
                               class="w-full pr-10 pl-4 py-2.5 border border-slate-200 rounded-lg text-sm text-[#2F2B3D] focus:outline-none focus:border-[#7367F0] transition"
                               placeholder="••••••••">
                    </div>
                </div>

                @if($errors->any())
                    <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-100 text-red-600 rounded-lg text-[11px] font-bold">
                        <i class="ti ti-alert-circle text-lg"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <button type="submit" class="w-full py-3 btn-primary text-white font-bold rounded-lg text-sm transition">
                    تسجيل الدخول
                </button>
            </form>
        </div>

        <div class="text-center mt-8">
            <p class="text-xs text-secondary font-bold opacity-40">
                &copy; {{ date('Y') }} جميع الحقوق محفوظة لمنصة تفويلة
            </p>
        </div>
    </div>

</body>
</html>
