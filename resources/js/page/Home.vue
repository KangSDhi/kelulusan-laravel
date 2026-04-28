<script setup>
import { computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNtpTime } from '../composable/useNtpTime.js'

const router = useRouter()

// ── Konfigurasi: ganti tanggal & jam pengumuman di sini ──
// Format ISO dengan timezone eksplisit +07:00 (WIB)
const TARGET_DATE = new Date('2026-04-28T22:00:00')

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

// Auto-redirect sekali saat countdown habis
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
    <div class="min-h-screen bg-[#0a0a0f] flex flex-col items-center justify-center relative overflow-hidden font-mono">

        <!-- Background aurora blobs -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="aurora-blob aurora-1"></div>
            <div class="aurora-blob aurora-2"></div>
            <div class="aurora-blob aurora-3"></div>
            <div class="grid-overlay"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col items-center text-center px-6">

            <!-- Badge -->
            <div class="mb-8 inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-amber-400/30 bg-amber-400/10 text-amber-300 text-xs tracking-widest uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse inline-block"></span>
                Pengumuman Kelulusan 2025
            </div>

            <!-- Title -->
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-3 leading-tight">
                <span class="text-white">Menuju</span><br>
                <span class="gradient-text">Hari Istimewa</span>
            </h1>

            <p class="text-slate-400 text-sm md:text-base mb-14 max-w-sm leading-relaxed">
                Pengumuman kelulusan akan segera dibuka.<br>
                Persiapkan dirimu untuk momen bersejarah ini.
            </p>

            <!-- ── Loading NTP ── -->
            <div v-if="ntpLoading" class="flex flex-col items-center gap-5">
                <div class="flex gap-2">
          <span v-for="i in 3" :key="i"
                class="w-2.5 h-2.5 rounded-full bg-amber-400 dot-bounce"
                :style="{ animationDelay: `${(i - 1) * 0.18}s` }">
          </span>
                </div>
                <p class="text-slate-500 text-xs tracking-widest uppercase">Menyinkronkan waktu server NTP…</p>
            </div>

            <!-- ── NTP Gagal ── -->
            <div v-else-if="ntpError" class="flex flex-col items-center gap-4 max-w-xs">
                <div class="text-5xl">⚠️</div>
                <p class="text-rose-400 text-sm font-semibold">Gagal terhubung ke server waktu</p>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Tidak dapat memverifikasi waktu dari NTP.<br>
                    Pastikan perangkat terhubung ke internet.
                </p>
                <button @click="syncNtp"
                        class="mt-1 px-5 py-2.5 text-xs font-bold tracking-widest uppercase rounded-xl border border-amber-400/40 text-amber-300 bg-amber-400/10 hover:bg-amber-400/20 transition-all">
                    ↻ Coba Lagi
                </button>
            </div>

            <!-- ── Countdown Aktif ── -->
            <template v-else-if="!isExpired && diff">
                <div class="grid grid-cols-4 gap-4 md:gap-6">
                    <div v-for="(item, i) in [
            { label: 'Hari',   value: diff.days    },
            { label: 'Jam',    value: diff.hours   },
            { label: 'Menit',  value: diff.minutes },
            { label: 'Detik',  value: diff.seconds },
          ]" :key="i" class="countdown-card">
                        <span class="countdown-number">{{ pad(item.value) }}</span>
                        <span class="countdown-label">{{ item.label }}</span>
                    </div>
                </div>

                <!-- NTP indicator -->
                <div class="mt-10 inline-flex items-center gap-2 text-slate-600 text-xs">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
                    Waktu disinkronkan dari NTP Server · WIB (UTC+7)
                </div>

                <p class="mt-4 text-slate-700 text-xs tracking-widest uppercase">
                    Halaman login akan terbuka secara otomatis
                </p>
            </template>

            <!-- ── Countdown Selesai ── -->
            <div v-else-if="isExpired" class="flex flex-col items-center gap-4 animate-fade-in">
                <div class="text-6xl">🎓</div>
                <p class="text-2xl font-bold text-white tracking-wide">Saatnya Pengumuman!</p>
                <p class="text-slate-400 text-sm">Mengalihkan ke halaman login…</p>
                <div class="mt-2 w-48 h-1 rounded-full bg-slate-800 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-amber-400 to-rose-400 animate-progress"></div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@700;800&display=swap');
* { font-family: 'Space Mono', monospace; }
h1 { font-family: 'Syne', sans-serif; }

.aurora-blob {
    position: absolute; border-radius: 50%;
    filter: blur(80px); opacity: 0.18;
    animation: drift 12s ease-in-out infinite alternate;
}
.aurora-1 { width:600px;height:600px;background:radial-gradient(circle,#f59e0b,transparent 70%);top:-200px;left:-100px;animation-duration:14s; }
.aurora-2 { width:500px;height:500px;background:radial-gradient(circle,#e11d48,transparent 70%);bottom:-150px;right:-80px;animation-duration:10s; }
.aurora-3 { width:400px;height:400px;background:radial-gradient(circle,#7c3aed,transparent 70%);top:40%;left:50%;transform:translate(-50%,-50%);animation-duration:17s; }
@keyframes drift { 0%{transform:translate(0,0) scale(1)} 100%{transform:translate(40px,30px) scale(1.1)} }

.grid-overlay {
    position:absolute;inset:0;
    background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
    background-size:60px 60px;
}

.gradient-text {
    background:linear-gradient(135deg,#fbbf24,#f43f5e);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

.countdown-card {
    display:flex;flex-direction:column;align-items:center;
    padding:20px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);
    border-radius:16px;backdrop-filter:blur(12px);min-width:72px;
    box-shadow:0 0 30px rgba(0,0,0,.4),inset 0 1px 0 rgba(255,255,255,.06);
    transition:transform .2s;
}
.countdown-card:hover { transform:translateY(-4px); }

.countdown-number {
    font-family:'Syne',sans-serif;font-size:2.8rem;font-weight:800;line-height:1;
    background:linear-gradient(180deg,#fff 60%,rgba(255,255,255,.4));
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.countdown-label { font-size:.6rem;letter-spacing:.2em;text-transform:uppercase;color:#64748b;margin-top:8px; }

/* Dot bounce */
@keyframes dot-bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
.dot-bounce { animation:dot-bounce .75s ease-in-out infinite; }

/* Fade in */
@keyframes fade-in { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
.animate-fade-in { animation:fade-in .6s ease both; }

/* Progress */
@keyframes progress { from{width:0%} to{width:100%} }
.animate-progress { animation:progress 1.5s linear forwards; }

/* Ping */
@keyframes ping { 75%,100%{transform:scale(2);opacity:0} }
.animate-ping { animation:ping 1.2s cubic-bezier(0,0,.2,1) infinite; }
</style>
