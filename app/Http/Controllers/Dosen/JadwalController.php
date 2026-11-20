<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\JadwalBimbingan;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\SlotBimbingan;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $d = $this->dosen();
        return view('dosen.jadwal', [
            'slot' => SlotBimbingan::withCount(['jadwal as terisi' => fn ($q) => $q->whereIn('status', ['diajukan', 'disetujui', 'selesai'])])
                ->where('id_dosen', $d->id_dosen)->where('tanggal', '>=', today()->subDays(7))->orderBy('tanggal')->orderBy('jam_mulai')->get(),
            'permintaan' => JadwalBimbingan::with('skripsi.mahasiswa.pengguna', 'slot')->where('id_dosen', $d->id_dosen)
                ->whereIn('status', ['diajukan', 'diusulkan_ulang'])->orderBy('tanggal')->get(),
            'mendatang' => JadwalBimbingan::with('skripsi.mahasiswa.pengguna')->where('id_dosen', $d->id_dosen)->where('status', 'disetujui')
                ->where('tanggal', '>=', today())->orderBy('tanggal')->orderBy('jam')->get(),
        ]);
    }

    public function simpanSlot(Request $r)
    {
        $v = $r->validate(['tanggal' => 'required|date|after_or_equal:today', 'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai', 'mode' => 'required|in:luring,daring',
            'lokasi' => 'nullable|string|max:150', 'kuota' => 'required|integer|min:1|max:20', 'ulang' => 'nullable|integer|min:0|max:12'],
            [], ['jam_mulai' => 'jam mulai', 'jam_selesai' => 'jam selesai']);
        $d = $this->dosen(); $n = 0;
        for ($i = 0; $i <= (int) ($v['ulang'] ?? 0); $i++) {
            $tgl = \Carbon\Carbon::parse($v['tanggal'])->addWeeks($i);
            $bentrok = SlotBimbingan::where('id_dosen', $d->id_dosen)->whereDate('tanggal', $tgl)->where('status', 'buka')
                ->where('jam_mulai', '<', $v['jam_selesai'])->where('jam_selesai', '>', $v['jam_mulai'])->exists();
            if ($bentrok) continue;
            SlotBimbingan::create(['id_dosen' => $d->id_dosen, 'tanggal' => $tgl, 'jam_mulai' => $v['jam_mulai'], 'jam_selesai' => $v['jam_selesai'],
                'mode' => $v['mode'], 'lokasi' => $v['lokasi'] ?? null, 'kuota' => $v['kuota'], 'status' => 'buka']);
            $n++;
        }
        LogAktivitas::catat("Membuka {$n} slot bimbingan", 'slot_bimbingan');
        return $n ? $this->ok("{$n} slot dibuka.") : $this->gagal('Slot bertabrakan dengan slot lain yang masih dibuka.');
    }

    public function statusSlot(SlotBimbingan $slot)
    {
        abort_unless($slot->id_dosen === $this->dosen()->id_dosen, 403);
        $slot->update(['status' => $slot->status === 'buka' ? 'tutup' : 'buka']);
        return $this->ok('Status slot diperbarui.');
    }

    private function milik(JadwalBimbingan $j): void { abort_unless($j->id_dosen === $this->dosen()->id_dosen, 403); }

    public function setujui(JadwalBimbingan $jadwal)
    {
        $this->milik($jadwal);
        if ($jadwal->status !== 'diajukan') return $this->gagal('Jadwal ini tidak dalam status diajukan.');
        $jadwal->update(['status' => 'disetujui']);
        LogAktivitas::catat('Menyetujui jadwal bimbingan', 'jadwal_bimbingan', $jadwal->id_jadwal);
        Notifikasi::kirim($jadwal->skripsi->mahasiswa->id_pengguna, 'Jadwal disetujui', 'Bimbingan '.$jadwal->tanggal->translatedFormat('d M Y').' pukul '.substr($jadwal->jam, 0, 5).' disetujui.', route('mahasiswa.bimbingan'));
        return $this->ok('Jadwal disetujui.');
    }

    public function tolak(Request $r, JadwalBimbingan $jadwal)
    {
        $this->milik($jadwal);
        $v = $r->validate(['catatan_dosen' => 'required|string|max:255'], [], ['catatan_dosen' => 'alasan']);
        $jadwal->update(['status' => 'ditolak', 'catatan_dosen' => $v['catatan_dosen']]);
        Notifikasi::kirim($jadwal->skripsi->mahasiswa->id_pengguna, 'Jadwal ditolak', $v['catatan_dosen'], route('mahasiswa.bimbingan'));
        return $this->ok('Jadwal ditolak.');
    }

    public function usul(Request $r, JadwalBimbingan $jadwal)
    {
        $this->milik($jadwal);
        $v = $r->validate(['tanggal' => 'required|date|after_or_equal:today', 'jam' => 'required|date_format:H:i', 'catatan_dosen' => 'nullable|string|max:255']);
        $jadwal->update(['status' => 'diusulkan_ulang', 'tanggal' => $v['tanggal'], 'jam' => $v['jam'], 'id_slot' => null,
            'catatan_dosen' => $v['catatan_dosen'] ?? 'Dosen mengusulkan waktu lain.']);
        Notifikasi::kirim($jadwal->skripsi->mahasiswa->id_pengguna, 'Usulan waktu bimbingan', 'Dosen mengusulkan '.\Carbon\Carbon::parse($v['tanggal'])->translatedFormat('d M Y').' pukul '.$v['jam'].'.', route('mahasiswa.bimbingan'));
        return $this->ok('Usulan waktu dikirim ke mahasiswa.');
    }
}
