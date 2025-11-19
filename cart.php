<?php
session_start();
require_once __DIR__ . '/db.php';
function is_logged(){ return !empty($_SESSION['user_id']); }
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['add'])){
        $pid = intval($_POST['add']);
        $qty = max(1,intval($_POST['qty'] ?? 1));
        $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + $qty;
        $_SESSION['flash'] = "Produkt dodany do koszyka.";
        header("Location: cart.php"); exit;
    }
    if(isset($_POST['remove'])){
        $pid = intval($_POST['remove']);
        unset($_SESSION['cart'][$pid]);
        $_SESSION['flash'] = "Produkt usunięty.";
        header("Location: cart.php"); exit;
    }
    if(isset($_POST['order'])){
        if(!is_logged()){ $_SESSION['flash']="Zaloguj się, aby złożyć zamówienie."; header("Location: login.php"); exit; }
        $cart = $_SESSION['cart'] ?? [];
        if(empty($cart)){ $_SESSION['flash']="Koszyk jest pusty."; header("Location: cart.php"); exit; }
        $total = 0;
        foreach($cart as $pid=>$qty){
            $price = (float)$db->query("SELECT price FROM products WHERE id=".(int)$pid)->fetchColumn();
            $total += $price * $qty;
        }
        $db->prepare("INSERT INTO orders (user_id,total,created_at) VALUES (?,?,datetime('now'))")
           ->execute([$_SESSION['user_id'],$total]);
        $oid = $db->lastInsertId();
        $ins = $db->prepare("INSERT INTO order_items (order_id,product_id,qty,price) VALUES (?,?,?,?)");
        foreach($cart as $pid=>$qty){
            $price = (float)$db->query("SELECT price FROM products WHERE id=".(int)$pid)->fetchColumn();
            $ins->execute([$oid,$pid,$qty,$price]);
        }
        unset($_SESSION['cart']);
        $_SESSION['flash']="Zamówienie złożone. Nr {$oid}";
        header("Location: cart.php"); exit;
    }
}

$cart = $_SESSION['cart'] ?? [];
function flash(){ if(!empty($_SESSION['flash'])){$m=$_SESSION['flash']; unset($_SESSION['flash']); return "<div class='flash'>$m</div>"; } return ''; }
?>
<!doctype html>
<html lang="pl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Koszyk — Bilsel</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container"><a class="brand" href="index.php"><span class="logo">Bilsel</span></a><nav><a href="index.php">Katalog</a></nav></div></header>
<main class="container">
  <?= flash() ?>
  <h1>Koszyk</h1>
  <?php if(empty($cart)): ?>
    <p class="muted">Koszyk jest pusty.</p>
  <?php else: ?>
    <table class="cart-table">
      <thead><tr><th>Produkt</th><th>Ilość</th><th>Cena</th><th>Suma</th><th></th></tr></thead>
      <tbody>
      <?php $sum=0; foreach($cart as $pid=>$qty): $p=$db->query("SELECT * FROM products WHERE id=".(int)$pid)->fetch(PDO::FETCH_ASSOC); $line=$p['price']*$qty; $sum+=$line; ?>
        <tr>
          <td><?=htmlspecialchars($p['title'])?></td>
          <td><?=intval($qty)?></td>
          <td><?=number_format($p['price'],2,'.',',')?> zł</td>
          <td><?=number_format($line,2,'.',',')?> zł</td>
          <td>
            <form method="post" style="display:inline"><button class="btn small" name="remove" value="<?=intval($pid)?>">Usuń</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div class="cart-summary">
      <div>Razem: <strong><?=number_format($sum,2,'.',',')?> zł</strong></div>
      <form method="post"><button class="btn" name="order">Złóż zamówienie</button></form>
    </div>
  <?php endif; ?>
</main>
<footer class="site-footer"><div class="container">Bilsel</div></footer>
</body>
</html>