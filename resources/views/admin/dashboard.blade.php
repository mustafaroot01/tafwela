@extends('admin.layouts.app')
@section('title', 'لوحة التحكم')

@section('content')

{{-- ── Stat Cards ── --}}
@php
    $cards = [
        ['label' => 'إجمالي المستخدمين', 'value' => $stats['total_users'],       'icon' => 'ti-users',          'color' => 'primary'],
        ['label' => 'إجمالي المحطات',    'value' => $stats['total_stations'],    'icon' => 'ti-gas-station',    'color' => 'success'],
        ['label' => 'المحطات النشطة',    'value' => $stats['active_stations'],   'icon' => 'ti-bolt',           'color' => 'info'],
        ['label' => 'تحديثات اليوم',     'value' => $stats['updates_today'],     'icon' => 'ti-calendar-event', 'color' => 'warning'],
        ['label' => 'المستخدمون المحظورون','value' => $stats['banned_users'],    'icon' => 'ti-user-off',       'color' => 'error'],
        ['label' => 'تحديثات موثقة',     'value' => $stats['verified_updates'],  'icon' => 'ti-discount-check', 'color' => 'success'],
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
    @foreach($cards as $card)
    <div class="materio-card p-5 flex flex-col gap-2">
        <div class="flex items-center justify-between mb-2">
            <div class="w-10 h-10 rounded-lg bg-{{ $card['color'] }}/10 text-{{ $card['color'] }} flex items-center justify-center">
                <i class="ti {{ $card['icon'] }} text-2xl"></i>
            </div>
            <button class="text-secondary opacity-50 hover:opacity-100 transition">
                <i class="ti ti-dots-vertical"></i>
            </button>
        </div>
        <div>
            <p class="text-[#2F2B3D] text-lg font-bold">{{ number_format($card['value']) }}</p>
            <p class="text-secondary text-xs">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Updates (Main Content Style) --}}
    <div class="lg:col-span-2 materio-card overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
            <div>
                <h3 class="text-[#2F2B3D] font-bold text-lg">أحدث التحديثات</h3>
                <p class="text-secondary text-xs">آخر 10 تحديثات تم استلامها من المستخدمين</p>
            </div>
            <a href="{{ route('admin.updates.index') }}" class="btn-primary text-xs !py-1.5 !px-3">عرض الكل</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-3 text-xs font-bold text-secondary uppercase tracking-wider">المحطة</th>
                        <th class="px-6 py-3 text-xs font-bold text-secondary uppercase tracking-wider">المستخدم</th>
                        <th class="px-6 py-3 text-xs font-bold text-secondary uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-xs font-bold text-secondary uppercase tracking-wider">التوقيت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentUpdates as $update)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                    {{ mb_substr($update->station?->name_ar ?? 'م', 0, 1) }}
                                </div>
                                <span class="text-sm font-semibold text-[#2F2B3D]">{{ $update->station?->name_ar ?? $update->station?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary">{{ $update->user?->phone ?? 'مجهول' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $update->is_verified ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                                {{ $update->is_verified ? 'موثق' : 'قيد المراجعة' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-secondary">{{ $update->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-secondary text-sm">لا توجد تحديثات حديثة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Stations Card --}}
    <div class="materio-card">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-[#2F2B3D] font-bold text-lg">أكثر المحطات نشاطاً</h3>
            <p class="text-secondary text-xs">المحطات الأكثر استلاماً للتحديثات</p>
        </div>
        <div class="p-6 space-y-6">
            @forelse($topStations as $i => $station)
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-primary font-bold">
                        {{ $i + 1 }}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-[#2F2B3D] truncate">{{ $station->name_ar ?? $station->name }}</p>
                    <p class="text-xs text-secondary">{{ $station->city }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-primary">{{ $station->updates_count }}</p>
                    <p class="text-[10px] text-secondary uppercase">تحديث</p>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-secondary text-sm">لا توجد بيانات</div>
            @endforelse
        </div>
    </div>
</div>

@endsection