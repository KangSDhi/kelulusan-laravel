<script setup>
import { computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNtpTime } from '../composable/useNtpTime.js'

const router = useRouter()
const TARGET_DATE = new Date('2026-04-29T12:08:00')
const { now, ntpReady, ntpError, ntpLoading, syncNtp } = useNtpTime()

const diff = computed(() => {
    if (!ntpReady.value || !now.value) return null
    const delta = TARGET_DATE - now.value
    if (delta <= 0) return null
    return {
        days:    Math.floor(delta / (1000 * 60 * 60 * 24)),
        hours:   Math.floor((delta / (1000 * 60 * 60)) % 24),
        minutes: Math.floor((delta / (1000 * 60)) % 60),
        seconds: Math.floor((delta / 1000) % 60),
    }
})

const isExpired = computed(() =>
    ntpReady.value && now.value !== null && now.value >= TARGET_DATE
)

let redirected = false
watch(isExpired, (val) => {
    if (val && !redirected) {
        redirected = true
        setTimeout(() => router.push('/login'), 1500)
    }
})

const pad = (n) => String(n).padStart(2, '0')
</script>

<template>
    <div class="min-h-screen bg-blue-50 flex flex-col items-center justify-center px-5 py-12 relative overflow-hidden font-sans">

        <!-- Background decoration -->
        <div class="absolute inset-0 pointer-events-none">
            <!-- Blobs -->
            <div class="absolute w-[700px] h-[700px] rounded-full bg-blue-200 opacity-60 blur-[80px] -top-72 -right-44"></div>
            <div class="absolute w-[600px] h-[600px] rounded-full bg-blue-100 opacity-50 blur-[80px] -bottom-64 -left-36"></div>
            <div class="absolute w-[400px] h-[400px] rounded-full bg-blue-300 opacity-15 blur-[80px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 animate-pulse"></div>
            <!-- Dot grid -->
            <div class="absolute inset-0" style="background-image: radial-gradient(circle, #93c5fd55 1px, transparent 1px); background-size: 36px 36px;"></div>
            <!-- Wave top/bottom -->
            <div class="absolute top-0 left-0 right-0 h-28" style="background: linear-gradient(to bottom, #dbeafe50, transparent)"></div>
            <div class="absolute bottom-0 left-0 right-0 h-28" style="background: linear-gradient(to top, #dbeafe50, transparent)"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col items-center text-center">

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-blue-300 bg-blue-50 text-blue-700 text-[0.68rem] font-bold tracking-[0.15em] uppercase mb-7 shadow-sm shadow-blue-200">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                Pengumuman Kelulusan 2026
            </div>

            <!-- Title -->
            <h1 class="text-5xl md:text-7xl font-extrabold leading-[1.05] tracking-tight text-blue-900 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-0.03em">
                Menuju<br>
                <span style="background: linear-gradient(135deg,#2563eb,#60a5fa,#93c5fd); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">
                    Hari Istimewa
                </span>
            </h1>

            <p class="text-sm text-slate-500 leading-relaxed max-w-sm mb-12">
                Pengumuman kelulusan akan segera dibuka.<br>
                Persiapkan dirimu untuk momen bersejarah ini.
            </p>

            <!-- ── Loading NTP ── -->
            <div v-if="ntpLoading" class="flex flex-col items-center gap-4">
                <div class="flex gap-2">
                    <span v-for="i in 3" :key="i"
                          class="w-2.5 h-2.5 rounded-full bg-blue-500 block animate-bounce"
                          :style="{ animationDelay: `${(i-1)*0.18}s` }">
                    </span>
                </div>
                <p class="text-[0.72rem] font-semibold tracking-[0.12em] uppercase text-blue-300">
                    Menyinkronkan waktu server NTP…
                </p>
            </div>

            <!-- ── NTP Gagal ── -->
            <div v-else-if="ntpError" class="flex flex-col items-center gap-4 max-w-xs">
                <div class="text-5xl">⚠️</div>
                <p class="text-sm font-bold text-red-500 m-0">Gagal terhubung ke server waktu</p>
                <p class="text-[0.78rem] text-slate-500 leading-relaxed m-0">
                    Tidak dapat memverifikasi waktu dari NTP.<br>
                    Pastikan perangkat terhubung ke internet.
                </p>
                <button @click="syncNtp"
                        class="mt-1 px-5 py-2.5 rounded-xl border border-blue-300 bg-blue-50 text-blue-700 text-[0.75rem] font-bold tracking-[0.1em] uppercase cursor-pointer transition-all hover:bg-blue-100 hover:shadow-md hover:-translate-y-0.5">
                    ↻ Coba Lagi
                </button>
            </div>

            <!-- ── Countdown Aktif ── -->
            <template v-else-if="!isExpired && diff">
                <div class="grid grid-cols-4 gap-3 md:gap-4">
                    <div
                        v-for="(item, i) in [
                            { label: 'Hari',   value: diff.days    },
                            { label: 'Jam',    value: diff.hours   },
                            { label: 'Menit',  value: diff.minutes },
                            { label: 'Detik',  value: diff.seconds },
                        ]"
                        :key="i"
                        class="flex flex-col items-center bg-white border border-blue-100 rounded-2xl px-5 py-5 min-w-[76px] shadow-md shadow-blue-100 transition-transform duration-200 hover:-translate-y-1"
                        style="box-shadow: 0 2px 8px rgba(37,99,235,.08), 0 8px 32px rgba(37,99,235,.1)"
                    >
                        <span class="text-4xl md:text-5xl font-medium leading-none text-blue-700 tracking-tight" style="font-family:'DM Mono',monospace;">
                            {{ pad(item.value) }}
                        </span>
                        <span class="text-[0.6rem] font-bold tracking-[0.2em] uppercase text-blue-300 mt-2.5">
                            {{ item.label }}
                        </span>
                    </div>
                </div>

                <!-- NTP indicator -->
                <div class="inline-flex items-center gap-2 mt-8 text-[0.7rem] text-slate-400 font-medium">
                    <span class="relative flex w-2.5 h-2.5 flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-60"></span>
                        <span class="relative inline-flex w-2.5 h-2.5 rounded-full bg-green-500"></span>
                    </span>
                    Waktu disinkronkan dari NTP Server · WIB (UTC+7)
                </div>

                <p class="mt-3 text-[0.68rem] font-semibold tracking-[0.1em] uppercase text-slate-300">
                    Halaman login akan terbuka secara otomatis
                </p>
            </template>

            <!-- ── Countdown Selesai ── -->
            <div v-else-if="isExpired" class="flex flex-col items-center gap-3 animate-[fadeRise_0.6s_ease_both]">
                <div class="text-6xl">🎓</div>
                <p class="text-2xl font-extrabold text-blue-900 tracking-tight m-0" style="font-family:'Plus Jakarta Sans',sans-serif">
                    Saatnya Pengumuman!
                </p>
                <p class="text-[0.8rem] text-slate-500 m-0">Mengalihkan ke halaman login…</p>
                <div class="w-48 h-1 rounded-full bg-blue-100 overflow-hidden mt-2">
                    <div class="h-full rounded-full animate-[progress_1.5s_linear_forwards]"
                         style="background: linear-gradient(90deg, #1d4ed8, #60a5fa)">
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

@keyframes fadeRise {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes progress {
    from { width: 0%; }
    to   { width: 100%; }
}
</style>
