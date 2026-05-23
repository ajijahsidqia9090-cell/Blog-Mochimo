<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* =========================================================
   VALIDASI CHECKOUT
========================================================= */
if(!isset($_POST['checkout']) || empty($_POST['checkout'])){
    echo "
    <script>
    alert('Pilih produk dulu!');
    window.location='cart.php';
    </script>
    ";
    exit;
}

/* =========================================================
   INPUT & SANITASI
========================================================= */
$checkout_ids = $_POST['checkout'] ?? [];

$alamat_final = mysqli_real_escape_string($conn, trim($_POST['alamat_final'] ?? ''));
$metode_bayar = strtoupper(trim($_POST['metode_pembayaran'] ?? 'COD'));
$bank         = strtoupper(mysqli_real_escape_string($conn, trim($_POST['nama_bank'] ?? '')));

$diskon       = intval($_POST['diskon'] ?? 0);
$total_akhir  = intval($_POST['total_akhir'] ?? 0);

if(empty($alamat_final)){
    die("Alamat pengiriman wajib diisi");
}
if(empty($metode_bayar)){
    die("Metode pembayaran wajib dipilih");
}
if($metode_bayar == 'BANK' && empty($bank)){
    die("Silakan pilih bank tujuan");
}

/* =========================================================
   TAHAPAN 1: VALIDASI SEMUA ITEM & VARIAN SEBELUM INSERT PESANAN
========================================================= */
$items_to_process = [];

