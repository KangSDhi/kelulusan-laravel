<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNtpTime } from '../composable/useNtpTime.js'

const router = useRouter()
const nisn = ref('')
const tanggalLahir = ref('')
const loading = ref(false)
const error = ref('')
const shake = ref(false)

const TARGET_DATE = new Date('2026-04-28T22:17:00')
const { now, ntpReady, ntpError, ntpLoading } = useNtpTime()

watch([ntpReady, ntpError], () => {
    if (ntpLoading.value) return
    if (ntpError.value) { router.replace('/'); return }
    if (now.value && now.value < TARGET_DATE) router.replace('/')
})

const isValid = computed(() => nisn.value.trim().length >= 10 && tanggalLahir.value)

async function handleLogin() {
    if (!isValid.value) { triggerShake('Mohon lengkapi NISN dan Tanggal Lahir.'); return }
    if (!ntpReady.value || !now.value || now.value < TARGET_DATE) {
        triggerShake('Sistem belum siap atau waktu belum valid.'); return
    }
    loading.value = true
    error.value = ''
    await new Promise(r => setTimeout(r, 1800))
    loading.value = false
    triggerShake('NISN atau Tanggal Lahir tidak ditemukan.')
}

function triggerShake(msg) {
    error.value = msg
    shake.value = true
    setTimeout(() => shake.value = false, 600)
}
</script>

<template>
    <div class="min-h-screen bg-blue-50 flex items-center justify-center px-4 py-10 relative overflow-hidden" style="font-family:'Plus Jakarta Sans',sans-serif">

        <!-- Background decoration -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute w-[600px] h-[600px] rounded-full bg-blue-200 opacity-60 blur-[70px] -top-52 -right-36"></div>
            <div class="absolute w-[500px] h-[500px] rounded-full bg-blue-100 opacity-55 blur-[70px] -bottom-52 -left-24"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(circle, #93c5fd44 1px, transparent 1px); background-size: 36px 36px;"></div>
        </div>

        <!-- NTP Loading overlay -->
        <transition name="fade">
            <div v-if="ntpLoading" class="fixed inset-0 bg-blue-50 z-50 flex items-center justify-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-11 h-11 border-[3px] border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="font-bold text-[0.95rem] text-blue-800 m-0" style="font-family:'Plus Jakarta Sans',sans-serif">
                        Menyinkronkan waktu server…
                    </p>
                    <p class="text-[0.75rem] text-blue-300 m-0">Mohon tunggu sebentar</p>
                </div>
            </div>
        </transition>

        <!-- Card -->
        <transition name="rise">
            <div v-if="!ntpLoading"
                 class="relative z-10 w-full max-w-[440px]"
                 :class="{ 'animate-[shake_0.55s_ease]': shake }">

                <div class="bg-white rounded-2xl overflow-hidden border border-blue-100"
                     style="box-shadow: 0 1px 3px rgba(37,99,235,.08), 0 8px 32px rgba(37,99,235,.12), 0 24px 64px rgba(37,99,235,.08)">

                    <!-- Accent bar -->
                    <div class="h-[5px]" style="background: linear-gradient(90deg, #1d4ed8, #3b82f6, #60a5fa)"></div>

                    <!-- Header -->
                    <div class="flex flex-col items-center text-center px-9 pt-8 pb-6 gap-2.5">
                        <div class="w-16 h-16 rounded-[18px] flex items-center justify-center text-[1.8rem] mb-1"
                             style="background: linear-gradient(135deg,#2563eb,#3b82f6); box-shadow: 0 8px 24px rgba(37,99,235,.35)">
                            🎓
                        </div>
                        <h1 class="text-2xl font-extrabold text-blue-900 m-0 tracking-tight" style="letter-spacing:-0.02em; font-family:'Plus Jakarta Sans',sans-serif">
                            Cek Kelulusan
                        </h1>
                        <p class="text-[0.78rem] text-slate-500 m-0 leading-relaxed">
                            Masukkan data diri untuk melihat hasil kelulusan
                        </p>

                        <!-- NTP time badge -->
                        <div v-if="ntpReady && now"
                             class="inline-flex items-center gap-1.5 bg-blue-50 border border-blue-200 rounded-full px-3 py-1 text-[0.68rem] text-blue-700 mt-1"
                             style="font-family:'DM Mono',monospace;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ now.toLocaleString('id-ID', { timeZone:'Asia/Jakarta', hour:'2-digit', minute:'2-digit', second:'2-digit', day:'2-digit', month:'short', year:'numeric' }) }} WIB
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="h-px mx-6" style="background: linear-gradient(90deg, transparent, #dbeafe, transparent)"></div>

                    <!-- Form -->
                    <div class="px-9 pt-6 pb-7 flex flex-col gap-[18px]">

                        <!-- NISN -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[0.7rem] font-bold tracking-[0.1em] uppercase text-slate-500">NISN</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-300 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                    </svg>
                                </span>
                                <input
                                    v-model="nisn"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="10"
                                    placeholder="Masukkan 10 digit NISN"
                                    class="w-full bg-blue-50/60 border border-blue-100 rounded-xl pl-10 pr-3.5 py-3 text-[0.82rem] text-blue-900 outline-none transition-all placeholder:text-slate-300 focus:border-blue-400 focus:bg-blue-50 focus:shadow-[0_0_0_4px_rgba(59,130,246,0.12)]"
                                    style="font-family:'DM Mono',monospace;"
                                />
                            </div>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[0.7rem] font-bold tracking-[0.1em] uppercase text-slate-500">Tanggal Lahir</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-300 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                </span>
                                <input
                                    v-model="tanggalLahir"
                                    type="date"
                                    class="w-full bg-blue-50/60 border border-blue-100 rounded-xl pl-10 pr-3.5 py-3 text-[0.82rem] text-blue-900 outline-none transition-all focus:border-blue-400 focus:bg-blue-50 focus:shadow-[0_0_0_4px_rgba(59,130,246,0.12)] color-scheme-light"
                                    style="font-family:'DM Mono',monospace; color-scheme: light;"
                                />
                            </div>
                        </div>

                        <!-- Error -->
                        <transition name="slide-down">
                            <div v-if="error" class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-xl px-3.5 py-3 text-[0.75rem] text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ error }}
                            </div>
                        </transition>

                        <!-- Submit -->
                        <button
                            @click="handleLogin"
                            :disabled="loading || !ntpReady"
                            class="w-full mt-1 py-3.5 rounded-xl font-bold text-[0.88rem] text-white tracking-wide border-none cursor-pointer transition-all disabled:opacity-55 disabled:cursor-not-allowed hover:not-disabled:-translate-y-0.5 hover:not-disabled:shadow-xl"
                            style="background: linear-gradient(135deg,#1d4ed8,#3b82f6); box-shadow: 0 4px 20px rgba(37,99,235,.38); font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:0.02em"
                        >
                            <span v-if="!loading" class="flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                Lihat Hasil Kelulusan
                            </span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Memverifikasi…
                            </span>
                        </button>
                    </div>

                    <!-- Footer -->
                    <p class="text-center text-[0.68rem] text-slate-400 pb-5 px-9">
                        © 2025 · Sistem Pengumuman Kelulusan
                    </p>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%       { transform: translateX(-7px); }
    40%       { transform: translateX(7px); }
    60%       { transform: translateX(-4px); }
    80%       { transform: translateX(4px); }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.rise-enter-active { transition: opacity 0.5s ease, transform 0.5s ease; }
.rise-enter-from { opacity: 0; transform: translateY(18px); }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.28s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
