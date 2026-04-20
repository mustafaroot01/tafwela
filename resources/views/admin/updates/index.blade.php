@extends('admin.layouts.app')
@section('title', 'التحديثات')
@section('header', 'مراقبة التحديثات')

@section('content')

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    @php $sel = 'px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-700'; @endphp

    <select name="station_id" class="{{ $sel }}">
        <option value="">جميع المحطات</option>
        @foreach($stations as $st)
            <option value="{{ $st->id }}" {{ request('station_id') == $st->id ? 'selected' : '' }}>{{ $st->name_ar ?? $st->name }}</option>
        @endforeach
    </select>

    <select name="is_verified" class="{{ $sel }}">
        <option value="">حالة التوثيق</option>
        <option value="1" {{ request('is_verified') === '1' ? 'selected' : '' }}>موثق</option>
        <option value="0" {{ request('is_verified') === '0' ? 'selected' : '' }}>غير موثق</option>
    </select>

    <select name="is_admin_update" class="{{ $sel }}">
        <option value="">جميع المصادر</option>
        <option value="1" {{ request('is_admin_update') === '1' ? 'selected' : '' }}>إداري</option>
        <option value="0" {{ request('is_admin_update') === '0' ? 'selected' : '' }}>مستخدمون</option>
    </select>

    <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">تصفية</button>
    @if(request()->hasAny(['station_id','is_verified','is_admin_update']))
        <a href="{{ route('admin.updates.index') }}" class="px-5 py-2.5 text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إعادة ضبط</a>
    @endif

    <div class="flex-grow"></div>

    <button type="button"
            @click="$dispatch('open-confirm', { message: 'هل أنت متأكد من اعتماد جميع التحديثات الظاهرة حالياً؟ سيتم توثيقها جميعاً دفعة واحدة.', action: () => document.getElementById('approve-all-form').submit() })"
            class="px-5 py-2.5 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition flex items-center gap-2 shadow-lg shadow-emerald-100">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        اعتماد الكل
    </button>
</form>

<form id="approve-all-form" action="{{ route('admin.updates.approve-all') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="station_id" value="{{ request('station_id') }}">
</form>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs">المحطة</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs hidden md:table-cell">المُبلِّغ</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs hidden lg:table-cell">الوقود</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs hidden lg:table-cell text-center">الازدحام</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs text-center">الحالة</th>
                    <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs text-left">حذف</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($updates as $update)
                <tr class="hover:bg-slate-50 transition group">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-slate-800">{{ $update->station?->name_ar ?? $update->station?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $update->created_at->diffForHumans() }}</p>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell text-xs text-slate-500">
                        {{ $update->user?->phone ?? 'مجهول' }}
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell">
                        <div class="flex gap-1 flex-wrap">
                            @foreach(['petrol'=>'بنزين','diesel'=>'ديزل','gas'=>'غاز'] as $fuel => $label)
                                @if($update->$fuel)
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    {{ $update->$fuel==='available' ? 'bg-emerald-50 text-emerald-600' : ($update->$fuel==='limited' ? 'bg-orange-50 text-orange-500' : 'bg-red-50 text-red-500') }}">
                                    {{ $label }}
                                </span>
                                @endif
                            @endforeach
                        </div>
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell text-center">
                        @if($update->congestion)
                        <span class="text-xs px-2.5 py-0.5 rounded-full
                            {{ $update->congestion==='low' ? 'bg-emerald-50 text-emerald-600' : ($update->congestion==='medium' ? 'bg-orange-50 text-orange-500' : 'bg-red-50 text-red-500') }}">
                            {{ $update->congestion==='low' ? 'خفيف' : ($update->congestion==='medium' ? 'متوسط' : 'عالي') }}
                        </span>
                        @else <span class="text-slate-300">—</span> @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full
                            {{ $update->is_admin_update ? 'bg-blue-50 text-blue-600' : ($update->is_verified ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-500') }}">
                            {{ $update->is_admin_update ? 'إداري' : ($update->is_verified ? 'موثق' : 'مراجعة') }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-left flex items-center justify-end gap-2">
                        @if(!$update->is_verified && !$update->is_admin_update)
                        <form method="POST" action="{{ route('admin.updates.approve', $update) }}">
                            @csrf
                            <button type="submit" title="اعتماد التحديث" class="px-3 py-1 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                                اعتماد
                            </button>
                        </form>
                        @endif

                        <form method="POST" action="{{ route('admin.updates.destroy', $update) }}"
                              @submit.prevent="$dispatch('open-confirm', { message: 'حذف هذا التحديث من السجلات؟ سيؤثر هذا على إحصائيات المحطة.', action: () => $el.submit() })"
                              class="transition">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-16 text-center text-sm text-slate-400">لا توجد تحديثات مسجلة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($updates->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $updates->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
