import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
Chart.defaults.font.family = 'Outfit, system-ui, sans-serif';
Chart.defaults.color = '#6B7285';
window.Chart = Chart;

// Pencarian judul serupa saat mahasiswa mengetik (AB-03)
window.judulSerupa = (url) => ({
    hasil: [], memuat: false, t: null,
    cari(judul, kk) {
        clearTimeout(this.t);
        if ((judul || '').length < 10 && (kk || '').length < 4) { this.hasil = []; return; }
        this.t = setTimeout(async () => {
            this.memuat = true;
            const q = new URLSearchParams({ judul: judul || '', kata_kunci: kk || '' });
            try { this.hasil = await (await fetch(url + '?' + q, { headers: { Accept: 'application/json' } })).json(); } catch (e) { this.hasil = []; }
            this.memuat = false;
        }, 450);
    },
});

// ---------- Aplikasi terpasang (PWA) ----------
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
window.__promptPasang = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    window.__promptPasang = e;
    window.dispatchEvent(new Event('sibimta:siap-pasang'));
});
window.pasangAplikasi = () => ({
    terpasang: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
    siap: !!window.__promptPasang,
    panduan: false,
    platform: /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
        ? 'ios' : (/android/i.test(navigator.userAgent) ? 'android' : 'laptop'),
    init() {
        window.addEventListener('sibimta:siap-pasang', () => { this.siap = true; });
        window.addEventListener('appinstalled', () => { this.terpasang = true; this.panduan = false; });
    },
    async pasang() {
        if (window.__promptPasang) {
            window.__promptPasang.prompt();
            const hasil = await window.__promptPasang.userChoice;
            if (hasil.outcome === 'accepted') this.terpasang = true;
            window.__promptPasang = null; this.siap = false;
        } else {
            this.panduan = true;
        }
    },
});

Alpine.start();
