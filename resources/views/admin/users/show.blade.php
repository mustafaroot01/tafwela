@extends('admin.layouts.app')
@section('title', 'تفاصيل المستخدم')
@section('header', 'ملف المستخدم')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    {{-- Profile Card --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row items-center gap-5">
            <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold flex-shrink-0">
                {{ mb_strtoupper(mb_substr($user->name ?? $user->phone, 0, 1)) }}
            </div>
            <div class="flex-1 text-center sm:text-right">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
                    <h2 class="text-lg font-bold text-slate-800">{{ $user->name ?? 'بدون اسم' }}</h2>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full
                        {{ $user->is_banned ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-600' }}">
                        {{ $user->is_banned ? 'محظور' : 'نشط' }}
                    </span>
                </div>
                <p class="text-sm text-slate-500">{{ $user->phone }}</p>
                <p class="text-xs text-slate-400 mt-1">انضم في {{ $user->created_at->format('Y/m/d') }}</p>
            </div>
            <div class="flex gap-2">
                @if($user->role !== 'employee')
                <a href="{{ route('admin.employees.create', ['phone' => $user->phone]) }}" 
                   class="px-4 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition">
                    تعيين كموظف
                </a>
                @endif

                @if($user->is_banned)
                    <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                        @csrf
                        <button class="px-4 py-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition">فك الحظر</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.ban', $user) }}" onsubmit="return confirm('حظر هذا المستخدم؟')">
                        @csrf
                        <button class="px-4 py-2 text-sm font-semibold bg-red-600 hover:bg-red-700 text-white rounded-xl transition">حظر</button>
                    </form>
                @endif
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">رجوع</a>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-3 gap-4 mt-5 pt-5 border-t border-slate-100">
            <div class="text-center">
                <p class="text-xl font-bold text-slate-800">{{ number_format($user->stationUpdates->count()) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">إجمالي التحديثات</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-emerald-600">{{ number_format($user->stationUpdates->where('is_verified', true)->count()) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">تحديثات موثقة</p>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-slate-600">{{ $user->last_active_at?->diffForHumans() ?? '—' }}</p>
                <p class="text-xs text-slate-400 mt-0.5">آخر نشاط</p>
            </div>
        </div>
    </div>

    {{-- Activity Log --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-700">سجل التحديثات</h3>
            <span class="text-xs text-slate-400">آخر 15 تحديث</span>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($user->stationUpdates->sortByDesc('created_at')->take(15) as $update)
            <div class="flex flex-wrap items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $update->station?->name_ar ?? $update->station?->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $update->created_at->format('Y/m/d H:i') }}</p>
                </div>
                <div class="flex gap-1.5 flex-wrap">
                    @foreach(['petrol'=>'بنزين','diesel'=>'ديزل','gas'=>'غاز'] as $fuel => $label)
                        @if($update->$fuel)
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ $update->$fuel==='available' ? 'bg-emerald-50 text-emerald-600' : ($update->$fuel==='limited' ? 'bg-orange-50 text-orange-500' : 'bg-red-50 text-red-500') }}">
                            {{ $label }}
                        </span>
                        @endif
                    @endforeach
                </div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full
                    {{ $update->is_verified ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-500' }}">
                    {{ $update->is_verified ? 'موثق' : 'قيد المراجعة' }}
                </span>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-sm text-slate-400">لا توجد تحديثات</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

