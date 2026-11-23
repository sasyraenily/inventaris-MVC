<?php
declare(strict_types=1);
class Barang
{
    public const KATEGORI = ['Elektronik', 'Furnitur', 'Alat Tulis', 'Kebersihan', 'Olahraga', 'Lainnya'];
    public const KONDISI  = ['Baik', 'Rusak'];
    public const STATUS   = ['Aman', 'Stok Rendah', 'Habis'];

    public const URUTAN = [
        'nama_asc'     => ['Nama A–Z',          'nama_barang ASC'],
        'nama_desc'    => ['Nama Z–A',          'nama_barang DESC'],
        'jumlah_desc'  => ['Jumlah terbanyak',  'jumlah DESC'],
        'jumlah_asc'   => ['Jumlah tersedikit', 'jumlah ASC'],
        'tanggal_desc' => ['Tanggal terbaru',   'tanggal_masuk DESC'],
        'tanggal_asc'  => ['Tanggal terlama',   'tanggal_masuk ASC'],
    ];

    private const STATUS_SQL = "CASE WHEN jumlah = 0 THEN 'Habis'
                                     WHEN jumlah < stok_minimum THEN 'Stok Rendah'
                                     ELSE 'Aman' END";

    public function __construct(private PDO $db) {}

    public function getAll(array $f): array
    {
        $where  = [];
        $params = [];

        if (($f['q'] ?? '') !== '') {
            $like = '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $f['q']) . '%';
            $where[] = "(nama_barang LIKE :q1 ESCAPE '|' OR kode_barang LIKE :q2 ESCAPE '|')";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }
        if (($f['kategori'] ?? '') !== '') {
            $where[] = 'kategori = :kategori';
            $params[':kategori'] = $f['kategori'];
        }
        if (($f['kondisi'] ?? '') !== '') {
            $where[] = 'kondisi = :kondisi';
            $params[':kondisi'] = $f['kondisi'];
        }
        switch ($f['status'] ?? '') {
            case 'Habis':       $where[] = 'jumlah = 0'; break;
            case 'Stok Rendah': $where[] = 'jumlah > 0 AND jumlah < stok_minimum'; break;
            case 'Aman':        $where[] = 'jumlah >= stok_minimum'; break;
        }
        if (($f['dari'] ?? '') !== '') {
            $where[] = 'tanggal_masuk >= :dari';
            $params[':dari'] = $f['dari'];
        }
        if (($f['sampai'] ?? '') !== '') {
            $where[] = 'tanggal_masuk <= :sampai';
            $params[':sampai'] = $f['sampai'];
        }

        $orderKey = isset(self::URUTAN[$f['urut'] ?? '']) ? $f['urut'] : 'nama_asc';
        $order    = self::URUTAN[$orderKey][1];

        $sql = 'SELECT *, ' . self::STATUS_SQL . ' AS status FROM barang'
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . " ORDER BY $order, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM barang WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Cek kode unik (saat edit, data sendiri dikecualikan) */
    public function kodeExists(string $kode, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM barang WHERE kode_barang = :k AND id <> :id');
        $stmt->execute([':k' => $kode, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $d): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO barang (kode_barang, nama_barang, kategori, jumlah, stok_minimum, kondisi, tanggal_masuk, keterangan)
             VALUES (:kode, :nama, :kategori, :jumlah, :stokmin, :kondisi, :tanggal, :ket)'
        );
        $stmt->execute($this->bind($d));
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE barang SET kode_barang = :kode, nama_barang = :nama, kategori = :kategori, jumlah = :jumlah,
                    stok_minimum = :stokmin, kondisi = :kondisi, tanggal_masuk = :tanggal, keterangan = :ket
             WHERE id = :id'
        );
        $stmt->execute($this->bind($d) + [':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM barang WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** Statistik otomatis dari database */
    public function stats(): array
    {
        $row = $this->db->query(
            "SELECT COUNT(*)                                                        AS jenis,
                    COALESCE(SUM(jumlah), 0)                                        AS total_unit,
                    COALESCE(SUM(CASE WHEN kondisi = 'Baik'  THEN jumlah END), 0)   AS unit_baik,
                    COALESCE(SUM(CASE WHEN kondisi = 'Rusak' THEN jumlah END), 0)   AS unit_rusak,
                    COALESCE(SUM(jumlah = 0), 0)                                    AS habis,
                    COALESCE(SUM(jumlah > 0 AND jumlah < stok_minimum), 0)          AS rendah
             FROM barang"
        )->fetch();
        return array_map('intval', $row);
    }

    private function bind(array $d): array
    {
        return [
            ':kode'     => $d['kode_barang'],
            ':nama'     => $d['nama_barang'],
            ':kategori' => $d['kategori'],
            ':jumlah'   => $d['jumlah'],
            ':stokmin'  => $d['stok_minimum'],
            ':kondisi'  => $d['kondisi'],
            ':tanggal'  => $d['tanggal_masuk'],
            ':ket'      => $d['keterangan'] === '' ? null : $d['keterangan'],
        ];
    }
}
