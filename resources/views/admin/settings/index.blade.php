@extends('admin.layouts.app')
@section('title', 'الإعدادات')

@section('content')
<div x-data="{ activeTab: '{{ session('active_tab', 'otp') }}' }">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[#2F2B3D] text-2xl font-bold">إعدادات النظام</h1>
            <p class="text-secondary text-sm">إدارة كافة إعدادات المنصة، الربط البرمجي، وتنبيهات النظام</p>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($groups as $key => $group)
        <button @click="activeTab = '{{ $key }}'"
                :class="activeTab === '{{ $key }}' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white text-secondary hover:bg-slate-50'"
                class="px-6 py-2.5 rounded-lg text-sm font-bold transition flex items-center gap-2 border border-slate-100">
            <i class="ti {{ $key === 'otp' ? 'ti-lock-check' : ($key === 'stations' ? 'ti-gas-station' : ($key === 'notifications' ? 'ti-bell' : ($key === 'telegram' ? 'ti-brand-telegram' : ($key === 'pages' ? 'ti-file-description' : 'ti-settings')))) }} text-lg"></i>
            {{ $group['label'] }}
        </button>
        @endforeach
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('POST')
        <input type="hidden" name="active_tab" :value="activeTab">

        @foreach($groups as $groupKey => $group)
        <div x-show="activeTab === '{{ $groupKey }}'" x-cloak x-transition>
            <div class="materio-card">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                         <div class="w-10 h-10 rounded bg-primary/10 text-primary flex items-center justify-center">
                             <i class="ti {{ $groupKey === 'otp' ? 'ti-shield-lock' : ($groupKey === 'stations' ? 'ti-gas-station' : ($groupKey === 'notifications' ? 'ti-bell' : ($groupKey === 'telegram' ? 'ti-brand-telegram' : ($groupKey === 'pages' ? 'ti-file-description' : 'ti-app-window')))) }} text-2xl"></i>
                         </div>
                         <h3 class="text-[#2F2B3D] font-bold text-lg">{{ $group['label'] }}</h3>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        @if($groupKey === 'otp')
                        <button type="button" onclick="openTestOtp()" class="px-4 py-2 bg-success/10 text-success rounded-lg text-xs font-bold hover:bg-success hover:text-white transition flex items-center gap-2">
                            <i class="ti ti-device-mobile-message text-lg"></i> اختبار إرسال OTP
                        </button>
                        @endif

                        @if($groupKey === 'telegram')
                        <button type="button" onclick="sendTestTelegram()" id="btnTestTelegram" class="px-4 py-2 bg-info/10 text-info rounded-lg text-xs font-bold hover:bg-info hover:text-white transition flex items-center gap-2">
                            <i class="ti ti-brand-telegram text-lg"></i> اختبار البوت
                        </button>
                        @endif

                        @if($groupKey === 'notifications')
                        <button type="button" onclick="sendTestFcm()" id="btnTestFcm" class="px-4 py-2 bg-warning/10 text-warning rounded-lg text-xs font-bold hover:bg-warning hover:text-white transition flex items-center gap-2">
                            <i class="ti ti-notification text-lg"></i> اختبار الإشعارات (FCM)
                        </button>
                        @endif
                    </div>
                </div>

                <div class="p-6 divide-y divide-slate-100">
                    @foreach($group['settings'] as $setting)
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col gap-2">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-[#2F2B3D] mb-1">
                                {{ $setting->label ?? $setting->key }}
                            </label>
                            @if($setting->description)
                            <p class="text-xs text-secondary leading-relaxed mb-2">{{ $setting->description }}</p>
                            @endif
                        </div>
                        <div class="w-full">
                            @if($setting->type === 'boolean')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="{{ $setting->key }}" value="1"
                                           {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-12 h-6 bg-slate-200 rounded-full peer peer-checked:bg-primary transition-all duration-300 relative
                                                after:content-[''] after:absolute after:top-[2px] after:right-[2px] 
                                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all 
                                                peer-checked:after:-translate-x-6 shadow-inner"></div>
                                </label>
                            @elseif($setting->key === 'otpiq_channel')
                                <select name="{{ $setting->key }}"
                                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary bg-white text-sm">
                                    @foreach(['whatsapp-sms' => 'واتساب ثم SMS', 'whatsapp-telegram-sms' => 'واتساب ثم تيليغرام ثم SMS', 'whatsapp' => 'واتساب فقط', 'telegram' => 'تيليغرام فقط', 'sms' => 'SMS فقط'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ $setting->value === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            @elseif($setting->type === 'integer')
                                <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm">
                            @elseif($setting->type === 'textarea')
                                <textarea name="{{ $setting->key }}" rows="4"
                                          class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm">{{ $setting->value }}</textarea>
                            @elseif($setting->type === 'text')
                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm">
                            @elseif(str_contains($setting->key, 'key') || str_contains($setting->key, 'secret') || str_contains($setting->key, 'token'))
                                <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                       autocomplete="new-password"
                                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm font-mono" dir="ltr">
                            @else
                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="btn-primary flex items-center gap-2">
                        <i class="ti ti-device-floppy text-lg"></i> حفظ كافة التغييرات
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </form>
</div>

{{-- ── Test OTP Modal ── --}}
<div id="testOtpModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#171925]/60 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-materio w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-[#2F2B3D] font-bold text-lg">اختبار إرسال OTP</h3>
            <button onclick="closeTestOtp()" class="text-secondary hover:text-error transition"><i class="ti ti-x text-2xl"></i></button>
        </div>
        <div class="p-6">
            <p class="text-sm text-secondary mb-4">أدخل رقم الهاتف لاختبار بوابة الربط الحالية</p>
            <input type="text" id="testPhone" placeholder="964750XXXXXXX"
                   class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-primary text-sm font-mono mb-4"
                   dir="ltr">

            <div id="testOtpResult" class="hidden mb-4 p-4 rounded-lg text-xs font-bold leading-relaxed"></div>

            <div class="flex gap-3">
                <button onclick="sendTestOtp()" class="btn-primary flex-1">إرسال الاختبار</button>
                <button onclick="closeTestOtp()" class="px-6 py-2 bg-slate-100 text-secondary rounded-lg font-bold">إلغاء</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openTestOtp()  { document.getElementById('testOtpModal').classList.remove('hidden'); }
function closeTestOtp() { document.getElementById('testOtpModal').classList.add('hidden'); }

async function sendTestOtp() {
    const phone  = document.getElementById('testPhone').value.trim();
    const result = document.getElementById('testOtpResult');

    if (!phone) { alert('أدخل رقم الهاتف'); return; }

    result.className = 'mb-4 p-4 rounded-lg text-xs font-bold bg-slate-100 text-secondary';
    result.textContent = 'جارٍ الإرسال...';
    result.classList.remove('hidden');

    try {
        const res  = await fetch('{{ route("admin.settings.test-otp") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body:    JSON.stringify({ phone }),
        });
        const data = await res.json();

        if (data.success) {
            result.className = 'mb-4 p-4 rounded-lg text-xs font-bold bg-success/10 text-success';
            result.innerHTML = '✅ ' + data.message + (data.remaining !== undefined ? ` — الرصيد: ${data.remaining}` : '');
        } else {
            result.className = 'mb-4 p-4 rounded-lg text-xs font-bold bg-error/10 text-error';
            result.textContent = '❌ ' + data.message;
        }
    } catch (e) {
        result.className = 'mb-4 p-4 rounded-lg text-xs font-bold bg-error/10 text-error';
        result.textContent = 'خطأ في الاتصال';
    }
}

async function sendTestTelegram() {
    const btn = document.getElementById('btnTestTelegram');
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> جارٍ الإرسال...';
    
    try {
        const res = await fetch('{{ route("admin.settings.test-telegram") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        alert(data.message);
    } catch (e) {
        alert('خطأ في الاتصال');
    } finally {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
    }
}

async function sendTestFcm() {
    const btn = document.getElementById('btnTestFcm');
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> جارٍ الإرسال...';
    
    try {
        const res = await fetch('{{ route("admin.settings.test-fcm") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        alert(data.message);
    } catch (e) {
        alert('خطأ في الاتصال');
    } finally {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
    }
}
</script>
@endpush
@endsection
