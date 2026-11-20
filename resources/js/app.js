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

Alpine.start();
