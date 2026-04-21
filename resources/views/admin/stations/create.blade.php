@extends('admin.layouts.app')
@section('title', 'إضافة محطة جديدة')

@section('content')
<div class="max-w-4xl mx-auto">
    
    {{-- Page Header --}}
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.stations.index') }}" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-secondary hover:text-primary transition shadow-sm">
            <i class="ti ti-arrow-right text-xl"></i>
        </a>
        <div>
            <h1 class="text-[#2F2B3D] text-2xl font-bold">إضافة محطة وقود جديدة</h1>
            <p class="text-secondary text-sm">إضافة بيانات الموقع والمعلومات الأساسية للمحطة</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.stations.store') }}" class="space-y-6">
        @csrf
        
        <div class="materio-card p-10">
            @include('admin.stations._form')
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="btn-primary px-10 flex items-center gap-2">
                <i class="ti ti-device-floppy text-lg"></i> حفظ بيانات المحطة
            </button>
            <a href="{{ route('admin.stations.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-secondary rounded-lg font-bold hover:bg-slate-50 transition">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
