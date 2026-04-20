@extends('admin.layouts.app')
@section('title', isset($employee) ? 'تعديل موظف' : 'إضافة موظف')
@section('header', isset($employee) ? 'تعديل موظف' : 'إضافة موظف جديد')

@section('content')

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="w-full">
    <form method="POST"
          action="{{ isset($employee) ? route('admin.employees.update', $employee) : route('admin.employees.store') }}"
          class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
        @csrf
        @isset($employee) @method('PUT') @endisset

        {{-- Phone --}}
        @if(!isset($employee))
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">رقم الهاتف <span class="text-red-500">*</span></label>
            <input type="text" name="phone" value="{{ old('phone', $prefilledPhone ?? '') }}"
                   placeholder="+9647xxxxxxxxx" dir="ltr"
                   class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="mt-2 text-xs text-slate-400">إذا لم يكن للمستخدم حساب سيُنشأ تلقائياً.</p>
        </div>
        @else
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">رقم الهاتف</label>
            <p class="px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-700" dir="ltr">
                {{ $employee->phone }}
            </p>
        </div>
        @endif

        {{-- Name --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">الاسم (اختياري)</label>
            <input type="text" name="name"
                   value="{{ old('name', $employee->name ?? '') }}"
                   placeholder="اسم الموظف"
                   class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Station --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">المحطة المعينة <span class="text-red-500">*</span></label>
            <select name="station_id"
                    class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="" class="text-slate-500">— اختر المحطة —</option>
                @foreach($stations as $station)
                    <option value="{{ $station->id }}" class="text-slate-900"
                        {{ old('station_id', $employee->station_id ?? $prefilledStation ?? '') == $station->id ? 'selected' : '' }}>
                        {{ $station->name_ar ?: $station->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-400">الموظف يستطيع تحديث هذه المحطة فقط بشكل مباشر بدون انتظار تأكيدات.</p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition">
                {{ isset($employee) ? 'حفظ التعديلات' : 'إضافة الموظف' }}
            </button>
            <a href="{{ route('admin.employees.index') }}"
               class="text-sm text-slate-500 hover:text-slate-700">إلغاء</a>
        </div>
    </form>
</div>

@endsection
