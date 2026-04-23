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

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- ── Sidebar Navigation ── --}}
        <div class="w-full lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden sticky top-24">
                <div class="p-4 border-b border-slate-50 bg-slate-50/50">
                    <p class="text-[10px] font-bold text-secondary uppercase tracking-wider">أقسام الإعدادات</p>
                </div>
                <div class="p-2 flex flex-col gap-1">
                    @foreach($groups as $key => $group)
                    <button @click="activeTab = '{{ $key }}'"
                            :class="activeTab === '{{ $key }}' ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-slate-50'"
                            class="w-full px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 flex items-center gap-3 group text-right">
                        @php
                            $icon = match($key) {
                                'otp' => 'ti-shield-lock',
                                'stations' => 'ti-gas-station',
                                'notifications' => 'ti-bell',
                                'telegram', 'telegram_public' => 'ti-brand-telegram',
                                'app' => 'ti-settings',
                                'pages' => 'ti-file-description',
                                default => 'ti-app-window'
                            };
                        @endphp
                        <div :class="activeTab === '{{ $key }}' ? 'bg-primary text-white' : 'bg-slate-100 text-secondary group-hover:bg-slate-200'" 
                             class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors">
                            <i class="ti {{ $icon }} text-base"></i>
                        </div>
                        <span class="flex-1">{{ $group['label'] }}</span>
                        <i class="ti ti-chevron-left text-xs opacity-0 group-hover:opacity-100 transition-all" :class="activeTab === '{{ $key }}' ? 'opacity-100 translate-x-0' : 'translate-x-2'"></i>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Settings Content ── --}}
        <div class="flex-1">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('POST')
                <input type="hidden" name="active_tab" :value="activeTab">

                @foreach($groups as $groupKey => $group)
                <div x-show="activeTab === '{{ $groupKey }}'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/50 backdrop-blur-md">
                            <div class="flex items-center gap-3">
                                <h3 class="text-[#2F2B3D] font-bold text-xl">{{ $group['label'] }}</h3>
                            </div>
                            
                            <div class="flex flex-wrap gap-2">
                                @if($groupKey === 'otp')
                                <button type="button" onclick="openTestOtp()" class="px-4 py-2 bg-success/10 text-success rounded-lg text-xs font-bold hover:bg-success hover:text-white transition flex items-center gap-2">
                                    <i class="ti ti-device-mobile-message text-lg"></i> اختبار OTP
                                </button>
                                @endif

                                @if($groupKey === 'telegram')
                                <button type="button" onclick="sendTestTelegram()" id="btnTestTelegram" class="px-4 py-2 bg-info/10 text-info rounded-lg text-xs font-bold hover:bg-info hover:text-white transition flex items-center gap-2">
                                    <i class="ti ti-brand-telegram text-lg"></i> اختبار البوت
                                </button>
                                @endif

                                @if($groupKey === 'telegram_public')
                                <button type="button" onclick="setPublicWebhook()" id="btnSetWebhook" class="px-4 py-2 bg-primary/10 text-primary rounded-lg text-xs font-bold hover:bg-primary hover:text-white transition flex items-center gap-2">
                                    <i class="ti ti-link text-lg"></i> ضبط Webhook
                                </button>
                                @endif

                                @if($groupKey === 'notifications')
                                <button type="button" onclick="sendTestFcm()" id="btnTestFcm" class="px-4 py-2 bg-warning/10 text-warning rounded-lg text-xs font-bold hover:bg-warning hover:text-white transition flex items-center gap-2">
                                    <i class="ti ti-notification text-lg"></i> اختبار FCM
                                </button>
                                @endif
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                                @foreach($group['settings'] as $setting)
                                <div class="flex flex-col gap-2 {{ $setting->type === 'textarea' ? 'md:col-span-2' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-bold text-[#2F2B3D]">
                                            {{ $setting->label ?? $setting->key }}
                                        </label>
                                        @if($setting->type === 'boolean')
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="{{ $setting->key }}" value="1"
                                                       {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-primary transition-all duration-300 relative
                                                            after:content-[''] after:absolute after:top-[2px] after:right-[2px] 
                                                            after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all 
                                                            peer-checked:after:-translate-x-5 shadow-inner"></div>
                                            </label>
                                        @endif
                                    </div>
                                    
                                    @if($setting->description)
                                    <p class="text-[11px] text-secondary/70 leading-relaxed mb-1">{{ $setting->description }}</p>
                                    @endif

                                    <div class="w-full">
                                        @if($setting->type === 'boolean')
                                            {{-- Toggle handled above for layout --}}
                                        @elseif($setting->key === 'otpiq_channel')
                                            <select name="{{ $setting->key }}"
                                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all bg-slate-50/30 text-sm">
                                                @foreach(['whatsapp-sms' => 'واتساب ثم SMS', 'whatsapp-telegram-sms' => 'واتساب ثم تيليغرام ثم SMS', 'whatsapp' => 'واتساب فقط', 'telegram' => 'تيليغرام فقط', 'sms' => 'SMS فقط'] as $val => $lbl)
                                                <option value="{{ $val }}" {{ $setting->value === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($setting->type === 'integer')
                                            <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all bg-slate-50/30 text-sm font-semibold">
                                        @elseif($setting->type === 'textarea')
                                            <textarea name="{{ $setting->key }}" rows="4"
                                                      class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all bg-slate-50/30 text-sm leading-relaxed">{{ $setting->value }}</textarea>
                                        @elseif($setting->type === 'text')
                                            <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all bg-slate-50/30 text-sm font-medium">
                                        @elseif(str_contains($setting->key, 'key') || str_contains($setting->key, 'secret') || str_contains($setting->key, 'token'))
                                            <div class="relative group/input">
                                                <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                       autocomplete="new-password"
                                                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all bg-slate-50/30 text-sm font-mono" dir="ltr">
                                                <div class="absolute inset-y-0 left-3 flex items-center opacity-40 group-focus-within/input:opacity-100 transition-opacity">
                                                    <i class="ti ti-lock text-xs"></i>
                                                </div>
                                            </div>
                                        @else
                                            <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all bg-slate-50/30 text-sm">
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="bg-primary text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                                <i class="ti ti-device-floppy text-lg"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </form>
        </div>
    </div>
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
async function setPublicWebhook() {
    const btn = document.getElementById('btnSetWebhook');
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> جارٍ الضبط...';
    
    try {
        const res = await fetch('{{ route("admin.settings.set-telegram-webhook") }}', {
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
