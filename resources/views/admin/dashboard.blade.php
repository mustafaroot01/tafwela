@extends('admin.layouts.app')
@section('title', 'لوحة التحكم')
@section('header', 'نظرة عامة')

@section('content')

{{-- ── Stat Cards ── --}}
@php
    $cards = [
        ['label' => 'إجمالي المستخدمين', 'value' => $stats['total_users'],       'sub' => 'مستخدم مسجل',     'color' => 'blue',    'bar' => 'bg-blue-500',    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['label' => 'إجمالي المحطات',    'value' => $stats['total_stations'],     'sub' => 'محطة وقود',       'color' => 'emerald', 'bar' => 'bg-emerald-500', 'icon' => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
        ['label' => 'المحطات النشطة',    'value' => $stats['active_stations'],    'sub' => 'محطة تعمل الآن',  'color' => 'green',   'bar' => 'bg-green-500',   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'تحديثات اليوم',     'value' => $stats['updates_today'],      'sub' => 'تحديث اليوم',     'color' => 'orange',  'bar' => 'bg-orange-400',  'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
        ['label' => 'المستخدمون المحظورون','value' => $stats['banned_users'],     'sub' => 'محظور',           'color' => 'red',     'bar' => 'bg-red-400',     'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
        ['label' => 'تحديثات موثقة',     'value' => $stats['verified_updates'],   'sub' => 'تحديث موثق',      'color' => 'violet',  'bar' => 'bg-violet-500',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    ];
    $iconBg = ['blue'=>'bg-blue-50 text-blue-600','emerald'=>'bg-emerald-50 text-emerald-600','green'=>'bg-green-50 text-green-600','orange'=>'bg-orange-50 text-orange-500','red'=>'bg-red-50 text-red-500','violet'=>'bg-violet-50 text-violet-600'];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @foreach($cards as $card)
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col gap-3 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 mb-1">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($card['value']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $iconBg[$card['color']] }} flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
        </div>
        <div class="h-1 bg-slate-100 rounded-full overflow-hidden">
            <div class="{{ $card['bar'] }} h-full rounded-full" style="width: 65%"></div>
        </div>
        <p class="text-xs text-slate-400">{{ number_format($card['value']) }} {{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Bottom Grid ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Recent Updates --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-700">أحدث التحديثات</h3>
            <a href="{{ route('admin.updates.index') }}" class="text-xs text-blue-600 hover:underline font-semibold">عرض الكل</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentUpdates as $update)
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50 transition">
                <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $update->station?->name_ar ?? $update->station?->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $update->user?->phone ?? 'مجهول' }} · {{ $update->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                    {{ $update->is_verified ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-500' }}">
                    {{ $update->is_verified ? 'موثق' : 'قيد المراجعة' }}
                </span>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-sm text-slate-400">لا توجد تحديثات حديثة</div>
            @endforelse
        </div>
    </div>

    {{-- Top Stations --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-700">أكثر المحطات نشاطاً</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($topStations as $i => $station)
            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                    {{ $i === 0 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ $i + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $station->name_ar ?? $station->name }}</p>
                    <p class="text-xs text-slate-400">{{ $station->city }}</p>
                </div>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                    {{ $station->updates_count }}
                </span>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-sm text-slate-400">لا توجد بيانات</div>
            @endforelse
        </div>
    </div>
</div>

@endsection