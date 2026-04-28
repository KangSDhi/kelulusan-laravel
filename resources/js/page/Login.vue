<script setup>
import { ref, computed, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const nisn = ref('')
const tanggalLahir = ref('')
const loading = ref(false)
const error = ref('')
const shake = ref(false)

// Guard: jika countdown belum selesai, redirect ke home
const TARGET_DATE = new Date('2026-04-28T22:00:00')
onBeforeMount(() => {
    if (new Date() < TARGET_DATE) {
        router.replace('/')
    }
})

const isValid = computed(() => nisn.value.trim().length >= 10 && tanggalLahir.value)

async function handleLogin() {
    if (!isValid.value) {
        triggerShake('Mohon lengkapi NISN dan Tanggal Lahir.')
        return
    }
    loading.value = true
    error.value = ''
    // Simulasi request — ganti dengan API call sesungguhnya
    await new Promise(r => setTimeout(r, 1800))
    loading.value = false
    // Contoh: tampilkan error jika NISN tidak ditemukan
    triggerShake('NISN atau Tanggal Lahir tidak ditemukan.')
}

function triggerShake(msg) {
    error.value = msg
    shake.value = true
    setTimeout(() => shake.value = false, 600)
}
</script>

<template>
    <div class="min-h-screen bg-[#0a0a0f] flex items-center justify-center px-4 relative overflow-hidden">

        <!-- Background effects -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="grid-bg"></div>
        </div>

        <!-- Card -->
        <div
            class="relative z-10 w-full max-w-md"
            :class="{ 'animate-shake': shake }"
        >
            <!-- Glow ring -->
            <div class="absolute -inset-[1px] rounded-2xl bg-gradient-to-br from-amber-400/40 via-transparent to-rose-500/30 blur-sm"></div>

            <div class="relative bg-[#0f0f18]/90 border border-white/10 rounded-2xl p-8 md:p-10 backdrop-blur-xl shadow-2xl">

                <!-- Header -->
                <div class="flex flex-col items-center mb-8">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-400 to-rose-500 flex items-center justify-center text-3xl shadow-lg shadow-amber-500/30 mb-5">
                        🎓
                    </div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight" style="font-family:'Syne',sans-serif">
                        Cek Kelulusanmu
                    </h1>
                    <p class="text-slate-500 text-xs mt-1.5 tracking-wide text-center">
                        Masukkan data diri untuk melihat hasil kelulusan
                    </p>
                </div>

                <!-- Form -->
                <div class="space-y-5">

                    <!-- NISN -->
                    <div class="form-group">
                        <label class="form-label">NISN</label>
                        <div class="input-wrapper">
              <span class="input-icon">
                <!-- ID card icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                </svg>
              </span>
                            <input
                                v-model="nisn"
                                type="text"
                                inputmode="numeric"
                                maxlength="10"
                                placeholder="Masukkan 10 digit NISN"
                                class="form-input"
                            />
                        </div>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <div class="input-wrapper">
              <span class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
              </span>
                            <input
                                v-model="tanggalLahir"
                                type="date"
                                class="form-input"
                            />
                        </div>
                    </div>

                    <!-- Error message -->
                    <transition name="slide-down">
                        <div v-if="error" class="flex items-center gap-2 text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-lg px-4 py-3 text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            {{ error }}
                        </div>
                    </transition>

                    <!-- Submit button -->
                    <button
                        @click="handleLogin"
                        :disabled="loading"
                        class="login-btn w-full mt-2"
                    >
            <span v-if="!loading" class="flex items-center justify-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                <p class="text-center text-slate-700 text-xs mt-8">
                    © 2025 · Sistem Pengumuman Kelulusan
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@700;800&display=swap');

* { font-family: 'Space Mono', monospace; }

/* Blobs */
.blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(90px);
    opacity: 0.14;
}
.blob-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, #fbbf24, transparent 70%);
    top: -150px; right: -100px;
}
.blob-2 {
    width: 450px; height: 450px;
    background: radial-gradient(circle, #f43f5e, transparent 70%);
    bottom: -150px; left: -80px;
}
.grid-bg {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 50px 50px;
}

/* Form */
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-label {
    font-size: 0.65rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #94a3b8;
}
.input-wrapper {
    position: relative;
}
.input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #475569;
    pointer-events: none;
    display: flex;
}
.form-input {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 10px;
    padding: 13px 14px 13px 40px;
    color: #f1f5f9;
    font-family: 'Space Mono', monospace;
    font-size: 0.82rem;
    outline: none;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    color-scheme: dark;
}
.form-input::placeholder { color: #334155; }
.form-input:focus {
    border-color: rgba(251, 191, 36, 0.5);
    background: rgba(251, 191, 36, 0.05);
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.08);
}

/* Login button */
.login-btn {
    padding: 14px;
    border-radius: 10px;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.04em;
    color: #0a0a0f;
    background: linear-gradient(135deg, #fbbf24, #f43f5e);
    border: none;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 24px rgba(244, 63, 94, 0.3);
}
.login-btn:hover:not(:disabled) {
    opacity: 0.92;
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(244, 63, 94, 0.4);
}
.login-btn:active:not(:disabled) { transform: translateY(0); }
.login-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Shake animation */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%       { transform: translateX(-8px); }
    40%       { transform: translateX(8px); }
    60%       { transform: translateX(-5px); }
    80%       { transform: translateX(5px); }
}
.animate-shake { animation: shake 0.55s ease; }

/* Error slide-down */
.slide-down-enter-active, .slide-down-leave-active {
    transition: all 0.3s ease;
}
.slide-down-enter-from, .slide-down-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
