<?php

namespace App\Services;

use App\Models\Berkas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Penyimpanan berkas privat (KNF-02). Bawaan: isi berkas disimpan di database
 * karena hosting gratis tidak memiliki disk permanen. Set FILE_STORE=local bila
 * server memiliki disk permanen.
 */
class Penyimpanan
{
    public static function simpan(UploadedFile $file): string
    {
        if (config('sibimta.file_store') === 'local') {
            return 'local:'.$file->store('dokumen', 'local');
        }
        $b = Berkas::create([
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'ukuran_byte' => $file->getSize(),
            'isi_base64' => base64_encode(file_get_contents($file->getRealPath())),
        ]);
        return 'db:'.$b->id_berkas;
    }

    public static function unduh(string $path, string $namaUnduhan)
    {
        $namaUnduhan = preg_replace('/[^\w.\- ]+/u', '_', $namaUnduhan);
        if (str_starts_with($path, 'db:')) {
            $b = Berkas::findOrFail((int) substr($path, 3));
            $isi = base64_decode($b->isi_base64);
            return response($isi, 200, [
                'Content-Type' => $b->mime,
                'Content-Length' => strlen($isi),
                'Content-Disposition' => 'attachment; filename="'.$namaUnduhan.'"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]);
        }
        if (str_starts_with($path, 'local:')) {
            return Storage::disk('local')->download(substr($path, 6), $namaUnduhan);
        }
        abort(404);
    }

    public static function aturanUnggah(): array
    {
        $kb = \App\Models\Pengaturan::angka('ukuran_berkas_mb') * 1024;
        return ['required', 'file', 'mimes:pdf,docx,doc', 'max:'.$kb];
    }
}
