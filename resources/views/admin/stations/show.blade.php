@extends('admin.layouts.app')
@section('title', 'بروفايل المحطة')

@section('content')

{{-- Header --}}
<div class="flex items-center gap-4 mb-6">
    <a href="{{ route('admin.reports.index') }}" class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-secondary hover:text-primary transition shadow-sm">
        <i class="ti ti-arrow-right text-xl"></i>
    </a>
    <div>
        <h1 class="text-[#2F2B3D] text-2xl font-bold">إدارة المحطة: {{ $station->name_ar }}</h1>
        <p class="text-secondary text-sm">عرض شامل للتبليغات والبيانات التقنية للمحطة</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Edit & Basic Info --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="materio-card p-8">
            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-slate-100">
                <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center shadow-sm">
                    <i class="ti ti-settings text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-[#2F2B3D]">تعديل البيانات</h3>
            </div>

            <form action="{{ route('admin.stations.update', $station) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                @foreach([
                    ['name'=>'name',     'label'=>'الاسم (EN)',    'val'=>$station->name],
                    ['name'=>'name_ar',  'label'=>'الاسم بالعربي', 'val'=>$station->name_ar],
                    ['name'=>'city',     'label'=>'المدينة',       'val'=>$station->city],
                    ['name'=>'district', 'label'=>'المنطقة',       'val'=>$station->district],
                    ['name'=>'latitude', 'label'=>'خط العرض',      'val'=>$station->latitude],
                    ['name'=>'longitude','label'=>'خط الطول',      'val'=>$station->longitude],
                ] as $f)
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D] uppercase tracking-wide opacity-70">{{ $f['label'] }}</label>
                    <input type="text" name="{{ $f['name'] }}" value="{{ $f['val'] }}"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm transition-all focus:ring-4 focus:ring-primary/5">
                </div>
                @endforeach
                
                <button type="submit" class="btn-primary w-full !py-3 mt-4 flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
                    <i class="ti ti-device-floppy text-lg"></i> حفظ التغييرات
                </button>
            </form>
        </div>

        {{-- Status Card --}}
        <div class="materio-card p-6 bg-gradient-to-br from-primary/5 to-transparent">
            <h4 class="text-xs font-bold text-secondary uppercase mb-4 opacity-70">الحالة الحالية للوقود</h4>
            @if($station->status)
            <div class="space-y-3">
                @foreach(['petrol' => 'بنزين', 'diesel' => 'ديزل', 'gas' => 'غاز'] as $f => $l)
                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-slate-100">
                        <span class="text-sm font-bold">{{ $l }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $station->status->$f === 'available' ? 'bg-success/10 text-success' : ($station->status->$f === 'limited' ? 'bg-warning/10 text-warning' : 'bg-error/10 text-error') }}">
                            {{ $station->status->$f === 'available' ? 'متوفر' : ($station->status->$f === 'limited' ? 'محدود' : 'نافذ') }}
                        </span>
                    </div>
                @endforeach
                <p class="text-[10px] text-secondary text-center mt-4">آخر تحديث: {{ $station->status->last_updated_at->diffForHumans() }}</p>
            </div>
            @else
            <p class="text-xs text-secondary italic text-center py-4">لا توجد بيانات وقود مسجلة</p>
            @endif
        </div>
    </div>

    {{-- Right: Reports & Activity --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Reports List --}}
        <div class="materio-card overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[#2F2B3D] font-bold text-lg">سجل التبليغات الواردة</h3>
                <span class="text-xs bg-error/10 text-error px-3 py-1 rounded-full font-bold">{{ $station->reports->count() }} بلاغ</span>
            </div>
            
            <div class="divide-y divide-slate-100">
                @forelse($station->reports->sortByDesc('created_at') as $report)
                <div class="p-6 hover:bg-slate-50 transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-secondary">
                                <i class="ti ti-user-exclamation text-xl"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-[#2F2B3D]">{{ $report->user->name }}</span>
                                    <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded text-secondary font-mono" dir="ltr">{{ $report->user->phone }}</span>
                                </div>
                                <p class="text-xs text-error font-bold mt-1">
                                    السبب: {{ match($report->reason) {
                                        'wrong_name' => 'اسم خاطئ',
                                        'not_existing' => 'المحطة غير موجودة',
                                        'wrong_location' => 'موقع خاطئ',
                                        'out_of_service' => 'خارج الخدمة',
                                        'other' => 'سبب آخر',
                                        default => $report->reason
                                    } }}
                                </p>
                                <p class="text-[11px] text-secondary mt-2 leading-relaxed italic">"{{ $report->comment }}"</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="text-[10px] text-secondary font-mono">{{ $report->created_at->format('Y-m-d H:i') }}</span>
                            <form action="{{ route('admin.reports.update', $report->id) }}" method="POST" class="action-form">
                                @csrf @method('PUT')
                                <select name="status" onchange="this.form.submit()" class="h-8 px-2 border border-slate-200 rounded bg-white text-[10px] font-bold text-secondary">
                                    <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>تعديل الحالة</option>
                                    <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                    <option value="dismissed" {{ $report->status == 'dismissed' ? 'selected' : '' }}>تجاهل</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-secondary opacity-50">
                    <i class="ti ti-mood-smile text-4xl block mb-2"></i>
                    <p class="text-sm">لا توجد تبليغات مسجلة لهذه المحطة</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity Log --}}
        <div class="materio-card overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[#2F2B3D] font-bold text-lg">آخر تحديثات الوقود</h3>
                <span class="text-xs bg-primary/10 text-primary px-3 py-1 rounded-full font-bold">آخر 10 تحديثات</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-secondary uppercase">
                            <th class="px-6 py-3">المُبلِّغ</th>
                            <th class="px-6 py-3">التفاصيل</th>
                            <th class="px-6 py-3 text-center">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($station->stationUpdates as $up)
                        <tr>
                            <td class="px-6 py-3">
                                <span class="text-[11px] font-bold text-[#2F2B3D]">{{ $up->user?->name ?: $up->user?->phone }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex gap-1">
                                    @foreach(['petrol','diesel','gas'] as $f)
                                        @if($up->$f)
                                            <span class="w-2 h-2 rounded-full {{ $up->$f === 'available' ? 'bg-success' : ($up->$f === 'limited' ? 'bg-warning' : 'bg-error') }}" title="{{ $f }}"></span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-3 text-center text-[10px] text-secondary font-mono">
                                {{ $up->created_at->format('m-d H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
