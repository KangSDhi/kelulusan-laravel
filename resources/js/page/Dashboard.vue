<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useNtpTime } from '../composable/useNtpTime.js'

const router = useRouter()
const user = ref(null)
const loading = ref(true)
const confettiCanvas = ref(null)

const TARGET_DATE = new Date('2026-04-29T12:08:00')
const { now, ntpReady } = useNtpTime()

let animFrame = null
const particles = []

// ── Confetti setup ──────────────────────────────────────────────────────────
const COLORS = ['#1d4ed8','#3b82f6','#60a5fa','#93c5fd','#fbbf24','#34d399','#f472b6','#a78bfa']

function createParticle(canvas) {
    return {
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height * -1,
        w: Math.random() * 8 + 4,
        h: Math.random() * 4 + 2,
        color: COLORS[Math.floor(Math.random() * COLORS.length)],
        speed: Math.random() * 3 + 1.5,
        spin: Math.random() * 0.15 - 0.075,
        angle: Math.random() * Math.PI * 2,
        sway: Math.random() * 0.5 - 0.25,
        swaySpeed: Math.random() * 0.02 + 0.005,
        swayAngle: Math.random() * Math.PI * 2,
        opacity: Math.random() * 0.6 + 0.4
    }
}

function initConfetti() {
    const canvas = confettiCanvas.value
    if (!canvas) return
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
    particles.length = 0
    for (let i = 0; i < 140; i++) {
        const p = createParticle(canvas)
        p.y = Math.random() * canvas.height // start spread
        particles.push(p)
    }
    drawConfetti()
}

function drawConfetti() {
    const canvas = confettiCanvas.value
    if (!canvas) return
    const ctx = canvas.getContext('2d')
    ctx.clearRect(0, 0, canvas.width, canvas.height)

    for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i]
        p.swayAngle += p.swaySpeed
        p.x += Math.sin(p.swayAngle) * p.sway + (Math.random() - 0.5) * 0.3
        p.y += p.speed
        p.angle += p.spin

        ctx.save()
        ctx.translate(p.x, p.y)
        ctx.rotate(p.angle)
        ctx.globalAlpha = p.opacity
        ctx.fillStyle = p.color
        ctx.beginPath()
        ctx.rect(-p.w / 2, -p.h / 2, p.w, p.h)
        ctx.fill()
        ctx.restore()

        if (p.y > canvas.height + 20) {
            particles[i] = createParticle(canvas)
        }
    }
    animFrame = requestAnimationFrame(drawConfetti)
}

function handleResize() {
    if (!confettiCanvas.value) return
    confettiCanvas.value.width = window.innerWidth
    confettiCanvas.value.height = window.innerHeight
}

// ── Auth guard ───────────────────────────────────────────────────────────────
async function checkAuth() {
    const token = localStorage.getItem('graduation_token')
    const tokenType = localStorage.getItem('graduation_token_type') || 'bearer'
    const cachedUser = localStorage.getItem('graduation_user')

    if (!token) {
        redirectToLogin(); return
    }

    // Set token
    axios.defaults.headers.common['Authorization'] = `${tokenType} ${token}`

    // Load user from cache sementara API dicek
    if (cachedUser) {
        try { user.value = JSON.parse(cachedUser) } catch {}
    }

    try {
        const res = await axios.get('/api/auth/check/user')
        if (res.data?.data) user.value = res.data.data
        loading.value = false
        initConfetti()
    } catch (err) {
        if (err.response?.status === 401) {
            // Cek apakah waktu belum pengumuman
            const isAnnounced = ntpReady.value && now.value && now.value >= TARGET_DATE
            clearAuth()
            if (!isAnnounced) {
                await router.replace('/')
            } else {
                await router.replace('/login')
            }
        } else {
            // Error lain (network, 500, dll) — tetap tampil jika ada cache
            loading.value = false
            if (user.value) initConfetti()
            else redirectToLogin()
        }
    }
}

function clearAuth() {
    localStorage.removeItem('graduation_token')
    localStorage.removeItem('graduation_token_type')
    localStorage.removeItem('graduation_user')
    delete axios.defaults.headers.common['Authorization']
}

function redirectToLogin() {
    const isAnnounced = ntpReady.value && now.value && now.value >= TARGET_DATE
    loading.value = false
    router.replace(isAnnounced ? '/login' : '/')
}

function handleLogout() {
    if (animFrame) cancelAnimationFrame(animFrame)
    clearAuth()
    router.replace('/')
}

