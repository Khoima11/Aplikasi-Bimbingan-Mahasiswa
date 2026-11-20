#!/bin/sh
set -e
cd /var/www/html
export PORT="${PORT:-10000}"

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Database gratis (Neon) bisa butuh beberapa detik untuk "bangun".
n=0
until php artisan migrate --force; do
  n=$((n+1))
  if [ "$n" -ge 10 ]; then echo "Gagal terhubung ke database. Periksa DB_URL."; exit 1; fi
  echo "Database belum siap, mencoba lagi ($n/10)..."; sleep 5
done
# Data acuan selalu dipastikan ada; data contoh hanya diisi bila database masih kosong.
php artisan db:seed --force

exec apache2-foreground
