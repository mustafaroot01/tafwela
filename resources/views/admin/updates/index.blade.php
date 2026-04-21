@extends('admin.layouts.app')
@section('title', 'التحديثات')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-[#2F2B3D] text-2xl font-bold">مراقبة التحديثات</h1>
        <p class="text-secondary text-sm">مراجعة واعتماد تحديثات حالة الوقود المرسلة</p>
    </div>
    <form id="approve-all-form" action="{{ route('admin.updates.approve-all') }}" method="POST"
          @submit.prevent="$dispatch('open-confirm', { message: 'اعتماد جميع التحديثات؟', action: () => $el.submit() })">
        @csrf
        <input type="hidden" name="station_id" value="{{ request('station_id') }}">
        <button type="submit" class="btn-primary !bg-success flex items-center gap-2">
            <i class="ti ti-checklist text-lg"></i> اعتماد الكل
        </button>
    </form>
</div>

<div class="materio-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">المحطة</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">المُبلِّغ</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-center">الحالة</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-left">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($updates as $update)
                <tr class="hover:bg-slate-50/50 transition group">
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-[#2F2B3D]">{{ $update->station?->name_ar ?? '—' }}</span>
                            <span class="text-[11px] text-secondary font-mono">{{ $update->created_at->diffForHumans() }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-secondary font-mono" dir="ltr">{{ $update->user?->phone ?? 'مجهول' }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                            {{ $update->is_admin_update ? 'bg-primary/10 text-primary' : ($update->is_verified ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning') }}">
                            {{ $update->is_admin_update ? 'إداري' : ($update->is_verified ? 'موثق' : 'مراجعة') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @if(!$update->is_verified && !$update->is_admin_update)
                            <form method="POST" action="{{ route('admin.updates.approve', $update) }}" class="action-form">
                                @csrf
                                <button type="submit" title="اعتماد" class="action-btn w-9 h-9 rounded-lg bg-success/10 text-success hover:bg-success hover:text-white">
                                    <i class="ti ti-check text-xl"></i>
                                </button>
                            </form>
                            @endif

                            <form method="POST" action="{{ route('admin.updates.destroy', $update) }}"
                                  @submit.prevent="$dispatch('open-confirm', { message: 'حذف التحديث؟', action: () => $el.submit() })"
                                  class="action-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn w-9 h-9 rounded-lg bg-error/10 text-error hover:bg-error hover:text-white" title="حذف">
                                    <i class="ti ti-trash text-xl"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-16 text-center text-secondary">لا توجد تحديثات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
