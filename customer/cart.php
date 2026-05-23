<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$cart = mysqli_query($conn, "
SELECT c.*, p.nama_produk, p.harga, p.gambar
FROM cart c
JOIN produk p ON c.id_produk = p.id_produk
WHERE c.id_user='$id_user'
");

include "../template/header.php";
include "../template/navbar_customer.php";
?>

<style>
body { background:#f5f5f5; font-family:'Segoe UI', sans-serif; }

/* BACK BUTTON (HEADER STYLE) */
.back-nav {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #777;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s ease-in-out;
    font-size: 13px;
    background: transparent;
    border: 1.5px solid #ddd;
    padding: 6px 14px;
    border-radius: 8px;
}

.back-btn:hover {
    color: #ff4d6d;
    border-color: #ff4d6d;
    background: #fffafa;
}

/* CART STYLING */
.cart-box { background:#fff; border-radius:18px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }

.cart-header {
    display:grid;
    grid-template-columns: 50px 2fr 1fr 1fr 1fr 100px;
    padding:15px 0;
    border-bottom:1px solid #eee;
    font-weight:700;
    color:#666;
    font-size: 14px;
}

.cart-row {
    display:grid;
    grid-template-columns: 50px 2fr 1fr 1fr 1fr 100px;
    align-items:center;
    padding:18px 0;
    border-bottom:1px solid #f1f1f1;
}

.product { display:flex; align-items:center; gap:15px; }
.product img { width:75px; height:75px; object-fit:cover; border-radius:12px; border:1px solid #eee; }
.name { font-size:15px; font-weight:600; }
.price { color:#e53935; font-weight:700; }

/* QTY */
.qty-box { display:flex; align-items:center; gap:8px; }
.qty-btn { width:30px; height:30px; border:none; border-radius:8px; background:#f1f1f1; font-weight:bold; cursor:pointer; }
.qty-input { width:40px; text-align:center; border:none; background:transparent; font-weight:600; }

.delete-link { color:#ff4d6d; text-decoration:none; font-weight:600; }

/* SUMMARY */
.summary {
    margin-top:25px;
    background:#fff;
    padding:20px 30px;
    border-radius:18px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.total-price { font-size:24px; font-weight:700; color:#ff4d6d; }

.checkout-btn {
    background:#ff4d6d;
    border:none;
    border-radius:12px;
    padding:12px 40px;
    color:white;
    font-weight:600;
    cursor:pointer;
}

/* ===================== */
/* FLOATING HOME BUTTON */
/* ===================== */
.back-home-btn{
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 48px;
    height: 48px;
    background: #ff4d6d;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    text-decoration: none;
    z-index: 999;
    transition: 0.2s ease;
}

.back-home-btn:hover{
    transform: scale(1.08);
    background: #ff355d;
}

@media(max-width:768px){
    .cart-header { display:none; }
    .cart-row { grid-template-columns:1fr; gap:10px; }
    .summary { flex-direction:column; gap:15px; text-align:center; }
}
</style>

<div class="container py-4">

    <!-- ✅ FLOATING BACK HOME -->
    <a href="dashboard.php" class="back-home-btn">
        <i class="bi bi-house-door-fill"></i>
    </a>

   

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">🛒 Keranjang Saya</h3>
        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
            <?= mysqli_num_rows($cart); ?> Item
        </span>
    </div>

    <?php if(mysqli_num_rows($cart) > 0) : ?>
    <form action="checkout.php" method="POST">

        <div class="cart-box">
            <div class="cart-header">
                <div><input type="checkbox" id="checkAll"></div>
                <div>Produk</div>
                <div>Harga</div>
                <div>Kuantitas</div>
                <div>Subtotal</div>
                <div>Aksi</div>
            </div>

            <?php while($c = mysqli_fetch_assoc($cart)) : 
                $subtotal = $c['harga'] * $c['qty'];
            ?>
            <div class="cart-row">
                <div>
                    <input type="checkbox" class="check-item" name="checkout[]" value="<?= $c['id_cart']; ?>" data-price="<?= $c['harga']; ?>">
                </div>

                <div class="product">
                    <img src="../admin/upload/<?= $c['gambar']; ?>">
                    <div class="name"><?= $c['nama_produk']; ?></div>
                </div>

                <div class="price">Rp <?= number_format($c['harga']); ?></div>

                <div class="qty-box">
                    <button type="button" class="qty-btn minus">-</button>
                    <input type="text" class="qty-input" value="<?= $c['qty']; ?>" data-id="<?= $c['id_cart']; ?>" readonly>
                    <button type="button" class="qty-btn plus">+</button>
                </div>

                <div class="price subtotal">Rp <?= number_format($subtotal); ?></div>

                <div>
                    <a href="hapus_cart.php?id=<?= $c['id_cart']; ?>" class="delete-link">Hapus</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="summary">
            <div>
                <div class="text-muted small fw-semibold">Total</div>
                <div class="total-price" id="grandTotal">Rp 0</div>
            </div>
            <button type="submit" class="checkout-btn">Lanjut Checkout</button>
        </div>

    </form>
    <?php else : ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm mt-3">
            <i class="bi bi-cart-x" style="font-size:4rem;"></i>
            <h5 class="mt-3 fw-bold">Keranjang kosong</h5>
            <a href="dashboard.php" class="btn btn-danger px-4 rounded-pill">Belanja</a>
        </div>
    <?php endif; ?>

</div>

<script>
// (script kamu tetap, tidak diubah)
</script>

<?php include "../template/footer.php"; ?>