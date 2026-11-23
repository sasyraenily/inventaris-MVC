<?php
/** View: hanya menampilkan data dari Controller. */
function e(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function tgl(string $d): string {
    $bln = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    [$y, $m, $dd] = explode('-', $d);
    return (int) $dd . ' ' . $bln[(int) $m - 1] . ' ' . $y;
}
$isEdit = $editId > 0;
$statusClass = ['Aman' => 'ok', 'Stok Rendah' => 'warn', 'Habis' => 'bad'];
$resetUrl = $isEdit ? $url(['edit' => $editId]) : $url();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventaris Kelas</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="topbar">
  <div class="wrap">
    <h1>Inventaris Kelas</h1>
    <p>Data barang kelas</p>
  </div>
</header>

<main class="wrap">

  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
      <span><?= e($flash['msg']) ?></span>
      <button type="button" class="alert-x" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Statistik -->
  <section class="stats" aria-label="Ringkasan statistik">
    <div class="stat"><div class="stat-ico ico-a"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg></div><div><span class="lbl">Jenis barang</span><strong><?= $stats['jenis'] ?></strong></div></div>
    <div class="stat"><div class="stat-ico ico-a"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l10 5-10 5L2 7l10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg></div><div><span class="lbl">Total unit</span><strong><?= $stats['total_unit'] ?></strong></div></div>
    <div class="stat"><div class="stat-ico ico-ok"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/></svg></div><div><span class="lbl">Unit kondisi baik</span><strong><?= $stats['unit_baik'] ?></strong></div></div>
    <div class="stat"><div class="stat-ico ico-bad"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg></div><div><span class="lbl">Unit kondisi rusak</span><strong><?= $stats['unit_rusak'] ?></strong></div></div>
    <div class="stat"><div class="stat-ico ico-warn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></div><div><span class="lbl">Habis / stok rendah</span><strong><?= $stats['habis'] ?> / <?= $stats['rendah'] ?></strong></div></div>
  </section>

  <div class="layout">

    <aside class="card form-card" id="form">
      <div class="card-head">
        <h2><?= $isEdit ? 'Edit barang' : 'Tambah barang' ?></h2>
        <?php if ($isEdit): ?><span class="editing-note">sedang mengedit</span><?php endif; ?>
      </div>

      <form method="post" action="<?= e($url()) ?>" novalidate>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editId ?>">

        <div class="row2">
          <div class="field <?= isset($errors['kode_barang']) ? 'has-err' : '' ?>">
            <label for="kode_barang">Kode</label>
            <input type="text" id="kode_barang" name="kode_barang" maxlength="10" placeholder="BRG009"
                   value="<?= e($form['kode_barang']) ?>" autocomplete="off">
            <?php if (isset($errors['kode_barang'])): ?><em class="err"><?= e($errors['kode_barang']) ?></em><?php endif; ?>
          </div>
          <div class="field <?= isset($errors['nama_barang']) ? 'has-err' : '' ?>">
            <label for="nama_barang">Nama Barang</label>
            <input type="text" id="nama_barang" name="nama_barang" maxlength="100" placeholder="Papan Tulis"
                   value="<?= e($form['nama_barang']) ?>">
            <?php if (isset($errors['nama_barang'])): ?><em class="err"><?= e($errors['nama_barang']) ?></em><?php endif; ?>
          </div>
        </div>

        <div class="field <?= isset($errors['kategori']) ? 'has-err' : '' ?>">
          <label for="kategori">Kategori</label>
          <select id="kategori" name="kategori">
            <option value="">-- Pilih kategori --</option>
            <?php foreach ($kategoriList as $k): ?>
              <option value="<?= e($k) ?>" <?= $form['kategori'] === $k ? 'selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['kategori'])): ?><em class="err"><?= e($errors['kategori']) ?></em><?php endif; ?>
        </div>

        <div class="row2">
          <div class="field <?= isset($errors['jumlah']) ? 'has-err' : '' ?>">
            <label for="jumlah">Jumlah</label>
            <input type="number" id="jumlah" name="jumlah" min="0" step="1" placeholder="0" value="<?= e($form['jumlah']) ?>">
            <?php if (isset($errors['jumlah'])): ?><em class="err"><?= e($errors['jumlah']) ?></em><?php endif; ?>
          </div>
          <div class="field <?= isset($errors['stok_minimum']) ? 'has-err' : '' ?>">
            <label for="stok_minimum">Stok Minimum</label>
            <input type="number" id="stok_minimum" name="stok_minimum" min="1" step="1" value="<?= e($form['stok_minimum']) ?>">
            <?php if (isset($errors['stok_minimum'])): ?><em class="err"><?= e($errors['stok_minimum']) ?></em><?php endif; ?>
          </div>
        </div>

        <div class="field <?= isset($errors['kondisi']) ? 'has-err' : '' ?>">
          <span class="label">Kondisi</span>
          <div class="radios">
            <?php foreach ($kondisiList as $k): ?>
              <label class="radio">
                <input type="radio" name="kondisi" value="<?= e($k) ?>" <?= $form['kondisi'] === $k ? 'checked' : '' ?>>
                <span><?= e($k) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($errors['kondisi'])): ?><em class="err"><?= e($errors['kondisi']) ?></em><?php endif; ?>
        </div>

        <div class="field <?= isset($errors['tanggal_masuk']) ? 'has-err' : '' ?>">
          <label for="tanggal_masuk">Tanggal Masuk</label>
          <input type="date" id="tanggal_masuk" name="tanggal_masuk" max="<?= date('Y-m-d') ?>" value="<?= e($form['tanggal_masuk']) ?>">
          <?php if (isset($errors['tanggal_masuk'])): ?><em class="err"><?= e($errors['tanggal_masuk']) ?></em><?php endif; ?>
        </div>

        <div class="field <?= isset($errors['keterangan']) ? 'has-err' : '' ?>">
          <label for="keterangan">Keterangan <small>(opsional)</small></label>
          <textarea id="keterangan" name="keterangan" rows="3" maxlength="500" placeholder=""><?= e($form['keterangan']) ?></textarea>
          <?php if (isset($errors['keterangan'])): ?><em class="err"><?= e($errors['keterangan']) ?></em><?php endif; ?>
        </div>

        <div class="actions">
          <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Barang' ?></button>
          <a class="btn btn-ghost" href="<?= e($resetUrl) ?>" role="button">Reset</a>
          <?php if ($isEdit): ?><a class="btn btn-ghost" href="<?= e($url()) ?>">Batal</a><?php endif; ?>
        </div>
      </form>
    </aside>

    <!-- Tabel + filter -->
    <div class="right-col">
    <section class="card filter-card">
      <div class="card-head">
        <h2>Cari &amp; filter</h2>
      </div>

      <form method="get" action="index.php" class="filters" id="filterForm">
        <div class="field full">
          <label for="q">Cari Barang</label>
          <input type="search" id="q" name="q" maxlength="100" placeholder="Ketik nama atau kode barang..." value="<?= e($filters['q']) ?>">
        </div>

        <div class="field">
          <label for="f_kategori">Kategori</label>
          <select id="f_kategori" name="kategori" data-auto>
            <option value="">Semua</option>
            <?php foreach ($kategoriList as $k): ?>
              <option value="<?= e($k) ?>" <?= $filters['kategori'] === $k ? 'selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="f_kondisi">Kondisi</label>
          <select id="f_kondisi" name="kondisi" data-auto>
            <option value="">Semua</option>
            <?php foreach ($kondisiList as $k): ?>
              <option value="<?= e($k) ?>" <?= $filters['kondisi'] === $k ? 'selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="f_status">Status Stok</label>
          <select id="f_status" name="status" data-auto>
            <option value="">Semua</option>
            <?php foreach ($statusList as $s): ?>
              <option value="<?= e($s) ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="f_urut">Urutkan</label>
          <select id="f_urut" name="urut" data-auto>
            <?php foreach ($urutanList as $key => $u): ?>
              <option value="<?= e($key) ?>" <?= $filters['urut'] === $key ? 'selected' : '' ?>><?= e($u[0]) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="f_dari">Tanggal Masuk Dari</label>
          <input type="date" id="f_dari" name="dari" value="<?= e($filters['dari']) ?>" data-auto>
        </div>
        <div class="field">
          <label for="f_sampai">Sampai</label>
          <input type="date" id="f_sampai" name="sampai" value="<?= e($filters['sampai']) ?>" data-auto>
        </div>

        <div class="filter-bar full">
          <span class="count"><b><?= count($items) ?></b> barang ditemukan</span>
          <div class="filter-btns">
            <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
            <a href="index.php" class="btn btn-ghost btn-sm <?= $hasFilter ? '' : 'is-disabled' ?>">Reset Filter</a>
          </div>
        </div>
      </form>
    </section>

    <section class="card list-card">
      <div class="card-head">
        <h2>Daftar inventaris</h2>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Barang</th><th>Kategori</th><th class="num">Jumlah</th><th>Kondisi</th>
              <th>Status</th><th>Tanggal Masuk</th><th class="right">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$items): ?>
            <tr><td colspan="7" class="empty">
              <strong>Tidak ada barang ditemukan</strong>
              <span><?= $hasFilter ? 'Coba ubah atau reset filter pencarian.' : 'Belum ada data. Tambahkan barang pertama Anda.' ?></span>
            </td></tr>
          <?php endif; ?>
          <?php foreach ($items as $it): ?>
            <tr class="<?= $editId === (int) $it['id'] ? 'is-editing' : '' ?>">
              <td data-l="Barang">
                <b><?= e($it['nama_barang']) ?></b>
                <small class="code"><?= e($it['kode_barang']) ?></small>
                <?php if ($it['keterangan'] !== null && $it['keterangan'] !== ''): ?>
                  <small class="note"><?= e($it['keterangan']) ?></small>
                <?php endif; ?>
              </td>
              <td data-l="Kategori"><?= e($it['kategori']) ?></td>
              <td data-l="Jumlah" class="num"><b><?= (int) $it['jumlah'] ?></b><small>min. <?= (int) $it['stok_minimum'] ?></small></td>
              <td data-l="Kondisi"><span class="tag <?= $it['kondisi'] === 'Baik' ? 't-plain' : 't-rusak' ?>"><?= e($it['kondisi']) ?></span></td>
              <td data-l="Status"><span class="tag <?= $statusClass[$it['status']] ?>"><?= e($it['status']) ?></span></td>
              <td data-l="Tanggal"><?= e(tgl($it['tanggal_masuk'])) ?></td>
              <td class="right actions-cell">
                <a class="btn btn-sm btn-edit" href="<?= e($url(['edit' => $it['id']])) ?>#form">Edit</a>
                <form method="post" action="<?= e($url()) ?>" class="inline"
                      onsubmit="return confirm('Hapus barang &quot;<?= e(addslashes($it['nama_barang'])) ?>&quot;? Tindakan ini tidak dapat dibatalkan.');">
                  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-del">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    </div>
  </div>
</main>

<footer class="foot">Inventaris Kelas &middot; Praktik Pemrograman Web</footer>

<script>
  document.querySelectorAll('#filterForm [data-auto]').forEach(function (el) {
    el.addEventListener('change', function () { document.getElementById('filterForm').submit(); });
  });
  // Tombol Reset Filter yang nonaktif tidak perlu diklik
  document.querySelectorAll('.is-disabled').forEach(function (a) {
    a.addEventListener('click', function (ev) { ev.preventDefault(); });
  });
  // Hilangkan pesan sukses otomatis
  var ok = document.querySelector('.alert-success');
  if (ok) setTimeout(function () { ok.remove(); }, 5000);
  // Saat mengedit, geser tampilan ke form
  <?php if ($isEdit): ?>document.getElementById('form').scrollIntoView({behavior: 'smooth', block: 'start'});<?php endif; ?>
</script>
</body>
</html>
