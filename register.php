<?php
include 'db.php';

if(isset($_POST['register'])){

    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users(email,password,role)
            VALUES('$email','$password','$role')";

    if(mysqli_query($conn,$sql)){
        header("Location: login.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/registerstyle.css">
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <h1>City Care Hospital</h1>
</div>

<!-- Center Card -->
<div class="auth-container">
    <div class="auth-box">
        <h2>Create your account</h2>
        <p>Please fill in the details to register</p>

        <form method="POST">
            <select name="role" required style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;">
                <option value="" disabled selected>Select Role</option>
                <option value="Receptionist">Receptionist</option>
                <option value="Manager">Manager</option>
                <option value="Admin">Admin</option>
            </select>

            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="register">Register →</button>
        </form>

        <p class="bottom-text">
            Already have an account?
            <a href="login.php">Login</a>
        </p>
    </div>
</div>

</body>
</html>