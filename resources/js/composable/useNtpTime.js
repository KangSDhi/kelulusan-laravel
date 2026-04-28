import { ref, onUnmounted } from 'vue'

// TimeAPI.io lebih stabil dibanding worldtimeapi
const NTP_API = 'https://timeapi.io/api/Time/current/zone?timeZone=Asia/Jakarta'

export function useNtpTime() {
    const now        = ref(null)
    const ntpReady   = ref(false)
    const ntpError   = ref(false)
    const ntpLoading = ref(true)

    let offset   = 0
    let interval = null

    async function syncNtp() {
        ntpLoading.value = true
        ntpError.value   = false

        try {
            const fetchStart = Date.now()

            // Menggunakan mode cors agar bisa diakses dari aplikasi web
            const res = await fetch(NTP_API, {
                method: 'GET',
                cache: 'no-store'
            })

            if (!res.ok) throw new Error(`HTTP ${res.status}`)

            const data      = await res.json()
            const fetchEnd  = Date.now()

            // Estimasi latensi
            const latency = (fetchEnd - fetchStart) / 2

            // TimeAPI mengembalikan field 'dateTime' ISO String atau 'milliSeconds' sejak epoch
            // Kita gunakan milliSeconds untuk akurasi lebih baik
            const ntpMs = new Date(data.dateTime).getTime() + latency

            offset = ntpMs - Date.now()

            now.value  = new Date(Date.now() + offset)
            ntpReady.value  = true
            ntpLoading.value = false

            if (interval) clearInterval(interval)
            interval = setInterval(() => {
                now.value = new Date(Date.now() + offset)
            }, 1000)

        } catch (err) {
            console.error('[useNtpTime] Gagal mengambil waktu NTP:', err)
            ntpError.value   = true
            ntpLoading.value = false
            ntpReady.value   = false

            // Fallback sederhana: gunakan waktu lokal jika API gagal total
            now.value = new Date()
        }
    }

    syncNtp()

    onUnmounted(() => {
        if (interval) clearInterval(interval)
    })

    return { now, ntpReady, ntpError, ntpLoading, syncNtp }
}