// ── Format helpers ────────────────────────────────────────────────────────────
function formatDate(str) {
    if (!str) return '-'
    const d = new Date(str)
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

onMounted(() => {
    window.addEventListener('resize', handleResize)
    checkAuth()
})

onUnmounted(() => {
    if (animFrame) cancelAnimationFrame(animFrame)
    window.removeEventListener('resize', handleResize)
})
</script>

<template>
    <div class="min-h-screen relative overflow-hidden" style="font-family:'Plus Jakarta Sans',sans-serif; background: linear-gradient(135deg, #eff6ff 0%, #fff 40%, #f0f9ff 100%)">

        <!-- Confetti canvas -->
        <canvas ref="confettiCanvas" class="fixed inset-0 pointer-events-none z-10" style="opacity:0.75"></canvas>

        <!-- Background blobs -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute w-[700px] h-[700px] rounded-full opacity-40 blur-[100px] -top-64 -right-48" style="background: radial-gradient(circle, #bfdbfe, #93c5fd)"></div>
            <div class="absolute w-[500px] h-[500px] rounded-full opacity-35 blur-[90px] -bottom-48 -left-32" style="background: radial-gradient(circle, #dbeafe, #bfdbfe)"></div>
            <div class="absolute w-[300px] h-[300px] rounded-full opacity-25 blur-[60px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="background: radial-gradient(circle, #fde68a, #fcd34d)"></div>
            <!-- Dot grid -->
            <div class="absolute inset-0" style="background-image: radial-gradient(circle, #93c5fd33 1px, transparent 1px); background-size: 40px 40px;"></div>
        </div>

        <!-- Loading -->
        <transition name="fade">
            <div v-if="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-blue-50/80 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-[3px] border-blue-100 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-blue-700 font-semibold text-sm">Memuat data kelulusan...</p>
                </div>
            </div>
        </transition>

        <!-- Main content -->
        <transition name="rise">
            <div v-if="!loading && user" class="relative z-20 min-h-screen flex flex-col items-center justify-center px-4 py-12">

                <!-- Top label -->
                <div class="mb-6 inline-flex items-center gap-2 bg-white/80 backdrop-blur border border-blue-100 rounded-full px-4 py-1.5 text-[0.72rem] font-semibold text-blue-600 shadow-sm"
                     style="letter-spacing:0.08em; text-transform:uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse inline-block"></span>
                    Pengumuman Resmi Kelulusan
                </div>

                <!-- Hero card -->
                <div class="w-full max-w-lg">
                    <div class="bg-white/90 backdrop-blur-sm rounded-3xl overflow-hidden border border-blue-100/80"
                         style="box-shadow: 0 2px 4px rgba(37,99,235,.06), 0 12px 40px rgba(37,99,235,.14), 0 40px 80px rgba(37,99,235,.08)">

                        <!-- Top gradient banner -->
                        <div class="relative px-8 pt-10 pb-8 text-center overflow-hidden"
                             style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%)">

                            <!-- Decorative circles in banner -->
                            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full bg-blue-400/20 blur-xl"></div>
                            <div class="absolute -bottom-6 -left-6 w-32 h-32 rounded-full bg-blue-300/20 blur-lg"></div>
                            <div class="absolute top-4 left-8 w-2 h-2 rounded-full bg-yellow-300/60"></div>
                            <div class="absolute top-8 right-14 w-1.5 h-1.5 rounded-full bg-blue-200/80"></div>
                            <div class="absolute bottom-6 right-8 w-3 h-3 rounded-full bg-yellow-400/40"></div>

                            <!-- Graduate icon -->
                            <div class="relative inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-4 shadow-2xl"
                                 style="background: linear-gradient(135deg, rgba(255,255,255,0.25), rgba(255,255,255,0.10)); border: 1.5px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px)">
                                <span class="text-4xl select-none">🎓</span>
                                <!-- Glow ring -->
                                <div class="absolute inset-0 rounded-2xl" style="box-shadow: 0 0 0 8px rgba(255,255,255,0.08), 0 0 40px rgba(96,165,250,0.4)"></div>
                            </div>

                            <div class="relative">
                                <p class="text-blue-200 text-[0.72rem] font-semibold tracking-[0.15em] uppercase mb-2">Selamat!</p>
                                <h1 class="text-white text-3xl font-extrabold leading-tight mb-1" style="letter-spacing:-0.02em; font-family:'Plus Jakarta Sans',sans-serif; text-shadow: 0 2px 12px rgba(0,0,0,0.2)">
                                    LULUS
                                </h1>
                                <p class="text-blue-200 text-[0.8rem] leading-relaxed">
                                    Anda telah dinyatakan <strong class="text-white">LULUS</strong><br>dari satuan pendidikan
                                </p>
                            </div>
                        </div>

                        <!-- Student info -->
                        <div class="px-8 py-7">

                            <!-- Name -->
                            <div class="text-center mb-6">
                                <p class="text-[0.65rem] font-bold tracking-[0.12em] uppercase text-slate-400 mb-1">Nama Lengkap</p>
                                <h2 class="text-2xl font-extrabold text-blue-900" style="letter-spacing:-0.01em">
                                    {{ user.name }}
                                </h2>
                            </div>

                            <!-- Info grid -->
                            <div class="grid grid-cols-2 gap-3 mb-6">
                                <!-- NISN -->
                                <div class="bg-blue-50/70 border border-blue-100 rounded-2xl px-4 py-3.5">
                                    <p class="text-[0.62rem] font-bold tracking-[0.1em] uppercase text-blue-400 mb-1">NISN</p>
                                    <p class="text-[0.88rem] font-bold text-blue-900" style="font-family:'DM Mono',monospace">{{ user.nisn }}</p>
                                </div>

                                <!-- Tanggal Lahir -->
                                <div class="bg-blue-50/70 border border-blue-100 rounded-2xl px-4 py-3.5">
                                    <p class="text-[0.62rem] font-bold tracking-[0.1em] uppercase text-blue-400 mb-1">Tanggal Lahir</p>
                                    <p class="text-[0.82rem] font-bold text-blue-900">{{ formatDate(user.birth_date) }}</p>
                                </div>

                                <!-- Jurusan -->
                                <div class="col-span-2 bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl px-4 py-3.5">
                                    <p class="text-[0.62rem] font-bold tracking-[0.1em] uppercase text-blue-200 mb-1">Program Keahlian</p>
                                    <p class="text-[0.95rem] font-extrabold text-white">{{ user.major }}</p>
                                </div>
                            </div>

                            <!-- Status badge -->
                            <div class="flex items-center justify-center gap-2.5 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3.5 mb-6">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[0.72rem] font-bold text-emerald-700">Status Kelulusan</p>
                                    <p class="text-[0.8rem] font-extrabold text-emerald-800">DINYATAKAN LULUS ✓</p>
                                </div>
                            </div>

                            <!-- Motivational quote -->
                            <div class="text-center px-2 mb-7">
                                <p class="text-[0.78rem] text-slate-500 leading-relaxed italic">
                                    "Setiap akhir adalah awal yang baru. Selamat menempuh babak selanjutnya dalam hidupmu!"
                                </p>
                            </div>

                            <!-- Divider -->
                            <div class="h-px mb-6" style="background: linear-gradient(90deg, transparent, #dbeafe, transparent)"></div>

                            <!-- Lampiran -->
                            <a
                                href="LINK_GOOGLE_DRIVE_DISINI"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full py-3 rounded-xl text-[0.88rem] font-bold text-white border-none flex items-center justify-center gap-2 mb-3 transition-all hover:-translate-y-0.5 hover:shadow-xl"
                                style="background: linear-gradient(135deg,#1d4ed8,#3b82f6); box-shadow: 0 4px 20px rgba(37,99,235,.35); text-decoration:none"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                </svg>
                                Lihat Lampiran Kelulusan
                            </a>

                            <!-- Logout -->
                            <button
                                @click="handleLogout"
                                class="w-full py-3 rounded-xl text-[0.82rem] font-semibold text-blue-500 border border-blue-200 bg-white hover:bg-blue-50 hover:border-blue-300 transition-all flex items-center justify-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                Keluar
                            </button>
                        </div>

                        <!-- Footer strip -->
                        <div class="h-[4px]" style="background: linear-gradient(90deg, #1d4ed8, #3b82f6, #60a5fa, #fbbf24, #34d399)"></div>
                    </div>

                    <!-- Bottom note -->
                    <p class="text-center text-[0.65rem] text-slate-400 mt-5">
                        © 2026 · Sistem Pengumuman Kelulusan · Data bersifat resmi dan rahasia
                    </p>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

.fade-enter-active, .fade-leave-active { transition: opacity 0.35s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.rise-enter-active { transition: opacity 0.6s ease, transform 0.6s cubic-bezier(.22,1,.36,1); }
.rise-enter-from { opacity: 0; transform: translateY(24px); }
</style>
