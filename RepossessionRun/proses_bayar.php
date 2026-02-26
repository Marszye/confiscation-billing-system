<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['danger'] = "Metode tidak valid!";
    header("Location: index.php");
    exit;
}

// Ambil data dari form
$nama = trim($_POST['nama'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$jenis_barang = trim($_POST['jenis_barang'] ?? '');
$jumlah_barang = intval($_POST['jumlah_barang'] ?? 0);
$nominal = intval($_POST['nominal_value'] ?? 0);
$pengawas = trim($_POST['pengawas'] ?? '');

// Validasi data
if (empty($nama) || empty($kelas) || empty($jenis_barang) || $jumlah_barang <= 0 || $nominal <= 0 || empty($pengawas)) {
    $_SESSION['danger'] = "Semua field harus diisi dengan benar!";
    header("Location: index.php");
    exit;
}

try {
    // 1. Cari atau buat ID santri
    $sql = "SELECT id FROM santri WHERE nama = :nama LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':nama' => $nama]);
    $santri = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $santri_id = null;
    if ($santri) {
        $santri_id = $santri['id'];
    } else {
        // Jika santri belum ada, tambahkan
        $sql = "INSERT INTO santri (nama, kelas) VALUES (:nama, :kelas)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nama' => $nama,
            ':kelas' => $kelas
        ]);
        $santri_id = $conn->lastInsertId();
    }
    
    // 2. Simpan data sitaan
    $sql = "INSERT INTO sitaan (nama, kelas, jenis_barang, jumlah_barang, nominal, pengawas) 
            VALUES (:nama, :kelas, :jenis_barang, :jumlah_barang, :nominal, :pengawas)";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        ':nama' => $nama,
        ':kelas' => $kelas,
        ':jenis_barang' => $jenis_barang,
        ':jumlah_barang' => $jumlah_barang,
        ':nominal' => $nominal,
        ':pengawas' => $pengawas
    ]);
    
    if ($result) {
        // 3. Update track_sitaan untuk bulan ini
        $bulan = date('n'); // 1-12
        $tahun = date('Y');
        
        $sql = "SELECT id, jumlah_sitaan FROM track_sitaan 
                WHERE santri_id = :santri_id AND bulan = :bulan AND tahun = :tahun LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':santri_id' => $santri_id,
            ':bulan' => $bulan,
            ':tahun' => $tahun
        ]);
        $track = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($track) {
            // Update jumlah sitaan
            $sql = "UPDATE track_sitaan SET jumlah_sitaan = jumlah_sitaan + 1 
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $track['id']]);
        } else {
            // Buat track baru
            $sql = "INSERT INTO track_sitaan (santri_id, bulan, tahun, jumlah_sitaan) 
                    VALUES (:santri_id, :bulan, :tahun, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':santri_id' => $santri_id,
                ':bulan' => $bulan,
                ':tahun' => $tahun
            ]);
        }
        
        $_SESSION['message'] = [
            'type' => 'Sukses',
            'text' => "Pembayaran sitaan berhasil disimpan!"
        ];
    } else {
        throw new Exception("Gagal menyimpan data");
    }
    
} catch (Exception $e) {
    error_log('Error in proses_bayar.php: ' . $e->getMessage());
    $_SESSION['danger'] = "Terjadi kesalahan: " . $e->getMessage();
}

header("Location: index.php");
exit;