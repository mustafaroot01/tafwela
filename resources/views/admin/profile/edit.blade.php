@extends('admin.layouts.app')
@section('title', 'إعدادات الحساب')
@section('header', 'الملف الشخصي')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-black text-slate-800 text-lg">تحديث بيانات الحساب</h3>
            <p class="text-slate-400 text-sm mt-1">تعديل المعلومات الشخصية وكلمة المرور</p>
        </div>

        <form action="{{ route('admin.profile.update') }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('POST')

            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700">الاسم الكامل</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                @error('name') <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                @error('email') <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    تغيير كلمة المرور
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">كلمة المرور الجديدة</label>
                        <input type="password" name="password"
                               class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation"
                               class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-2 font-semibold">اتركه فارغاً إذا كنت لا ترغب في تغيير كلمة المرور</p>
                @error('password') <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-6 flex justify-end">
                <button type="submit"
                        class="flex items-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-2xl transition shadow-lg shadow-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    حفظ التغييرات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
