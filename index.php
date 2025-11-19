<?php
session_start();
require_once __DIR__ . '/db.php';
$products = $db->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
$cart = $_SESSION['cart'] ?? [];
function is_logged(){ return !empty($_SESSION['user_id']); }
function current_user_email(){ global $db; if(!is_logged()) return ''; $st=$db->prepare("SELECT email FROM users WHERE id=?"); $st->execute([$_SESSION['user_id']]); return $st->fetchColumn(); }
function flash(){ if(!empty($_SESSION['flash'])){$m=$_SESSION['flash']; unset($_SESSION['flash']); return "<div class='flash'>$m</div>"; } return ''; }
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Bilsel — Sklep z elektroniką</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
  <div class="container">
    <a class="brand" href="index.php"><span class="logo">Bilsel</span></a>
    <nav>
      <a href="index.php">Katalog</a>
      <a href="cart.php">Koszyk (<?= array_sum($cart)?:0 ?>)</a>
      <?php if(is_logged()): ?>
        <a href="#"><?=htmlspecialchars(current_user_email())?></a>
        <form class="logout-form" method="post" action="register.php" style="display:inline">
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="btn small">Wyloguj</button>
        </form>
      <?php else: ?>
        <a href="login.php">Zaloguj</a>
      <?php endif; ?>
      <?php if(is_logged() && !empty($_SESSION['is_admin'])): ?>
        <a href="admin.php">Panel admina</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">
  <?= flash() ?>
  <h1>Katalog produktów</h1>
  <div class="grid">
    <?php foreach($products as $p): ?>
      <div class="card">
        <img src="<?=htmlspecialchars($p['image'])?>" alt="">
        <h3><?=htmlspecialchars($p['title'])?></h3>
        <p class="muted"><?=htmlspecialchars($p['description'])?></p>
        <div class="card-footer">
          <strong><?=number_format($p['price'],2,'.',',')?> zł</strong>
          <form method="post" action="cart.php" class="inline-form">
            <input type="hidden" name="add" value="<?=intval($p['id'])?>">
            <input type="number" name="qty" value="1" min="1" class="qty" aria-label="qty">
            <button class="btn">Dodaj</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<footer class="site-footer">
  <div class="container">Bilsel — sklep z elektroniką.</div>
</footer>
</body>
</html>