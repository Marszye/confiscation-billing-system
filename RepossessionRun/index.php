<?php
session_start();
include 'config.php'; // Koneksi PDO

// Ambil data santri untuk autocomplete nama (datalist)
$sql = "SELECT nama FROM santri ORDER BY nama";
$stmt = $conn->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar barang
$sql = "SELECT id, nama_barang, harga FROM barang_list ORDER BY nama_barang";
$stmt = $conn->prepare($sql);
$stmt->execute();
$barang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar pengawas
$sql = "SELECT id, nama_pengawas FROM pengawas_list ORDER BY nama_pengawas";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pengawas_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>RepossessionRun - Sistem Pembayaran Sitaan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brown: {
              50: '#FAF5F0',
              100: '#F5EBE0',
              200: '#E6D5C1',
              300: '#D4BEA2',
              400: '#C2A783',
              500: '#B08D59',
              600: '#8D703F',
              700: '#6A5430',
              800: '#473720',
              900: '#231B10',
            }
          },
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'sans-serif'],
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #FAF5F0; /* latar coklat muda */
      color: #473720;
    }
    /* Header dan navigasi */
    .header {
      background-color: #6A5430;
      color: #F5EBE0;
    }
    .header-title {
      font-size: 1.25rem;
      font-weight: 600;
    }
    .header-subtitle {
      font-size: 0.75rem;
    }
    .header-nav a {
      background-color: #8D703F;
      color: #F5EBE0;
      border: 1px solid #8D703F;
      transition: all 0.2s ease;
    }
    .header-nav a:hover {
      background-color: #473720;
      border-color: #473720;
    }
    /* Tombol default (kecuali BAYAR): latar coklat terang, teks lebih gelap */
    .btn-inverse {
      background-color: #E6D5C1;
      color: #473720;
      border: 1px solid #C2A783;
      transition: all 0.3s ease;
    }
    .btn-inverse:hover {
      background-color: #6A5430;
      color: #F5EBE0;
      border-color: #6A5430;
    }
    /* Tombol BAYAR: latar #6A5430, teks putih */
    .btn-bayar {
      background-color: #6A5430;
      color: #F5EBE0;
      border: 1px solid #6A5430;
    }
    /* Modal header warna coklat, teks putih */
    .modal-header-brown {
      background-color: #6A5430;
      color: #F5EBE0;
      border-bottom: 1px solid #6A5430;
    }
    .modal-header-brown:hover {
      background-color: #6A5430;
      color: #F5EBE0;
    }
    /* Box styling untuk form dan tabel */
    .box-shadow {
      background-color: #FFFFFF;
      border: 1px solid #E6D5C1;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    /* Tabel */
    table.min-w-full {
      border: 1px solid #D4BEA2;
    }
    table.min-w-full th {
      background-color: #E6D5C1;
      color: #473720;
      border: 1px solid #D4BEA2;
      padding: 8px;
    }
    table.min-w-full td {
      border: 1px solid #E6D5C1;
      padding: 8px;
    }
    /* Tabel header tetap */
    .fixed-header-table {
      height: 600px;
      overflow-y: auto;
    }
    .fixed-header-table thead th {
      position: sticky;
      top: 0;
      z-index: 1;
    }
    
  </style>
</head>
<body>
  <?php if (isset($_SESSION['danger'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
      <div class="toast show bg-danger text-white" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
          <strong class="me-auto">Error</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <?= htmlspecialchars($_SESSION['danger']) ?>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['danger']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
      <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
          <strong class="me-auto"><?= htmlspecialchars($_SESSION['message']['type']) ?></strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <?= htmlspecialchars($_SESSION['message']['text']) ?>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <header class="header px-4 py-2 flex justify-between items-center">
    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px;">
      <h1 style="font-size: 2rem; font-weight: bold;">REPOSSESSION RUN</h1>
      <p style="font-size: 0.5rem;">V1.0 - SISTEM PEMBAYARAN SITAAN</p>
    </div>
    <nav class="header-nav space-x-2">
      <a href="admin.php" class="px-3 py-1 rounded">ADMIN</a>
      <a href="best_record.php" class="px-3 py-1 rounded">BEST RECORD</a>
      <a href="cek_denda.php" class="px-3 py-1 rounded">CEK DENDA</a>
      <a href="download.php" class="px-3 py-1 rounded">DOWNLOAD</a>
    </nav>
  </header>

  <main class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row gap-6">
      <div class="mt-4 w-full md:w-1/4">
        <div class="box-shadow rounded p-4 custom-height">
          <form id="sitaanForm" method="POST" action="proses_bayar.php">
            <div class="mb-4">
              <label class="block mb-1">Nama</label>
              <input type="text" list="namaList" id="nama" name="nama" class="w-full p-2 border rounded" placeholder="Ketik nama..." required>
              <datalist id="namaList">
                <?php
                  foreach ($students as $row) {
                    echo '<option value="' . htmlspecialchars($row['nama']) . '"></option>';
                  }
                ?>
              </datalist>
            </div>
            <div class="mb-4">
              <label class="block mb-1">Kelas</label>
              <input type="text" id="kelas" name="kelas" class="w-full p-2 border rounded" readonly />
            </div>
            <div class="mb-4">
              <label class="block mb-1">Jenis Barang</label>
              <select class="w-full p-2 border rounded" name="jenis_barang" id="jenis_barang" required onchange="hitungNominal()">
                <option value="">Pilih Jenis Barang</option>
                <?php foreach ($barang_list as $barang): ?>
                  <option value="<?= htmlspecialchars($barang['nama_barang']) ?>" data-harga="<?= $barang['harga'] ?>">
                    <?= htmlspecialchars($barang['nama_barang']) ?> (Rp <?= number_format($barang['harga']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-4">
              <label class="block mb-1">Jumlah Barang</label>
              <input type="number" id="jumlah_barang" name="jumlah_barang" class="w-full p-2 border rounded" min="1" value="1" required onchange="hitungNominal()">
            </div>

            <input type="hidden" id="nominal_value" name="nominal_value" />
            <input type="hidden" id="nominal_display_text" name="nominal_display_text" /> <div class="mb-4">
              <label class="block mb-1">Pengawas</label>
              <select class="w-full p-2 border rounded" name="pengawas" id="pengawas" required>
                <option value="">Pilih Pengawas</option>
                <?php foreach ($pengawas_list as $pengawas): ?>
                  <option value="<?= htmlspecialchars($pengawas['nama_pengawas']) ?>">
                    <?= htmlspecialchars($pengawas['nama_pengawas']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="button" id="submitBtn" class="w-full py-2 rounded btn-bayar" onclick="konfirmasiBayar()" disabled>
              BAYAR
            </button>
            <p class="text-center text-xs mt-4 text-gray-500">&copy; DEVELOPER BY MARSZYE</p>
          </form>
        </div>
      </div>

      <div class="mt-4 w-full md:w-3/4">
  <div class="box-shadow rounded p-4 overflow-hidden" style="height: 100%;">
    <div class="fixed-header-table" style="max-height: calc(100vh - 200px);">
      <table class="min-w-full border-collapse">
        <thead>
          <tr>
            <th class="px-2 py-2 text-left">No</th>
            <th class="px-2 py-2 text-left">Nama Santri</th>
            <th class="px-2 py-2 text-left">Kelas</th>
            <th class="px-2 py-2 text-left">Jenis</th>
            <th class="px-2 py-2 text-left">Jumlah</th>
            <th class="px-2 py-2 text-left">Waktu</th>
            <th class="px-2 py-2 text-left">Nominal</th>
            <th class="px-2 py-2 text-left">Pengawas</th>
          </tr>
        </thead>
        <tbody>
          <?php include 'history.php'; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

  <div class="modal fade" id="konfirmasiModal" tabindex="-1" aria-labelledby="konfirmasiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header modal-header-brown">
          <h5 class="modal-title" id="konfirmasiModalLabel">Konfirmasi Pembayaran</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> </div>
        <div class="modal-body">
          <p id="konfirmasiPesan"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="konfirmasiBayarBtn" style="background-color: #6A5430; border-color: #6A5430;">Konfirmasi</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

  <script>
    // Inisialisasi toast
    document.addEventListener('DOMContentLoaded', function() {
      var toastElList = [].slice.call(document.querySelectorAll('.toast'));
      var toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
      });

      // Aktifkan semua toast
      toastList.forEach(toast => toast.show());

      // Panggil hitungNominal saat halaman load untuk memastikan state tombol bayar benar
      hitungNominal();
    });

    // Fungsi untuk menghitung nominal berdasarkan jenis dan jumlah barang
    function hitungNominal() {
      const jenisSelect = document.getElementById('jenis_barang');
      const jumlahInput = document.getElementById('jumlah_barang');
      const nominalValueInput = document.getElementById('nominal_value');
      const nominalDisplayText = document.getElementById('nominal_display_text'); // Input tersembunyi baru
      const submitBtn = document.getElementById('submitBtn');
      const namaInput = document.getElementById('nama');

      // Nonaktifkan tombol bayar secara default
      submitBtn.disabled = true;
      nominalValueInput.value = '';
      nominalDisplayText.value = '';

      if (jenisSelect.selectedIndex > 0 && parseInt(jumlahInput.value) > 0 && namaInput.value.trim() !== '') {
        const harga = jenisSelect.options[jenisSelect.selectedIndex].getAttribute('data-harga');
        const jumlah = parseInt(jumlahInput.value);
        const total = parseInt(harga) * jumlah;

        // Cek denda tambahan 50% via AJAX
        $.ajax({
          url: 'cek_denda.php', //
          type: 'POST',
          data: {
            nama: namaInput.value,
            check_only: true
          },
          success: function(response) {
            try {
              const data = JSON.parse(response);
              let finalTotal = total;
              let displayText = '';

              if (data.apply_denda) { //
                finalTotal = total * 1.5; // Tambahan 50%
                displayText = 'Rp ' + finalTotal.toLocaleString('id-ID') + ' (Termasuk denda 50%)';
              } else {
                displayText = 'Rp ' + total.toLocaleString('id-ID');
              }

              nominalValueInput.value = finalTotal; // Simpan nilai angka
              nominalDisplayText.value = displayText; // Simpan teks display
              submitBtn.disabled = false; // Aktifkan tombol bayar jika semua valid

            } catch (e) {
              console.error('Error parsing JSON:', e);
              // Jika error parsing, gunakan nilai dasar
              nominalValueInput.value = total;
              nominalDisplayText.value = 'Rp ' + total.toLocaleString('id-ID');
              submitBtn.disabled = false;
            }
          },
          error: function() {
            console.error('AJAX call to cek_denda.php failed.');
            // Jika AJAX gagal, gunakan nilai dasar
            nominalValueInput.value = total;
            nominalDisplayText.value = 'Rp ' + total.toLocaleString('id-ID');
            submitBtn.disabled = false;
          }
        });
      }
    }

    // AJAX untuk mendapatkan kelas santri berdasarkan nama
    document.getElementById('nama').addEventListener('input', function() { // Ubah ke 'input' agar lebih responsif
      const nama = this.value;
      const kelasInput = document.getElementById('kelas');

      if (nama.trim() !== '') {
        $.ajax({
          url: 'get_kelas.php', //
          type: 'POST',
          data: { nama: nama },
          success: function(response) {
            kelasInput.value = response; //
            hitungNominal(); // Hitung ulang nominal setiap kali nama berubah
          },
          error: function() {
              console.error('AJAX call to get_kelas.php failed.');
              kelasInput.value = '';
              hitungNominal(); // Hitung ulang (akan menonaktifkan tombol jika perlu)
          }
        });
      } else {
        kelasInput.value = '';
        hitungNominal(); // Hitung ulang (akan menonaktifkan tombol)
      }
    });

    // Panggil hitungNominal saat jenis barang atau jumlah berubah
    document.getElementById('jenis_barang').addEventListener('change', hitungNominal);
    document.getElementById('jumlah_barang').addEventListener('input', hitungNominal); // Ubah ke 'input'

    // Fungsi konfirmasi pembayaran
    function konfirmasiBayar() {
      const form = document.getElementById('sitaanForm');
      const nominalText = document.getElementById('nominal_display_text').value; // Ambil teks display dari input tersembunyi

      if (form.checkValidity() && nominalText) { // Pastikan nominalText tidak kosong
        // Buat pesan konfirmasi
        const pesan = `Apakah Anda sudah menyiapkan uang ${nominalText} untuk melakukan pembayaran?`;
        document.getElementById('konfirmasiPesan').textContent = pesan; // Set pesan ke elemen <p> di modal

        // Tampilkan modal
        const modal = new bootstrap.Modal(document.getElementById('konfirmasiModal'));
        modal.show();
      } else {
        // Jika form tidak valid atau nominal belum terhitung, tampilkan validasi browser standar
        form.reportValidity();
         // Beri tahu pengguna jika nominal belum siap (opsional)
        if (!nominalText) {
             alert("Nominal pembayaran belum dapat dihitung. Pastikan semua field terisi dengan benar.");
        }
      }
    }

    // Event listener untuk tombol konfirmasi pada modal
    document.getElementById('konfirmasiBayarBtn').addEventListener('click', function() {
      document.getElementById('sitaanForm').submit(); // Kirim form jika dikonfirmasi
    });
  </script>
</body>
</html>