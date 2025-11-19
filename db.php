<?php
$dbfile = __DIR__ . '/bilsel.db';
try {
    $db = new PDO('sqlite:' . $dbfile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Nie udało się połączyć z bazą danych: " . htmlspecialchars($e->getMessage()));
}
$res = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
if(!$res){
    $db->exec("
      CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE,
        password TEXT,
        is_admin INTEGER DEFAULT 0
      );
    ");
    $db->exec("
      CREATE TABLE products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT,
        description TEXT,
        price REAL,
        image TEXT
      );
    ");
    $db->exec("
      CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        status TEXT DEFAULT 'new',
        total REAL,
        created_at TEXT
      );
    ");
    $db->exec("
      CREATE TABLE order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER,
        product_id INTEGER,
        qty INTEGER,
        price REAL
      );
    ");
    // admin
    $ph = password_hash('adminpass', PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO users (email,password,is_admin) VALUES (?,?,1)")->execute(['admin@local',$ph]);
    $products = [
        ['Laptop gamingowy MSI GP66', 'Wydajny laptop gamingowy z procesorem Intel i7, 16GB RAM, RTX 4060. Idealny do gier i pracy.', 8999,  "images/1.jpg"],
        ['Smartfon Samsung Galaxy', 'Nowoczesny smartfon z wyświetlaczem AMOLED, potężną baterią i świetnym aparatem.', 3499,  "images/6.jpg"],
        ['Słuchawki bezprzewodowe Sony', 'Aktywna redukcja hałasu (ANC), długa żywotność baterii i doskonała jakość dźwięku.', 899,  "images/3.jpg"],
        ['Klawiatura mechaniczna Keychron', 'Nowoczesna mechaniczna klawiatura z podświetleniem RGB i przełącznikami hot-swap.', 459,  "images/4.jpg"],
        ['Monitor 27" 144Hz', '27-calowy monitor z matrycą IPS, odświeżanie 144Hz i szybkim czasem reakcji — świetny do grania.', 1299,  "images/5.jpg"],
        ['Aparat cyfrowy Canon', 'Kompaktowy aparat cyfrowy z wymienną optyką — idealny do fotografii amatorskiej i półprofesjonalnej.', 4599,  "images/9.jpg"],
        ['Tablet Apple iPad', 'Lekki i wydajny tablet do pracy i rozrywki z obsługą rysika.', 3199,  "images/7.jpg"],
        ['Dysk SSD 1TB Samsung', 'Szybki dysk SSD NVMe 1TB — duża wydajność odczytu/zapisu.', 699,  "images/8.jpg"],
        ['Mysz gamingowa Logitech', 'Ergonomiczna mysz z regulowanym DPI i programowalnymi przyciskami.', 249,  "images/2.jpg"],
        ['Głośnik Bluetooth JBL', 'Przenośny głośnik Bluetooth z mocnym basem i wodoodporną konstrukcją.', 399,  "images/10.jpg"]
    ];
    $ins = $db->prepare("INSERT INTO products (title,description,price,image) VALUES (?,?,?,?)");
    foreach($products as $p){
        $ins->execute([$p[0], $p[1], $p[2], $p[3]]);
    }
}