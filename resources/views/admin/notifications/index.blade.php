@extends('admin.layouts.app')
@section('title', 'الإشعارات')
@section('header', 'إرسال وإدارة الإشعارات')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Compose Card ── --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden sticky top-24">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-l from-blue-50">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    إرسال إشعار جديد
                </h3>
            </div>

            <form action="{{ route('admin.notifications.send') }}" method="POST" class="p-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">العنوان</label>
                    <input type="text" name="title" required maxlength="100"
                           placeholder="مثال: تحديث حالة الوقود"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">نص الرسالة</label>
                    <textarea name="body" required maxlength="500" rows="3"
                              placeholder="اكتب نص الإشعار هنا..."
                              class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>

                <div x-data="{ hasImage: false }">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 mb-1.5 cursor-pointer">
                        <input type="checkbox" x-model="hasImage" class="rounded">
                        إضافة صورة (إشعار غني)
                    </label>
                    <div x-show="hasImage" x-cloak>
                        <input type="url" name="image_url"
                               placeholder="https://example.com/image.jpg"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" dir="ltr">
                        <p class="text-xs text-slate-400 mt-1">رابط الصورة (PNG/JPG) — يظهر في الإشعار كبطاقة صورة</p>
                    </div>
                </div>

                <div x-data="{ target: 'all' }">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">الجمهور المستهدف</label>
                    <select name="target" x-model="target"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white mb-3">
                        <option value="all">جميع المستخدمين</option>
                        <option value="station">محبّو محطة معينة</option>
                        <option value="user">مستخدم محدد</option>
                    </select>

                    <div x-show="target === 'station'" x-cloak>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">اختر المحطة</label>
                        <select name="station_id"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">-- اختر محطة --</option>
                            @foreach($stations as $station)
                            <option value="{{ $station->id }}">{{ $station->name_ar ?? $station->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="target === 'user'" x-cloak>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">رقم هاتف المستخدم</label>
                        <input type="text" name="phone" placeholder="+9647XXXXXXXXX" dir="ltr"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    إرسال الإشعار
                </button>
            </form>
        </div>
    </div>

    {{-- ── History ── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">سجل الإشعارات المرسلة</h3>
                <span class="text-xs text-slate-400 bg-slate-100 px-3 py-1 rounded-full font-semibold">
                    {{ $recent->total() }} إشعار
                </span>
            </div>

            <div class="divide-y divide-slate-50">
                @forelse($recent as $notif)
                <div class="flex items-start gap-4 px-5 py-4 hover:bg-slate-50 transition">
                    <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center
                        {{ $notif->type === 'admin_broadcast' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-500' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-bold text-slate-800">{{ $notif->title }}</p>
                            <span class="text-xs text-slate-400 whitespace-nowrap flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $notif->body }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-xs font-semibold text-slate-400">
                                إلى: 
                                @if($notif->user)
                                    {{ $notif->user->phone }}
                                @elseif(($notif->data['target'] ?? '') === 'all')
                                    <span class="text-blue-600">جميع المستخدمين</span>
                                @else
                                    —
                                @endif
                            </span>
                            @if($notif->station)
                            <span class="text-xs bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full font-semibold">
                                {{ $notif->station->name_ar ?? $notif->station->name }}
                            </span>
                            @endif
                            @if($notif->image_url)
                            <span class="text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full font-semibold flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                صورة
                            </span>
                            @endif
                            @if($notif->user)
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                {{ $notif->is_read ? 'bg-slate-100 text-slate-400' : 'bg-blue-50 text-blue-600' }}">
                                {{ $notif->is_read ? 'مقروء' : 'غير مقروء' }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('admin.notifications.destroy', $notif) }}" method="POST" class="flex-shrink-0">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('حذف هذا الإشعار؟')"
                                class="text-slate-300 hover:text-red-400 transition p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-16 text-center">
                    <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-sm text-slate-400">لا توجد إشعارات مرسلة بعد</p>
                </div>
                @endforelse
            </div>

            @if($recent->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $recent->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
