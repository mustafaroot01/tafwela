@extends('admin.layouts.app')
@section('title', 'المستخدمون')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-[#2F2B3D] text-2xl font-bold">قائمة المستخدمين</h1>
        <p class="text-secondary text-sm">إدارة المستخدمين المسجلين في المنصة ومراقبة نشاطهم</p>
    </div>
</div>

{{-- Filters --}}
<div class="materio-card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2 relative">
            <i class="ti ti-search absolute inset-y-0 right-3 my-auto text-xl text-secondary opacity-50 flex items-center"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="البحث برقم الهاتف أو الاسم..."
                   class="w-full pr-10 pl-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm">
        </div>
        <div>
            <select name="is_banned" class="w-full px-3 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary bg-white text-sm text-secondary">
                <option value="">جميع الحالات</option>
                <option value="0" {{ request('is_banned') === '0' ? 'selected' : '' }}>نشط</option>
                <option value="1" {{ request('is_banned') === '1' ? 'selected' : '' }}>محظور</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary flex-1 flex items-center justify-center gap-2">تصفية</button>
        </div>
    </form>
</div>

{{-- Users Table --}}
<div class="materio-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">المستخدم</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-center">التحديثات</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-center">الحالة</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-left">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50/50 transition group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                                {{ mb_strtoupper(mb_substr($user->name ?? $user->phone, 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-[#2F2B3D]">{{ $user->phone }}</span>
                                <span class="text-xs text-secondary">{{ $user->name ?? 'بدون اسم' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center text-sm font-bold text-[#2F2B3D]">{{ $user->station_updates_count }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $user->is_banned ? 'bg-error/10 text-error' : 'bg-success/10 text-success' }}">
                            {{ $user->is_banned ? 'محظور' : 'نشط' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.show', $user) }}" 
                               class="action-btn w-9 h-9 rounded-lg bg-slate-100 text-secondary hover:bg-primary hover:text-white" title="التفاصيل">
                                <i class="ti ti-eye text-xl"></i>
                            </a>

                            <form method="POST" action="{{ route('admin.users.toggle-trusted', $user) }}" class="action-form">
                                @csrf
                                <button type="submit" 
                                        class="action-btn w-9 h-9 rounded-lg {{ $user->is_trusted ? 'bg-primary/20 text-primary hover:bg-primary hover:text-white' : 'bg-slate-100 text-slate-400 hover:bg-primary hover:text-white' }}"
                                        title="{{ $user->is_trusted ? 'إلغاء التوثيق' : 'توثيق' }}">
                                    <i class="ti ti-certificate text-xl"></i>
                                </button>
                            </form>

                            @if($user->is_banned)
                                <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="action-form">
                                    @csrf
                                    <button type="submit" class="action-btn w-9 h-9 rounded-lg bg-success/10 text-success hover:bg-success hover:text-white" title="فك الحظر">
                                        <i class="ti ti-user-check text-xl"></i>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.ban', $user) }}"
                                      @submit.prevent="$dispatch('open-confirm', { message: 'حظر المستخدم؟', action: () => $el.submit() })"
                                      class="action-form">
                                    @csrf
                                    <button type="submit" class="action-btn w-9 h-9 rounded-lg bg-error/10 text-error hover:bg-error hover:text-white" title="حظر">
                                        <i class="ti ti-user-off text-xl"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-16 text-center text-secondary">لا يوجد نتائج</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
