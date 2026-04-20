@php
$inp = 'w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500';
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">اسم المحطة بالعربي</label>
        <input type="text" name="name_ar" value="{{ old('name_ar', $station->name_ar ?? '') }}" required placeholder="محطة المنصور" class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">المدينة</label>
        <input type="text" name="city" value="{{ old('city', $station->city ?? '') }}" required placeholder="بغداد" class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">المنطقة / الحي</label>
        <input type="text" name="district" value="{{ old('district', $station->district ?? '') }}" placeholder="المنصور" class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">العنوان</label>
        <input type="text" name="address" value="{{ old('address', $station->address ?? '') }}" placeholder="شارع الأميرات..." class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">خط العرض (Latitude)</label>
        <input type="text" name="latitude" value="{{ old('latitude', $station->latitude ?? '') }}" required placeholder="33.3152" dir="ltr" class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">خط الطول (Longitude)</label>
        <input type="text" name="longitude" value="{{ old('longitude', $station->longitude ?? '') }}" required placeholder="44.3661" dir="ltr" class="{{ $inp }}">
    </div>
</div>
