@extends('admin.layouts.app')
@section('title', 'تعديل المحطة')

@section('content')
<div class="max-w-4xl mx-auto">
    
    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.stations.index') }}" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-secondary hover:text-primary transition shadow-sm">
                <i class="ti ti-arrow-right text-xl"></i>
            </a>
            <div>
                <h1 class="text-[#2F2B3D] text-2xl font-bold">تعديل بيانات المحطة</h1>
                <p class="text-secondary text-sm">تحديث معلومات الموقع، العناوين، وإدارة الموظفين</p>
            </div>
        </div>
        <div class="flex items-center gap-2 px-4 py-1.5 rounded-lg border {{ $station->is_active ? 'bg-success/10 border-success/20 text-success' : 'bg-slate-100 border-slate-200 text-secondary' }}">
            <div class="w-2 h-2 rounded-full {{ $station->is_active ? 'bg-success' : 'bg-secondary' }}"></div>
            <span class="text-[11px] font-bold uppercase tracking-wider">{{ $station->is_active ? 'نشطة حالياً' : 'معطلة' }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.stations.update', $station) }}" class="space-y-6">
        @csrf @method('PUT')
        
        <div class="materio-card p-10">
            @include('admin.stations._form')
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="btn-primary px-10 flex items-center gap-2">
                <i class="ti ti-device-floppy text-lg"></i> تحديث البيانات الأساسية
            </button>
            <a href="{{ route('admin.stations.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-secondary rounded-lg font-bold hover:bg-slate-50 transition">
                إلغاء
            </a>
        </div>
    </form>

    {{-- Admin Status Update (Materio Style) --}}
    <div class="materio-card mt-8 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3 bg-primary/5">
            <div class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">
                <i class="ti ti-bolt text-2xl"></i>
            </div>
            <div>
                <h3 class="text-[#2F2B3D] font-bold text-lg">تحديث الحالة الإداري (Admin Override)</h3>
                <p class="text-[11px] text-secondary">سيتم تطبيق الحالة فوراً وتوثيقها من قِبلك مباشرة</p>
            </div>
        </div>
        
        <form method="POST" action="{{ route('admin.stations.admin-update', $station) }}" class="p-8 space-y-6">
            @csrf
            @php
            $fuels = [
                'petrol_normal'   => ['label' => 'بنزين عادي', 'icon' => 'ti-gas-station'],
                'petrol_improved' => ['label' => 'بنزين محسّن', 'icon' => 'ti-gas-station'],
                'petrol_super'    => ['label' => 'بنزين سوبر', 'icon' => 'ti-gas-station'],
                'diesel'          => ['label' => 'ديزل', 'icon' => 'ti-truck-delivery'],
                'kerosene'        => ['label' => 'كيروسين', 'icon' => 'ti-droplet-filled'],
                'gas'             => ['label' => 'غاز', 'icon' => 'ti-flame'],
            ];
            $statuses = ['available' => 'متاح', 'limited' => 'محدود', 'unavailable' => 'غير متاح'];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($fuels as $key => $meta)
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D] flex items-center gap-1.5">
                        <i class="ti {{ $meta['icon'] }} text-primary"></i>
                        {{ $meta['label'] }}
                    </label>
                    <select name="{{ $key }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm bg-white">
                        <option value="">— بدون تغيير —</option>
                        @foreach($statuses as $val => $txt)
                            <option value="{{ $val }}" {{ ($station->status?->$key === $val) ? 'selected' : '' }}>{{ $txt }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pt-4 border-t border-slate-100">
                <div class="w-full md:w-64 flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D] flex items-center gap-1.5">
                        <i class="ti ti-users-group text-primary"></i>
                        حالة الازدحام
                    </label>
                    <select name="congestion" required class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm bg-white font-bold">
                        <option value="low"    {{ ($station->status?->congestion === 'low')    ? 'selected' : '' }}>منخفض</option>
                        <option value="medium" {{ ($station->status?->congestion === 'medium') ? 'selected' : '' }}>متوسط</option>
                        <option value="high"   {{ ($station->status?->congestion === 'high')   ? 'selected' : '' }}>مرتفع / مزدحم جداً</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary !bg-secondary hover:!bg-slate-700 px-8 flex items-center gap-2">
                    <i class="ti ti-bolt text-lg"></i> تطبيق التحديث الفوري الآن
                </button>
            </div>
        </form>
    </div>

    {{-- Station Employees (Materio Style) --}}
    <div class="materio-card mt-8 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded bg-info/10 text-info flex items-center justify-center">
                    <i class="ti ti-users text-2xl"></i>
                </div>
                <h3 class="text-[#2F2B3D] font-bold text-lg">موظفو المحطة الحاليين</h3>
            </div>
            <a href="{{ route('admin.employees.create', ['station_id' => $station->id]) }}" 
               class="px-4 py-2 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-lg text-xs font-bold transition flex items-center gap-2">
                <i class="ti ti-plus text-lg"></i> تعيين موظف جديد
            </a>
        </div>
        
        <div class="p-6">
            @if($station->employees->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($station->employees as $emp)
                        <div class="p-4 border border-slate-100 rounded-xl flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-secondary font-bold">
                                    {{ mb_strtoupper(mb_substr($emp->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[#2F2B3D]">{{ $emp->name ?? 'بدون اسم' }}</p>
                                    <p class="text-[11px] text-secondary font-mono" dir="ltr">{{ $emp->phone }}</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.employees.edit', $emp->id) }}" class="w-8 h-8 rounded text-slate-300 hover:bg-info/10 hover:text-info transition flex items-center justify-center" title="تعديل">
                                <i class="ti ti-edit text-lg"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center text-secondary">
                    <i class="ti ti-user-off text-4xl block mb-2 opacity-20"></i>
                    <p class="text-sm">لا يوجد موظفون معينون لهذه المحطة حالياً</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
