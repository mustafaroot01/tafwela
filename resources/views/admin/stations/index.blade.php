@extends('admin.layouts.app')
@section('title', 'المحطات')

@section('content')
<div x-data="{ addOpen: false, editOpen: false, editStation: {} }">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[#2F2B3D] text-2xl font-bold">إدارة المحطات</h1>
            <p class="text-secondary text-sm">إدارة كافة محطات الوقود المسجلة ومتابعة حالتها الفنية</p>
        </div>
        <button @click="addOpen = true" class="btn-primary flex items-center gap-2 self-start shadow-md shadow-primary/20">
            <i class="ti ti-plus text-lg"></i> إضافة محطة جديدة
        </button>
    </div>

    {{-- Toolbar --}}
    <div class="materio-card p-6 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <i class="ti ti-search absolute inset-y-0 right-3 my-auto text-xl text-secondary opacity-50 flex items-center"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="ابحث باسم المحطة أو المدينة..."
                       class="w-full pr-10 pl-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm">
            </div>
            <button type="submit" class="btn-primary !bg-secondary hover:!bg-slate-700 flex items-center justify-center gap-2">
                <i class="ti ti-filter text-lg"></i> بحث
            </button>
            @if(request('search'))
                <a href="{{ route('admin.stations.index') }}" class="px-4 py-2.5 bg-slate-100 text-secondary rounded-lg hover:bg-slate-200 transition flex items-center justify-center">
                    <i class="ti ti-refresh text-lg"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="materio-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">المحطة</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">الموقع</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-center">الإحداثيات</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-center">الحالة</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-left">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stations as $station)
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                    <i class="ti ti-gas-station text-2xl"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-[#2F2B3D]">{{ $station->name_ar ?? $station->name }}</span>
                                    <span class="text-[11px] text-secondary">{{ $station->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-700">{{ $station->city }}</span>
                                <span class="text-xs text-secondary">{{ $station->district }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 bg-slate-100 text-secondary rounded text-[10px] font-mono">
                                {{ $station->latitude }}, {{ $station->longitude }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $station->is_active ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                                {{ $station->is_active ? 'نشطة' : 'متوقفة' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Force Verify --}}
                                @if($station->status && $station->status->source !== 'admin')
                                <form method="POST" action="{{ route('admin.stations.force-verify', $station) }}"
                                      @submit.prevent="$dispatch('open-confirm', { message: 'توثيق هذه المحطة يدوياً؟', action: () => $el.submit() })"
                                      class="action-form">
                                    @csrf
                                    <button type="submit" title="توثيق يدوي" class="action-btn w-9 h-9 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white">
                                        <i class="ti ti-shield-check text-xl"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Toggle Visibility --}}
                                <form method="POST" action="{{ route('admin.stations.toggle-status', $station) }}"
                                      @submit.prevent="$dispatch('open-confirm', { message: 'تغيير حالة ظهور المحطة؟', action: () => $el.submit() })"
                                      class="action-form">
                                    @csrf
                                    <button type="submit" title="{{ $station->is_active ? 'إخفاء' : 'إظهار' }}"
                                            class="action-btn w-9 h-9 rounded-lg {{ $station->is_active ? 'bg-error/10 text-error hover:bg-error hover:text-white' : 'bg-success/10 text-success hover:bg-success hover:text-white' }}">
                                        <i class="ti {{ $station->is_active ? 'ti-eye-off' : 'ti-eye' }} text-xl"></i>
                                    </button>
                                </form>

                                {{-- Edit Button (Alpine.js trigger) --}}
                                <button @click="editStation = {{ $station->toJson() }}; editOpen = true"
                                        class="action-btn w-9 h-9 rounded-lg bg-info/10 text-info hover:bg-info hover:text-white" title="تعديل">
                                    <i class="ti ti-edit text-xl"></i>
                                </button>

                                {{-- Delete --}}
                                <form method="POST" action="{{ route('admin.stations.destroy', $station) }}"
                                      @submit.prevent="$dispatch('open-confirm', { message: 'حذف المحطة نهائياً؟', action: () => $el.submit() })"
                                      class="action-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn w-9 h-9 rounded-lg bg-error/10 text-error hover:bg-error hover:text-white" title="حذف">
                                        <i class="ti ti-trash text-xl"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-16 text-center text-secondary">لا توجد محطات مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stations->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
            {{ $stations->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    {{-- Add Modal --}}
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#171925]/60 backdrop-blur-sm">
        <div x-show="addOpen" x-transition @click.away="addOpen = false" class="bg-white rounded-xl shadow-materio w-full max-w-lg overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-[#2F2B3D] font-bold text-lg">إضافة محطة جديدة</h3>
                <button @click="addOpen = false" class="text-secondary hover:text-error transition"><i class="ti ti-x text-2xl"></i></button>
            </div>
            <form action="{{ route('admin.stations.store') }}" method="POST" class="p-8 space-y-5">
                @csrf
                @include('admin.stations._form')
                <div class="pt-4 flex gap-3">
                    <button type="submit" class="btn-primary flex-1">حفظ البيانات</button>
                    <button type="button" @click="addOpen = false" class="px-6 py-2 bg-slate-100 text-secondary rounded-lg font-bold">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#171925]/60 backdrop-blur-sm">
        <div x-show="editOpen" x-transition @click.away="editOpen = false" class="bg-white rounded-xl shadow-materio w-full max-w-lg overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-[#2F2B3D] font-bold text-lg">تعديل بيانات المحطة</h3>
                <button @click="editOpen = false" class="text-secondary hover:text-error transition"><i class="ti ti-x text-2xl"></i></button>
            </div>
            <form :action="'{{ url('admin/stations') }}/' + editStation.id" method="POST" class="p-8 space-y-5">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach([
                        ['name'=>'name',     'label'=>'الاسم (EN)',    'ph'=>'Station Name'],
                        ['name'=>'name_ar',  'label'=>'الاسم بالعربي', 'ph'=>'محطة المنصور'],
                        ['name'=>'city',     'label'=>'المدينة',       'ph'=>'بغداد'],
                        ['name'=>'district', 'label'=>'المنطقة',       'ph'=>'المنصور'],
                        ['name'=>'address',  'label'=>'العنوان',       'ph'=>'شارع...'],
                        ['name'=>'latitude', 'label'=>'خط العرض',      'ph'=>'33.XXX'],
                        ['name'=>'longitude','label'=>'خط الطول',      'ph'=>'44.XXX'],
                    ] as $f)
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-[#2F2B3D] uppercase">{{ $f['label'] }}</label>
                        <input type="text" name="{{ $f['name'] }}" :value="editStation.{{ $f['name'] }}"
                               placeholder="{{ $f['ph'] }}"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm transition">
                    </div>
                    @endforeach
                </div>
                <div class="pt-6 flex flex-col gap-3">
                    <button type="submit" class="btn-primary w-full !py-3">تحديث البيانات</button>
                    <a :href="'{{ url('admin/employees/create') }}?station_id=' + editStation.id" 
                       class="block w-full text-center py-2.5 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-lg text-sm font-bold transition">
                        تعيين موظف للمحطة
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
