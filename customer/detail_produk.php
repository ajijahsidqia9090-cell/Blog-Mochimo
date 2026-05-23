<?php
session_start();
include '../config/koneksi.php';
include '../template/header.php';
include '../template/navbar_customer.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Query Produk
$query = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk='$id'");
$p = mysqli_fetch_assoc($query);

if (!$p) { echo "<script>alert('Produk tidak ditemukan!'); window.location='dashboard.php';</script>"; exit; }

// 2. Query Varian dengan filter ketat
$qVarian = mysqli_query($conn, "SELECT * FROM varian_produk WHERE id_produk='$id'");
$varianData = []; 
$adaVarianValid = false;

while($v = mysqli_fetch_assoc($qVarian)){
    // Varian dianggap valid hanya jika ada isi di ukuran atau warna
    if (!empty(trim($v['ukuran'])) || !empty(trim($v['warna']))) {
        $adaVarianValid = true;
    }
    $varianData[] = $v;
}

$qGaleri = mysqli_query($conn, "SELECT * FROM galeri_produk WHERE id_produk='$id'");
$countGaleri = mysqli_num_rows($qGaleri);
// Gunakan variabel baru ini untuk menentukan tampil/tidaknya UI varian
$hasVarian = $adaVarianValid;

// 3. Logika Harga
$hargaUtama = (int)($p['harga'] ?? 0);

if ($hasVarian) {
    $h = array_column($varianData, 'harga_varian');
    $minH = min(array_map('intval', $h));
    $maxH = max(array_map('intval', $h));
    $hargaTampil = ($minH == $maxH) ? "Rp " . number_format($minH, 0, ',', '.') : "Rp " . number_format($minH, 0, ',', '.') . " - Rp " . number_format($maxH, 0, ',', '.');
} else {
    $hargaTampil = "Rp " . number_format($hargaUtama, 0, ',', '.');
}
?>


<div class="container my-5">
    <div class="product-wrapper shadow-sm p-4 bg-white rounded">
        <div class="row">
            <div class="col-md-5">
                <img src="../admin/upload/<?= $p['gambar']; ?>" class="main-image w-100 rounded border" id="mainImage" style="height:450px; object-fit:cover;">
                <div class="d-flex gap-2 mt-3 overflow-auto pb-2">
                    <img src="../admin/upload/<?= $p['gambar']; ?>" class="thumb-image border p-1 rounded" onclick="changeImage(this.src)" style="width:75px; height:75px; object-fit:cover; cursor:pointer;">
                    <?php while($g = mysqli_fetch_assoc($qGaleri)): ?>
                        <img src="../admin/upload/<?= $g['nama_file']; ?>" class="thumb-image border p-1 rounded" onclick="changeImage(this.src)" style="width:75px; height:75px; object-fit:cover; cursor:pointer;">
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="col-md-7 ps-md-4">
                <h2 class="fw-bold text-dark"><?= $p['nama_produk']; ?></h2>

                <div class="my-4 p-3 bg-light rounded border-start border-danger border-4">
                    <span class="fs-2 fw-bold text-danger" id="hargaText"><?= $hargaTampil; ?></span>
                </div>
                
                <form action="add_cart.php" method="POST" onsubmit="return checkForm(event)">
                    <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">

                    <?php if($hasVarian): ?>
                        <input type="hidden" name="id_varian" id="inputVarian" value="">
                        <div class="mb-4">
                            <label class="fw-bold text-dark mb-2">Pilih Varian:</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php foreach($varianData as $v): 
                                    // Hanya tampilkan tombol jika varian memiliki nama
                                    if (!empty(trim($v['ukuran'])) || !empty(trim($v['warna']))): ?>
                                    <button type="button" class="btn btn-outline-dark btn-sm" 
                                            onclick="selectVarian(<?= htmlspecialchars(json_encode($v)) ?>, this)">
                                        <?= $v['warna'] ?> - <?= $v['ukuran'] ?>
                                    </button>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-4">Stok Tersedia: <span id="stokText" class="fw-bold text-dark">-</span></div>
                    <?php else: ?>
                        <div class="mb-4">Stok Tersedia: <span class="fw-bold text-dark"><?= $p['stok']; ?></span></div>
                    <?php endif; ?>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-outline-danger px-4 py-2">🛒 Keranjang</button>
                        <button type="submit" formaction="beli_sekarang.php" class="btn btn-danger px-4 py-2">Beli Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="row mt-5 border-top pt-4">
            <div class="col-12">
                <h5 class="fw-bold mb-3">Deskripsi Produk</h5>
                <p class="text-secondary"><?= nl2br($p['deskripsi']); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
let hasVarian = <?= $hasVarian ? 'true' : 'false' ?>;

function selectVarian(v, btn) {
    document.getElementById('hargaText').innerText = 'Rp ' + parseInt(v.harga_varian).toLocaleString('id-ID');
    document.getElementById('stokText').innerText = v.stok;
    document.getElementById('inputVarian').value = v.id_varian;
    document.querySelectorAll('.btn-outline-dark').forEach(b => b.classList.remove('btn-dark', 'text-white'));
    btn.classList.add('btn-dark', 'text-white');
}

function checkForm(event) {
    if (hasVarian && document.getElementById('inputVarian').value === "") {
        event.preventDefault();
        alert("Mohon pilih varian produk terlebih dahulu!");
        return false;
    }
    return true;
}
function changeImage(src) { document.getElementById('mainImage').src = src; }
</script>


<?php include '../template/footer.php'; ?>