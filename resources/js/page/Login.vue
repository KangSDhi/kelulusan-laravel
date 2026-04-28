<script setup>
import { ref, computed, onBeforeMount, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNtpTime } from '../composable/useNtpTime.js'

const router = useRouter()
const nisn = ref('')
const tanggalLahir = ref('')
const loading = ref(false)
const error = ref('')
const shake = ref(false)

const TARGET_DATE = new Date('2026-05-04T22:00:00')

// Ambil waktu dari NTP — tidak bisa dimanipulasi via clock lokal
const { now, ntpReady, ntpError, ntpLoading } = useNtpTime()

// Guard: tunggu NTP siap, lalu cek apakah sudah lewat target
watch([ntpReady, ntpError], () => {
    if (ntpLoading.value) return

    const currentTime = ntpReady.value ? now.value : null

    // Jika NTP error, tolak akses untuk keamanan (jangan fallback ke lokal)
    if (ntpError.value) {
        router.replace('/')
        return
    }

    if (currentTime && currentTime < TARGET_DATE) {
        router.replace('/')
    }
})

const isValid = computed(() => nisn.value.trim().length >= 10 && tanggalLahir.value)

async function handleLogin() {
    if (!isValid.value) {
        triggerShake('Mohon lengkapi NISN dan Tanggal Lahir.')
        return
    }
    // Cek ulang waktu NTP sebelum submit
    if (!ntpReady.value || !now.value || now.value < TARGET_DATE) {
        triggerShake('Sistem belum siap atau waktu belum valid.')
        return
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
    <div class="page-root">

        <!-- Background decoration -->
        <div class="bg-deco" aria-hidden="true">
            <div class="deco-circle deco-c1"></div>
            <div class="deco-circle deco-c2"></div>
            <div class="deco-wave"></div>
            <div class="dot-grid"></div>
        </div>

        <!-- NTP Loading screen -->
        <transition name="fade">
            <div v-if="ntpLoading" class="ntp-overlay">
                <div class="ntp-card">
                    <div class="ntp-spinner"></div>
                    <p class="ntp-text">Menyinkronkan waktu server…</p>
                    <p class="ntp-sub">Mohon tunggu sebentar</p>
                </div>
            </div>
        </transition>

        <!-- Main card -->
        <transition name="rise">
            <div v-if="!ntpLoading" class="card-wrap" :class="{ 'animate-shake': shake }">
                <div class="card">

                    <!-- Top accent bar -->
                    <div class="accent-bar"></div>

                    <!-- Header -->
                    <div class="card-header">
                        <div class="icon-badge">
                            🎓
                        </div>
                        <h1 class="card-title">Cek Kelulusan</h1>
                        <p class="card-sub">Masukkan data diri untuk melihat hasil kelulusan</p>

                        <!-- NTP timestamp badge -->
                        <div v-if="ntpReady && now" class="time-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" class="time-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ now.toLocaleString('id-ID', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: 'short', year: 'numeric' }) }} WIB
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="divider"></div>

                    <!-- Form -->
                    <div class="form-body">

                        <!-- NISN -->
                        <div class="field">
                            <label class="field-label">NISN</label>
                            <div class="field-wrap">
                                <span class="field-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                    </svg>
                                </span>
                                <input
                                    v-model="nisn"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="10"
                                    placeholder="Masukkan 10 digit NISN"
                                    class="field-input"
                                />
                            </div>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="field">
                            <label class="field-label">Tanggal Lahir</label>
                            <div class="field-wrap">
                                <span class="field-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                </span>
                                <input
                                    v-model="tanggalLahir"
                                    type="date"
                                    class="field-input"
                                />
                            </div>
                        </div>

                        <!-- Error -->
                        <transition name="slide-down">
                            <div v-if="error" class="error-box">
                                <svg xmlns="http://www.w3.org/2000/svg" class="err-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ error }}
                            </div>
                        </transition>

                        <!-- Submit -->
                        <button @click="handleLogin" :disabled="loading || !ntpReady" class="submit-btn">
                            <span v-if="!loading" class="btn-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                Lihat Hasil Kelulusan
                            </span>
                            <span v-else class="btn-inner">
                                <svg class="spin btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Memverifikasi…
                            </span>
                        </button>

                    </div>

                    <!-- Footer -->
                    <p class="card-footer">© 2025 · Sistem Pengumuman Kelulusan</p>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

/* ── Root ── */
.page-root {
    min-height: 100vh;
    background: #f0f6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    position: relative;
    overflow: hidden;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

/* ── Background decoration ── */
.bg-deco { position: absolute; inset: 0; pointer-events: none; }

.deco-circle {
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
}
.deco-c1 {
    width: 600px; height: 600px;
    background: radial-gradient(circle, #bfdbfe 0%, transparent 70%);
    top: -200px; right: -150px;
    opacity: 0.7;
}
.deco-c2 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, #dbeafe 0%, transparent 70%);
    bottom: -200px; left: -100px;
    opacity: 0.6;
}
.deco-wave {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 180px;
    background: linear-gradient(to top, #dbeafe40, transparent);
}
.dot-grid {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, #93c5fd44 1px, transparent 1px);
    background-size: 36px 36px;
}

/* ── NTP Overlay ── */
.ntp-overlay {
    position: fixed;
    inset: 0;
    background: #f0f6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}
.ntp-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}
.ntp-spinner {
    width: 44px; height: 44px;
    border: 3px solid #bfdbfe;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
.ntp-text {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 700;
    font-size: 0.95rem;
    color: #1e40af;
    margin: 0;
}
.ntp-sub {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.75rem;
    color: #93c5fd;
    margin: 0;
}

/* ── Card ── */
.card-wrap {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 440px;
}
.card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow:
        0 1px 3px rgba(37, 99, 235, 0.08),
        0 8px 32px rgba(37, 99, 235, 0.12),
        0 24px 64px rgba(37, 99, 235, 0.08);
    overflow: hidden;
    border: 1px solid #dbeafe;
}

/* Top accent */
.accent-bar {
    height: 5px;
    background: linear-gradient(90deg, #1d4ed8, #3b82f6, #60a5fa);
}

/* ── Header ── */
.card-header {
    padding: 32px 36px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
}
.icon-badge {
    width: 64px; height: 64px;
    border-radius: 18px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 4px;
}
.card-title {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    color: #1e3a8a;
    margin: 0;
    letter-spacing: -0.02em;
}
.card-sub {
    font-size: 0.78rem;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}

/* NTP time badge */
.time-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    padding: 5px 12px;
    font-family: 'DM Mono', monospace;
    font-size: 0.68rem;
    color: #1d4ed8;
    letter-spacing: 0.01em;
    margin-top: 4px;
}
.time-icon {
    width: 12px; height: 12px;
    flex-shrink: 0;
    color: #3b82f6;
}

/* Divider */
.divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #dbeafe, transparent);
    margin: 0 24px;
}

/* ── Form ── */
.form-body {
    padding: 24px 36px 28px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.field { display: flex; flex-direction: column; gap: 7px; }
.field-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #475569;
}
.field-wrap { position: relative; }
.field-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #93c5fd;
    pointer-events: none;
    display: flex;
    align-items: center;
}
.icon { width: 16px; height: 16px; }
.field-input {
    width: 100%;
    box-sizing: border-box;
    background: #f8fbff;
    border: 1.5px solid #dbeafe;
    border-radius: 10px;
    padding: 12px 14px 12px 40px;
    font-family: 'DM Mono', monospace;
    font-size: 0.82rem;
    color: #1e3a8a;
    outline: none;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}
.field-input::placeholder { color: #cbd5e1; }
.field-input:focus {
    border-color: #3b82f6;
    background: #eff6ff;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}

/* Error box */
.error-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-radius: 9px;
    padding: 11px 14px;
    font-size: 0.75rem;
    color: #dc2626;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.err-icon { width: 15px; height: 15px; flex-shrink: 0; }

/* Submit button */
.submit-btn {
    width: 100%;
    padding: 14px;
    border-radius: 11px;
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
    border: none;
    cursor: pointer;
    color: #ffffff;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 700;
    font-size: 0.88rem;
    letter-spacing: 0.02em;
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.38);
    transition: opacity 0.2s, transform 0.18s, box-shadow 0.2s;
    margin-top: 4px;
}
.submit-btn:hover:not(:disabled) {
    opacity: 0.93;
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(37, 99, 235, 0.45);
}
.submit-btn:active:not(:disabled) { transform: translateY(0); }
.submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }

.btn-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-icon { width: 16px; height: 16px; }

/* Footer */
.card-footer {
    text-align: center;
    font-size: 0.68rem;
    color: #94a3b8;
    padding: 0 36px 22px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

/* ── Animations ── */
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.8s linear infinite; }

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%       { transform: translateX(-7px); }
    40%       { transform: translateX(7px); }
    60%       { transform: translateX(-4px); }
    80%       { transform: translateX(4px); }
}
.animate-shake { animation: shake 0.55s ease; }

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.rise-enter-active { transition: opacity 0.5s ease, transform 0.5s ease; }
.rise-enter-from { opacity: 0; transform: translateY(18px); }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.28s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
