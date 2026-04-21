@extends('admin.layouts.app')
@section('title', 'موظفو المحطات')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-[#2F2B3D] text-2xl font-bold">إدارة الموظفين</h1>
        <p class="text-secondary text-sm">إدارة حسابات موظفي المحطات</p>
    </div>
    <a href="{{ route('admin.employees.create') }}" class="btn-primary flex items-center gap-2 self-start">
        <i class="ti ti-user-plus text-lg"></i> إضافة موظف جديد
    </a>
</div>

{{-- Table --}}
<div class="materio-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">الموظف</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider">المحطة</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-center">الحالة</th>
                    <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-wider text-left">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($employees as $emp)
                <tr class="hover:bg-slate-50/50 transition group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
                                {{ mb_strtoupper(mb_substr($emp->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-[#2F2B3D]">{{ $emp->name ?? '—' }}</span>
                                <span class="text-[10px] text-secondary font-mono" dir="ltr">{{ $emp->phone }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-[#2F2B3D]">
                        {{ $emp->assignedStation->name_ar ?? 'غير معين' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $emp->is_banned ? 'bg-error/10 text-error' : 'bg-success/10 text-success' }}">
                            {{ $emp->is_banned ? 'محظور' : 'نشط' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.employees.edit', $emp) }}" title="تعديل"
                               class="action-btn w-9 h-9 rounded-lg bg-info/10 text-info hover:bg-info hover:text-white">
                                <i class="ti ti-edit text-xl"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.employees.destroy', $emp) }}"
                                  @submit.prevent="$dispatch('open-confirm', { message: 'إلغاء صلاحية الموظف؟', action: () => $el.submit() })"
                                  class="action-form">
                                @csrf @method('DELETE')
                                <button type="submit" title="حذف" class="action-btn w-9 h-9 rounded-lg bg-error/10 text-error hover:bg-error hover:text-white">
                                    <i class="ti ti-trash text-xl"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-16 text-center text-secondary">لا يوجد موظفون</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
