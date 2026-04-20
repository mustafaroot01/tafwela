@extends('admin.layouts.app')
@section('title', 'تعديل المحطة')
@section('header', 'تعديل بيانات المحطة')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <a href="{{ route('admin.stations.index') }}" class="text-sm font-black text-slate-400 hover:text-brand-600 transition-colors flex items-center gap-2">
            <span>→</span>
            العودة لقائمة المحطات
        </a>
        
        <div class="flex items-center gap-2 px-4 py-1.5 bg-brand-50 rounded-full border border-brand-100">
            <div class="w-2 h-2 rounded-full {{ $station->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
            <span class="text-[10px] font-black text-brand-700 uppercase">{{ $station->is_active ? 'نشطة حالياً' : 'معطلة' }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.stations.update', $station) }}" class="space-y-8">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-[2.5rem] p-10 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-32 h-32 bg-brand-500 opacity-5 rounded-full -translate-x-16 -translate-y-16"></div>
            @include('admin.stations._form')
        </div>

        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.stations.index') }}" 
               class="px-8 py-4 bg-slate-100 text-slate-600 text-sm font-black rounded-2xl hover:bg-slate-200 transition-all">
                إلغاء
            </a>
            <button type="submit" 
                    class="px-12 py-4 bg-brand-600 text-white text-sm font-black rounded-2xl hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                تحديث البيانات
            </button>
        </div>
    </form>

    {{-- Admin Status Update --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mt-8">
        <h3 class="font-semibold text-slate-800 mb-1 flex items-center gap-2">
            <span>⚡</span> تحديث الحالة الفوري
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Admin Override</span>
        </h3>
        <p class="text-xs text-slate-500 mb-4">يُطبَّق فوراً بدون انتظار تأكيدات المستخدمين ويُرسَل إشعار لجميع المستخدمين.</p>
        <form method="POST" action="{{ route('admin.stations.admin-update', $station) }}" class="space-y-4">
            @csrf
            @php
            $fuels = [
                'petrol_normal'   => 'بنزين عادي',
                'petrol_improved' => 'بنزين محسّن',
                'petrol_super'    => 'بنزين سوبر',
                'diesel'          => 'ديزل',
                'kerosene'        => 'كيروسين',
                'gas'             => 'غاز',
            ];
            $statuses = ['available' => 'متاح', 'limited' => 'محدود', 'unavailable' => 'غير متاح'];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($fuels as $key => $label)
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
                        <select name="{{ $key }}"
                                class="w-full text-sm border border-slate-300 rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">— بدون تغيير —</option>
                            @foreach($statuses as $val => $txt)
                                <option value="{{ $val }}"
                                    {{ ($station->status?->$key === $val) ? 'selected' : '' }}>
                                    {{ $txt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-slate-600 mb-1">الازدحام</label>
                <select name="congestion" required
                        class="w-full text-sm border border-slate-300 rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="low"    {{ ($station->status?->congestion === 'low')    ? 'selected' : '' }}>منخفض</option>
                    <option value="medium" {{ ($station->status?->congestion === 'medium') ? 'selected' : '' }}>متوسط</option>
                    <option value="high"   {{ ($station->status?->congestion === 'high')   ? 'selected' : '' }}>مرتفع</option>
                </select>
            </div>
            <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 text-white font-semibold text-sm rounded-lg hover:bg-blue-700 transition">
                ⚡ تطبيق التحديث الفوري
            </button>
        </form>
    </div>

    {{-- Station Employees --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mt-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <span>👨‍💼</span> موظفو المحطة
            </h3>
            <a href="{{ route('admin.employees.create', ['station_id' => $station->id]) }}" 
               class="text-xs bg-brand-50 text-brand-700 hover:bg-brand-100 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                + تعيين موظف
            </a>
        </div>
        
        @if($station->employees->count() > 0)
            <div class="divide-y divide-slate-100 border border-slate-100 rounded-lg">
                @foreach($station->employees as $emp)
                    <div class="flex items-center justify-between p-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $emp->name ?? 'بدون اسم' }}</p>
                            <p class="text-xs text-slate-500 font-mono" dir="ltr">{{ $emp->phone }}</p>
                        </div>
                        <a href="{{ route('admin.employees.edit', $emp->id) }}" class="text-xs text-blue-600 hover:underline">تعديل</a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 bg-slate-50 rounded-lg border border-dashed border-slate-200 text-slate-500 text-sm">
                لا يوجد موظفون معينون لهذه المحطة حالياً.
            </div>
        @endif
    </div>
</div>
@endsection
