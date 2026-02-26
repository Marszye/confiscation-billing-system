<?php
session_start();

// 检查是否有访问权限
if (!isset($_SESSION['access_code']) || $_SESSION['access_code'] !== 'kormaapps') {
    header('Location: access.php');
    exit;
}

include 'config.php'; // PDO Connection

// --- LOGIKA UNTUK BARANG (ITEM MANAGEMENT) ---
// Check if form submitted to add new item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_item') {
    try {
        $nama_barang = $_POST['nama_barang'];
        $stok = $_POST['stok'];

        $sql = "INSERT INTO barang_list (nama_barang, stok)
                VALUES (:nama_barang, :stok)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':stok', $stok);
        $stmt->execute();

        $_SESSION['message'] = [
            'type' => 'Success',
            'text' => 'Barang berhasil ditambahkan'
        ];
    } catch (Exception $e) {
        $_SESSION['danger'] = 'Error Barang: ' . $e->getMessage();
    }
    header('Location: admin.php');
    exit;
}

// Check if delete item request
if (isset($_GET['delete_item']) && is_numeric($_GET['delete_item'])) {
    try {
        $id = $_GET['delete_item'];

        // Check if item is currently borrowed
        $check_sql = "SELECT COUNT(*) FROM peminjaman WHERE jenis_barang = (
                      SELECT nama_barang FROM barang_list WHERE id = :id) AND status = 'Dipinjam'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':id', $id);
        $check_stmt->execute();

        if ($check_stmt->fetchColumn() > 0) {
            $_SESSION['danger'] = 'Tidak dapat menghapus barang yang sedang dipinjam';
        } else {
            $sql = "DELETE FROM barang_list WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $_SESSION['message'] = [
                'type' => 'Success',
                'text' => 'Barang berhasil dihapus'
            ];
        }
    } catch (Exception $e) {
        $_SESSION['danger'] = 'Error Barang: ' . $e->getMessage();
    }
    header('Location: admin.php');
    exit;
}

// Check if update item request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_item') {
    try {
        $id = $_POST['id'];
        $nama_barang = $_POST['nama_barang'];
        $stok = $_POST['stok'];

        $sql = "UPDATE barang_list SET nama_barang = :nama_barang,
                stok = :stok WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':stok', $stok);
        $stmt->execute();

        $_SESSION['message'] = [
            'type' => 'Success',
            'text' => 'Barang berhasil diupdate'
        ];
    } catch (Exception $e) {
        $_SESSION['danger'] = 'Error Barang: ' . $e->getMessage();
    }
    header('Location: admin.php');
    exit;
}

// --- LOGIKA UNTUK ADMIN (ADMIN MANAGEMENT) ---
// Check if form submitted to add new admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_admin') {
    try {
        $nama_admin = $_POST['nama_admin'];

        // Optional: Tambahkan validasi jika nama admin sudah ada
        $check_sql = "SELECT COUNT(*) FROM admin_list WHERE nama_admin = :nama_admin";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':nama_admin', $nama_admin);
        $check_stmt->execute();

        if ($check_stmt->fetchColumn() > 0) {
             $_SESSION['danger'] = 'Nama admin sudah ada.';
        } else {
            $sql = "INSERT INTO admin_list (nama_admin) VALUES (:nama_admin)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nama_admin', $nama_admin);
            $stmt->execute();

            $_SESSION['message'] = [
                'type' => 'Success',
                'text' => 'Admin berhasil ditambahkan'
            ];
        }
    } catch (Exception $e) {
        $_SESSION['danger'] = 'Error Admin: ' . $e->getMessage();
    }
    header('Location: admin.php');
    exit;
}

