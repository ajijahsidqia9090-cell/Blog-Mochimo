<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$id = intval($_GET['id']);
$produk = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk='$id'");
$data = mysqli_fetch_assoc($produk);

if (!$data) { echo "<script>alert('Produk tidak ditemukan');window.location='produk.php';</script>"; exit; }

$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$varian = mysqli_query($conn, "SELECT * FROM varian_produk WHERE id_produk='$id'");
$galeri = mysqli_query($conn, "SELECT * FROM galeri_produk WHERE id_produk='$id'");

if (isset($_POST['update'])) {
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $id_kategori = intval($_POST['id_kategori']);
    $harga = intval($_POST['harga']);
    $stok_utama = intval($_POST['stok_utama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    // Proses Gambar Utama
    $gambar = $data['gambar'];
    if (!empty($_FILES['gambar_utama']['name'])) {
        $gambar = time() . '_utama_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['gambar_utama']['name']);
        if (move_uploaded_file($_FILES['gambar_utama']['tmp_name'], "upload/" . $gambar)) {
            // Optional: hapus gambar lama jika ingin hemat storage
            if (file_exists("upload/".$data['gambar'])) unlink("upload/".$data['gambar']);
        }
    }

    // Update Data Produk
mysqli_query($conn, "UPDATE produk SET nama_produk='$nama_produk', id_kategori='$id_kategori', harga='$harga', stok='$stok_utama', deskripsi='$deskripsi', gambar='$gambar' WHERE id_produk='$id'");
    // Update Varian: Hapus semua varian lama lalu masukkan yang baru
    mysqli_query($conn, "DELETE FROM varian_produk WHERE id_produk='$id'");
    
    if (isset($_POST['stok'])) {
        for ($i = 0; $i < count($_POST['stok']); $i++) {
            $u = mysqli_real_escape_string($conn, $_POST['ukuran'][$i]);
            $w = mysqli_real_escape_string($conn, $_POST['warna'][$i]);
            $s = intval($_POST['stok'][$i]);
            $hv = intval($_POST['harga_varian'][$i]);
            
            // Ambil gambar lama dari input hidden sebagai default
            $g_var = $_POST['gambar_lama'][$i] ?? '';
            
            // Jika ada file baru di upload, proses file tersebut
            if (isset($_FILES['gambar_varian']['name'][$i]) && $_FILES['gambar_varian']['error'][$i] == 0) {
                $namaFile = time() . '_variant_' . $i . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['gambar_varian']['name'][$i]);
                if (move_uploaded_file($_FILES['gambar_varian']['tmp_name'][$i], "upload/" . $namaFile)) {
                    $g_var = $namaFile;
                }
            }
            
            mysqli_query($conn, "INSERT INTO varian_produk(id_produk,ukuran,warna,stok,harga_varian,gambar_varian) VALUES('$id','$u','$w','$s','$hv','$g_var')");
        }
    }
    echo "<script>alert('Produk berhasil diupdate');window.location='produk.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body{
            background:#f5f5f5;
            font-family:'Segoe UI',sans-serif;
        }

        /* NAVBAR */
        .navbar{
            background:linear-gradient(135deg,#ff4d6d,#ff758f);
            padding:15px 25px;
        }

        .navbar-brand{
            font-size:32px;
            font-weight:800;
        }

        .nav-link{
            color:white !important;
            margin-right:10px;
            font-weight:500;
            transition:0.3s;
        }

        .nav-link:hover{
            transform:translateY(-2px);
        }

        .dropdown-menu{
            border:none;
            border-radius:18px;
            padding:10px;
            box-shadow:0 5px 20px rgba(0,0,0,0.08);
        }

        .dropdown-item{
            padding:10px 15px;
            border-radius:12px;
            transition:0.2s;
        }

        .dropdown-item:hover{
            background:#fff0f3;
            color:#ff4d6d;
        }

        /* CONTENT */
        .content{
            padding:30px;
        }

        .topbox{
            background:white;
            border-radius:24px;
            padding:25px;
            margin-bottom:30px;
            box-shadow:0 5px 15px rgba(0,0,0,0.05);
        }

        /* CARD */
        .dashboard-card{
            border:none;
            border-radius:24px;
            color:white;
            padding:25px;
            position:relative;
            overflow:hidden;
            transition:0.3s;
            min-height:160px;
        }

        .dashboard-card:hover{
            transform:translateY(-5px);
        }

        .dashboard-card i{
            position:absolute;
            right:20px;
            bottom:10px;
            font-size:55px;
            opacity:0.2;
        }

        .bg1{ background:linear-gradient(135deg,#667eea,#764ba2); }
        .bg2{ background:linear-gradient(135deg,#43cea2,#185a9d); }
        .bg3{ background:linear-gradient(135deg,#f7971e,#ffd200); }
        .bg4{ background:linear-gradient(135deg,#ff416c,#ff4b2b); }

        /* TABLE */
        .table-box{
            background:white;
            border-radius:24px;
            padding:25px;
            margin-top:30px;
            box-shadow:0 5px 15px rgba(0,0,0,0.05);
        }

        .badge-status{
            padding:8px 14px;
            border-radius:30px;
            font-size:12px;
        }

        .btn-pink { background-color: #ff4d6d; color: white; border: none; }
        .btn-pink:hover { background-color: #ff758f; color: white; }
        .btn-delete { background: #fee2e2; color: #dc3545; border: none; padding: 5px 10px; border-radius: 8px; }
        .btn-delete:hover { background: #dc3545; color: white; }
        .variant-box { background: #f8f9fa; padding: 10px; border-radius: 12px; border: 1px solid #e9ecef; }
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="dashboard.php">MiniShop</a>

        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-folder-fill"></i> Master Data
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="produk.php"><i class="bi bi-box-seam"></i> Produk</a></li>
                        <li><a class="dropdown-item" href="kategori.php"><i class="bi bi-tags-fill"></i> Kategori</a></li>
                        <li><a class="dropdown-item" href="voucher.php"><i class="bi bi-ticket-perforated-fill"></i> Voucher</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="tambah_tracking.php"><i class="bi bi-truck"></i> Update Tracking</a></li>
        
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="pesanan.php"><i class="bi bi-cart-fill"></i> Pesanan</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="pemasukan.php"><i class="bi bi-cash-stack"></i> Pemasukan</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="customer.php"><i class="bi bi-people-fill"></i> Customer</a>
                </li>

                <li class="nav-item ms-lg-3">
                    <a href="logout.php" class="btn btn-light text-danger rounded-pill px-4">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="card shadow-sm border-0 rounded-4 p-4">
        <h3 class="fw-bold mb-4"><i class="bi bi-pencil-square text-danger"></i> Edit Produk</h3>
        <form method="POST" enctype="multipart/form-data">
            
            <div class="row">
                <div class="col-md-12">
                    <label class="fw-bold mb-1">Nama Produk:</label>
                    <input type="text" name="nama_produk" class="form-control mb-3" value="<?= htmlspecialchars($data['nama_produk']) ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="fw-bold mb-1">Kategori:</label>
                    <select name="id_kategori" class="form-select" required>
                        <?php while($k = mysqli_fetch_assoc($kategori)): ?>
                            <option value="<?= $k['id_kategori'] ?>" <?= $k['id_kategori'] == $data['id_kategori'] ? 'selected' : '' ?>><?= $k['nama_kategori'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold mb-1">Harga Dasar:</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="harga" class="form-control" value="<?= $data['harga'] ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold mb-1">Stok Utama:</label>
                    <input type="number" name="stok_utama" class="form-control" value="<?= $data['stok'] ?>" required>
                </div>
            </div>
            
            <label class="fw-bold mb-1">Deskripsi Lengkap:</label>
            <textarea name="deskripsi" class="form-control mb-3" rows="4" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="fw-bold mb-2">Gambar Utama:</label><br>
                    <img src="upload/<?= $data['gambar'] ?>" width="100" class="mb-2 rounded border shadow-sm">
                    <input type="file" name="gambar_utama" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold mb-2">Galeri Foto Detail:</label>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <?php while($g = mysqli_fetch_assoc($galeri)): ?>
                            <div class="position-relative">
                                <img src="upload/<?= $g['nama_file'] ?>" width="80" height="80" style="object-fit:cover;" class="rounded border">
                                <a href="hapus_galeri.php?id=<?= $g['id_galeri'] ?>&id_produk=<?= $id ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="padding: 0px 6px;">×</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <input type="file" name="galeri[]" class="form-control" multiple accept="image/*">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-layers-fill"></i> Varian Produk</h5>
                <button type="button" class="btn btn-dark btn-sm" onclick="tambahVarian()"><i class="bi bi-plus-lg"></i> Tambah Varian</button>
            </div>
            
            <div id="container-varian" class="mb-4">
                <?php while($v = mysqli_fetch_assoc($varian)): ?>
                <div class="variant-box row g-2 align-items-center mb-2">
                    <input type="hidden" name="gambar_lama[]" value="<?= $v['gambar_varian'] ?>">
                    <div class="col"><input type="text" name="ukuran[]" class="form-control form-control-sm" value="<?= $v['ukuran'] ?>" placeholder="Ukuran"></div>
                    <div class="col"><input type="text" name="warna[]" class="form-control form-control-sm" value="<?= $v['warna'] ?>" placeholder="Warna"></div>
                    <div class="col"><input type="number" name="stok[]" class="form-control form-control-sm" value="<?= $v['stok'] ?>" placeholder="Stok"></div>
                    <div class="col"><input type="number" name="harga_varian[]" class="form-control form-control-sm" value="<?= $v['harga_varian'] ?>" placeholder="Harga"></div>
                    <div class="col-md-2">
                        <?php if($v['gambar_varian']): ?><img src="upload/<?= $v['gambar_varian'] ?>" width="35" class="rounded"><?php endif; ?>
                                <input type="file" name="gambar_varian[]" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="col-auto"><button type="button" class="btn-delete" onclick="this.closest('.variant-box').remove()"><i class="bi bi-trash"></i></button></div>
                        </div>
                <?php endwhile; ?>
            </div>

            <div class="row g-2 mt-4 pt-3 border-top">
                <div class="col-md-3">
                    <a href="produk.php" class="btn btn-outline-secondary w-100 py-3 fw-bold">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="col-md-9">
                    <button type="submit" name="update" class="btn btn-pink w-100 py-3 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle"></i> Update Perubahan Produk
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
function tambahVarian(){
    let html = `
    <div class="variant-box row g-2 align-items-center">
        <input type="hidden" name="gambar_lama[]" value="">
        <div class="col"><input type="text" name="ukuran[]" class="form-control" placeholder="Ukuran"></div>
        <div class="col"><input type="text" name="warna[]" class="form-control" placeholder="Warna"></div>
        <div class="col"><input type="number" name="stok[]" class="form-control" placeholder="Stok"></div>
        <div class="col"><input type="number" name="harga_varian[]" class="form-control" placeholder="Harga"></div>
        <div class="col-md-3"><input type="file" name="gambar_varian[]" class="form-control" accept="image/*"></div>
        <div class="col-auto"><button type="button" class="btn-delete" onclick="this.closest('.variant-box').remove()"><i class="bi bi-trash"></i></button></div>
    </div>`;
    document.getElementById('container-varian').insertAdjacentHTML('beforeend', html);
}
</script>

</body>
</html>