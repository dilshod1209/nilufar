<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
include("../includes/db.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $description = $_POST['description'];
    $image = $_POST['image'];

    $stmt = $conn->prepare("INSERT INTO flora (name, description, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $description, $image);
    if($stmt->execute()){
        echo "O‘simlik qo‘shildi!";
    } else {
        echo "Xatolik yuz berdi!";
    }
}
?>
<form method="post">
  <input type="text" name="name" placeholder="O‘simlik nomi" required><br>
  <textarea name="description" placeholder="Tavsif" required></textarea><br>
  <input type="text" name="image" placeholder="Rasm fayl nomi (images/ ichida)" required><br>
  <button type="submit">Qo‘shish</button>
</form>
