<?php 
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/koneksi.php';
include '../template/header.php';
include '../template/navbar_customer.php';

$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$keyword  = isset($_GET['cari']) ? trim($_GET['cari']) : '';

/* QUERY PRODUK */
$sql = "SELECT produk.*, kategori.nama_kategori FROM produk LEFT JOIN kategori ON produk.id_kategori = kategori.id_kategori WHERE 1=1";
if (!empty($kategori)) { $sql .= " AND kategori.nama_kategori = '".mysqli_real_escape_string($conn, $kategori)."'"; }
if (!empty($keyword)) { $sql .= " AND (produk.nama_produk LIKE '%".mysqli_real_escape_string($conn, $keyword)."%' OR produk.deskripsi LIKE '%".mysqli_real_escape_string($conn, $keyword)."%' OR kategori.nama_kategori LIKE '%".mysqli_real_escape_string($conn, $keyword)."%')"; }
$sql .= " ORDER BY produk.id_produk DESC";
$produk = mysqli_query($conn, $sql);

/* QUERY KATEGORI */
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

/* QUERY VOUCHER */
$voucher = mysqli_query($conn, "SELECT * FROM voucher WHERE status='aktif' ORDER BY id_voucher DESC LIMIT 1");
$v = mysqli_fetch_assoc($voucher);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
    body{ background:#f5f5f5; }
    .main-slider img{ height:260px; object-fit:cover; }
    .product-card{ border:none; border-radius:18px; overflow:hidden; transition:0.25s; background:white; box-shadow:0 2px 10px rgba(0,0,0,0.05); height:100%; }
    .product-card:hover{ transform:translateY(-4px); }
    .product-img{ height:220px; width:100%; object-fit:cover; background:#fff; }
    .discount-badge{ position:absolute; top:10px; left:10px; background:#ff4d6d; color:white; padding:5px 10px; border-radius:30px; font-size:11px; font-weight:600; z-index:2; }
    .kategori-wrapper{ background:white; border-radius:18px; overflow:hidden; border:1px solid #eee; box-shadow:0 2px 10px rgba(0,0,0,0.04); }
    .kategori-title{ padding:18px 20px; border-bottom:1px solid #f1f1f1; font-size:26px; font-weight:700; color:#444; }
    .kategori-item{ display:block; text-decoration:none; color:#333; background:white; height:100%; transition:0.2s; border-right:1px solid #f1f1f1; border-bottom:1px solid #f1f1f1; padding:18px 10px; }
    .kategori-item:hover{ transform:translateY(-3px); background:#fff7f8; }
    .kategori-img-wrap{ width:90px; height:90px; margin:auto; border-radius:50%; background:#fafafa; display:flex; align-items:center; justify-content:center; overflow:hidden; transition:0.2s; }
    .kategori-item:hover .kategori-img-wrap{ transform:scale(1.06); background:#fff0f3; }
    .kategori-img{ width:58px; height:58px; object-fit:contain; }
    .kategori-text{ margin-top:12px; font-size:14px; font-weight:600; line-height:1.3; min-height:38px; color:#444; }
    .voucher-box{ background:white; border-radius:18px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
    .flash-banner{ background:linear-gradient(135deg,#ff5f6d,#ff8fa3); border-radius:20px; padding:25px; color:white; }
</style>

<div class="container py-4">
    <div id="homeSlider" class="carousel slide main-slider mb-4 rounded-4 overflow-hidden shadow-sm" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active"><img src="../admin/upload/cute.png" class="d-block w-100"></div>
            <div class="carousel-item"><img src="../admin/upload/tls.png" class="d-block w-100"></div>
            <div class="carousel-item"><img src="../admin/upload/mkn.png" class="d-block w-100"></div>
        </div>
    </div>

    <div class="flash-banner mb-4">
        <h3 class="fw-bold mb-2">✨ Flash Sale Weekend</h3>
        <p class="mb-0">Diskon sampai 50% + Gratis Ongkir</p>
    </div>

    <?php if($v) { ?>
    <div class="voucher-box mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 d-flex align-items-center justify-content-center" style="width:55px; height:55px; border-radius:15px; background:#fff1f4; color:#ff4d6d; font-size:24px;">🎟</div>
            <div>
                <h6 class="fw-bold text-danger mb-1"><?= $v['kode_voucher']; ?></h6>
                <small class="text-muted">Diskon <?= $v['diskon']; ?>% minimal belanja Rp <?= number_format($v['minimal_belanja']); ?></small>
            </div>
        </div>
    </div>
    <?php } ?>

    <div class="kategori-wrapper mb-5">
        <div class="kategori-title">KATEGORI</div>
        <div class="row g-0 text-center">
        <?php 
        $limit = 5; $no = 0;
        while($k = mysqli_fetch_assoc($kategori_list)) { 
            if($no >= $limit) break;

            // LOGIKA GAMBAR: Mengambil dari kolom 'ikon' di database
            if (!empty($k['ikon'])) {
                $gambar = "../admin/upload/" . $k['ikon'];
            } else {
                // FALLBACK: Ikon Otomatis jika kolom 'ikon' kosong
                $namaKategori = strtolower(trim($k['nama_kategori']));
                $gambar = "https://cdn-icons-png.flaticon.com/512/2331/2331970.png";
                if(strpos($namaKategori,'elektronik') !== false || strpos($namaKategori,'gadget') !== false) $gambar = "https://cdn-icons-png.flaticon.com/512/3659/3659898.png";
                elseif(strpos($namaKategori,'komputer') !== false || strpos($namaKategori,'laptop') !== false) $gambar = "https://cdn-icons-png.flaticon.com/512/5977/5977575.png";
                elseif(strpos($namaKategori,'makanan') !== false || strpos($namaKategori,'snack') !== false) $gambar = "https://cdn-icons-png.flaticon.com/512/1046/1046784.png";
            }
        ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="dashboard.php?kategori=<?= urlencode($k['nama_kategori']); ?>" class="kategori-item">
                    <div class="kategori-img-wrap">
                        <img src="<?= $gambar; ?>" class="kategori-img" onerror="this.src='../assets/img/no-image.png'">
                    </div>
                    <div class="kategori-text"><?= $k['nama_kategori']; ?></div>
                </a>
            </div>
        <?php $no++; } ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="semua_kategori.php" class="kategori-item">
                <div class="kategori-img-wrap"><img src="https://cdn-icons-png.flaticon.com/512/3524/3524388.png" class="kategori-img"></div>
                <div class="kategori-text">Semua Kategori</div>
            </a>
        </div>
        </div>
    </div>

    <div class="row g-4">
        <?php if(mysqli_num_rows($produk) > 0){ ?>
        <?php while($p = mysqli_fetch_assoc($produk)) { ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card product-card position-relative">
                <div class="discount-badge">15% OFF</div>
                <a href="detail_produk.php?id=<?= $p['id_produk']; ?>"><img src="../admin/upload/<?= $p['gambar']; ?>" class="product-img" onerror="this.src='../assets/img/no-image.png'"></a>
                <div class="card-body">
                    <h6 class="fw-semibold mb-2"><?= $p['nama_produk']; ?></h6>
                    <small class="badge bg-light text-dark border"><?= $p['nama_kategori']; ?></small>
                    <div class="text-danger fw-bold fs-5 mt-2">Rp <?= number_format($p['harga']); ?></div>
                    <small class="text-muted d-block mt-1">Ukuran : <?= !empty($p['ukuran']) ? str_replace(',', ', ', $p['ukuran']) : "All Size"; ?></small>
                    <a href="detail_produk.php?id=<?= $p['id_produk']; ?>" class="btn btn-outline-danger btn-sm w-100 mt-3 rounded-pill">Lihat Produk</a>
                </div>
            </div>
        </div>
        <?php } } else { echo '<div class="col-12"><div class="alert alert-light text-center border">Produk tidak ditemukan</div></div>'; } ?>
    </div>
</div>

<?php include '../template/footer.php'; ?>