<?php
session_start();
require_once __DIR__ . '/db.php';
if(empty($_SESSION['is_admin'])){ header("Location: index.php"); exit; }
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['set_status'])){
    $oid = intval($_POST['order_id']);
    $status = $_POST['status'];
    $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status,$oid]);
    $_SESSION['flash']="Status zamówienia zaktualizowany.";
    header("Location: admin.php"); exit;
}
$orders = $db->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Panel admina — Bilsel</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container"><a class="brand" href="index.php"><span class="logo">Bilsel</span></a></div></header>
<main class="container">
  <?php if(!empty($_SESSION['flash'])){echo "<div class='flash'>".$_SESSION['flash']."</div>"; unset($_SESSION['flash']);} ?>
  <h1>Panel admina — Zamówienia</h1>
  <table class="admin-table">
    <thead><tr><th>#</th><th>Klient</th><th>Produkty</th><th>Kwota</th><th>Status</th><th>Data</th><th></th></tr></thead>
    <tbody>
    <?php foreach($orders as $o):
       $user = $db->query("SELECT email FROM users WHERE id=".(int)$o['user_id'])->fetchColumn();
       $items = $db->query("SELECT p.title,i.qty,i.price FROM order_items i JOIN products p ON p.id=i.product_id WHERE order_id=".(int)$o['id'])->fetchAll(PDO::FETCH_ASSOC);
       $txt=''; foreach($items as $it) $txt .= htmlspecialchars($it['title']).' × '.$it['qty'].'; ';
    ?>
      <tr>
        <td><?=$o['id']?></td>
        <td><?=htmlspecialchars($user)?></td>
        <td><?=$txt?></td>
        <td><?=number_format($o['total'],2,'.',',')?> zł</td>
        <td><?=htmlspecialchars($o['status'])?></td>
        <td><?=$o['created_at']?></td>
        <td>
          <form method="post">
            <input type="hidden" name="order_id" value="<?=$o['id']?>">
            <select name="status">
              <option<?= $o['status']=='new'?' selected':'' ?>>new</option>
              <option<?= $o['status']=='accepted'?' selected':'' ?>>accepted</option>
              <option<?= $o['status']=='cancelled'?' selected':'' ?>>cancelled</option>
            </select>
            <button class="btn small" name="set_status">OK</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</main>
<footer class="site-footer"><div class="container">Bilsel</div></footer>
</body>
</html>