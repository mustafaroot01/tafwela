@extends('admin.layouts.app')

@section('title', 'تبليغات المحطات')
@section('header', 'إدارة التبليغات')

@section('content')
<div x-data="{ detailOpen: false, selectedReport: {} }">

    {{-- Filter Header & Search --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
        <form method="GET" class="flex gap-2 flex-1 max-w-md">
            <div class="relative flex-1">
                <svg class="absolute inset-y-0 right-3 my-auto w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="ابحث باسم المحطة أو المستخدم..."
                       class="w-full pr-9 pl-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm transition-all">
            </div>
            @if(request('station_id'))
                <input type="hidden" name="station_id" value="{{ request('station_id') }}">
            @endif
            <button type="submit" class="px-5 py-2.5 text-sm font-bold bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition text-slate-700 shadow-sm">بحث</button>
        </form>

        @if(request('station_id'))
        <div class="flex items-center gap-3 bg-blue-50 border border-blue-100 px-4 py-2 rounded-xl">
            <p class="text-xs font-bold text-blue-700">تصفية حسب المحطة</p>
            <a href="{{ route('admin.reports.index') }}" class="text-[10px] bg-white text-blue-600 px-2 py-1 rounded-md border border-blue-200 hover:bg-blue-50 transition font-black">إلغاء</a>
        </div>
        @endif
    </div>

    {{-- Table Container --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4 font-semibold text-slate-500 text-xs">المستخدم</th>
                        <th class="px-5 py-4 font-semibold text-slate-500 text-xs">المحطة</th>
                        <th class="px-5 py-4 font-semibold text-slate-500 text-xs">السبب</th>
                        <th class="px-5 py-4 font-semibold text-slate-500 text-xs text-center">الحالة</th>
                        <th class="px-5 py-4 font-semibold text-slate-500 text-xs text-center">التاريخ</th>
                        <th class="px-5 py-4 font-semibold text-slate-500 text-xs text-left">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $report)
                    <tr class="hover:bg-slate-50 transition group">
                        {{-- User --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $report->user->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $report->user->phone }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Station --}}
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <p class="font-semibold text-slate-700">{{ $report->station->name_ar }}</p>
                                <p class="text-[10px] text-slate-400">{{ $report->station->city }}</p>
                            </div>
                        </td>

                        {{-- Reason --}}
                        <td class="px-5 py-4">
                            <div class="flex flex-col gap-1.5">
                                <span class="font-bold text-slate-700">
                                    {{ match($report->reason) {
                                        'wrong_name' => 'اسم خاطئ',
                                        'not_existing' => 'المحطة غير موجودة',
                                        'wrong_location' => 'موقع خاطئ',
                                        'out_of_service' => 'خارج الخدمة',
                                        'other' => 'سبب آخر',
                                        default => $report->reason
                                    } }}
                                </span>
                                @if($report->duplicates_count > 1)
                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-orange-50 text-orange-600 text-[10px] font-black w-fit">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    مكرر {{ $report->duplicates_count }} مرات
                                </div>
                                @endif
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold
                                @if($report->status == 'pending') bg-amber-50 text-amber-600
                                @elseif($report->status == 'resolved') bg-emerald-50 text-emerald-600
                                @else bg-slate-100 text-slate-500 @endif">
                                {{ match($report->status) {
                                    'pending' => 'قيد الانتظار',
                                    'resolved' => 'تم المعالجة',
                                    'dismissed' => 'تم التجاهل',
                                    default => $report->status
                                } }}
                            </span>
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4 text-center">
                            <p class="text-xs text-slate-500 font-medium">{{ $report->created_at->format('Y-m-d') }}</p>
                            <p class="text-[10px] text-slate-300 font-mono">{{ $report->created_at->format('H:i') }}</p>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-left">
                            <div class="flex items-center justify-end gap-2 transition">
                                {{-- View Details --}}
                                <button @click="selectedReport = {
                                    user: '{{ $report->user->name }}',
                                    station: '{{ $report->station->name_ar }}',
                                    reason: '{{ $report->reason }}',
                                    comment: '{{ addslashes($report->comment) }}',
                                    date: '{{ $report->created_at->format('Y-m-d H:i') }}'
                                }; detailOpen = true"
                                        title="عرض التفاصيل"
                                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>

                                {{-- Status Update --}}
                                <form action="{{ route('admin.reports.update', $report->id) }}" method="POST" class="flex items-center">
                                    @csrf @method('PUT')
                                    <select name="status" onchange="this.form.submit()" 
                                            class="text-[11px] border-slate-200 rounded-lg focus:ring-blue-500 py-1 pr-7 pl-2 bg-white shadow-sm">
                                        <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>تغيير الحالة</option>
                                        <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                        <option value="dismissed" {{ $report->status == 'dismissed' ? 'selected' : '' }}>تجاهل</option>
                                    </select>
                                </form>

                                {{-- Hide Station Toggle --}}
                                <form method="POST" action="{{ route('admin.stations.toggle-status', $report->station_id) }}" 
                                      @submit.prevent="$dispatch('open-confirm', { message: 'هل تريد تغيير حالة ظهور المحطة في الخريطة؟ سيؤثر هذا على جميع المستخدمين.', action: () => $el.submit() })">
                                    @csrf
                                    <button type="submit" 
                                            title="{{ $report->station->is_active ? 'إخفاء المحطة من الخريطة' : 'إظهار المحطة في الخريطة' }}"
                                            class="p-1.5 rounded-lg transition {{ $report->station->is_active ? 'text-slate-400 hover:text-red-600 hover:bg-red-50' : 'text-emerald-500 hover:bg-emerald-50' }}">
                                        @if($report->station->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Delete Report --}}
                                <form action="{{ route('admin.reports.destroy', $report->id) }}" method="POST" 
                                      @submit.prevent="$dispatch('open-confirm', { message: 'حذف هذا التبليغ؟ سيتم إزالته نهائياً من سجلات الإدارة.', action: () => $el.submit() })">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-20 text-center text-slate-400 italic">لا توجد تبليغات حالياً</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $reports->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="detailOpen = false" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="detailOpen" x-transition class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="font-black text-slate-800">تفاصيل التبليغ</h3>
                <button @click="detailOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-8 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4a1 1 0 011-1h2a1 1 0 011 1v3M12 21v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">المحطة</p>
                        <p class="text-lg font-black text-slate-800" x-text="selectedReport.station"></p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-50 text-slate-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">المُبلّغ</p>
                        <p class="text-lg font-black text-slate-800" x-text="selectedReport.user"></p>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-2">ملاحظات إضافية</p>
                    <p class="text-slate-700 leading-relaxed font-medium" x-text="selectedReport.comment || 'لا توجد ملاحظات إضافية'"></p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] text-slate-300 font-bold tracking-widest uppercase" x-text="selectedReport.date"></p>
                </div>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-center">
                <button @click="detailOpen = false" class="px-8 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-900 transition">إغلاق</button>
            </div>
        </div>
    </div>

</div>

<style>
    .overflow-x-auto { scrollbar-width: thin; scrollbar-color: #cbd5e1 #f8fafc; }
    table { min-width: 800px; }
</style>
@endsection
