@extends('admin.layouts.app')
@section('title', 'موظفو المحطات')
@section('header', 'موظفو المحطات')

@section('content')

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-slate-500">إدارة حسابات موظفي المحطات — يمكن كل موظف تحديث محطته مباشرة بدون انتظار تأكيدات.</p>
    <a href="{{ route('admin.employees.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        إضافة موظف
    </a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm text-right">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3 font-semibold text-slate-600">#</th>
                <th class="px-4 py-3 font-semibold text-slate-600">الاسم</th>
                <th class="px-4 py-3 font-semibold text-slate-600">رقم الهاتف</th>
                <th class="px-4 py-3 font-semibold text-slate-600">المحطة المعينة</th>
                <th class="px-4 py-3 font-semibold text-slate-600">الحالة</th>
                <th class="px-4 py-3 font-semibold text-slate-600">تاريخ الإنشاء</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($employees as $emp)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3 text-slate-400">{{ $emp->id }}</td>
                <td class="px-4 py-3 font-medium text-slate-800">{{ $emp->name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600 font-mono" dir="ltr">{{ $emp->phone }}</td>
                <td class="px-4 py-3">
                    @if($emp->assignedStation)
                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            {{ $emp->assignedStation->name_ar ?: $emp->assignedStation->name }}
                        </span>
                    @else
                        <span class="text-slate-400 text-xs">لم تُعيَّن</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($emp->is_banned)
                        <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold">محظور</span>
                    @else
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">نشط</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-slate-400 text-xs">{{ $emp->created_at->format('Y/m/d') }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2 justify-end">
                        <a href="{{ route('admin.employees.edit', $emp) }}"
                           class="text-xs text-blue-600 hover:underline font-semibold">تعديل</a>
                        <form method="POST" action="{{ route('admin.employees.destroy', $emp) }}"
                              @submit.prevent="$dispatch('open-confirm', { message: 'هل أنت متأكد من إلغاء صلاحية هذا الموظف؟ لن يتمكن من تحديث حالة المحطة بعد الآن.', action: () => $el.submit() })">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline font-semibold">
                                إلغاء الصلاحية
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                    لا يوجد موظفون مضافون بعد.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $employees->links() }}
    </div>
</div>

@endsection
