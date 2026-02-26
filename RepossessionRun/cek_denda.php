<?php
session_start();
include 'config.php';

function cekDenda($conn, $nama) {
    try {
        $sql = "SELECT COUNT(*) as total_pengambilan 
                FROM sitaan 
                WHERE nama = :nama 
                AND waktu_sitaan >= DATE_FORMAT(NOW(), '%Y-%m-01') 
                AND waktu_sitaan <= LAST_DAY(NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nama', $nama);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_pengambilan = $result['total_pengambilan'];
        $apply_denda = ($total_pengambilan >= 4);
        
        return [
            'total_pengambilan' => $total_pengambilan,
            'apply_denda' => $apply_denda
        ];
    } catch (Exception $e) {
        error_log('Error in cekDenda: ' . $e->getMessage());
        return [
            'total_pengambilan' => 0,
            'apply_denda' => false,
            'error' => $e->getMessage()
        ];
    }
}

if (isset($_POST['check_only']) && isset($_POST['nama'])) {
    $nama = trim($_POST['nama']);
    $hasil = cekDenda($conn, $nama);
    echo json_encode($hasil);
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cek Denda - RepossessionRun</title>
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
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #FAF5F0;
      color: #473720;
    }
    .header {
      background-color: #6A5430;
      color: #F5EBE0;
    }
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
    .box-shadow {
      background-color: #FFFFFF;
      border: 1px solid #E6D5C1;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <header class="header px-4 py-2 flex justify-between items-center">
    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px;">
      <h1 style="font-size: 2rem; font-weight: bold;">REPOSSESSION RUN</h1>        
      <p style="font-size: 0.5rem;">V1.0 - CEK DENDA</p>
    </div>
    <nav class="space-x-2">
      <a href="index.php" class="px-3 py-1 rounded btn-inverse">&larr; Kembali</a>
    </nav>
  </header>

  <main class="container mx-auto px-4 py-6">
    <div class="max-w-md mx-auto">
      <div class="box-shadow rounded p-4">
        <h2 class="text-xl font-bold mb-4">Cek Status Denda Santri</h2>
        
        <form id="dendaForm" method="POST">
          <div class="mb-4">
            <label class="block mb-1">Nama Santri</label>
            <input type="text" id="nama" name="nama" class="w-full p-2 border rounded" placeholder="Ketik nama santri..." required list="namaList">
            <datalist id="namaList"></datalist>
          </div>
          <button type="button" id="checkBtn" class="px-4 py-2 rounded text-white" style="background-color: #6A5430;">
            Cek Status
          </button>
        </form>
        
        <div id="resultContainer" class="mt-6 p-4 rounded hidden">
          <h3 class="font-bold mb-2">Hasil Pengecekan:</h3>
          <p>Nama Santri: <strong id="resultNama"></strong></p>
          <p>Jumlah Pengambilan Bulan Ini: <strong id="resultTotal"></strong></p>
          <p>Status Denda: <strong id="resultStatus"></strong></p>
          <p class="mt-3 text-sm">
            <i class="fas fa-info-circle mr-1"></i>
            Denda 50% berlaku jika santri memiliki 4 atau lebih pengambilan dalam satu bulan.
          </p>
        </div>
      </div>
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      // Load initial list of students for autocomplete
      function loadStudentNames(query = '') {
        $.ajax({
          url: 'get_kelas.php',
          type: 'GET',
          data: { get_namelist: true, query: query },
          success: function(response) {
            try {
              const studentList = JSON.parse(response);
              const namaList = $('#namaList');
              namaList.empty(); // Clear previous entries

              studentList.forEach(nama => {
                const option = $('<option>', { value: nama });
                namaList.append(option);
              });
            } catch (e) {
              console.error('Failed to parse student list:', e);
            }
          },
          error: function() {
            console.error('Failed to load student list');
          }
        });
      }

      // Initial load of student names
      loadStudentNames();

      // Autocomplete functionality
      $('#nama').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) { // Only load suggestions after at least 2 characters
          loadStudentNames(query);
        } else {
          loadStudentNames(); // Load full list if less than 2 characters
        }
      });

      // Check denda on button click
      $('#checkBtn').click(function() {
        const nama = $('#nama').val();

        if (!nama) {
          alert('Silakan ketik nama santri!');
          return;
        }

        $.ajax({
          url: 'cek_denda.php',
          type: 'POST',
          data: { check_only: true, nama: nama },
          success: function(response) {
            try {
              const hasil = JSON.parse(response);
              const statusColor = hasil.apply_denda ? 'bg-red-100' : 'bg-green-100';
              const statusText = hasil.apply_denda ? 
                '<strong class="text-red-600">AKTIF (50%)</strong>' : 
                '<strong class="text-green-600">TIDAK AKTIF</strong>';

              $('#resultContainer').removeClass('hidden').addClass(statusColor);
              $('#resultNama').text(nama);
              $('#resultTotal').text(hasil.total_pengambilan);
              $('#resultStatus').html(statusText);
            } catch (e) {
              console.error('Error parsing response:', e);
              alert('Terjadi kesalahan saat memproses permintaan.');
            }
          },
          error: function() {
            alert('Gagal memuat data. Silakan coba lagi.');
          }
        });
      });
    });
  </script>
</body>
</html>