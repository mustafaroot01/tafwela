@extends('admin.layouts.app')
@section('title', 'المحطات')
@section('header', 'إدارة المحطات')

@section('content')
<div x-data="{ addOpen: false, editOpen: false, editStation: {} }">

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <form method="GET" class="flex gap-2 flex-1 max-w-md">
            <div class="relative flex-1">
                <svg class="absolute inset-y-0 right-3 my-auto w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="ابحث باسم المحطة أو المدينة..."
                       class="w-full pr-9 pl-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            </div>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-slate-100 hover:bg-slate-200 rounded-xl transition text-slate-700">بحث</button>
        </form>
        <button @click="addOpen = true"
                class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            إضافة محطة
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs">المحطة</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs hidden md:table-cell">الموقع</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs hidden lg:table-cell text-center">الإحداثيات</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs text-center">الحالة</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs text-left">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stations as $station)
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $station->name_ar ?? $station->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $station->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell">
                            <p class="text-slate-700 font-medium">{{ $station->city }}</p>
                            <p class="text-xs text-slate-400">{{ $station->district }}</p>
                        </td>
                        <td class="px-5 py-3.5 hidden lg:table-cell text-center">
                            <span class="text-xs text-slate-500 font-mono">{{ $station->latitude }}, {{ $station->longitude }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                    {{ $station->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                                    {{ $station->is_active ? 'نشطة' : 'متوقفة' }}
                                </span>
                                @if($station->status)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                    {{ $station->status->source === 'admin' ? 'bg-blue-50 text-blue-600' :
                                       ($station->status->source === 'verified_users' ? 'bg-purple-50 text-purple-600' : 'bg-slate-100 text-slate-400') }}">
                                    {{ $station->status->source === 'admin' ? '✓ إدارة' :
                                       ($station->status->source === 'verified_users' ? '✓ موثق' : 'غير موثق') }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-left">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Force Verify --}}
                                @if($station->status && $station->status->source !== 'admin')
                                <form method="POST" action="{{ route('admin.stations.force-verify', $station) }}"
                                      @submit.prevent="$dispatch('open-confirm', { message: 'توثيق هذه المحطة يدوياً؟ ستعتبر البيانات من الإدارة مباشرة.', action: () => $el.submit() })">
                                    @csrf
                                    <button type="submit"
                                            title="توثيق يدوي"
                                            class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif

                                {{-- Toggle Visibility --}}
                                <form method="POST" action="{{ route('admin.stations.toggle-status', $station) }}"
                                      @submit.prevent="$dispatch('open-confirm', { message: '{{ $station->is_active ? 'إخفاء هذه المحطة من الخريطة؟' : 'إعادة إظهار هذه المحطة؟' }}', action: () => $el.submit() })">
                                    @csrf
                                    <button type="submit" 
                                            title="{{ $station->is_active ? 'إخفاء المحطة' : 'إظهار المحطة' }}"
                                            class="p-1.5 rounded-lg transition {{ $station->is_active ? 'text-slate-400 hover:text-red-600 hover:bg-red-50' : 'text-emerald-500 hover:bg-emerald-50' }}">
                                        @if($station->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- View Reports --}}
                                <a href="{{ route('admin.reports.index', ['station_id' => $station->id]) }}"
                                   title="عرض التبليغات"
                                   class="p-1.5 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </a>
                                <button @click="editStation = {{ $station->toJson() }}; editOpen = true"
                                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.stations.destroy', $station) }}"
                                      @submit.prevent="$dispatch('open-confirm', { message: 'حذف هذه المحطة نهائياً من النظام؟ لا يمكن التراجع عن هذا الإجراء.', action: () => $el.submit() })">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-16 text-center text-sm text-slate-400">لا توجد محطات مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stations->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $stations->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    {{-- Add Modal --}}
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="addOpen = false" class="absolute inset-0 bg-black/40"></div>
        <div x-show="addOpen" x-transition class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-800">إضافة محطة جديدة</h3>
                <button @click="addOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.stations.store') }}" method="POST" class="space-y-4">
                @csrf
                @include('admin.stations._form')
                <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    حفظ المحطة
                </button>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="editOpen = false" class="absolute inset-0 bg-black/40"></div>
        <div x-show="editOpen" x-transition class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-800">تعديل المحطة</h3>
                <button @click="editOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form :action="'{{ url('admin/stations') }}/' + editStation.id" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    @foreach([
                        ['name'=>'name_ar',  'label'=>'الاسم بالعربي','ph'=>'محطة المنصور'],
                        ['name'=>'city',     'label'=>'المدينة',       'ph'=>'بغداد'],
                        ['name'=>'district', 'label'=>'المنطقة',       'ph'=>'المنصور'],
                        ['name'=>'address',  'label'=>'العنوان',       'ph'=>'شارع...'],
                        ['name'=>'latitude', 'label'=>'خط العرض',      'ph'=>'33.XXX'],
                        ['name'=>'longitude','label'=>'خط الطول',      'ph'=>'44.XXX'],
                    ] as $f)
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ $f['label'] }}</label>
                        <input type="text" name="{{ $f['name'] }}" :value="editStation.{{ $f['name'] }}"
                               placeholder="{{ $f['ph'] }}"
                               class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    حفظ التعديلات
                </button>
                <a :href="'{{ url('admin/employees/create') }}?station_id=' + editStation.id" 
                   class="block w-full text-center py-2.5 bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 text-sm font-semibold rounded-xl transition mt-2">
                    👨‍💼 تعيين موظف لهذه المحطة
                </a>
            </form>
        </div>
    </div>

</div>
@endsection
