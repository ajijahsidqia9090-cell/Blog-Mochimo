<?php
include 'koneksi.php';

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: pesanan.php");
    exit;
}

$id_pesanan = mysqli_real_escape_string($conn, $_GET['id']);

/* | 1. Ambil Data Utama Pesanan & User 
   | PERBAIKAN: Mengubah nama tabel menjadi 'users' dan mengambil kolom 'nama'
*/
$query_p = mysqli_query($conn, "
    SELECT p.*, u.nama 
    FROM pesanan p 
    LEFT JOIN users u ON p.id_user = u.id_user 
    WHERE p.id_pesanan = '$id_pesanan'
");
$p = mysqli_fetch_assoc($query_p);

// Jika data pesanan tidak ditemukan di database
if (!$p) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location='pesanan.php';</script>";
    exit;
}

/* | 2. Ambil Rincian Produk yang dibeli
*/
$query_d = mysqli_query($conn, "
    SELECT dp.*, pr.nama_produk, pr.foto 
    FROM detail_pesanan dp 
    JOIN produk pr ON dp.id_produk = pr.id_produk 
    WHERE dp.id_pesanan = '$id_pesanan'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi #<?= htmlspecialchars($id_pesanan); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .img-produk { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; }
        .border-dashed { border-style: dashed !important; }
        .rating-box { background: #fff9e6; border: 1px solid #ffeeba; border-radius: 10px; padding: 15px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Detail Pesanan</h2>
            <p class="text-muted mb-0">ID Transaksi: #<?= htmlspecialchars($p['id_pesanan']); ?> | <?= htmlspecialchars($p['tanggal_pesan']); ?></p>
        </div>
        <a href="pesanan.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-4">
                <h5 class="fw-bold mb-4">Produk yang Dipesan</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = mysqli_fetch_assoc($query_d)) : ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../assets/img/<?= htmlspecialchars($item['foto']); ?>" class="img-produk me-3" onerror="this.src='../assets/img/no-image.png';">
                                        <span class="fw-bold"><?= htmlspecialchars($item['nama_produk']); ?></span>
                                    </div>
                                </td>
                                <td>Rp <?= number_format($item['harga']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($item['qty']); ?></td>
                                <td class="text-end fw-bold text-dark">Rp <?= number_format($item['subtotal']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end border-0 pt-4 text-muted">Total Bayar:</td>
                                <td class="text-end border-0 pt-4 fw-bold fs-4 text-danger">
                                    Rp <?= number_format($p['total_bayar']); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt text-danger"></i> Alamat Tujuan</h5>
                <p class="mb-1 fw-bold"><?= htmlspecialchars($p['nama'] ?? 'User #'.$p['id_user']); ?></p>
                <p class="text-muted small mb-0"><?= htmlspecialchars($p['alamat_pengiriman']); ?></p>
                <hr class="border-dashed">
                <small class="text-muted d-block mb-1">Metode Pembayaran:</small>
                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['metode_pembayaran']); ?></span>
            </div>

            <div class="card p-4">
                <h5 class="fw-bold mb-3">Status Pengiriman</h5>
                <?php 
                    $status = strtolower(trim($p['status']));
                    $status_class = "bg-info";
                    
                    if($status == 'selesai') {
                        $status_class = "bg-success";
                    } elseif($status == 'diproses') {
                        $status_class = "bg-warning text-dark";
                    } elseif($status == 'dikirim') {
                        $status_class = "bg-primary";
                    } elseif($status == 'dibatalkan') {
                        $status_class = "bg-danger";
                    }
                ?>
                <div class="badge <?= $status_class; ?> py-2 px-3 fs-6 mb-4 w-100"><?= ucfirst($status); ?></div>

                <?php if($status == 'selesai') : ?>
                    <div class="rating-box">
                        <label class="small text-muted d-block mb-1">Kurir Pengantar:</label>
                        <p class="fw-bold mb-3"><i class="bi bi-person-badge"></i> <?= htmlspecialchars($p['nama_kurir'] ?? 'Kurir Internal'); ?></p>
                        
                        <label class="small text-muted d-block mb-1">Rating dari Pelanggan:</label>
                        <div class="text-warning fs-5">
                            <?php 
                            if(isset($p['rating']) && $p['rating'] > 0){
                                for($i=1; $i<=$p['rating']; $i++) echo '<i class="bi bi-star-fill"></i>';
                                echo " <span class='text-dark small'>(" . htmlspecialchars($p['rating']) . "/5)</span>";
                            } else {
                                echo '<span class="text-muted small italic">Belum dinilai</span>';
                            }
                            ?>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="text-center p-3 border rounded bg-light">
                        <i class="bi bi-truck fs-1 text-muted"></i>
                        <p class="small text-muted mb-0 mt-2">Nama kurir dan ulasan akan tampil setelah pesanan diselesaikan pelanggan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>