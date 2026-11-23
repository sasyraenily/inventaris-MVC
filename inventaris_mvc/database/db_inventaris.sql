-- Database Sistem Inventaris Kelas
CREATE DATABASE IF NOT EXISTS db_inventaris
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_inventaris;

DROP TABLE IF EXISTS barang;
CREATE TABLE barang (
  id            INT          NOT NULL AUTO_INCREMENT,
  kode_barang   VARCHAR(10)  NOT NULL,
  nama_barang   VARCHAR(100) NOT NULL,
  kategori      VARCHAR(30)  NOT NULL,
  jumlah        INT          NOT NULL DEFAULT 0,
  stok_minimum  INT          NOT NULL DEFAULT 5,
  kondisi       VARCHAR(20)  NOT NULL DEFAULT 'Baik',
  tanggal_masuk DATE         NOT NULL,
  keterangan    TEXT         NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kode_barang (kode_barang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO barang (kode_barang, nama_barang, kategori, jumlah, stok_minimum, kondisi, tanggal_masuk, keterangan) VALUES
('BRG001', 'Proyektor',        'Elektronik',  1, 2,  'Baik',  '2025-07-15', 'Proyektor LCD untuk presentasi kelas'),
('BRG002', 'Kursi',            'Furnitur',   30, 20, 'Baik',  '2025-07-14', 'Kursi siswa'),
('BRG003', 'Spidol',           'Alat Tulis',  0, 10, 'Rusak', '2025-08-02', 'Spidol papan tulis, tinta habis'),
('BRG004', 'Mouse',            'Elektronik',  3, 5,  'Baik',  '2025-09-10', 'Mouse USB untuk komputer kelas'),
('BRG005', 'Meja',             'Furnitur',   15, 10, 'Baik',  '2025-07-14', 'Meja siswa'),
('BRG006', 'Sapu',             'Kebersihan',  4, 3,  'Baik',  '2025-10-01', NULL),
('BRG007', 'Bola Basket',      'Olahraga',    2, 3,  'Rusak', '2026-01-20', 'Bola kempes, perlu diganti'),
('BRG008', 'Penghapus Papan',  'Alat Tulis', 12, 5,  'Baik',  '2026-02-05', NULL);
