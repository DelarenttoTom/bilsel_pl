<?php
session_start();
require_once __DIR__ . '/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=trim($_POST['email'] ?? '');
    $pass=$_POST['password'] ?? '';
    $st=$db->prepare("SELECT id,password,is_admin FROM users WHERE email=?");
    $st->execute([$email]);
    $u=$st->fetch(PDO::FETCH_ASSOC);
    if($u && password_verify($pass,$u['password'])){
        $_SESSION['user_id']=$u['id'];
        $_SESSION['is_admin']=$u['is_admin'];
        $_SESSION['flash']="Zalogowano.";
        header("Location: index.php"); exit;
    } else {
        $err="Nieprawidłowy login lub hasło.";
    }
}
?>
<!doctype html>
<html lang="pl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Logowanie — Bilsel</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container"><a class="brand" href="index.php"><span class="logo">Bilsel</span></a></div></header>
<main class="container">
  <div class="auth-container animate">
    <h2>Logowanie do Bilsel</h2>
    <?php if(!empty($err)) echo "<div class='flash'>$err</div>"; ?>
    <form method="post" class="form">
      <label>Email<input name="email" class="input" type="email" required></label>
      <label>Hasło<input name="password" class="input" type="password" required></label>
      <div style="display:flex;gap:10px;align-items:center">
        <button class="btn">Zaloguj</button>
        <a class="link" href="register.php">Rejestracja</a>
      </div>
    </form>
  </div>
</main>
<footer class="site-footer"><div class="container">Bilsel</div></footer>
</body>
</html>