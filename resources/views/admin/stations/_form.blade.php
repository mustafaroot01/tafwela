<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-[#2F2B3D]">اسم المحطة بالعربي</label>
        <input type="text" name="name_ar" value="{{ old('name_ar', $station->name_ar ?? '') }}" required placeholder="محطة المنصور" 
               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm">
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-[#2F2B3D]">المدينة</label>
        <input type="text" name="city" value="{{ old('city', $station->city ?? '') }}" required placeholder="بغداد" 
               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm">
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-[#2F2B3D]">المنطقة / الحي</label>
        <input type="text" name="district" value="{{ old('district', $station->district ?? '') }}" placeholder="المنصور" 
               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm">
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-[#2F2B3D]">العنوان</label>
        <input type="text" name="address" value="{{ old('address', $station->address ?? '') }}" placeholder="شارع الأميرات..." 
               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm">
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-[#2F2B3D]">خط العرض (Latitude)</label>
        <input type="text" name="latitude" value="{{ old('latitude', $station->latitude ?? '') }}" required placeholder="33.3152" dir="ltr" 
               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm font-mono">
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-[#2F2B3D]">خط الطول (Longitude)</label>
        <input type="text" name="longitude" value="{{ old('longitude', $station->longitude ?? '') }}" required placeholder="44.3661" dir="ltr" 
               class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition bg-white text-sm font-mono">
    </div>
</div>