// Check if delete admin request
if (isset($_GET['delete_admin']) && is_numeric($_GET['delete_admin'])) {
    try {
        $id = $_GET['delete_admin'];

        // Optional: Tambahkan pengecekan apakah admin terkait dengan peminjaman
        // $check_usage_sql = "SELECT COUNT(*) FROM peminjaman WHERE admin = (SELECT nama_admin FROM admin_list WHERE id = :id)";
        // ... (lakukan pengecekan jika diperlukan)

        $sql = "DELETE FROM admin_list WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $_SESSION['message'] = [
            'type' => 'Success',
            'text' => 'Admin berhasil dihapus'
        ];
    } catch (Exception $e) {
        $_SESSION['danger'] = 'Error Admin: ' . $e->getMessage();
    }
    header('Location: admin.php');
    exit;
}

// Check if update admin request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_admin') {
    try {
        $id = $_POST['admin_id']; // Sesuaikan nama input di modal edit admin
        $nama_admin = $_POST['nama_admin']; // Sesuaikan nama input di modal edit admin

        // Optional: Tambahkan validasi jika nama admin baru sudah ada (kecuali untuk ID yang sama)
        $check_sql = "SELECT COUNT(*) FROM admin_list WHERE nama_admin = :nama_admin AND id != :id";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':nama_admin', $nama_admin);
        $check_stmt->bindParam(':id', $id);
        $check_stmt->execute();

        if ($check_stmt->fetchColumn() > 0) {
             $_SESSION['danger'] = 'Nama admin sudah digunakan oleh admin lain.';
        } else {
            $sql = "UPDATE admin_list SET nama_admin = :nama_admin WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nama_admin', $nama_admin);
            $stmt->execute();

            $_SESSION['message'] = [
                'type' => 'Success',
                'text' => 'Admin berhasil diupdate'
            ];
        }
    } catch (Exception $e) {
        $_SESSION['danger'] = 'Error Admin: ' . $e->getMessage();
    }
    header('Location: admin.php');
    exit;
}

