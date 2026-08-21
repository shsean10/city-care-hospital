<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>City Care Hospital</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
/* ================= HERO ================= */
.hero {
    text-align: center;
    padding: 40px;
}

.hero h1 {
    font-size: 40px;
    margin-bottom: 20px;
}

.hero-content {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 50px;
}

.hero .text {
    max-width: 600px;
    font-size: 14px;
    color: #333;
}

.hero .image img {
    height: 250px;
}

/* ================= SERVICES ================= */
.services {
    display: flex;
    justify-content: space-around;
    padding: 50px;
    text-align: center;
}

.service-box i {
    font-size: 60px;
    margin-bottom: 15px;
}

</style>
</head>

<body>

<!-- ================= NAVBAR ================= -->
<div class="navbar">
    <div class="logo">City Care Hospital</div>

    <ul class="menu">

        <li style="color:white; font-weight:bold; font-size:24px;">
            <?php if(isset($_SESSION['email'])): ?>
                Welcome, <?php echo $_SESSION['email']; ?>
            <?php endif; ?>
        </li>

        <li><a href="home.php">Home</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="logout.php">Logout</a></li>

    </ul>
</div>


<!-- ================= HERO SECTION ================= -->
<section class="hero">

    <h1>Healthcare Powered By City Care Hospital</h1>

    <div class="hero-content">
        <div class="text">
            <p>City Care Hospital is an innovative medical management platform designed to make patient care, appointment scheduling, and record management efficient, fast, and reliable. It brings complete administrative control right to your fingertips, allowing staff to manage patient information, doctor schedules, and department records seamlessly.

The platform features a clean, user-friendly interface that helps administrative personnel easily navigate modules, access medical histories, and handle daily operations. With a secure login system and role-based access control, authorized staff can manage sensitive hospital data safely and without hassle.

Our hospital focuses on providing exceptional medical care and operational excellence. Every patient record and appointment is handled with precision, maintaining high standards of data integrity and patient care.
            </p>
        </div>

        <div class="image">
            <img src="images/hospital.jpg" alt="Hospital">
        </div>
    </div>

</section>


<!-- ================= SERVICES ================= -->
<section class="services">

    <div class="service-box">
        <i class="fas fa-user-md"></i>
        <h3>Expert Doctors</h3>
    </div>

    <div class="service-box">
        <i class="fas fa-ambulance"></i>
        <h3>24/7 Emergency</h3>
    </div>

    <div class="service-box">
        <i class="fas fa-heartbeat"></i>
        <h3>Quality Care</h3>
    </div>

</section>


<!-- ================= FOOTER ================= -->
<footer class="site-footer">

    <div class="footer-container">

        <div class="footer-left">
            <h3>City Care Hospital</h3>
            <p>Address: Mirpur-2, Dhaka, Bangladesh</p>
            <p>Phone: +880 1725895998</p>
            <p>Email: support@citycarehospital.com</p>
        </div>

        <div class="footer-right">
            <h3>@citycarehospital</h3>
        </div>

    </div>

</footer>

</body>
</html>