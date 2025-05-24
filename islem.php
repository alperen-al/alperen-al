<?php
header("Content-Type: application/json");

// MySQL bağlantısı (XAMPP için genelde kullanıcı: root, şifre: boş)
$baglanti = new mysqli("localhost", "root", "", "rezervasyon_db");

if ($baglanti->connect_error) {
    echo json_encode(["success" => false, "mesaj" => "Veritabanı bağlantı hatası"]);
    exit;
}

// Form verilerini al
$giris = $_POST['giris'] ?? '';
$cikis = $_POST['cikis'] ?? '';
$oda_ucreti = (int) ($_POST['oda'] ?? 0);
$cocuk_ucreti = (int) ($_POST['cocuk'] ?? 0);
$iptal_ucreti = (int) ($_POST['iptal'] ?? 0);
$adsoyad = $_POST['adsoyad'] ?? '';
$tc = $_POST['tc'] ?? '';
$email = $_POST['email'] ?? '';
$tel = $_POST['tel'] ?? '';
$kart_ad = $_POST['kartad'] ?? '';
$kart_no = $_POST['kartno'] ?? '';
$skt = $_POST['skt'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Gün farkını hesapla
$gun = 1;
try {
    $t1 = new DateTime($giris);
    $t2 = new DateTime($cikis);
    $gun = max(1, $t2->diff($t1)->days);
} catch (Exception $e) {
    echo json_encode(["success" => false, "mesaj" => "Tarih hatalı: " . $e->getMessage()]);
    exit;
}

// Toplam ücret
$toplam_ucret = ($oda_ucreti * 2 * $gun) + $cocuk_ucreti + $iptal_ucreti;

// Kayıt sorgusu
$sql = "INSERT INTO rezervasyonlar 
(adsoyad, tc, email, tel, oda_ucreti, cocuk_ucreti, iptal_ucreti, toplam_ucret, kart_ad, kart_no, skt, cvv, giris_tarihi, cikis_tarihi)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $baglanti->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "mesaj" => "Sorgu hazırlanamadı: " . $baglanti->error]);
    exit;
}

$stmt->bind_param(
    "ssssiiiissssss",
    $adsoyad, $tc, $email, $tel,
    $oda_ucreti, $cocuk_ucreti, $iptal_ucreti, $toplam_ucret,
    $kart_ad, $kart_no, $skt, $cvv,
    $giris, $cikis
);

// Sorguyu çalıştır
if ($stmt->execute()) {
    echo json_encode(["success" => true, "toplam" => $toplam_ucret]);
} else {
    echo json_encode(["success" => false, "mesaj" => "Kayıt başarısız: " . $stmt->error]);
}

$stmt->close();
$baglanti->close();
?>
