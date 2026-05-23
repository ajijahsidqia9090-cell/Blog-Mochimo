<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit; }
include 'koneksi.php';

$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

if(isset($_POST['simpan'])){
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $id_kategori = intval($_POST['id_kategori']);
    $harga = intval($_POST['harga']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    if(empty($_FILES['gambar_utama']['name'])){
        echo "<script>alert('Gambar utama wajib diupload!');window.history.back();</script>"; exit;
    }
    $namaAwalUtama = time().'_utama_'.preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['gambar_utama']['name']);
    move_uploaded_file($_FILES['gambar_utama']['tmp_name'], "upload/".$namaAwalUtama);

    $insert = mysqli_query($conn, "INSERT INTO produk(nama_produk,id_kategori,harga,deskripsi,gambar) VALUES('$nama_produk','$id_kategori','$harga','$deskripsi','$namaAwalUtama')");

    if($insert){
        $id_produk = mysqli_insert_id($conn);
        // Handle Galeri
        if(!empty($_FILES['galeri']['name'][0])){
            foreach($_FILES['galeri']['tmp_name'] as $key => $tmp_name){
                $namaGaleri = time().'_galeri_'.$key.'_'.preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['galeri']['name'][$key]);
                if(move_uploaded_file($tmp_name, "upload/".$namaGaleri)){
                    mysqli_query($conn, "INSERT INTO galeri_produk(id_produk, nama_file) VALUES('$id_produk', '$namaGaleri')");
                }
            }
        }
        // Handle Varian
        if(isset($_POST['stok'])){
            for($i=0; $i<count($_POST['stok']); $i++){
                $u = mysqli_real_escape_string($conn, $_POST['ukuran'][$i]);
                $w = mysqli_real_escape_string($conn, $_POST['warna'][$i]);
                $s = intval($_POST['stok'][$i]);
                $hv = intval($_POST['harga_varian'][$i]);
                $gambarVarian = '';
                if(!empty($_FILES['gambar_varian']['name'][$i])){
                    $namaVarian = time().'_variant_'.$i.'_'.preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['gambar_varian']['name'][$i]);
                    move_uploaded_file($_FILES['gambar_varian']['tmp_name'][$i], "upload/".$namaVarian);
                    $gambarVarian = $namaVarian;
                }
                mysqli_query($conn, "INSERT INTO varian_produk(id_produk,ukuran,warna,stok,harga_varian,gambar_varian) VALUES('$id_produk','$u','$w','$s','$hv','$gambarVarian')");
            }
        }
        echo "<script>alert('Produk berhasil ditambahkan');window.location='produk.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Data Pemasukan</title>

    <!-- Bootstrap -->

    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <!-- Bootstrap Icons -->

    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
            font-size:30px;
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
        }

        .dropdown-item:hover{
            background:#fff0f3;
            color:#ff4d6d;
        }

        /* CARD */

        .card-box{
            border:none;
            border-radius:22px;
            padding:25px;
            color:white;
            box-shadow:0 5px 15px rgba(0,0,0,0.08);
        }

        .bg-total{
            background:linear-gradient(135deg,#ff4d6d,#ff758f);
        }

        .bg-hari{
            background:linear-gradient(135deg,#36cfc9,#5cdbd3);
        }

        .bg-bulan{
            background:linear-gradient(135deg,#722ed1,#9254de);
        }

        /* TABLE */

        .table-card{
            background:white;
            border-radius:22px;
            padding:25px;
            box-shadow:0 5px 15px rgba(0,0,0,0.05);
        }

        .img-bukti{
            width:80px;
            height:80px;
            object-fit:cover;
            border-radius:12px;
            border:1px solid #eee;
        }

        /* FAB TOMBOL BACK (Rumah) */
.fab-dashboard {
    position: fixed; 
    bottom: 30px; 
    right: 30px;
    width: 60px; 
    height: 60px;
    background: #ff4d6d; 
    color: white; 
    border-radius: 50%;
    display: flex; 
    align-items: center; 
    justify-content: center;
    box-shadow: 0 10px 20px rgba(255, 77, 109, 0.4);
    z-index: 9999; 
    text-decoration: none; 
    transition: 0.3s;
}
.fab-dashboard:hover { 
    transform: translateY(-5px); 
    color: white; 
    background: #ff758f;
}

.btn-pink { 
    background: #ff4d6d; 
    color: white; 
    border-radius: 12px; 
    border: none; 
    font-weight: 600; 
}
.btn-pink:hover { 
    background: #ff758f; 
    color: white; 
}
    </style>

</head>
<body>



<a href="dashboard.php" class="fab-dashboard" title="Kembali ke Dashboard">
    <i class="bi bi-house-door-fill" style="font-size: 24px;"></i>
</a>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg navbar-dark shadow">

    <div class="container-fluid">

        <!-- LOGO -->

        <a class="navbar-brand text-white"
           href="dashboard.php">

            Mochimo

        </a>

        <!-- TOGGLER -->

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarAdmin">

            <span class="navbar-toggler-icon"></span>

        </button>

        <!-- MENU -->

        <div class="collapse navbar-collapse"
             id="navbarAdmin">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <!-- Dashboard -->

                <li class="nav-item">

                    <a class="nav-link"
                       href="dashboard.php">

                        <i class="bi bi-grid-fill"></i>
                        Dashboard

                    </a>

                </li>

                <!-- Master Data -->

                <li class="nav-item dropdown">

                    <a class="nav-link dropdown-toggle"
                       data-bs-toggle="dropdown">

                        <i class="bi bi-folder-fill"></i>
                        Master Data

                    </a>

                    <ul class="dropdown-menu">

                        <li>

                            <a class="dropdown-item"
                               href="produk.php">

                                <i class="bi bi-box-seam"></i>
                                Produk

                            </a>

                        </li>

                        <li>

                            <a class="dropdown-item"
                               href="kategori.php">

                                <i class="bi bi-tags-fill"></i>
                                Kategori

                            </a>

                        </li>

                        <li>

                            <a class="dropdown-item"
                               href="voucher.php">

                                <i class="bi bi-ticket-perforated-fill"></i>
                                Voucher

                            </a>

                        </li>

                        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="tambah_tracking.php"><i class="bi bi-truck"></i> Update Tracking</a></li>
        

                    </ul>

                </li>

                <!-- Pesanan -->

                <li class="nav-item">

                    <a class="nav-link"
                       href="pesanan.php">

                        <i class="bi bi-cart-fill"></i>
                        Pesanan

                    </a>

                </li>

                <!-- Pemasukan -->

                <li class="nav-item">

                    <a class="nav-link active"
                       href="pemasukan.php">

                        <i class="bi bi-cash-stack"></i>
                        Pemasukan

                    </a>

                </li>

                <!-- Customer -->

                <li class="nav-item">

                    <a class="nav-link"
                       href="customer.php">

                        <i class="bi bi-people-fill"></i>
                        Customer

                    </a>

                </li>

                <!-- Logout -->

                <li class="nav-item ms-lg-3">

                    <a href="logout.php"
                       class="btn btn-light text-danger px-4 rounded-pill">

                        <i class="bi bi-box-arrow-right"></i>
                        Logout

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>
<div class="container pb-5">
    <div class="main-box">
        <h3 class="fw-bold mb-4"><i class="bi bi-plus-circle-fill text-danger"></i> Tambah Produk Baru</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="section-box">
                <input type="text" name="nama_produk" class="form-control mb-3" placeholder="Nama Produk" required>
                <div class="row g-3">
                    <div class="col-md-6">
                        <select name="id_kategori" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <?php while($k=mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $k['id_kategori']; ?>"><?= $k['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><input type="number" name="harga" class="form-control" placeholder="Harga Dasar" required></div>
                </div>
                <textarea name="deskripsi" class="form-control mt-3" rows="4" placeholder="Tuliskan deskripsi lengkap produk di sini..." required></textarea>
            </div>
            <div class="col-md-4"><input type="number" name="stok_utama" class="form-control" placeholder="Stok Utama" required></div>

            <div class="section-box">
                <label class="fw-bold mb-2">Media Produk:</label>
                <input type="file" name="gambar_utama" class="form-control mb-2" accept="image/*" required>
                <input type="file" name="galeri[]" class="form-control" multiple accept="image/*">
            </div>

            <div class="section-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Varian Produk</h5>
                    <button type="button" class="btn btn-pink" onclick="tambahVarian()"><i class="bi bi-plus-lg"></i> Tambah Baris</button>
                </div>
                <div id="container-varian">
                    <div class="variant-box row g-2 align-items-center">
                        <div class="col"><input type="text" name="ukuran[]" class="form-control" placeholder="Ukuran"></div>
                        <div class="col"><input type="text" name="warna[]" class="form-control" placeholder="Warna"></div>
                        <div class="col"><input type="number" name="stok[]" class="form-control" placeholder="Stok"></div>
                        <div class="col"><input type="number" name="harga_varian[]" class="form-control" placeholder="Harga"></div>
                        <div class="col-md-3"><input type="file" name="gambar_varian[]" class="form-control" accept="image/*"></div>
                        <div class="col-auto"><button type="button" class="btn-delete" onclick="this.closest('.variant-box').remove()"><i class="bi bi-trash"></i></button></div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 mt-4">
                <a href="produk.php" class="btn btn-outline-secondary py-3 flex-fill rounded-pill">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" name="simpan" class="btn btn-pink py-3 flex-fill rounded-pill">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function tambahVarian(){
    let html = `
    <div class="variant-box row g-2 align-items-center">
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