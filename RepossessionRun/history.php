<?php
if (!defined('INCLUDED_FROM_INDEX')) {
    include_once 'config.php';
}

try {
    // Query to get latest borrowing history
    $sql = "SELECT p.id, p.nama, p.jenis_barang, 
            p.tipe_durasi, p.waktu_mulai, p.durasi, p.status, p.admin,
            p.waktu_peminjaman, p.waktu_pengembalian
            FROM peminjaman p
            ORDER BY p.waktu_peminjaman DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $counter = 1;

    foreach ($result as $row) {
        // Format duration based on type
        $durasiDisplay = '';
        if ($row['tipe_durasi'] == 'jam') {
            $durasiDisplay = $row['durasi'] . ' jam';
            // Also show start and end datetime
            $waktuMulai = new DateTime($row['waktu_mulai']);
            $waktuSelesai = clone $waktuMulai;
            $waktuSelesai->modify('+' . $row['durasi'] . ' hours');
            
            $durasiDisplay .= '<br><small>' . 
                              $waktuMulai->format("d/m/Y H:i") . ' - ' . 
                              $waktuSelesai->format("d/m/Y H:i") . '</small>';
        } else { // per day
            $durasiDisplay = $row['durasi'] . ' hari';
            // Also show start and end date
            $tanggalMulai = new DateTime($row['waktu_mulai']);
            $tanggalSelesai = clone $tanggalMulai;
            $tanggalSelesai->modify('+' . $row['durasi'] . ' days');
            
            $durasiDisplay .= '<br><small>' . 
                              $tanggalMulai->format("d/m/Y") . ' - ' . 
                              $tanggalSelesai->format("d/m/Y") . '</small>';
        }
        
        // Check if item is overdue
        $statusClass = '';
        $statusText = $row['status'];
        $lateMinutes = 0;
        
        if ($statusText == 'Dipinjam') {
            $statusClass = 'status-dipinjam';
            
            // Check if currently overdue
            $now = new DateTime();
            $batasPengembalian = null;
            
            if ($row['tipe_durasi'] == 'jam') {
                $waktuMulai = new DateTime($row['waktu_mulai']);
                $batasPengembalian = clone $waktuMulai;
                $batasPengembalian->modify('+' . $row['durasi'] . ' hours');
            } else {
                $tanggalMulai = new DateTime($row['waktu_mulai']);
                $batasPengembalian = clone $tanggalMulai;
                $batasPengembalian->modify('+' . $row['durasi'] . ' days');
            }
            
            if ($now > $batasPengembalian) {
                $statusText = 'Terlambat';
                $statusClass = 'status-terlambat';
                $lateMinutes = $now->getTimestamp() - $batasPengembalian->getTimestamp();
                $lateMinutes = floor($lateMinutes / 60); // Convert to minutes
            }
        } else if ($statusText == 'Dikembalikan') {
            $statusClass = 'status-dikembalikan';
        }
        
        // Action button based on status
        $actionBtn = '';
        if ($statusText == 'Dipinjam' || $statusText == 'Terlambat') {
            $actionBtn = '<button onclick="kembalikanBarang(' . $row['id'] . ')" class="btn btn-sm btn-inverse">Kembalikan</button>';
        } else {
            $actionBtn = '<span class="text-muted">-</span>';
        }
        
        echo '<tr>
                <td class="px-2 py-2">' . $counter++ . '</td>
                <td class="px-2 py-2">' . htmlspecialchars($row['nama'] ?? '') . '</td>
                <td class="px-2 py-2">' . htmlspecialchars($row['jenis_barang'] ?? '') . '</td>
                <td class="px-2 py-2">' . $durasiDisplay . '</td>
                <td class="px-2 py-2"><span class="' . $statusClass . '">' . $statusText . '</span></td>
                <td class="px-2 py-2">' . $actionBtn . '</td>
                <td class="px-2 py-2">' . ($lateMinutes > 0 ? $lateMinutes . ' menit' : '-') . '</td>
              </tr>';
    }

    // If no records found
    if (count($result) == 0) {
        echo '<tr><td colspan="7" class="text-center py-4">Tidak ada data peminjaman</td></tr>';
    }

} catch (Exception $e) {
    error_log('Error in history.php: ' . $e->getMessage());
    echo '<tr><td colspan="7" class="text-center">Error loading data</td></tr>';
}
?>