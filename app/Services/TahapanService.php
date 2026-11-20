<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Persetujuan;
use App\Models\Skripsi;
use App\Models\Tahapan;
use App\Models\Ujian;
use Illuminate\Support\Facades\DB;

/** Logika perpindahan tahapan (AB-04, AB-12, AB-13) dan gerbang persetujuan. */
class TahapanService
{
    /** Syarat persetujuan dosen tertentu pada tahap berjalan. */
    public static function syarat(Skripsi $s, Dosen $d): array
    {
        $tahap = $s->tahapan;
        $jenis = $tahap?->jenisPersetujuan();
        if (! $jenis || $s->status_skripsi !== 'aktif') {
            return ['jenis' => null, 'items' => [], 'terpenuhi' => false, 'sudah' => null];
        }
        $min = (int) ($tahap->min_bimbingan ?? 0);
        $n = $s->jumlahTervalidasi($d->id_dosen, true);
        $items = [['label' => "Bimbingan tervalidasi dengan Anda pada tahap ini: {$n} dari minimal {$min}", 'ok' => $n >= $min]];
        if ($jenis === 'sidang') {
            $terbuka = $s->revisi()->where('id_dosen', $d->id_dosen)->where('status', '!=', 'diterima')->count();
            $items[] = ['label' => $terbuka ? "Masih ada {$terbuka} item revisi dari Anda yang belum diterima" : 'Tidak ada item revisi terbuka dari Anda', 'ok' => $terbuka === 0];
        }
        $sudah = $s->persetujuan()->where('id_dosen', $d->id_dosen)->where('jenis', $jenis)->where('status', 'berlaku')->first();
        return ['jenis' => $jenis, 'items' => $items, 'terpenuhi' => collect($items)->every('ok'), 'sudah' => $sudah];
    }

    public static function beriPersetujuan(Skripsi $s, Dosen $d, ?string $catatan): string
    {
        $sy = self::syarat($s, $d);
        if (! $sy['jenis']) return 'Tahap saat ini tidak memerlukan persetujuan.';
        if ($sy['sudah']) return 'Anda sudah memberi persetujuan.';
        if (! $sy['terpenuhi']) return 'Syarat persetujuan belum terpenuhi.';

        DB::transaction(function () use ($s, $d, $sy, $catatan) {
            $p = Persetujuan::create(['id_skripsi' => $s->id_skripsi, 'id_dosen' => $d->id_dosen, 'jenis' => $sy['jenis'],
                'status' => 'berlaku', 'catatan' => $catatan, 'tanggal_persetujuan' => now()]);
            LogAktivitas::catat('Memberi persetujuan '.Persetujuan::JENIS[$sy['jenis']], 'persetujuan', $p->id_persetujuan);
            $mhs = $s->mahasiswa->id_pengguna;
            Notifikasi::kirim($mhs, 'Persetujuan pembimbing', $d->pengguna->nama.' memberi persetujuan maju '.Persetujuan::JENIS[$sy['jenis']].'.', route('mahasiswa.beranda'));

            // Pindah tahap jika semua pembimbing aktif sudah setuju.
            $aktif = $s->pembimbing()->pluck('id_dosen');
            $setuju = $s->persetujuan()->where('jenis', $sy['jenis'])->where('status', 'berlaku')->pluck('id_dosen');
            if ($aktif->count() >= 2 && $aktif->diff($setuju)->isEmpty()) {
                self::pindah($s, $s->tahapan->urutan + 1);
                Notifikasi::kirim($mhs, 'Tahap berpindah', 'Kedua pembimbing telah memberi persetujuan. Anda kini berada pada tahap '.$s->fresh()->tahapan->nama_tahapan.'.', route('mahasiswa.beranda'));
                $admin = \App\Models\Pengguna::whereHas('peran', fn ($q) => $q->where('nama_peran', 'admin'))->pluck('id_pengguna')->all();
                Notifikasi::kirim($admin, 'Mahasiswa siap ujian', $s->mahasiswa->pengguna->nama.' siap mengikuti '.Persetujuan::JENIS[$sy['jenis']].'.', route('admin.ujian.index'));
            }
        });
        return '';
    }

