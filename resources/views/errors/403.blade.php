@include('errors.layout', ['kode' => 403, 'judul' => 'Akses ditolak', 'pesan' => $exception->getMessage() ?: 'Halaman ini tidak tersedia untuk akun Anda.'])
