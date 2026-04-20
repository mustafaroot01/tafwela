@extends('admin.layouts.app')
@section('title', 'إضافة محطة جديدة')
@section('header', 'إضافة محطة جديدة')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('admin.stations.index') }}" class="text-sm font-black text-slate-400 hover:text-brand-600 transition-colors flex items-center gap-2">
            <span>→</span>
            العودة لقائمة المحطات
        </a>
    </div>

    <form method="POST" action="{{ route('admin.stations.store') }}" class="space-y-8">
        @csrf
        
        <div class="bg-white rounded-[2.5rem] p-10 border border-slate-100 shadow-sm">
            @include('admin.stations._form')
        </div>

        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.stations.index') }}" 
               class="px-8 py-4 bg-slate-100 text-slate-600 text-sm font-black rounded-2xl hover:bg-slate-200 transition-all">
                إلغاء
            </a>
            <button type="submit" 
                    class="px-12 py-4 bg-brand-600 text-white text-sm font-black rounded-2xl hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                حفظ المحطة الجديدة
            </button>
        </div>
    </form>
</div>
@endsection
