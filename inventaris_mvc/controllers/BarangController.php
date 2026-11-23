<?php
declare(strict_types=1);

class BarangController
{
    private Barang $model;

    public function __construct(PDO $db)
    {
        $this->model = new Barang($db);
    }

    public function index(): void
    {
        $filters = $this->readFilters();
        $errors  = [];
        $old     = null;
        $editId  = 0;
        $flash   = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
                $this->flashRedirect('error', 'Sesi tidak valid. Silakan coba lagi.', $filters);
            }
            $action = $_POST['action'] ?? '';
            if ($action === 'delete') {
                $this->handleDelete($filters);
            } elseif ($action === 'save') {
                $id = (int) ($_POST['id'] ?? 0);
                $data = $this->readForm();
                $errors = $this->validate($data, $id);
                if (!$errors) {
                    $this->handleSave($id, $data, $filters);
                }
                $old    = $data;      
                $editId = $id;
            }
        } elseif (isset($_GET['edit'])) {
            $row = $this->model->find((int) $_GET['edit']);
            if ($row) {
                $old    = $row;
                $editId = (int) $row['id'];
            } else {
                $flash = ['type' => 'error', 'msg' => 'Data barang yang akan diedit tidak ditemukan.'];
            }
        }

        $today = date('Y-m-d');
        $form  = $old ?? [
            'kode_barang' => '', 'nama_barang' => '', 'kategori' => '', 'jumlah' => '',
            'stok_minimum' => '5', 'kondisi' => 'Baik', 'tanggal_masuk' => $today, 'keterangan' => '',
        ];

        $items = $this->model->getAll($filters);
        $stats = $this->model->stats();
        $csrf  = $_SESSION['csrf'];
        $kategoriList = Barang::KATEGORI;
        $kondisiList  = Barang::KONDISI;
        $statusList   = Barang::STATUS;
        $urutanList   = Barang::URUTAN;
        $hasFilter    = $this->hasFilter($filters);
        $url = fn(array $extra = []): string => $this->url($filters, $extra);

        require __DIR__ . '/../views/index.php';
    }


    private function handleSave(int $id, array $data, array $filters): void
    {
        try {
            if ($id > 0) {
                if (!$this->model->find($id)) {
                    $this->flashRedirect('error', 'Data tidak ditemukan, mungkin sudah dihapus.', $filters);
                }
                $this->model->update($id, $data);
                $this->flashRedirect('success', "Barang \"{$data['nama_barang']}\" berhasil diperbarui.", $filters);
            }
            $this->model->create($data);
            $this->flashRedirect('success', "Barang \"{$data['nama_barang']}\" berhasil ditambahkan.", $filters);
        } catch (PDOException $e) {
            $msg = $e->getCode() === '23000'
                ? 'Kode barang sudah digunakan barang lain.'
                : 'Gagal menyimpan data. Silakan coba lagi.';
            $this->flashRedirect('error', $msg, $filters);
        }
    }

    private function handleDelete(array $filters): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $ok = $id > 0 && $this->model->delete($id);
        } catch (PDOException $e) {
            $ok = false;
        }
        $this->flashRedirect($ok ? 'success' : 'error',
            $ok ? 'Barang berhasil dihapus.' : 'Gagal menghapus: data tidak ditemukan.', $filters);
    }


    private function readForm(): array
    {
        $s = fn(string $k): string => trim((string) ($_POST[$k] ?? ''));
        return [
            'kode_barang'   => strtoupper($s('kode_barang')),
            'nama_barang'   => $s('nama_barang'),
            'kategori'      => $s('kategori'),
            'jumlah'        => $s('jumlah'),
            'stok_minimum'  => $s('stok_minimum'),
            'kondisi'       => $s('kondisi'),
            'tanggal_masuk' => $s('tanggal_masuk'),
            'keterangan'    => $s('keterangan'),
        ];
    }
    private function validate(array &$d, int $id): array
    {
        $e = [];

        if ($d['kode_barang'] === '') {
            $e['kode_barang'] = 'Kode barang wajib diisi.';
        } elseif (mb_strlen($d['kode_barang']) > 10) {
            $e['kode_barang'] = 'Kode barang maksimal 10 karakter.';
        } elseif (!preg_match('/^[A-Z0-9._-]+$/', $d['kode_barang'])) {
            $e['kode_barang'] = 'Kode hanya boleh huruf, angka, titik, strip, atau garis bawah.';
        } elseif ($this->model->kodeExists($d['kode_barang'], $id)) {
            $e['kode_barang'] = 'Kode barang sudah digunakan.';
        }

        if ($d['nama_barang'] === '') {
            $e['nama_barang'] = 'Nama barang wajib diisi.';
        } elseif (mb_strlen($d['nama_barang']) > 100) {
            $e['nama_barang'] = 'Nama barang maksimal 100 karakter.';
        }

        if (!in_array($d['kategori'], Barang::KATEGORI, true)) {
            $e['kategori'] = 'Pilih kategori.';
        }

        if (!preg_match('/^\d{1,9}$/', $d['jumlah'])) {
            $e['jumlah'] = 'Jumlah harus bilangan bulat 0 atau lebih.';
        }

        if (!preg_match('/^\d{1,9}$/', $d['stok_minimum']) || (int) $d['stok_minimum'] < 1) {
            $e['stok_minimum'] = 'Stok minimum harus bilangan bulat lebih dari 0.';
        }

        if (!in_array($d['kondisi'], Barang::KONDISI, true)) {
            $e['kondisi'] = 'Pilih kondisi barang.';
        }

        $tgl = DateTime::createFromFormat('!Y-m-d', $d['tanggal_masuk']);
        if (!$tgl || $tgl->format('Y-m-d') !== $d['tanggal_masuk']) {
            $e['tanggal_masuk'] = 'Tanggal masuk wajib diisi dengan format yang benar.';
        } elseif ($d['tanggal_masuk'] > date('Y-m-d')) {
            $e['tanggal_masuk'] = 'Tanggal masuk tidak boleh di masa depan.';
        }

        if (mb_strlen($d['keterangan']) > 500) {
            $e['keterangan'] = 'Keterangan maksimal 500 karakter.';
        }

        if (!$e) {
            $d['jumlah']       = (int) $d['jumlah'];
            $d['stok_minimum'] = (int) $d['stok_minimum'];
        }
        return $e;
    }

    private function readFilters(): array
    {
        $f = [
            'q' => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100),
            'kategori' => (string) ($_GET['kategori'] ?? ''),
            'kondisi'  => (string) ($_GET['kondisi'] ?? ''),
            'status'   => (string) ($_GET['status'] ?? ''),
            'dari'     => $this->validDate((string) ($_GET['dari'] ?? '')),
            'sampai'   => $this->validDate((string) ($_GET['sampai'] ?? '')),
            'urut'     => (string) ($_GET['urut'] ?? 'nama_asc'),
        ];
        if (!in_array($f['kategori'], Barang::KATEGORI, true)) $f['kategori'] = '';
        if (!in_array($f['kondisi'], Barang::KONDISI, true))   $f['kondisi']  = '';
        if (!in_array($f['status'], Barang::STATUS, true))     $f['status']   = '';
        if (!isset(Barang::URUTAN[$f['urut']]))                $f['urut']     = 'nama_asc';
        if ($f['dari'] !== '' && $f['sampai'] !== '' && $f['dari'] > $f['sampai']) {
            [$f['dari'], $f['sampai']] = [$f['sampai'], $f['dari']];   // tukar jika terbalik
        }
        return $f;
    }

    private function validDate(string $v): string
    {
        $d = DateTime::createFromFormat('!Y-m-d', $v);
        return ($d && $d->format('Y-m-d') === $v) ? $v : '';
    }

    private function hasFilter(array $f): bool
    {
        return $f['q'] !== '' || $f['kategori'] !== '' || $f['kondisi'] !== '' || $f['status'] !== ''
            || $f['dari'] !== '' || $f['sampai'] !== '' || $f['urut'] !== 'nama_asc';
    }
    private function url(array $f, array $extra = []): string
    {
        $p = array_filter($f, fn($v, $k) => $v !== '' && !($k === 'urut' && $v === 'nama_asc'),
            ARRAY_FILTER_USE_BOTH);
        $p = array_merge($p, $extra);
        return 'index.php' . ($p ? '?' . http_build_query($p) : '');
    }

    private function flashRedirect(string $type, string $msg, array $filters): never
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
        header('Location: ' . $this->url($filters));
        exit;
    }
}
