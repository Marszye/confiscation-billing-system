<?php
session_start();
include 'config.php';

// Set default time range for records (current month)
$start_date = date('Y-m-01'); // First day of current month
$end_date = date('Y-m-t'); // Last day of current month

// Handle custom date range if provided
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

try {
    // Query to get top students with most confiscated items
    $sql = "SELECT nama, kelas, COUNT(*) as total_pengambilan, SUM(nominal) as total_nominal
            FROM sitaan
            WHERE waktu_sitaan BETWEEN :start_date AND :end_date -- Pastikan nama kolom waktu benar
            GROUP BY nama, kelas
            ORDER BY total_pengambilan DESC, total_nominal DESC
            LIMIT 20";

    $stmt = $conn->prepare($sql);

    // Tetap gunakan bindParam untuk $start_date karena itu adalah variabel langsung
    $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);

    // *** PERBAIKAN: Gunakan bindValue untuk ekspresi ***
    $end_date_full = $end_date . ' 23:59:59'; // Gabungkan ke variabel baru (opsional tapi jelas)
    $stmt->bindValue(':end_date', $end_date_full, PDO::PARAM_STR);

    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Log error lebih detail jika memungkinkan
    error_log('Error in best_record.php: ' . $e->getMessage() . "\nQuery: " . $sql . "\nParams: start=" . $start_date . ", end=" . ($end_date_full ?? 'N/A'));
    $_SESSION['danger'] = "Gagal mengambil data best record. Error: " . $e->getMessage(); // Tampilkan pesan error ke user jika perlu
    $records = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Best Record - RepossessionRun</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    /* (Style CSS Anda tetap sama) */
     /* Brown color scheme */
    :root {
      --brown-lightest: #f5f0e1;
      --brown-light: #e6d7c3;
      --brown-medium: #c8b6a6;
      --brown-dark: #a47551;
      --brown-darkest: #5c3c23;
    }

    /* Base styling */
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: var(--brown-lightest);
      color: var(--brown-darkest);
    }

    /* Header and navigation */
    .header {
      background-color: var(--brown-darkest);
      color: var(--brown-lightest);
    }

    .header-nav a {
      background-color: var(--brown-dark);
      color: var(--brown-lightest);
      border: 1px solid var(--brown-dark);
      transition: all 0.2s ease;
    }

    .header-nav a:hover {
      background-color: var(--brown-darkest);
      border-color: var(--brown-darkest);
    }

    /* Table styling */
    .box-shadow {
      background-color: #fff;
      border: 1px solid var(--brown-medium);
      box-shadow: 0 2px 4px rgba(92, 60, 35, 0.1);
    }

    table.min-w-full {
      border: 1px solid var(--brown-dark);
    }

    table.min-w-full th {
      background-color: var(--brown-medium);
      border: 1px solid var(--brown-dark);
      padding: 8px;
    }

    table.min-w-full td {
      border: 1px solid var(--brown-light);
      padding: 8px;
    }

    .medal-gold {
      color: #FFD700;
    }

    .medal-silver {
      color: #C0C0C0;
    }

    .medal-bronze {
      color: #CD7F32;
    }

    /* Filter panel */
    .filter-panel {
      background-color: white;
      border: 1px solid var(--brown-medium);
      border-radius: 0.5rem;
    }

    .btn-brown {
      background-color: var(--brown-dark);
      color: white;
      border: none;
      transition: all 0.2s ease;
    }

    .btn-brown:hover {
      background-color: var(--brown-darkest);
      color: white;
    }
  </style>
</head>
<body>
  <?php if (isset($_SESSION['danger'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000;">
      <div class="toast show align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <?= htmlspecialchars($_SESSION['danger']) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['danger']); ?>
  <?php endif; ?>

  <header class="header px-4 py-2 flex justify-between items-center">
    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px;">
      <h1 style="font-size: 2rem; font-weight: bold;">BEST RECORD</h1>
      <p style="font-size: 0.5rem;">REPOSSESSION RUN CHAMPIONS</p>
    </div>
    <nav class="header-nav space-x-2">
      <a href="index.php" class="px-3 py-1 rounded">KEMBALI KE BERANDA</a>
    </nav>
  </header>


  <main class="container mx-auto px-4 py-6">
    <div class="filter-panel p-4 mb-6">
      <form method="GET" class="flex flex-wrap items-end gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Tanggal Mulai</label>
          <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="border rounded p-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Tanggal Akhir</label>
          <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="border rounded p-2">
        </div>
        <div>
          <button type="submit" class="btn btn-brown px-4 py-2">Filter</button>
        </div>
      </form>
    </div>

    <div class="box-shadow rounded p-4">
      <h2 class="text-xl font-bold mb-4">Top Santri Pengambil Sitaan</h2>
      <p class="mb-4">Periode: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?></p>

      <div class="overflow-x-auto">
        <table class="min-w-full border-collapse">
          <thead>
            <tr>
              <th class="px-2 py-2 text-left">Peringkat</th>
              <th class="px-2 py-2 text-left">Nama Santri</th>
              <th class="px-2 py-2 text-left">Kelas</th>
              <th class="px-2 py-2 text-left">Total Pengambilan</th>
              <th class="px-2 py-2 text-left">Total Nominal</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($records)): ?>
              <?php $rank = 1; ?>
              <?php foreach ($records as $record): ?>
                <tr>
                  <td class="px-2 py-2">
                    <?php if ($rank == 1): ?>
                      <i class="fas fa-medal medal-gold"></i> <?= $rank ?>
                    <?php elseif ($rank == 2): ?>
                      <i class="fas fa-medal medal-silver"></i> <?= $rank ?>
                    <?php elseif ($rank == 3): ?>
                      <i class="fas fa-medal medal-bronze"></i> <?= $rank ?>
                    <?php else: ?>
                      <?= $rank ?>
                    <?php endif; ?>
                  </td>
                  <td class="px-2 py-2"><?= htmlspecialchars($record['nama']) ?></td>
                  <td class="px-2 py-2"><?= htmlspecialchars($record['kelas']) ?></td>
                  <td class="px-2 py-2"><?= $record['total_pengambilan'] ?> kali</td>
                  <td class="px-2 py-2">Rp <?= number_format($record['total_nominal'], 0, ',', '.') ?></td>
                </tr>
                <?php $rank++; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="px-2 py-4 text-center">Tidak ada data untuk periode yang dipilih</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      // Auto-hide toast after 5 seconds
      var toastElList = [].slice.call(document.querySelectorAll('.toast'));
      var toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl); // Initialize toast
      });
      toastList.forEach(toast => toast.show()); // Show all toasts

      // Set timeout to hide them after 5 seconds
      setTimeout(function() {
          $('.toast.show').each(function() {
              var toastInstance = bootstrap.Toast.getInstance(this);
              if (toastInstance) {
                  toastInstance.hide();
              }
          });
      }, 5000);
    });
  </script>
</body>
</html>