foreach($checkout_ids as $id_cart){
    $id_cart = intval($id_cart);

    $q = mysqli_query($conn,"
        SELECT c.*, p.nama_produk, p.harga, p.stok
        FROM cart c
        JOIN produk p ON c.id_produk = p.id_produk
        WHERE c.id_cart='$id_cart' AND c.id_user='$id_user'
        LIMIT 1
    ");

    if(!$q) die(mysqli_error($conn));
    
    $d = mysqli_fetch_assoc($q);
    if(!$d) continue; 

    $stok_tersedia = 0;
    $pakaiVarian   = false;
    $target_id_varian = 0;

    // Deteksi string default / terpotong 'No Siz'
    $ukuran_cart = isset($d['ukuran']) ? trim($d['ukuran']) : '';
    $is_ukuran_empty = (empty($ukuran_cart) || strtolower($ukuran_cart) == 'no size' || strtolower($ukuran_cart) == 'no siz' || strtolower($ukuran_cart) == '-');

    $warna_cart = isset($d['warna']) ? trim($d['warna']) : '';
    $is_warna_empty = (empty($warna_cart) || strtolower($warna_cart) == 'no color' || strtolower($warna_cart) == '-');

    if (!$is_ukuran_empty || !$is_warna_empty) {
        $where = [];
        if (!$is_ukuran_empty) $where[] = "ukuran='" . mysqli_real_escape_string($conn, $ukuran_cart) . "'";
        if (!$is_warna_empty)  $where[] = "warna='" . mysqli_real_escape_string($conn, $warna_cart) . "'";

        $filter   = implode(" AND ", $where);
        $qVarian  = mysqli_query($conn, "SELECT * FROM varian_produk WHERE id_produk='{$d['id_produk']}' AND $filter LIMIT 1");
        if(!$qVarian) die(mysqli_error($conn));

        $varian = mysqli_fetch_assoc($qVarian);
        if($varian){
            $stok_tersedia    = intval($varian['stok']);
            $target_id_varian = intval($varian['id_varian']);
            $pakaiVarian      = true;
        } else {
            $qCekSistem = mysqli_query($conn, "SELECT COUNT(*) as total FROM varian_produk WHERE id_produk='{$d['id_produk']}'");
            $rowCek = mysqli_fetch_assoc($qCekSistem);

            if($rowCek['total'] > 0){
                echo "<script>alert('Varian produk [ " . $d['nama_produk'] . " ] tidak cocok / ditemukan.'); window.location='cart.php';</script>";
                exit;
            } else {
                $stok_tersedia = intval($d['stok']);
            }
        }
    } else {
        // Pengecekan varian baris kosong jika di cart bernilai 'No Siz' (Contoh Kasus id_produk 15 Anda)
        $qVarianKosong = mysqli_query($conn, "SELECT * FROM varian_produk WHERE id_produk='{$d['id_produk']}' AND (ukuran='' OR ukuran IS NULL OR ukuran='No Size') LIMIT 1");
        $varianKosong  = mysqli_fetch_assoc($qVarianKosong);

        if($varianKosong){
            $stok_tersedia    = intval($varianKosong['stok']);
            $target_id_varian = intval($varianKosong['id_varian']);
            $pakaiVarian      = true;
        } else {
            $stok_tersedia = intval($d['stok']);
        }
    }

    // Validasi kuantitas
    if($stok_tersedia <= 0){
        echo "<script>alert('Stok item \"".$d['nama_produk']."\" sudah habis.'); window.location='cart.php';</script>";
        exit;
    }
    if($d['qty'] > $stok_tersedia){
        echo "<script>alert('Stok item \"".$d['nama_produk']."\" tidak mencukupi.'); window.location='cart.php';</script>";
        exit;
    }

    // Simpan data tervalidasi ke dalam array sementara
    $d['stok_tersedia']    = $stok_tersedia;
    $d['pakai_varian']     = $pakaiVarian;
    $d['target_id_varian'] = $target_id_varian;
    $items_to_process[]    = $d;
}

/* =========================================================
   TAHAPAN 2: JIKA SEMUA AMAN, BARU BUAT INDUK PESANAN (INSERT PESANAN)
========================================================= */
$total_awal = $total_akhir + $diskon;
$tanggal    = date("Y-m-d H:i:s");

if($metode_bayar == "COD"){
    $status = "diproses";
    $status_pembayaran = "dibayar";
} else {
    $status = "menunggu pembayaran";
    $status_pembayaran = "pending";
}

$nomor_pesanan    = "ORD-" . date("Ymd") . "-" . rand(1000,9999);
$nomor_pembayaran = NULL;
$batas_pembayaran = NULL;

if($metode_bayar != "COD"){
    $kode_unik   = rand(1,999);
    $total_akhir = intval($total_akhir) + $kode_unik;

    if($metode_bayar == 'BANK') $nomor_pembayaran = "TRF-" . date("Ymd") . "-" . rand(100000,999999);
    if($metode_bayar == 'QRIS') $nomor_pembayaran = "QRS-" . date("Ymd") . "-" . rand(100000,999999);
    $batas_pembayaran = date("Y-m-d H:i:s", strtotime("+1 day"));
}

$insertPesanan = mysqli_query($conn, "
INSERT INTO pesanan (
    id_user, nomor_pesanan, nomor_pembayaran, batas_pembayaran, tanggal_pesan, 
    total, diskon, total_bayar, status, alamat_pengiriman, metode_pembayaran, bank_tujuan, status_pembayaran
) VALUES (
    '$id_user', '$nomor_pesanan', 
    ".($nomor_pembayaran ? "'$nomor_pembayaran'" : "NULL").", 
    ".($batas_pembayaran ? "'$batas_pembayaran'" : "NULL").", 
    '$tanggal', '$total_awal', '$diskon', '$total_akhir', '$status', '$alamat_final', '$metode_bayar', '$bank', '$status_pembayaran'
)
");

if(!$insertPesanan) die("Gagal insert pesanan : " . mysqli_error($conn));
$id_pesanan = mysqli_insert_id($conn);

/* =========================================================
   TAHAPAN 3: INSERT DETAIL, KURANGI STOK, & HAPUS CART
========================================================= */
foreach($items_to_process as $item){
    $subtotal   = $item['harga'] * $item['qty'];
    $val_ukuran = mysqli_real_escape_string($conn, $item['ukuran']);
    $val_warna  = isset($item['warna']) ? mysqli_real_escape_string($conn, $item['warna']) : '';

    // 1. Insert ke detail_pesanan
    $insertDetail = mysqli_query($conn,"
    INSERT INTO detail_pesanan (
        id_pesanan, id_produk, ukuran, warna, qty, harga, subtotal
    ) VALUES (
        '$id_pesanan', '{$item['id_produk']}', '$val_ukuran', '$val_warna', '{$item['qty']}', '{$item['harga']}', '$subtotal'
    )
    ");
    if(!$insertDetail) die("Gagal insert detail : " . mysqli_error($conn));

    // 2. Potong Stok secara rapi
    if($item['pakai_varian']){
        mysqli_query($conn, "UPDATE varian_produk SET stok = stok - {$item['qty']} WHERE id_varian='{$item['target_id_varian']}'");
        mysqli_query($conn, "UPDATE produk SET stok = stok - {$item['qty']} WHERE id_produk='{$item['id_produk']}'");
    } else {
        mysqli_query($conn, "UPDATE produk SET stok = stok - {$item['qty']} WHERE id_produk='{$item['id_produk']}'");
    }

    // 3. Hapus dari keranjang belanja (Cart)
    mysqli_query($conn, "DELETE FROM cart WHERE id_cart='{$item['id_cart']}' AND id_user='$id_user'");
}

/* =========================================================
   PENGALIHAN HALAMAN SELESAI
========================================================= */
echo "
<script>
alert('Pesanan berhasil dibuat!');
window.location='detail_pesanan.php?id=$id_pesanan';
</script>
";
?>