    public static function pindah(Skripsi $s, int $urutan): void
    {
        $t = Tahapan::urutan($urutan);
        if (! $t) return;
        $data = ['id_tahapan' => $t->id_tahapan, 'tanggal_masuk_tahap' => today()];
        if ($urutan >= Tahapan::max('urutan')) {
            $data['status_skripsi'] = 'selesai';
            $data['tanggal_selesai'] = today();
        }
        $s->update($data);
        $s->setRelation('tahapan', $t);
    }

    /** Catat hasil ujian (AB-13). Mengembalikan pesan galat atau string kosong. */
    public static function catatUjian(Skripsi $s, array $v): string
    {
        $urutan = $s->tahapan->urutan;
        $jenis = $v['jenis'];
        $harus = ['seminar_proposal' => 3, 'sidang' => 5][$jenis];
        if ($urutan !== $harus) return 'Mahasiswa tidak sedang berada pada tahap '.Persetujuan::JENIS[$jenis].'.';

        DB::transaction(function () use ($s, $v, $jenis, $urutan) {
            $u = Ujian::create(['id_skripsi' => $s->id_skripsi, 'jenis' => $jenis, 'tanggal_pelaksanaan' => $v['tanggal_pelaksanaan'],
                'hasil' => $v['hasil'], 'catatan' => $v['catatan'] ?? null,
                'tanggal_revisi_selesai' => $v['hasil'] === 'lulus_dengan_revisi' ? ($v['tanggal_revisi_selesai'] ?? null) : null,
                'id_admin' => auth()->id(), 'tanggal_input' => now()]);
            LogAktivitas::catat('Mencatat hasil '.Persetujuan::JENIS[$jenis].': '.Ujian::HASIL[$v['hasil']], 'ujian', $u->id_ujian);

            if ($v['hasil'] === 'tidak_lulus') {
                $s->persetujuan()->where('jenis', $jenis)->where('status', 'berlaku')->update(['status' => 'dibatalkan']);
                self::pindah($s, $urutan - 1);
            } elseif ($jenis === 'sidang' && $v['hasil'] === 'lulus_dengan_revisi' && empty($v['tanggal_revisi_selesai'])) {
                // tetap di tahap sidang sampai tanggal revisi selesai diisi
            } else {
                self::pindah($s, $urutan + 1);
            }
            $tujuan = array_merge([$s->mahasiswa->id_pengguna], $s->pembimbing->map(fn ($p) => $p->dosen->id_pengguna)->all());
            Notifikasi::kirim($tujuan, 'Hasil '.Persetujuan::JENIS[$jenis], $s->mahasiswa->pengguna->nama.': '.Ujian::HASIL[$v['hasil']].'.', null);
        });
        return '';
    }

    /** Menyelesaikan sidang "lulus dengan revisi" setelah tanggal revisi selesai diisi. */
    public static function selesaikanRevisiSidang(Ujian $u, string $tanggal): void
    {
        $u->update(['tanggal_revisi_selesai' => $tanggal]);
        $s = $u->skripsi;
        if ($s->tahapan->urutan === 5 && $s->status_skripsi === 'aktif') self::pindah($s, 6);
        LogAktivitas::catat('Mengisi tanggal revisi sidang selesai', 'ujian', $u->id_ujian);
    }

    /** Skripsi yang sedang menunggu pencatatan hasil ujian. */
    public static function siapUjian()
    {
        return Skripsi::with(['mahasiswa.pengguna', 'tahapan', 'pembimbing.dosen.pengguna', 'persetujuan', 'ujian'])
            ->where('status_skripsi', 'aktif')
            ->whereHas('tahapan', fn ($q) => $q->whereIn('urutan', [3, 5]))
            ->get()->map(function ($s) {
                $jenis = $s->tahapan->urutan === 3 ? 'seminar_proposal' : 'sidang';
                $s->jenis_ujian = $jenis;
                $s->ujian_tertunda = $s->ujian->where('jenis', 'sidang')->where('hasil', 'lulus_dengan_revisi')->whereNull('tanggal_revisi_selesai')
                    ->where('tanggal_input', '>=', $s->tanggal_masuk_tahap)->first();
                return $s;
            });
    }
}
