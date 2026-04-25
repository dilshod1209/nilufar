<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<h2>Xush kelibsiz, <?php echo $_SESSION['user']; ?>!</h2>
<nav>
  <ul>
    <li><a href="add_flora.php">Flora qo‘shish</a></li>
    <li><a href="add_fauna.php">Fauna qo‘shish</a></li>
    <li><a href="add_fact.php">Fakt qo‘shish</a></li>
    <li><a href="logout.php">Chiqish</a></li>
  </ul>
</nav>
</body>
</html>
