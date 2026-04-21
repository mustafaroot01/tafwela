@extends('admin.layouts.app')
@section('title', 'إعدادات الحساب')

@section('content')

{{-- Header --}}
<div class="flex items-center gap-4 mb-6">
    <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center shadow-sm">
        <i class="ti ti-user-circle text-3xl"></i>
    </div>
    <div>
        <h1 class="text-[#2F2B3D] text-2xl font-bold">الملف الشخصي</h1>
        <p class="text-secondary text-sm">إدارة بيانات حسابك الشخصي وكلمة المرور</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left Sidebar: Avatar & Quick Info --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="materio-card p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-primary to-primary-dark opacity-10"></div>
            
            <div class="relative z-10">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&size=150&background=7367F0&color=fff&bold=true"
                     class="w-32 h-32 rounded-full border-4 border-white shadow-lg mx-auto mb-4" alt="avatar">
                <h3 class="text-xl font-bold text-[#2F2B3D]">{{ Auth::user()->name ?? 'مدير النظام' }}</h3>
                <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-[10px] font-bold uppercase tracking-wider">Super Admin</span>
                
                <div class="mt-8 pt-6 border-t border-slate-100 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-xs text-secondary font-bold mb-1 opacity-60 uppercase">رقم الهاتف</p>
                        <p class="text-sm font-bold text-[#2F2B3D] font-mono">{{ Auth::user()->phone }}</p>
                    </div>
                    <div class="text-center border-r border-slate-100">
                        <p class="text-xs text-secondary font-bold mb-1 opacity-60 uppercase">تاريخ الانضمام</p>
                        <p class="text-sm font-bold text-[#2F2B3D]">{{ Auth::user()->created_at->format('Y-m-d') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Content: Forms --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Basic Information Form --}}
        <div class="materio-card p-8">
            <div class="flex items-center gap-4 mb-8 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 bg-info/10 text-info rounded-lg flex items-center justify-center">
                    <i class="ti ti-id text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-[#2F2B3D]">البيانات الأساسية</h3>
            </div>

            <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-[#2F2B3D] uppercase tracking-wide opacity-70">الاسم الكامل</label>
                        <input type="text" name="name" value="{{ Auth::user()->name }}"
                               class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm transition-all focus:ring-4 focus:ring-primary/5">
                    </div>
                    <div class="flex flex-col gap-1.5 opacity-60">
                        <label class="text-xs font-bold text-[#2F2B3D] uppercase tracking-wide opacity-70">البريد الإلكتروني (المعرف)</label>
                        <input type="text" value="{{ Auth::user()->phone }}" disabled
                               class="w-full px-4 py-3 border border-slate-100 bg-slate-50 rounded-lg text-sm cursor-not-allowed font-mono">
                        <p class="text-[10px] text-secondary mt-1">لا يمكن تغيير البريد الإلكتروني للمسؤول حالياً</p>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn-primary !px-8 !py-3 flex items-center gap-2 shadow-lg shadow-primary/20">
                        <i class="ti ti-device-floppy text-lg"></i> حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>

        {{-- Password Change Form --}}
        <div class="materio-card p-8">
            <div class="flex items-center gap-4 mb-8 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 bg-warning/10 text-warning rounded-lg flex items-center justify-center">
                    <i class="ti ti-lock-password text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-[#2F2B3D]">تغيير كلمة المرور</h3>
            </div>

            <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-[#2F2B3D] uppercase tracking-wide opacity-70">كلمة المرور الجديدة</label>
                        <input type="password" name="password" 
                               class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm transition-all focus:ring-4 focus:ring-primary/5"
                               placeholder="••••••••">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-[#2F2B3D] uppercase tracking-wide opacity-70">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" 
                               class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm transition-all focus:ring-4 focus:ring-primary/5"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn-primary !bg-secondary !px-8 !py-3 flex items-center gap-2 hover:!bg-slate-700">
                        <i class="ti ti-key text-lg"></i> تحديث كلمة المرور
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
