@extends('admin.layouts.app')
@section('title', 'الإشعارات')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-[#2F2B3D] text-2xl font-bold">إرسال وإدارة الإشعارات</h1>
        <p class="text-secondary text-sm">تواصل مع المستخدمين عبر إشعارات Push وتنبيهات مخصصة</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Compose Card ── --}}
    <div class="lg:col-span-1">
        <div class="materio-card sticky top-24 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded bg-primary/10 text-primary flex items-center justify-center">
                    <i class="ti ti-send text-2xl"></i>
                </div>
                <h3 class="text-[#2F2B3D] font-bold text-lg">إرسال إشعار جديد</h3>
            </div>

            <form action="{{ route('admin.notifications.send') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D]">العنوان</label>
                    <input type="text" name="title" required maxlength="100"
                           placeholder="مثال: تحديث حالة الوقود"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-[#2F2B3D]">نص الرسالة</label>
                    <textarea name="body" required maxlength="500" rows="3"
                               placeholder="اكتب نص الإشعار هنا..."
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm resize-none"></textarea>
                </div>

                <div x-data="{ hasImage: false }">
                    <label class="flex items-center gap-2 text-xs font-bold text-[#2F2B3D] cursor-pointer mb-2">
                        <input type="checkbox" x-model="hasImage" class="rounded text-primary focus:ring-primary">
                        إضافة صورة (إشعار غني)
                    </label>
                    <div x-show="hasImage" x-cloak x-transition>
                        <input type="url" name="image_url"
                               placeholder="https://example.com/image.jpg"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm font-mono" dir="ltr">
                        <p class="text-[10px] text-secondary mt-1">رابط الصورة (PNG/JPG) — يظهر كبطاقة صورة</p>
                    </div>
                </div>

                <div x-data="{ target: 'all' }" class="flex flex-col gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-[#2F2B3D]">الجمهور المستهدف</label>
                        <select name="target" x-model="target"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary bg-white text-sm text-secondary">
                            <option value="all">جميع المستخدمين</option>
                            <option value="station">محبّو محطة معينة</option>
                            <option value="user">مستخدم محدد</option>
                        </select>
                    </div>

                    <div x-show="target === 'station'" x-cloak x-transition class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-[#2F2B3D]">اختر المحطة</label>
                        <select name="station_id"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary bg-white text-sm text-secondary">
                            <option value="">-- اختر محطة --</option>
                            @foreach($stations as $station)
                            <option value="{{ $station->id }}">{{ $station->name_ar ?? $station->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="target === 'user'" x-cloak x-transition class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-[#2F2B3D]">رقم هاتف المستخدم</label>
                        <input type="text" name="phone" placeholder="+9647XXXXXXXXX" dir="ltr"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm font-mono">
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 !py-3 mt-2">
                    <i class="ti ti-send text-lg"></i> إرسال الإشعار الآن
                </button>
            </form>
        </div>
    </div>

    {{-- ── History ── --}}
    <div class="lg:col-span-2">
        <div class="materio-card">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-[#2F2B3D] font-bold text-lg">سجل الإشعارات المرسلة</h3>
                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-secondary text-[11px] font-bold">
                    {{ number_format($recent->total()) }} إشعار
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recent as $notif)
                <div class="flex items-start gap-4 px-6 py-5 hover:bg-slate-50/50 transition group">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center
                        {{ $notif->type === 'admin_broadcast' ? 'bg-primary/10 text-primary' : 'bg-warning/10 text-warning' }}">
                        <i class="ti ti-bell-ringing text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-bold text-[#2F2B3D]">{{ $notif->title }}</p>
                            <span class="text-[11px] text-secondary whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-secondary mt-1 line-clamp-2 leading-relaxed">{{ $notif->body }}</p>
                        
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">الهدف:</span>
                            @if($notif->user)
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-[#2F2B3D] text-[10px] font-mono">{{ $notif->user->phone }}</span>
                            @elseif(($notif->data['target'] ?? '') === 'all')
                                <span class="px-2 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-bold">الجميع</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-secondary text-[10px] font-bold">مجموعة</span>
                            @endif
                        </div>
                    </div>
                    
                    <form action="{{ route('admin.notifications.destroy', $notif) }}" method="POST" class="flex-shrink-0"
                          @submit.prevent="$dispatch('open-confirm', { message: 'هل أنت متأكد من حذف هذا الإشعار من السجل؟ لا يمكن التراجع عن هذا الإجراء.', action: () => $el.submit() })">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded text-slate-300 hover:bg-error/10 hover:text-error transition flex items-center justify-center">
                            <i class="ti ti-trash text-lg"></i>
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-6 py-16 text-center text-secondary">
                    <i class="ti ti-bell-off text-4xl block mb-2 opacity-20"></i>
                    <p class="text-sm">لا توجد إشعارات مرسلة في السجل</p>
                </div>
                @endforelse
            </div>

            @if($recent->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                {{ $recent->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
