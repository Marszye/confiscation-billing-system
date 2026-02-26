<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'])) {
    // Mendapatkan kelas berdasarkan nama
    $nama = trim($_POST['nama']);
    try {
        $sql = "SELECT kelas FROM santri WHERE nama = :nama LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':nama' => $nama]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo $result['kelas'];
        } else {
            echo '';
        }
    } catch (Exception $e) {
        error_log('Error in get_kelas.php: ' . $e->getMessage());
        echo '';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_namelist'])) {
    // Mendapatkan daftar nama santri untuk dropdown
    try {
        $sql = "SELECT nama FROM santri ORDER BY nama";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $studentList = [];
        foreach ($students as $student) {
            $studentList[] = $student['nama'];
        }
        
        echo json_encode($studentList);
    } catch (Exception $e) {
        error_log('Error fetching student list: ' . $e->getMessage());
        echo json_encode([]);
    }
} else {
    echo '';
}