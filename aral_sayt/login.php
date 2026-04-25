<?php
session_start();
include("../includes/db.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['user'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Parol noto‘g‘ri!";
        }
    } else {
        $error = "Foydalanuvchi topilmadi!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<h2>Admin Panelga Kirish</h2>
<form method="post">
  <input type="text" name="username" placeholder="Login" required><br>
  <input type="password" name="password" placeholder="Parol" required><br>
  <button type="submit">Kirish</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
