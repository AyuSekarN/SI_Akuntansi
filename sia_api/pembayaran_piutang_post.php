<?php

// Header untuk mengizinkan akses dan mengatur format respons
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With");

// Menghubungkan ke database
include 'db_connect.php'; // File ini harus berisi koneksi ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Ambil data JSON dari body request
        $inputData = json_decode(file_get_contents("php://input"), true);

        // Validasi input
        $kode_akun = $inputData['kode_akun'] ?? null;
        $id_pembayaranpiutang = $inputData['id_pembayaranpiutang'] ?? null;
        $id_penjualan = $inputData['id_penjualan'] ?? null;
        $tanggal_pembayaran = $inputData['tanggal_pembayaran'] ?? null;
        $jumlah_piutang = $inputData['jumlah_piutang'] ?? null;
        $jumlah_pembayaran = $inputData['jumlah_pembayaran'] ?? null;

        // Cek jika parameter wajib tidak ada
        if (!$kode_akun || !$id_pembayaranpiutang || !$id_penjualan || !$tanggal_pembayaran || !$jumlah_piutang || !$jumlah_pembayaran) {
            echo json_encode([
                "status" => "error",
                "message" => "Semua parameter wajib diisi. Pastikan semua data telah dikirim."
            ]);
            exit;
        }

        // Validasi tambahan (contoh: jumlah_pembayaran tidak boleh lebih besar dari jumlah_piutang)
        if ($jumlah_pembayaran > $jumlah_piutang) {
            echo json_encode([
                "status" => "error",
                "message" => "Jumlah pembayaran tidak boleh lebih besar dari jumlah piutang."
            ]);
            exit;
        }

        // Menyimpan data ke database
        $query = "INSERT INTO pembayaran_piutang (kode_akun, id_pembayaranpiutang, id_penjualan, tanggal_pembayaran, jumlah_piutang, jumlah_pembayaran)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal menyiapkan statement: " . $conn->error
            ]);
            exit;
        }

        // Bind parameter
        $stmt->bind_param("ssssss", $kode_akun, $id_pembayaranpiutang, $id_penjualan, $tanggal_pembayaran, $jumlah_piutang, $jumlah_pembayaran);

        // Eksekusi query
        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil disimpan ke database."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal menyimpan data: " . $stmt->error
            ]);
        }

        // Menutup statement dan koneksi
        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Terjadi kesalahan: " . $e->getMessage()
        ]);
    }
} else {
    // Menangani metode HTTP selain POST
    echo json_encode([
        "status" => "error",
        "message" => "Metode HTTP tidak valid. Gunakan POST untuk mengirim data."
    ]);
}

?>
