/* Service worker SIBIMTA.
 * - Aset tampilan (/build, ikon, logo, huruf) disimpan agar aplikasi cepat dibuka.
 * - Halaman berisi data (HTML) TIDAK disimpan, demi privasi; bila tidak ada internet,
 *   ditampilkan halaman offline. */
const VERSI = 'sibimta-v1';
const AWAL = ['/offline.html', '/logo-sibimta.png', '/logo.png', '/ikon/ikon-192.png'];

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(VERSI).then((c) => c.addAll(AWAL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((ks) => Promise.all(ks.filter((k) => k !== VERSI).map((k) => caches.delete(k)))).then(() => self.clients.claim()));
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);

  if (req.mode === 'navigate') {
    e.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
    return;
  }
  const statis = (url.origin === location.origin && (url.pathname.startsWith('/build/') || url.pathname.startsWith('/ikon/') || /^\/logo.*\.png$/.test(url.pathname)))
    || url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com';
  if (statis) {
    e.respondWith(caches.open(VERSI).then(async (c) => {
      const ada = await c.match(req);
      const baru = fetch(req).then((r) => { if (r.ok || r.type === 'opaque') c.put(req, r.clone()); return r; }).catch(() => ada);
      return ada || baru;
    }));
  }
});
