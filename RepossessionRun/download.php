<?php
session_start();
include 'config.php'; // Menginclude file konfigurasi database

// (Opsional) Tambahkan pengecekan sesi admin di sini jika diperlukan
/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Akses ditolak. Silakan login sebagai admin.");
}
*/

try {
    // 1. Ambil semua data dari tabel sitaan, diurutkan berdasarkan waktu terbaru
    $sql = "SELECT id, nama, kelas, jenis_barang, jumlah_barang, waktu_sitaan, nominal, pengawas
            FROM sitaan
            ORDER BY waktu_sitaan DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Siapkan nama file CSV
    $filename = "ReposessionRun_History_" . date('Y-m-d_H-i-s') . ".csv";

    // 3. Set header HTTP untuk memicu download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // 4. Buka output stream PHP
    $output = fopen('php://output', 'w');

    // 5. Tulis header kolom ke file CSV
    fputcsv($output, [
        'No',
        'Nama Santri',
        'Kelas',
        'Jenis Barang',
        'Jumlah',
        'Waktu Sitaan (WIB)',
        'Nominal (Rp)',
        'Pengawas'
    ]);

    // 6. Tulis data ke file CSV
    $counter = 1;
    if (count($results) > 0) {
        foreach ($results as $row) {
            // Format waktu
            $waktuSitaan = new DateTime($row['waktu_sitaan']);
            // Menggunakan zona waktu dari config.php (Asia/Jakarta)
            $waktuDisplay = $waktuSitaan->format("d/m/Y H:i:s"); 

            fputcsv($output, [
                $counter++,
                $row['nama'],
                $row['kelas'],
                $row['jenis_barang'],
                $row['jumlah_barang'],
                $waktuDisplay,
                $row['nominal'], // Tulis nominal sebagai angka saja di CSV
                $row['pengawas']
            ]);
        }
    } else {
        // Jika tidak ada data, tulis pesan
         fputcsv($output, ['Tidak ada data history.']);
    }

    // 7. Tutup output stream
    fclose($output);
    exit; // Pastikan tidak ada output lain setelah file CSV

} catch (PDOException $e) {
    // Tangani error jika query gagal
    // Anda bisa mencatat error atau menampilkan pesan
    error_log("Error downloading history: " . $e->getMessage());
    die("Gagal mengunduh data history. Silakan coba lagi nanti.");
} catch (Exception $e) {
    error_log("General error in download.php: " . $e->getMessage());
    die("Terjadi kesalahan sistem.");
}

?>