// --- FETCH DATA ---
// Get all items (Newest first)
$sql_items = "SELECT id, nama_barang, stok FROM barang_list ORDER BY id DESC"; // <-- Urutkan ID terbaru dulu
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute();
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Get all admins (Newest first)
$sql_admins = "SELECT id, nama_admin FROM admin_list ORDER BY id DESC"; // <-- Urutkan ID terbaru dulu
$stmt_admins = $conn->prepare($sql_admins);
$stmt_admins->execute();
$admins = $stmt_admins->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ZyeLends - Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            pastel: {
              pink: '#FFC0CB',
              blue: '#ADD8E6',
              green: '#90EE90',
              yellow: '#FFFF99',
              purple: '#E6E6FA',
            },
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
      background-color: #FFFFFF; /* white */
      color: #333333; /* dark grey */
    }
    /* Header and navigation */
    .header { background-color: #ADD8E6; color: #333333; }
    .header-title { font-size: 1.25rem; font-weight: 600; }
    .header-subtitle { font-size: 0.75rem; }
    .header-nav a { background-color: #90EE90; color: #333333; border: 1px solid #90EE90; transition: all 0.2s ease; }
    .header-nav a:hover { background-color: #66CD00; border-color: #66CD00; }
    /* Buttons */
    .btn-inverse { background-color: #FFFF99; color: #333333; border: 1px solid #FFD700; transition: all 0.3s ease; }
    .btn-inverse:hover { background-color: #FFD700; color: #333333; border-color: #FFD700; }
    .btn-primary-pastel { background-color: #FFC0CB; color: #333333; border: 1px solid #FFC0CB; }
    /* Modal */
    .modal-header-pastel { background-color: #FFC0CB; color: #333333; border-bottom: 1px solid #FFC0CB; }
    /* Box styling */
    .box-shadow { background-color: #FFFFFF; border: 1px solid #E6E6FA; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    /* Table */
    table.min-w-full { border: 1px solid #E6E6FA; }
    table.min-w-full th { background-color: #E6E6FA; color: #333333; border: 1px solid #E6E6FA; padding: 8px; }
    table.min-w-full td { border: 1px solid #E6E6FA; padding: 8px; }
    /* Fixed header table with scroll */
    .fixed-header-table {
      max-height: 300px; /* Atur tinggi maksimal area tabel */
      overflow-y: auto; /* Tambahkan scroll vertikal jika perlu */
      position: relative; /* Diperlukan untuk sticky header */
    }
    .fixed-header-table thead th {
      position: sticky; /* Buat header tabel tetap di atas saat scroll */
      top: 0;
      z-index: 1;
      background-color: #E6E6FA; /* Pastikan background header konsisten */
    }
  </style>
</head>
<body>
  <?php if (isset($_SESSION['danger'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
      <div class="toast show bg-danger text-white" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
          <strong class="me-auto">Error</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"> <?= htmlspecialchars($_SESSION['danger']) ?> </div>
      </div>
    </div>
    <?php unset($_SESSION['danger']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
      <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
          <strong class="me-auto"><?= htmlspecialchars($_SESSION['message']['type']) ?></strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"> <?= htmlspecialchars($_SESSION['message']['text']) ?> </div>
      </div>
    </div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <header class="header px-4 py-2 flex justify-between items-center">
    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px;">
      <h1 style="font-size: 2rem; font-weight: bold;">ZYELENDS</h1>
      <p style="font-size: 0.5rem;">V1.0 - ADMIN PANEL</p>
    </div>
    <nav class="header-nav space-x-2">
      <a href="index.php" class="px-3 py-1 rounded">KEMBALI KE BERANDA</a>
    </nav>
  </header>

  <main class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-4 text-pastel-pink">Manajemen Barang</h2>
    <div class="flex flex-col md:flex-row gap-6 mb-8">
      <div class="w-full md:w-1/3">
        <div class="box-shadow rounded p-4 h-full">
          <h3 class="text-xl font-bold mb-4">Tambah Barang Baru</h3>
          <form id="addItemForm" method="POST" action="admin.php">
            <input type="hidden" name="action" value="add_item">
            <div class="mb-3">
              <label class="block mb-1">Nama Barang</label>
              <input type="text" name="nama_barang" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-3">
              <label class="block mb-1">Stok Tersedia</label>
              <input type="number" name="stok" class="w-full p-2 border rounded" min="0" value="1" required> </div>
            <button type="submit" class="w-full py-2 rounded btn-primary-pastel">
              TAMBAH BARANG
            </button>
          </form>
        </div>
      </div>
      <div class="w-full md:w-2/3">
        <div class="box-shadow rounded p-4 h-full">
          <h3 class="text-xl font-bold mb-4">Daftar Barang</h3>
          <div class="fixed-header-table"> <table class="min-w-full border-collapse">
              <thead>
                <tr>
                  <th class="px-2 py-2">ID</th>
                  <th class="px-2 py-2">Nama Barang</th>
                  <th class="px-2 py-2">Stok</th>
                  <th class="px-2 py-2">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                  <td class="px-2 py-2"><?= $item['id'] ?></td>
                  <td class="px-2 py-2"><?= htmlspecialchars($item['nama_barang']) ?></td>
                  <td class="px-2 py-2"><?= $item['stok'] ?></td>
                  <td class="px-2 py-2">
                    <button class="btn btn-sm btn-inverse me-1" onclick="editItem(<?= $item['id'] ?>, '<?= addslashes(htmlspecialchars($item['nama_barang'])) ?>', <?= $item['stok'] ?>)">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <a href="admin.php?delete_item=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus barang ini?')">
                      <i class="fas fa-trash"></i> Hapus
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                <tr> <td colspan="4" class="text-center py-4">Tidak ada data barang</td> </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <hr class="my-8 border-pastel-blue">

    <h2 class="text-2xl font-bold mb-4 text-pastel-pink">Manajemen Admin</h2>
     <div class="flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-1/3">
            <div class="box-shadow rounded p-4 h-full">
                <h3 class="text-xl font-bold mb-4">Tambah Admin Baru</h3>
                <form id="addAdminForm" method="POST" action="admin.php">
                    <input type="hidden" name="action" value="add_admin">
                    <div class="mb-3">
                        <label class="block mb-1">Nama Admin</label>
                        <input type="text" name="nama_admin" class="w-full p-2 border rounded" required>
                    </div>
                    <button type="submit" class="w-full py-2 rounded btn-primary-pastel">
                        TAMBAH ADMIN
                    </button>
                </form>
            </div>
        </div>
        <div class="w-full md:w-2/3">
            <div class="box-shadow rounded p-4 h-full">
                <h3 class="text-xl font-bold mb-4">Daftar Admin</h3>
                 <div class="fixed-header-table"> <table class="min-w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Nama Admin</th>
                                <th class="px-2 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td class="px-2 py-2"><?= $admin['id'] ?></td>
                                <td class="px-2 py-2"><?= htmlspecialchars($admin['nama_admin']) ?></td>
                                <td class="px-2 py-2">
                                    <button class="btn btn-sm btn-inverse me-1" onclick="editAdmin(<?= $admin['id'] ?>, '<?= addslashes(htmlspecialchars($admin['nama_admin'])) ?>')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="admin.php?delete_admin=<?= $admin['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus admin ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                             <?php if (empty($admins)): ?>
                            <tr> <td colspan="3" class="text-center py-4">Tidak ada data admin</td> </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

  </main>

  <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header modal-header-pastel">
          <h5 class="modal-title" id="editItemModalLabel">Edit Barang</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editItemFormModal" method="POST" action="admin.php"> <div class="modal-body">
              <input type="hidden" name="action" value="update_item">
              <input type="hidden" name="id" id="edit_item_id"> <div class="mb-3">
                <label class="block mb-1">Nama Barang</label>
                <input type="text" name="nama_barang" id="edit_nama_barang" class="w-full p-2 border rounded" required>
              </div>
              <div class="mb-3">
                <label class="block mb-1">Stok Tersedia</label>
                <input type="number" name="stok" id="edit_stok" class="w-full p-2 border rounded" min="0" required> </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" style="background-color: #FFC0CB; border-color: #FFC0CB;">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header modal-header-pastel">
          <h5 class="modal-title" id="editAdminModalLabel">Edit Admin</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
         <form id="editAdminFormModal" method="POST" action="admin.php"> <div class="modal-body">
                <input type="hidden" name="action" value="update_admin">
                <input type="hidden" name="admin_id" id="edit_admin_id"> <div class="mb-3">
                    <label class="block mb-1">Nama Admin</label>
                    <input type="text" name="nama_admin" id="edit_nama_admin" class="w-full p-2 border rounded" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" style="background-color: #FFC0CB; border-color: #FFC0CB;">Simpan Perubahan</button>
            </div>
        </form>
      </div>
    </div>
  </div>


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

  <script>
    // Initialize toasts
    document.addEventListener('DOMContentLoaded', function() {
      var toastElList = [].slice.call(document.querySelectorAll('.toast'));
      var toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
      });
      // Activate all toasts
      toastList.forEach(toast => toast.show());
    });

    // Function to open edit item modal with item data
    function editItem(id, nama_barang, stok) {
      document.getElementById('edit_item_id').value = id; // Target ID unik
      document.getElementById('edit_nama_barang').value = nama_barang;
      document.getElementById('edit_stok').value = stok;
      const modal = new bootstrap.Modal(document.getElementById('editItemModal'));
      modal.show();
    }

     // Function to open edit admin modal with admin data
    function editAdmin(id, nama_admin) {
        document.getElementById('edit_admin_id').value = id; // Target ID unik
        document.getElementById('edit_nama_admin').value = nama_admin;
        const modal = new bootstrap.Modal(document.getElementById('editAdminModal'));
        modal.show();
    }
  </script>
</body>
</html>