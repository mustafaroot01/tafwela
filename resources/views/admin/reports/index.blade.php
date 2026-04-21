@extends('admin.layouts.app')
@section('title', 'التبليغات')

@section('content')
<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-[#2F2B3D] text-2xl font-bold">إدارة التبليغات</h1>
        <p class="text-secondary text-sm">متابعة ومعالجة بلاغات المستخدمين حول المحطات</p>
    </div>

    {{-- Filter Header & Search --}}
    <div class="materio-card p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form method="GET" class="flex gap-2 flex-1 max-w-lg">
                <div class="relative flex-1">
                    <i class="ti ti-search absolute inset-y-0 right-3 my-auto text-xl text-secondary opacity-50 flex items-center"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="ابحث باسم المحطة أو المستخدم..."
                           class="w-full pr-10 pl-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm">
                </div>
                <button type="submit" class="btn-primary !bg-secondary hover:!bg-slate-700 flex items-center justify-center px-6">بحث</button>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="materio-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">المُبلِّغ</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">المحطة</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">السبب</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-center">الحالة</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-center">التاريخ</th>
                        <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-left">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $report)
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 text-secondary flex items-center justify-center">
                                    <i class="ti ti-user text-lg"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-[#2F2B3D]">{{ $report->user->name }}</span>
                                    <span class="text-[10px] text-secondary font-mono" dir="ltr">{{ $report->user->phone }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-[#2F2B3D]">{{ $report->station->name_ar }}</span>
                                <span class="text-[10px] text-secondary">{{ $report->station->city }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-[#2F2B3D]">
                                {{ match($report->reason) {
                                    'wrong_name' => 'اسم خاطئ',
                                    'not_existing' => 'المحطة غير موجودة',
                                    'wrong_location' => 'موقع خاطئ',
                                    'out_of_service' => 'خارج الخدمة',
                                    'other' => 'سبب آخر',
                                    default => $report->reason
                                } }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                                @if($report->status == 'pending') bg-warning/10 text-warning
                                @elseif($report->status == 'resolved') bg-success/10 text-success
                                @else bg-slate-100 text-secondary @endif">
                                {{ match($report->status) {
                                    'pending' => 'قيد الانتظار',
                                    'resolved' => 'تم الحل',
                                    'dismissed' => 'تجاهل',
                                    default => $report->status
                                } }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-[10px] text-secondary font-mono">
                            {{ $report->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Changed: Direct link to Station Profile (Management View) --}}
                                <a href="{{ route('admin.stations.show', $report->station_id) }}" 
                                   up-follow up-target="#main"
                                   class="action-btn w-9 h-9 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all shadow-sm flex items-center justify-center" 
                                   title="إدارة المحطة والتبليغات">
                                    <i class="ti ti-layout-grid-add text-xl"></i>
                                </a>

                                <form action="{{ route('admin.reports.update', $report->id) }}" method="POST" class="action-form">
                                    @csrf @method('PUT')
                                    <select name="status" onchange="this.form.submit()" 
                                            class="h-9 px-2 border border-slate-200 rounded-lg focus:outline-none focus:border-primary bg-white text-[10px] font-bold text-secondary transition-all">
                                        <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>تغيير الحالة</option>
                                        <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                        <option value="dismissed" {{ $report->status == 'dismissed' ? 'selected' : '' }}>تجاهل</option>
                                    </select>
                                </form>

                                <form action="{{ route('admin.reports.destroy', $report->id) }}" method="POST" 
                                      @submit.prevent="$dispatch('open-confirm', { message: 'حذف التبليغ نهائياً؟', action: () => $el.submit() })"
                                      class="action-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn w-9 h-9 rounded-lg bg-error/10 text-error hover:bg-error hover:text-white transition-all" title="حذف">
                                        <i class="ti ti-trash text-xl"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center text-secondary">
                            <i class="ti ti-report-off text-4xl block mb-2 opacity-20"></i>
                            لا توجد تبليغات حالياً
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
            {{ $reports->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
