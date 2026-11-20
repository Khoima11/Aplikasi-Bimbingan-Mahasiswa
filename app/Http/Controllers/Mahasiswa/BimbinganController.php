<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\CatatanBimbingan;
use App\Models\JadwalBimbingan;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Pengaturan;
use App\Models\SlotBimbingan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BimbinganController extends Controller
{
    private function skripsi()
    {
        $s = $this->mahasiswa()->skripsi;
        if (! $s) abort(redirect()->route('mahasiswa.judul')->with('gagal', 'Fitur bimbingan tersedia setelah judul disetujui dan pembimbing ditetapkan.'));
        return $s->load('pembimbing.dosen.pengguna', 'tahapan');
    }

    public function index()
    {
        $s = $this->skripsi();
        $batas = now()->addHours(Pengaturan::angka('batas_ajuan_jadwal_jam'));
        $slot = [];
        foreach ($s->pembimbing as $p) {
            $slot[$p->id_dosen] = SlotBimbingan::withCount(['jadwal as terisi' => fn ($q) => $q->whereIn('status', ['diajukan', 'disetujui', 'selesai'])])
                ->where('id_dosen', $p->id_dosen)->where('status', 'buka')->where('tanggal', '>=', today())->orderBy('tanggal')->orderBy('jam_mulai')->get()
                ->filter(fn ($x) => $x->terisi < $x->kuota && $x->mulai()->gt($batas))->values();
        }
        return view('mahasiswa.bimbingan', [
            's' => $s, 'slot' => $slot, 'batasJam' => Pengaturan::angka('batas_ajuan_jadwal_jam'),
            'jadwal' => $s->jadwal()->with('dosen.pengguna', 'catatan')->orderByDesc('tanggal')->limit(20)->get(),
            'catatan' => $s->catatan()->with('dosen.pengguna')->orderByDesc('tanggal_bimbingan')->orderByDesc('id_catatan')->get(),
            'jadwalTanpaCatatan' => $s->jadwal()->with('dosen.pengguna')->whereIn('status', ['disetujui'])->where('tanggal', '<=', today())->doesntHave('catatan')->orderByDesc('tanggal')->get(),
        ]);
    }

    public function ajukan(Request $r)
    {
        $s = $this->skripsi();
        $v = $r->validate(['id_slot' => 'required|exists:slot_bimbingan,id_slot', 'rencana_bahasan' => 'required|string|max:255'], [], ['rencana_bahasan' => 'rencana bahasan']);
        $slot = SlotBimbingan::findOrFail($v['id_slot']);
        // AB-07
        if (! in_array($slot->id_dosen, $s->idDosenAktif())) return $this->gagal('Slot ini bukan milik pembimbing Anda.');
        if ($slot->status !== 'buka' || $slot->sisa() < 1) return $this->gagal('Slot sudah penuh atau ditutup.');
        if ($slot->mulai()->lte(now()->addHours(Pengaturan::angka('batas_ajuan_jadwal_jam')))) return $this->gagal('Pengajuan melewati batas waktu sebelum slot dimulai.');
        if ($s->jadwal()->where('id_slot', $slot->id_slot)->whereIn('status', ['diajukan', 'disetujui'])->exists()) return $this->gagal('Anda sudah mengajukan slot ini.');
        $j = JadwalBimbingan::create(['id_skripsi' => $s->id_skripsi, 'id_dosen' => $slot->id_dosen, 'id_slot' => $slot->id_slot, 'tanggal' => $slot->tanggal,
            'jam' => $slot->jam_mulai, 'mode' => $slot->mode, 'rencana_bahasan' => $v['rencana_bahasan'], 'status' => 'diajukan', 'tanggal_ajuan' => now()]);
        LogAktivitas::catat('Mengajukan jadwal bimbingan', 'jadwal_bimbingan', $j->id_jadwal);
        Notifikasi::kirim($slot->dosen->id_pengguna, 'Permintaan jadwal', $this->pengguna()->nama.' mengajukan bimbingan '.$slot->tanggal->translatedFormat('d M').' pukul '.substr($slot->jam_mulai, 0, 5).'.', route('dosen.jadwal'));
        return $this->ok('Jadwal diajukan. Menunggu konfirmasi dosen.');
    }

    private function milik(JadwalBimbingan $j): void { abort_unless($j->id_skripsi === $this->mahasiswa()->skripsi?->id_skripsi, 403); }

    public function terimaUsulan(JadwalBimbingan $jadwal)
    {
        $this->milik($jadwal);
        if ($jadwal->status !== 'diusulkan_ulang') return $this->gagal('Tidak ada usulan waktu pada jadwal ini.');
        $jadwal->update(['status' => 'disetujui']);
        Notifikasi::kirim($jadwal->dosen->id_pengguna, 'Usulan waktu diterima', $this->pengguna()->nama.' menerima usulan bimbingan '.$jadwal->tanggal->translatedFormat('d M Y').'.', route('dosen.jadwal'));
        return $this->ok('Usulan waktu diterima. Jadwal disetujui.');
    }

    public function batal(JadwalBimbingan $jadwal)
    {
        $this->milik($jadwal);
        if (! in_array($jadwal->status, ['diajukan', 'disetujui', 'diusulkan_ulang'])) return $this->gagal('Jadwal ini tidak dapat dibatalkan.');
        $jadwal->update(['status' => 'dibatalkan']);
        Notifikasi::kirim($jadwal->dosen->id_pengguna, 'Jadwal dibatalkan', $this->pengguna()->nama.' membatalkan bimbingan '.$jadwal->tanggal->translatedFormat('d M Y').'.', route('dosen.jadwal'));
        return $this->ok('Jadwal dibatalkan.');
    }

    public function simpanCatatan(Request $r)
    {
        $s = $this->skripsi();
        $v = $r->validate([
            'id_jadwal' => 'nullable|exists:jadwal_bimbingan,id_jadwal',
            'id_dosen' => ['required_without:id_jadwal', 'nullable', Rule::in($s->idDosenAktif())],
            'tanggal_bimbingan' => 'required_without:id_jadwal|nullable|date|before_or_equal:today',
            'mode' => 'required|in:luring,daring', 'bab_dibahas' => 'required|string|max:50', 'ringkasan' => 'required|string|min:20|max:5000',
        ], ['id_dosen.required_without' => 'Pilih pembimbing.'], ['bab_dibahas' => 'bab dibahas', 'tanggal_bimbingan' => 'tanggal bimbingan']);
        if (! empty($v['id_jadwal'])) {
            $j = JadwalBimbingan::findOrFail($v['id_jadwal']);
            $this->milik($j);
            if ($j->status !== 'disetujui' || $j->catatan) return $this->gagal('Jadwal ini tidak dapat diberi catatan.');
            $v['id_dosen'] = $j->id_dosen;
            $v['tanggal_bimbingan'] = $j->tanggal;
        }
        $c = CatatanBimbingan::create(['id_skripsi' => $s->id_skripsi, 'id_dosen' => $v['id_dosen'], 'id_jadwal' => $v['id_jadwal'] ?? null,
            'tanggal_bimbingan' => $v['tanggal_bimbingan'], 'mode' => $v['mode'], 'bab_dibahas' => $v['bab_dibahas'], 'ringkasan' => $v['ringkasan'],
            'status_validasi' => 'menunggu', 'tanggal_input' => now()]);
        LogAktivitas::catat('Mengisi catatan bimbingan', 'catatan_bimbingan', $c->id_catatan);
        Notifikasi::kirim($c->dosen->pengguna->id_pengguna, 'Catatan menunggu validasi', $this->pengguna()->nama.' mengisi catatan bimbingan '.$c->tanggal_bimbingan->format('d/m/Y').'.', route('dosen.tindakan'));
        return $this->ok('Catatan terkirim untuk divalidasi dosen.');
    }

    public function perbaikiCatatan(Request $r, CatatanBimbingan $catatan)
    {
        abort_unless($catatan->id_skripsi === $this->mahasiswa()->skripsi?->id_skripsi, 403);
        if ($catatan->status_validasi !== 'dikoreksi') return $this->gagal('Hanya catatan yang dikoreksi yang dapat diperbaiki.');
        $v = $r->validate(['bab_dibahas' => 'required|string|max:50', 'ringkasan' => 'required|string|min:20|max:5000']);
        $catatan->update($v + ['status_validasi' => 'menunggu', 'tanggal_input' => now(), 'tanggal_validasi' => null]);
        Notifikasi::kirim($catatan->dosen->id_pengguna, 'Catatan diperbaiki', $this->pengguna()->nama.' memperbaiki catatan bimbingan.', route('dosen.tindakan'));
        return $this->ok('Catatan diperbaiki dan dikirim ulang.');
    }
}
