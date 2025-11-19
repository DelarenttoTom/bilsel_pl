<?php
session_start();
require_once __DIR__ . '/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action = $_POST['action'] ?? '';
    if($action === 'logout'){
        session_destroy(); session_start();
        $_SESSION['flash']="Wylogowano.";
        header("Location: index.php"); exit;
    }
    if(isset($_POST['email']) && isset($_POST['password']) && empty($action)){
        $email=trim($_POST['email']);
        $pass=$_POST['password'];
        if(!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($pass)<4){
            $err="Nieprawidłowe dane.";
        } else {
            try{
                $ph=password_hash($pass,PASSWORD_DEFAULT);
                $db->prepare("INSERT INTO users (email,password) VALUES (?,?)")->execute([$email,$ph]);
                $_SESSION['flash']="Rejestracja zakończona. Zaloguj się.";
                header("Location: login.php"); exit;
            }catch(Exception $e){
                $err="Użytkownik już istnieje.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="pl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Rejestracja — Bilsel</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container"><a class="brand" href="index.php"><span class="logo">Bilsel</span></a></div></header>
<main class="container">
  <div class="auth-container animate">
    <h2>Rejestracja</h2>
    <?php if(!empty($err)) echo "<div class='flash'>$err</div>"; ?>
    <form method="post" class="form">
      <label>Email<input name="email" class="input" type="email" required></label>
      <label>Hasło<input name="password" class="input" type="password" required></label>
      <div><button class="btn">Zarejestruj</button></div>
    </form>
  </div>
</main>
<footer class="site-footer"><div class="container">Bilsel</div></footer>
</body>
</html>