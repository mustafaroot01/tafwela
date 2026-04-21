@extends('admin.layouts.app')
@section('title', isset($employee) ? 'تعديل بيانات موظف' : 'إضافة موظف جديد')

@section('content')

{{-- Header --}}
<div class="flex items-center gap-4 mb-6">
    <a href="{{ route('admin.employees.index') }}" class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-secondary hover:text-primary transition shadow-sm">
        <i class="ti ti-arrow-right text-xl"></i>
    </a>
    <div>
        <h1 class="text-[#2F2B3D] text-2xl font-bold">{{ isset($employee) ? 'تعديل بيانات الموظف' : 'إضافة موظف للمحطة' }}</h1>
        <p class="text-secondary text-sm">إدارة صلاحيات وبيانات حسابات موظفي المحطات</p>
    </div>
</div>

<div class="materio-card overflow-hidden">
    {{-- Decorative Top Bar --}}
    <div class="h-1.5 bg-gradient-to-r from-primary via-primary-dark to-primary"></div>

    <form action="{{ isset($employee) ? route('admin.employees.update', $employee) : route('admin.employees.store') }}" 
          method="POST" class="p-8 md:p-12">
        @csrf
        @if(isset($employee)) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-12 gap-y-8">
            
            {{-- Personal Info Section --}}
            <div class="space-y-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                        <i class="ti ti-user-edit text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#2F2B3D]">البيانات الشخصية</h3>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D] uppercase tracking-wide opacity-70">اسم الموظف</label>
                    <input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}"
                           placeholder="مثال: أحمد محمد"
                           class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm transition-all focus:ring-4 focus:ring-primary/5">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D] uppercase tracking-wide opacity-70">رقم الهاتف (الخاص بالدخول)</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone ?? $prefilledPhone ?? '') }}"
                           {{ isset($employee) ? 'disabled' : '' }}
                           placeholder="+964 7XX XXX XXXX" dir="ltr"
                           class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm transition-all focus:ring-4 focus:ring-primary/5 {{ isset($employee) ? 'bg-slate-50 cursor-not-allowed opacity-60' : '' }}">
                    @if(!isset($employee))
                        <p class="text-[10px] text-secondary mt-1">سيتم استخدام هذا الرقم لتسجيل الدخول إلى تطبيق الموظفين.</p>
                    @endif
                </div>
            </div>

            {{-- Assignment Section --}}
            <div class="space-y-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-warning/10 text-warning rounded-lg flex items-center justify-center">
                        <i class="ti ti-building-community text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#2F2B3D]">التعيين للمحطة</h3>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D] uppercase tracking-wide opacity-70">المحطة التابع لها</label>
                    <select name="station_id" class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm bg-white transition-all focus:ring-4 focus:ring-primary/5">
                        <option value="">اختر المحطة...</option>
                        @foreach($stations as $station)
                            @php
                                $displayName = $station->name_ar ?: ($station->name ?: "محطة #{$station->id}");
                                $displayCity = $station->city ? " ({$station->city})" : "";
                            @endphp
                            <option value="{{ $station->id }}" {{ (old('station_id', $employee->station_id ?? $prefilledStation ?? '') == $station->id) ? 'selected' : '' }}>
                                {{ $displayName }}{{ $displayCity }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="p-6 bg-slate-50 rounded-xl border border-slate-100 mt-4">
                    <div class="flex items-start gap-3">
                        <i class="ti ti-info-circle text-2xl text-info"></i>
                        <div>
                            <h4 class="text-sm font-bold text-[#2F2B3D] mb-1">صلاحيات الموظف</h4>
                            <p class="text-xs text-secondary leading-relaxed">
                                سيتمكن الموظف من تحديث حالة الوقود (متوفر، محدود، نافذ) للمحطة المعينة له فقط عبر تطبيق الهاتف.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-100 flex items-center gap-4">
            <button type="submit" class="btn-primary !px-12 !py-3.5 flex items-center gap-2 shadow-lg shadow-primary/20">
                <i class="ti ti-device-floppy text-xl"></i>
                {{ isset($employee) ? 'حفظ التعديلات' : 'إنشاء حساب الموظف' }}
            </button>
            <a href="{{ route('admin.employees.index') }}" class="px-8 py-3.5 bg-slate-100 text-secondary rounded-lg font-bold hover:bg-slate-200 transition text-sm">
                إلغاء
            </a>
        </div>
    </form>
</div>

@endsection
