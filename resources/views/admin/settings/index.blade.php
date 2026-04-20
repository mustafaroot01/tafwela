@extends('admin.layouts.app')
@section('title', 'الإعدادات')
@section('header', 'إعدادات النظام')

@section('content')
<div x-data="{ activeTab: '{{ session('active_tab', 'otp') }}' }">

    {{-- ── Tabs ── --}}
    <div class="flex gap-1 mb-6 bg-white border border-slate-200 rounded-xl p-1 w-fit">
        @foreach($groups as $key => $group)
        <button @click="activeTab = '{{ $key }}'"
                :class="activeTab === '{{ $key }}' ? 'bg-blue-600 text-white shadow' : 'text-slate-500 hover:text-slate-700'"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition">
            {{ $group['label'] }}
        </button>
        @endforeach
    </div>

    {{-- Local alert removed, using global modal from layout --}}

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('POST')
        
        {{-- Hidden input to persist active tab --}}
        <input type="hidden" name="active_tab" :value="activeTab">

        @foreach($groups as $groupKey => $group)
        <div x-show="activeTab === '{{ $groupKey }}'" x-cloak>
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800">{{ $group['label'] }}</h3>
                    
                    @if($groupKey === 'otp')
                    <button type="button" onclick="openTestOtp()"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-white text-emerald-600 border border-emerald-200 rounded-lg hover:bg-emerald-50 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        اختبار إرسال OTP
                    </button>
                    @endif

                    @if($groupKey === 'telegram')
                    <button type="button" onclick="sendTestTelegram()" id="btnTestTelegram"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-white text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        اختبار البوت
                    </button>
                    @endif

                    @if($groupKey === 'notifications')
                    <button type="button" onclick="sendTestFcm()" id="btnTestFcm"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-white text-orange-600 border border-orange-200 rounded-lg hover:bg-orange-50 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        اختبار الإشعارات (FCM)
                    </button>
                    @endif
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($group['settings'] as $setting)
                    <div class="px-6 py-5 flex items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <label class="block text-sm font-bold text-slate-700 mb-1">
                                {{ $setting->label ?? $setting->key }}
                            </label>
                            @if($setting->description)
                            <p class="text-xs text-slate-400 mb-2 leading-relaxed">{{ $setting->description }}</p>
                            @endif
                        </div>
                        <div class="w-80 flex-shrink-0 flex justify-end">
                            @if($setting->type === 'boolean')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="{{ $setting->key }}" value="1"
                                           {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer 
                                                peer-checked:bg-blue-600 transition-all duration-300 relative
                                                after:content-[''] after:absolute after:top-[2px] after:right-[2px] 
                                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all 
                                                peer-checked:after:-translate-x-5 shadow-inner"></div>
                                </label>
                            @elseif($setting->key === 'otpiq_channel')
                                <select name="{{ $setting->key }}"
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    @foreach(['whatsapp-sms' => 'واتساب ثم SMS', 'whatsapp-telegram-sms' => 'واتساب ثم تيليغرام ثم SMS', 'whatsapp' => 'واتساب فقط', 'telegram' => 'تيليغرام فقط', 'sms' => 'SMS فقط'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ $setting->value === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            @elseif($setting->type === 'integer')
                                <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @elseif(str_contains($setting->key, 'key') || str_contains($setting->key, 'secret') || str_contains($setting->key, 'token'))
                                <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                       autocomplete="new-password"
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                            @else
                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition shadow-md shadow-blue-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ التغييرات
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </form>
</div>

{{-- ── Test OTP Modal ── --}}
<div id="testOtpModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="font-bold text-slate-800 text-lg mb-4 text-right">اختبار إرسال OTP</h3>
        <p class="text-sm text-slate-500 mb-4 text-right">أدخل رقم الهاتف وسيُرسَل رمز تجريبي عبر القناة المضبوطة</p>

        <input type="text" id="testPhone" placeholder="964750XXXXXXX"
               class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
               dir="ltr">

        <div id="testOtpResult" class="hidden mb-4 p-3 rounded-xl text-sm font-semibold"></div>

        <div class="flex gap-3 flex-row-reverse">
            <button onclick="sendTestOtp()"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl text-sm transition">
                إرسال الاختبار
            </button>
            <button onclick="closeTestOtp()"
                    class="px-5 text-slate-500 hover:text-slate-700 font-bold text-sm transition">
                إغلاق
            </button>
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

    result.className = 'mb-4 p-3 rounded-xl text-sm font-semibold bg-slate-50 text-slate-500';
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
            result.className = 'mb-4 p-3 rounded-xl text-sm font-semibold bg-emerald-50 text-emerald-700';
            result.innerHTML = '✅ ' + data.message + (data.remaining !== undefined ? ` — الرصيد المتبقي: ${data.remaining}` : '');
        } else {
            result.className = 'mb-4 p-3 rounded-xl text-sm font-semibold bg-red-50 text-red-600';
            result.textContent = '❌ ' + data.message;
        }
    } catch (e) {
        result.className = 'mb-4 p-3 rounded-xl text-sm font-semibold bg-red-50 text-red-600';
        result.textContent = 'خطأ في الاتصال';
    }
}

async function sendTestTelegram() {
    const btn = document.getElementById('btnTestTelegram');
    const oldText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'جارٍ الإرسال...';
    
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
        btn.innerHTML = oldText;
    }
}

async function sendTestFcm() {
    const btn = document.getElementById('btnTestFcm');
    const oldText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'جارٍ الإرسال...';
    
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
        btn.innerHTML = oldText;
    }
}
</script>
@endpush
@endsection
