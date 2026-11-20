<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\LogAktivitas;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PenggunaController extends Controller
{
    public function index(Request $r)
    {
        $tab = in_array($r->tab, ['mahasiswa', 'dosen', 'admin']) ? $r->tab : 'mahasiswa';
        $q = Pengguna::with(['peran', 'mahasiswa', 'dosen'])->whereHas('peran', fn ($p) => $p->where('nama_peran', $tab === 'dosen' ? 'dosen' : $tab));
        if ($s = trim((string) $r->cari)) {
            $q->where(fn ($w) => $w->whereRaw('LOWER(nama) LIKE ?', ['%'.mb_strtolower($s).'%'])->orWhere('username', 'like', "%$s%"));
        }
        if (in_array($r->status, ['aktif', 'nonaktif'])) $q->where('status', $r->status);
        $items = $q->orderBy('nama')->paginate(20)->withQueryString();
        return view('admin.pengguna.index', compact('items', 'tab'));
    }

    public function create(Request $r) { return view('admin.pengguna.form', ['u' => new Pengguna(), 'jenis' => $r->get('jenis', 'mahasiswa')]); }

    private function aturan(?Pengguna $u, string $jenis): array
    {
        $id = $u?->id_pengguna;
        $a = [
            'nama' => 'required|string|max:100',
            'username' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('pengguna', 'username')->ignore($id, 'id_pengguna')],
            'email' => ['required', 'email', 'max:100', Rule::unique('pengguna', 'email')->ignore($id, 'id_pengguna')],
            'peran' => 'array', 'peran.*' => Rule::in(array_keys(Peran::LABEL)),
        ];
        if ($jenis === 'mahasiswa') {
            $a += ['angkatan' => 'required|integer|min:2000|max:2100', 'no_hp' => 'nullable|string|max:15', 'status_mahasiswa' => 'required|in:aktif,cuti,lulus,keluar'];
        }
        if ($jenis === 'dosen') {
            $a += ['jabatan_fungsional' => 'nullable|string|max:50', 'kuota_pembimbing_1' => 'required|integer|min:0|max:99',
                'kuota_pembimbing_2' => 'required|integer|min:0|max:99', 'status_dosen' => 'required|in:aktif,tugas_belajar,nonaktif'];
        }
        return $a;
    }

    public function store(Request $r)
    {
        $jenis = $r->input('jenis', 'mahasiswa');
        $v = $r->validate($this->aturan(null, $jenis) + ['password_awal' => 'nullable|string|min:6']);
        $u = DB::transaction(function () use ($v, $jenis, $r) {
            $u = Pengguna::create(['nama' => $v['nama'], 'username' => $v['username'], 'email' => $v['email'],
                'password' => $v['password_awal'] ?? $v['username'], 'status' => 'aktif', 'wajib_ganti_password' => true]);
            $this->simpanProfil($u, $jenis, $v);
            $peran = array_unique(array_merge([$jenis], $v['peran'] ?? []));
            $u->peran()->sync(Peran::whereIn('nama_peran', $peran)->pluck('id_peran'));
            return $u;
        });
        LogAktivitas::catat('Menambah pengguna '.$u->username, 'pengguna', $u->id_pengguna);
        return redirect()->route('admin.pengguna.index', ['tab' => $jenis])->with('ok', "Pengguna {$u->nama} ditambahkan. Kata sandi awal: ".($v['password_awal'] ?? $v['username']).' (wajib diganti saat login pertama).');
    }

    private function simpanProfil(Pengguna $u, string $jenis, array $v): void
    {
        if ($jenis === 'mahasiswa') {
            Mahasiswa::updateOrCreate(['id_pengguna' => $u->id_pengguna], ['nim' => $u->username, 'angkatan' => $v['angkatan'],
                'no_hp' => $v['no_hp'] ?? null, 'status_mahasiswa' => $v['status_mahasiswa'] ?? 'aktif']);
        } elseif ($jenis === 'dosen') {
            Dosen::updateOrCreate(['id_pengguna' => $u->id_pengguna], ['nidn' => $u->username, 'jabatan_fungsional' => $v['jabatan_fungsional'] ?? null,
                'kuota_pembimbing_1' => $v['kuota_pembimbing_1'] ?? 10, 'kuota_pembimbing_2' => $v['kuota_pembimbing_2'] ?? 10,
                'status_dosen' => $v['status_dosen'] ?? 'aktif']);
        }
    }

    public function edit(Pengguna $pengguna)
    {
        $pengguna->load('peran', 'mahasiswa', 'dosen');
        $jenis = $pengguna->mahasiswa ? 'mahasiswa' : ($pengguna->dosen ? 'dosen' : 'admin');
        return view('admin.pengguna.form', ['u' => $pengguna, 'jenis' => $jenis]);
    }

    public function update(Request $r, Pengguna $pengguna)
    {
        $jenis = $pengguna->mahasiswa ? 'mahasiswa' : ($pengguna->dosen ? 'dosen' : 'admin');
        $v = $r->validate($this->aturan($pengguna, $jenis));
        $peran = array_unique(array_merge([$jenis === 'admin' ? 'admin' : $jenis], $v['peran'] ?? []));
        if ($pengguna->id_pengguna === auth()->id() && ! in_array('admin', $peran)) {
            return $this->gagal('Anda tidak dapat menghapus peran admin dari akun sendiri.');
        }
        DB::transaction(function () use ($pengguna, $v, $jenis, $peran) {
            $pengguna->update(['nama' => $v['nama'], 'username' => $v['username'], 'email' => $v['email']]);
            $this->simpanProfil($pengguna, $jenis, $v);
            // Peran dosen membutuhkan data dosen; kaprodi biasanya juga dosen.
            $pengguna->peran()->sync(Peran::whereIn('nama_peran', $peran)->pluck('id_peran'));
        });
        LogAktivitas::catat('Mengubah pengguna '.$pengguna->username, 'pengguna', $pengguna->id_pengguna);
        return redirect()->route('admin.pengguna.index', ['tab' => $jenis])->with('ok', 'Data pengguna disimpan.');
    }

    public function reset(Pengguna $pengguna)
    {
        $pengguna->forceFill(['password' => $pengguna->username, 'wajib_ganti_password' => true])->save();
        LogAktivitas::catat('Reset kata sandi '.$pengguna->username, 'pengguna', $pengguna->id_pengguna);
        return $this->ok("Kata sandi {$pengguna->nama} direset menjadi username ({$pengguna->username}). Pengguna wajib menggantinya saat login.");
    }

    public function status(Pengguna $pengguna)
    {
        if ($pengguna->id_pengguna === auth()->id()) return $this->gagal('Anda tidak dapat menonaktifkan akun sendiri.');
        $baru = $pengguna->status === 'aktif' ? 'nonaktif' : 'aktif';
        $pengguna->update(['status' => $baru]);
        LogAktivitas::catat(($baru === 'aktif' ? 'Mengaktifkan' : 'Menonaktifkan').' pengguna '.$pengguna->username, 'pengguna', $pengguna->id_pengguna);
        return $this->ok("Akun {$pengguna->nama} kini {$baru}.");
    }

    public function templat(Request $r)
    {
        $jenis = $r->get('jenis') === 'dosen' ? 'dosen' : 'mahasiswa';
        $kol = $jenis === 'dosen'
            ? ['nidn', 'nama', 'email', 'jabatan_fungsional', 'kuota_pembimbing_1', 'kuota_pembimbing_2', 'kaprodi(ya/tidak)']
            : ['nim', 'nama', 'email', 'angkatan', 'no_hp'];
        $contoh = $jenis === 'dosen'
            ? ['198001012005011001', 'Dr. Nama Dosen, M.Pd.', 'dosen@contoh.ac.id', 'Lektor', '10', '10', 'tidak']
            : ['2405108000', 'Nama Mahasiswa', 'mhs@contoh.ac.id', '2024', '081234567890'];
        $csv = implode(',', $kol)."\n".implode(',', array_map(fn ($x) => '"'.$x.'"', $contoh))."\n";
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=templat_{$jenis}.csv"]);
    }

    /** Impor pengguna dari Excel (.xlsx) atau CSV. Baris dengan username yang sudah ada dilewati. */
    public function impor(Request $r)
    {
        $r->validate(['jenis' => 'required|in:mahasiswa,dosen', 'berkas' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120']);
        $jenis = $r->jenis;
        try {
            $rows = IOFactory::load($r->file('berkas')->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return $this->gagal('Berkas tidak dapat dibaca. Gunakan templat yang disediakan.');
        }
        $head = array_map(fn ($h) => strtolower(trim(preg_replace('/\(.*\)/', '', (string) $h))), array_shift($rows) ?? []);
        $idx = array_flip($head);
        $kunci = $jenis === 'dosen' ? 'nidn' : 'nim';
        if (! isset($idx[$kunci], $idx['nama'], $idx['email'])) return $this->gagal("Kolom wajib: {$kunci}, nama, email.");
        $tambah = 0; $lewat = [];
        foreach ($rows as $i => $row) {
            $g = fn ($k) => isset($idx[$k]) ? trim((string) ($row[$idx[$k]] ?? '')) : '';
            $username = preg_replace('/\s+/', '', $g($kunci));
            if ($username === '' && $g('nama') === '') continue;
            $email = $g('email') ?: $username.'@sibimta.local';
            if (! $username || ! $g('nama') || Pengguna::where('username', $username)->orWhere('email', $email)->exists()) {
                $lewat[] = $i + 2; continue;
            }
            DB::transaction(function () use ($jenis, $username, $email, $g, &$tambah) {
                $u = Pengguna::create(['nama' => $g('nama'), 'username' => $username, 'email' => $email, 'password' => $username, 'status' => 'aktif', 'wajib_ganti_password' => true]);
                $peran = [$jenis];
                if ($jenis === 'dosen' && in_array(strtolower($g('kaprodi')), ['ya', 'y', '1'])) $peran[] = 'kaprodi';
                $this->simpanProfil($u, $jenis, ['angkatan' => (int) ($g('angkatan') ?: date('Y')), 'no_hp' => $g('no_hp') ?: null,
                    'jabatan_fungsional' => $g('jabatan_fungsional') ?: null, 'kuota_pembimbing_1' => (int) ($g('kuota_pembimbing_1') ?: 10),
                    'kuota_pembimbing_2' => (int) ($g('kuota_pembimbing_2') ?: 10)]);
                $u->peran()->sync(Peran::whereIn('nama_peran', $peran)->pluck('id_peran'));
                $tambah++;
            });
        }
        LogAktivitas::catat("Impor {$tambah} pengguna {$jenis}", 'pengguna');
        $msg = "{$tambah} pengguna ditambahkan. Kata sandi awal = username (wajib diganti saat login pertama).";
        if ($lewat) $msg .= ' Baris dilewati (data kosong atau sudah terdaftar): '.implode(', ', array_slice($lewat, 0, 20)).(count($lewat) > 20 ? ', ...' : '');
        return redirect()->route('admin.pengguna.index', ['tab' => $jenis])->with('ok', $msg);
    }
}
