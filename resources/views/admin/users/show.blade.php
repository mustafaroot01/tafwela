@extends('admin.layouts.app')
@section('title', 'تفاصيل المستخدم')

@section('content')

{{-- Page Header --}}
<div class="flex items-center gap-4 mb-6">
    <a href="{{ route('admin.users.index') }}" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-secondary hover:text-primary transition shadow-sm">
        <i class="ti ti-arrow-right text-xl"></i>
    </a>
    <div>
        <h1 class="text-[#2F2B3D] text-2xl font-bold">ملف المستخدم</h1>
        <p class="text-secondary text-sm">عرض تفاصيل النشاط والبيانات المسجلة للمستخدم</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: User Profile Info --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="materio-card p-6 text-center">
            <div class="relative w-24 h-24 mx-auto mb-4">
                <div class="w-full h-full rounded-full bg-primary/10 text-primary flex items-center justify-center text-3xl font-bold">
                    {{ mb_strtoupper(mb_substr($user->name ?? $user->phone, 0, 1)) }}
                </div>
                @if(!$user->is_banned)
                <div class="absolute bottom-1 right-1 w-5 h-5 bg-success border-2 border-white rounded-full"></div>
                @endif
            </div>
            
            <h2 class="text-xl font-bold text-[#2F2B3D] mb-1">{{ $user->name ?? 'بدون اسم' }}</h2>
            <div class="flex items-center justify-center gap-2 mb-4">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $user->is_banned ? 'bg-error/10 text-error' : 'bg-success/10 text-success' }}">
                    {{ $user->is_banned ? 'محظور حالياً' : 'مستخدم نشط' }}
                </span>
                @if($user->is_trusted)
                <span class="px-2.5 py-1 rounded-full bg-primary/10 text-primary text-[11px] font-bold">موثوق</span>
                @endif
            </div>

            <div class="space-y-4 text-right border-t border-slate-100 pt-6">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-secondary">رقم الهاتف:</span>
                    <span class="font-bold text-[#2F2B3D] font-mono" dir="ltr">{{ $user->phone }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-secondary">تاريخ الانضمام:</span>
                    <span class="font-bold text-[#2F2B3D]">{{ $user->created_at->format('Y/m/d') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-secondary">آخر نشاط:</span>
                    <span class="font-bold text-[#2F2B3D]">{{ $user->last_active_at?->diffForHumans() ?? 'غير متوفر' }}</span>
                </div>
            </div>

            <div class="mt-8 space-y-3">
                @if($user->role !== 'employee')
                <a href="{{ route('admin.employees.create', ['phone' => $user->phone]) }}" 
                   class="btn-primary w-full flex items-center justify-center gap-2 !bg-info hover:!bg-opacity-90">
                    <i class="ti ti-user-cog text-lg"></i> تعيين كموظف محطة
                </a>
                @endif

                @if($user->is_banned)
                    <form method="POST" action="{{ route('admin.users.unban', $user) }}"
                          @submit.prevent="$dispatch('open-confirm', { message: 'إلغاء حظر هذا المستخدم؟ سيتمكن من استخدام التطبيق فوراً.', action: () => $el.submit() })">
                        @csrf
                        <button class="btn-primary w-full !bg-success hover:!bg-opacity-90 font-bold">إلغاء حظر المستخدم</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.ban', $user) }}" 
                          @submit.prevent="$dispatch('open-confirm', { message: 'هل أنت متأكد من حظر هذا المستخدم؟ لن يتمكن من استخدام التطبيق.', action: () => $el.submit() })">
                        @csrf
                        <button class="btn-primary w-full !bg-error hover:!bg-opacity-90 font-bold">حظر هذا المستخدم</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="materio-card p-4 text-center">
                <div class="w-10 h-10 rounded bg-primary/10 text-primary flex items-center justify-center mx-auto mb-2">
                    <i class="ti ti-history text-xl"></i>
                </div>
                <p class="text-lg font-bold text-[#2F2B3D]">{{ number_format($user->stationUpdates->count()) }}</p>
                <p class="text-[11px] text-secondary">إجمالي التحديثات</p>
            </div>
            <div class="materio-card p-4 text-center">
                <div class="w-10 h-10 rounded bg-success/10 text-success flex items-center justify-center mx-auto mb-2">
                    <i class="ti ti-discount-check text-xl"></i>
                </div>
                <p class="text-lg font-bold text-[#2F2B3D]">{{ number_format($user->stationUpdates->where('is_verified', true)->count()) }}</p>
                <p class="text-[11px] text-secondary">تحديثات موثقة</p>
            </div>
        </div>
    </div>

    {{-- Right: Activity Log --}}
    <div class="lg:col-span-2">
        <div class="materio-card h-full">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[#2F2B3D] font-bold text-lg">آخر التحديثات المرسلة</h3>
                <span class="text-xs text-secondary bg-slate-100 px-3 py-1 rounded-full font-bold">آخر 15 تحديث</span>
            </div>
            
            <div class="divide-y divide-slate-100">
                @forelse($user->stationUpdates->sortByDesc('created_at')->take(15) as $update)
                <div class="p-6 hover:bg-slate-50/50 transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded bg-slate-100 flex items-center justify-center text-primary">
                                <i class="ti ti-map-pin text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-[#2F2B3D]">{{ $update->station?->name_ar ?? $update->station?->name ?? 'محطة غير موجودة' }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-secondary">{{ $update->created_at->format('Y/m/d H:i') }}</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded {{ $update->is_verified ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }} font-bold">
                                        {{ $update->is_verified ? 'موثق' : 'قيد المراجعة' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach(['petrol' => ['label' => 'بنزين', 'icon' => 'ti-gas-station'], 'diesel' => ['label' => 'ديزل', 'icon' => 'ti-truck-delivery'], 'gas' => ['label' => 'غاز', 'icon' => 'ti-flame']] as $fuel => $meta)
                                @if($update->$fuel)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded border {{ $update->$fuel === 'available' ? 'border-success/30 text-success bg-success/5' : ($update->$fuel === 'limited' ? 'border-warning/30 text-warning bg-warning/5' : 'border-error/30 text-error bg-error/5') }}">
                                    <i class="ti {{ $meta['icon'] }} text-sm"></i>
                                    <span class="text-[11px] font-bold">{{ $meta['label'] }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-20 text-center text-secondary">
                    <i class="ti ti-database-off text-5xl block mb-3 opacity-20"></i>
                    <p class="text-sm">لم يقم هذا المستخدم بإرسال أي تحديثات بعد</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
