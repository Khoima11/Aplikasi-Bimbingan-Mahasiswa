<?php

return [
    // database (bawaan, cocok untuk hosting gratis tanpa disk permanen) atau local
    'file_store' => env('FILE_STORE', 'database'),
    // Isi data contoh saat database pertama kali disiapkan
    'seed_demo' => env('SEED_DEMO', true),
    'demo_password' => env('DEMO_PASSWORD', 'sibimta123'),
    'admin_password' => env('ADMIN_PASSWORD', env('DEMO_PASSWORD', 'sibimta123')),
];
