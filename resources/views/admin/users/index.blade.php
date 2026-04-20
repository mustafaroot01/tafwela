@extends('admin.layouts.app')
@section('title', 'المستخدمون')
@section('header', 'إدارة المستخدمين')

@section('content')

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <div class="relative">
        <svg class="absolute inset-y-0 right-3 my-auto w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="رقم الهاتف أو الاسم..."
               class="pr-9 pl-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white w-64">
    </div>
    <select name="is_banned" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-700">
        <option value="">جميع الحالات</option>
        <option value="0" {{ request('is_banned') === '0' ? 'selected' : '' }}>نشط</option>
        <option value="1" {{ request('is_banned') === '1' ? 'selected' : '' }}>محظور</option>
    </select>
    <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">تصفية</button>
    @if(request()->hasAny(['search','is_banned']))
        <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إعادة ضبط</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs">المستخدم</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs hidden md:table-cell text-center">التحديثات</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs hidden lg:table-cell text-center">تاريخ الانضمام</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs text-center">الحالة</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs text-left">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition group">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                                {{ mb_strtoupper(mb_substr($user->name ?? $user->phone, 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <p class="font-semibold text-slate-800">{{ $user->phone }}</p>
                                    @if($user->is_trusted)
                                        <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400">{{ $user->name ?? 'بدون اسم' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell text-center">
                        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                            {{ $user->station_updates_count }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell text-center text-xs text-slate-400">
                        {{ $user->created_at->format('Y/m/d') }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $user->is_banned ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-600' }}">
                            {{ $user->is_banned ? 'محظور' : 'نشط' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-left">
                        <div class="flex items-center justify-end gap-2 transition">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                تفاصيل
                            </a>

                            <form method="POST" action="{{ route('admin.users.toggle-trusted', $user) }}">
                                @csrf
                                <button type="submit" 
                                        title="{{ $user->is_trusted ? 'إلغاء التوثيق' : 'توثيق كمساهم موثوق' }}"
                                        class="px-3 py-1.5 text-xs font-semibold {{ $user->is_trusted ? 'text-blue-600 bg-blue-50 hover:bg-blue-100' : 'text-slate-400 bg-slate-50 hover:bg-slate-100' }} rounded-lg transition flex items-center gap-1">
                                    @if($user->is_trusted)
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" /><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 10a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                                        موثق
                                    @else
                                        توثيق
                                    @endif
                                </button>
                            </form>
                            @if($user->is_banned)
                                <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition">فك الحظر</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.ban', $user) }}"
                                      @submit.prevent="$dispatch('open-confirm', { message: 'هل أنت متأكد من حظر هذا المستخدم؟ لن يتمكن من استخدام التطبيق أو إرسال تحديثات.', action: () => $el.submit() })">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">حظر</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-16 text-center text-sm text-slate-400">لا يوجد مستخدمون</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
