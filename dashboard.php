<?php
session_start();
include 'db.php';

// Strict Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* =======================
   1. ADD DOCTOR & UPDATE STATUS
======================= */
if (isset($_POST['add_doctor'])) {
    $stmt = $conn->prepare("INSERT INTO doctor (doctor_fullname, specialization, phone_no, doctor_email, department_id, consultation_fee, qualification, joining_date, employment_status) VALUES (?, ?, ?, ?, ?, 1000.00, 'MBBS', CURDATE(), 'Active')");
    $stmt->bind_param("ssssi", $_POST['doctor_name'], $_POST['specialization'], $_POST['phone'], $_POST['email'], $_POST['department_id']);
    $stmt->execute();
    header("Location: dashboard.php#doctor-section");
    exit();
}

if (isset($_POST['update_doctor_status'])) {
    $stmt = $conn->prepare("UPDATE doctor SET employment_status = ? WHERE doctor_id = ?");
    $stmt->bind_param("si", $_POST['new_status'], $_POST['doctor_id']);
    $stmt->execute();
    header("Location: dashboard.php#doctor-section");
    exit();
}

/* =======================
   2. ADD NURSE & UPDATE STATUS
======================= */
if (isset($_POST['add_nurse'])) {
    $joining_date = date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO nurse (nurse_fullname, nurse_phone_no, nurse_email, nurse_qualification, nurse_joining_date, nurse_employment_status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $_POST['nurse_name'], $_POST['nurse_phone'], $_POST['nurse_email'], $_POST['nurse_qualification'], $joining_date, $_POST['nurse_employment_status']);
    $stmt->execute();
    header("Location: dashboard.php#nurse-section");
    exit();
}

if (isset($_POST['update_nurse_status'])) {
    $stmt = $conn->prepare("UPDATE nurse SET nurse_employment_status = ? WHERE nurse_id = ?");
    $stmt->bind_param("si", $_POST['new_status'], $_POST['nurse_id']);
    $stmt->execute();
    header("Location: dashboard.php#nurse-section");
    exit();
}

/* =======================
   3. ADD PATIENT
======================= */
if (isset($_POST['add_patient'])) {
    $reg_date = date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO patient (patient_fullname, date_of_birth, gender, blood_group, phone_number, Emergency_contact, address, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $_POST['patient_name'], $_POST['dob'], $_POST['gender'], $_POST['blood_group'], $_POST['phone'], $_POST['emergency'], $_POST['address'], $reg_date);
    $stmt->execute();
    header("Location: dashboard.php#patient-section");
    exit();
}

/* =======================
   4. BOOK, CANCEL, & UPDATE APPOINTMENT STATUS
======================= */
if (isset($_POST['add_appointment'])) {
    $status = 'Yet to Consult';
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $app_date = $_POST['app_date'];
    $app_time = $_POST['app_time'];
    $purpose = $_POST['purpose'];
    
    // Fetch doctor consultation fee
    $doc_query = mysqli_query($conn, "SELECT consultation_fee FROM doctor WHERE doctor_id = '$doctor_id'");
    $doc_row = mysqli_fetch_assoc($doc_query);
    $total_charge = isset($doc_row['consultation_fee']) ? (float)$doc_row['consultation_fee'] : 500.00;

    // Add selected tests/diagnosis prices
    if (!empty($_POST['tests'])) {
        foreach ($_POST['tests'] as $test_price) {
            $total_charge += (float)$test_price;
        }
    }

    // Insert appointment
    $stmt = $conn->prepare("INSERT INTO appointment (patient_id, doctor_id, appointment_date, appointment_time, purpose, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $patient_id, $doctor_id, $app_date, $app_time, $purpose, $status);
    $stmt->execute();

    // Automatically generate distinct billing record for each addition time
    $bill_date = date('Y-m-d');
    $zero = 0.00;
    $method = 'Cash';
    $p_status = 'Pending';
    $stmt_bill = $conn->prepare("INSERT INTO billing (patient_id, bill_date, total_charge, discount, tax, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_bill->bind_param("isdddss", $patient_id, $bill_date, $total_charge, $zero, $zero, $method, $p_status);
    $stmt_bill->execute();

    header("Location: dashboard.php#appointment-section");
    exit();
}

if (isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['appointment_id'];
    $stmt = $conn->prepare("UPDATE appointment SET status = 'Cancelled' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    header("Location: dashboard.php#appointment-section");
    exit();
}

if (isset($_POST['update_appointment_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['appointment_status'];
    $stmt = $conn->prepare("UPDATE appointment SET status = ? WHERE appointment_id = ?");
    $stmt->bind_param("si", $new_status, $appointment_id);
    $stmt->execute();
    header("Location: dashboard.php#appointment-section");
    exit();
}

/* =======================
   5. ALLOCATE ROOM & ASSIGN NURSE
======================= */
if (isset($_POST['add_room'])) {
    $adm_date = date('Y-m-d');
    $availability = 'Occupied';
    $stmt = $conn->prepare("UPDATE room SET patient_id = ?, nurse_id = ?, availability = ?, admission_date = ? WHERE room_id = ?");
    $stmt->bind_param("iissi", $_POST['patient_id'], $_POST['nurse_id'], $availability, $adm_date, $_POST['room_id']);
    $stmt->execute();
    header("Location: dashboard.php#room-section");
    exit();
}

/* =======================
   6. DISCHARGE PATIENT
======================= */
if (isset($_POST['discharge_patient'])) {
    $room_id = $_POST['room_id'];
    $patient_id = $_POST['patient_id'];
    $admission_date = $_POST['admission_date'];
    $daily_charge = (float)$_POST['daily_charge'];
    $discharge_date = date('Y-m-d');

    $days = max(1, (int)((strtotime($discharge_date) - strtotime($admission_date)) / 86400));
    $total_bill = $days * $daily_charge;

    $stmt1 = $conn->prepare("UPDATE room SET availability = 'Available', patient_id = NULL, nurse_id = NULL, admission_date = NULL WHERE room_id = ?");
    $stmt1->bind_param("i", $room_id);
    $stmt1->execute();

    $zero = 0.00;
    $method = 'Cash';
    $p_status = 'Pending';
    $stmt2 = $conn->prepare("INSERT INTO billing (bill_date, total_charge, discount, tax, payment_method, payment_status, patient_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("sddsssi", $discharge_date, $total_bill, $zero, $zero, $method, $p_status, $patient_id);
    $stmt2->execute();

    header("Location: dashboard.php#room-section");
    exit();
}

/* =======================
   7. UPDATE BILLING STATUS & METHOD
======================= */
if (isset($_POST['update_bill_details'])) {
    $bill_id = $_POST['bill_id'];
    $new_status = $_POST['new_status'];
    $payment_method = $_POST['payment_method'];
    
    $stmt = $conn->prepare("UPDATE billing SET payment_status = ?, payment_method = ? WHERE bill_id = ?");
    $stmt->bind_param("ssi", $new_status, $payment_method, $bill_id);
    $stmt->execute();
    header("Location: dashboard.php#billing-section");
    exit();
}

/* =======================
   FETCH DATA & STATS
======================= */
$departments = mysqli_query($conn, "SELECT * FROM department");
$doctors     = mysqli_query($conn, "SELECT d.*, dept.department_name FROM doctor d LEFT JOIN department dept ON d.department_id = dept.department_id ORDER BY d.doctor_id DESC");
$nurses      = mysqli_query($conn, "SELECT * FROM nurse ORDER BY nurse_id DESC");
$patients    = mysqli_query($conn, "SELECT * FROM patient ORDER BY patient_id DESC");
$appointments = mysqli_query($conn, "SELECT a.*, p.patient_fullname, d.doctor_fullname FROM appointment a LEFT JOIN patient p ON a.patient_id = p.patient_id LEFT JOIN doctor d ON a.doctor_id = d.doctor_id ORDER BY a.appointment_id DESC");
$rooms        = mysqli_query($conn, "SELECT r.*, p.patient_fullname, n.nurse_fullname FROM room r LEFT JOIN patient p ON r.patient_id = p.patient_id LEFT JOIN nurse n ON r.nurse_id = n.nurse_id ORDER BY r.room_id ASC");

// Search filter implementation
$search_query = "";
if (isset($_GET['search_patient']) && !empty($_GET['search_patient'])) {
    $s = mysqli_real_escape_string($conn, $_GET['search_patient']);
    $search_query = " AND p.patient_fullname LIKE '%$s%' ";
}

// Separate queries for Due bills vs Paid bills
$bills_due = mysqli_query($conn, "SELECT b.*, p.patient_fullname FROM billing b LEFT JOIN patient p ON b.patient_id = p.patient_id WHERE b.payment_status = 'Pending' $search_query ORDER BY b.bill_id DESC");
$bills_paid = mysqli_query($conn, "SELECT b.*, p.patient_fullname FROM billing b LEFT JOIN patient p ON b.patient_id = p.patient_id WHERE b.payment_status = 'Paid' $search_query ORDER BY b.bill_id DESC");

$total_doctors_res = mysqli_query($conn, "SELECT COUNT(*) FROM doctor");
$total_doctors = mysqli_fetch_row($total_doctors_res)[0];

$total_nurses_res = mysqli_query($conn, "SELECT COUNT(*) FROM nurse");
$total_nurses = mysqli_fetch_row($total_nurses_res)[0];

$total_patients_res = mysqli_query($conn, "SELECT COUNT(*) FROM patient");
$total_patients = mysqli_fetch_row($total_patients_res)[0];

$total_beds_res = mysqli_query($conn, "SELECT COUNT(*) FROM room WHERE availability = 'Occupied'");
$total_occupied_beds = mysqli_fetch_row($total_beds_res)[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>City Care Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f9f9f9; color: #333; line-height: 1.6; }
        
        .navbar { background: #4c98a6; color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 24px; font-weight: bold; color: white; }
        .navbar .menu { list-style: none; display: flex; gap: 20px; align-items: center; }
        .navbar .menu a { color: white; text-decoration: none; font-size: 16px; transition: color 0.2s; }
        .navbar .menu a:hover { color: #d1e8eb; }
        
        .page-title { text-align: center; margin: 30px 0 10px; color: #2c3e50; font-size: 28px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

        .stats-container { display: flex; justify-content: space-between; width: 90%; max-width: 1200px; margin: 20px auto; gap: 15px; flex-wrap: wrap; }
        .stat-card { background: #ffffff; border-left: 5px solid #4c98a6; padding: 20px; border-radius: 6px; text-align: center; flex: 1; min-width: 200px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-card i { font-size: 28px; margin-bottom: 8px; color: #4c98a6; }
        .stat-card h3 { font-size: 22px; margin-bottom: 2px; color: #2c3e50; }
        .stat-card p { font-size: 13px; color: #777; }

        .section-wrapper { width: 90%; max-width: 1200px; margin: 30px auto; background: #ffffff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .section-wrapper h2 { color: #2c3e50; margin-bottom: 20px; font-size: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; }
        .section-wrapper h2 i { color: #4c98a6; }

        .form-card { background: #fdfdfd; padding: 20px; border-radius: 6px; border: 1px solid #eee; margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; flex: 1; min-width: 160px; }
        .form-group label { font-weight: 600; font-size: 13px; margin-bottom: 6px; color: #555; }
        .form-card input, .form-card select { padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; outline: none; background: #fff; }
        .form-card input:focus, .form-card select:focus { border-color: #4c98a6; }

        .checkbox-group { width: 100%; border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #fff; max-height: 120px; overflow-y: auto; }
        .checkbox-group label { font-weight: normal; font-size: 13px; display: block; margin-bottom: 4px; }

        .btn { background: #4c98a6; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; text-decoration: none; display: inline-block; text-align: center; transition: background 0.2s; }
        .btn:hover { background: #3b7984; }
        .btn-action { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-action:hover { background: #c0392b; }
        .btn-toggle { background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-toggle:hover { background: #219653; }

        .table-scroll-container { max-height: 400px; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; margin-top: 10px; }
        .cart-table { width: 100%; border-collapse: collapse; background: #ffffff; }
        .cart-table th { background-color: #4c98a6; color: white; text-align: left; padding: 12px 15px; font-size: 14px; position: sticky; top: 0; z-index: 2; }
        .cart-table td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 14px; color: #333; }
        .cart-table tr:hover { background-color: #f9f9f9; }
        .empty-row { text-align: center; color: #888; font-style: italic; }

        .site-footer { background: #4c98a6; color: white; padding: 30px 5%; margin-top: 50px; }
        .footer-container { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; flex-wrap: wrap; gap: 20px; }
        .footer-left h3, .footer-right h3 { margin-bottom: 8px; font-size: 18px; color: #fff; }
        .footer-left p { font-size: 14px; color: #f1f1f1; margin-bottom: 4px; }
        .footer-right h3 { font-size: 20px; letter-spacing: 1px; color: #ffffff; }
    </style>
</head>

<body>

<div class="navbar">
    <div class="logo">City Care Hospital</div>
    <ul class="menu">
        <li style="color:white; font-weight:bold; font-size:18px;">
            <?php if(isset($_SESSION['username'])): ?>
                Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>
            <?php elseif(isset($_SESSION['email'])): ?>
                Hi, <?php echo htmlspecialchars($_SESSION['email']); ?>
            <?php endif; ?>
        </li>
        <li><a href="home.php">Home</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<h1 class="page-title">Hospital Management Dashboard</h1>

<div class="stats-container">
    <div class="stat-card">
        <i class="fas fa-user-md"></i>
        <h3><?php echo htmlspecialchars((string)$total_doctors); ?></h3>
        <p>Total Doctors</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-nurse"></i>
        <h3><?php echo htmlspecialchars((string)$total_nurses); ?></h3>
        <p>Total Nurses</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-procedures"></i>
        <h3><?php echo htmlspecialchars((string)$total_patients); ?></h3>
        <p>Total Patients</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-bed"></i>
        <h3><?php echo htmlspecialchars((string)$total_occupied_beds); ?></h3>
        <p>Occupied Beds</p>
    </div>
</div>

<!-- 1. DOCTOR MANAGEMENT -->
<div class="section-wrapper" id="doctor-section">
    <h2><i class="fas fa-user-md"></i> Doctor Management</h2>
    <form method="POST" class="form-card">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="doctor_name" placeholder="Dr. John Doe" required>
        </div>
        <div class="form-group">
            <label>Specialization</label>
            <input type="text" name="specialization" placeholder="Cardiologist" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" placeholder="Phone Number" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="doctor@hospital.com" required>
        </div>
        <div class="form-group">
            <label>Department</label>
            <select name="department_id" required>
                <option value="">Select Department</option>
                <?php while ($dept = mysqli_fetch_assoc($departments)): ?>
                    <option value="<?php echo htmlspecialchars($dept['department_id']); ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" name="add_doctor" class="btn" style="width: auto;">Add Doctor</button>
    </form>

    <div class="table-scroll-container">
        <table class="cart-table">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Specialization</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Department</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if (mysqli_num_rows($doctors) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($doctors)): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['doctor_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_fullname'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['specialization'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_no'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_email'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['department_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['employment_status'] ?? 'Active'); ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px; align-items:center;">
                            <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($row['doctor_id']); ?>">
                            <select name="new_status" style="padding:4px; font-size:12px; border:1px solid #ccc; border-radius:3px;">
                                <option value="Active" <?php if(($row['employment_status'] ?? 'Active')=='Active') echo 'selected'; ?>>Active</option>
                                <option value="On Leave" <?php if(($row['employment_status'] ?? '')=='On Leave') echo 'selected'; ?>>On Leave</option>
                                <option value="Inactive" <?php if(($row['employment_status'] ?? '')=='Inactive') echo 'selected'; ?>>Inactive</option>
                            </select>
                            <button type="submit" name="update_doctor_status" class="btn-toggle" style="padding:4px 8px;">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="empty-row">No records found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- 2. NURSE MANAGEMENT -->
<div class="section-wrapper" id="nurse-section">
    <h2><i class="fas fa-user-nurse"></i> Nurse Management</h2>
    <form method="POST" class="form-card">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="nurse_name" placeholder="Nurse Name" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="nurse_phone" placeholder="Phone Number" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="nurse_email" placeholder="nurse@hospital.com" required>
        </div>
        <div class="form-group">
            <label>Qualification</label>
            <input type="text" name="nurse_qualification" placeholder="BSN, RN" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="nurse_employment_status" required>
                <option value="Active">Active</option>
                <option value="On Leave">On Leave</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <button type="submit" name="add_nurse" class="btn" style="width: auto;">Add Nurse</button>
    </form>

    <div class="table-scroll-container">
        <table class="cart-table">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Qualification</th>
                <th>Joining Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if (mysqli_num_rows($nurses) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($nurses)): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['nurse_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nurse_fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['nurse_phone_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['nurse_email']); ?></td>
                    <td><?php echo htmlspecialchars($row['nurse_qualification']); ?></td>
                    <td><?php echo htmlspecialchars($row['nurse_joining_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['nurse_employment_status']); ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px; align-items:center;">
                            <input type="hidden" name="nurse_id" value="<?php echo htmlspecialchars($row['nurse_id']); ?>">
                            <select name="new_status" style="padding:4px; font-size:12px; border:1px solid #ccc; border-radius:3px;">
                                <option value="Active" <?php if($row['nurse_employment_status']=='Active') echo 'selected'; ?>>Active</option>
                                <option value="On Leave" <?php if($row['nurse_employment_status']=='On Leave') echo 'selected'; ?>>On Leave</option>
                                <option value="Inactive" <?php if($row['nurse_employment_status']=='Inactive') echo 'selected'; ?>>Inactive</option>
                            </select>
                            <button type="submit" name="update_nurse_status" class="btn-toggle" style="padding:4px 8px;">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="empty-row">No records found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- 3. PATIENT REGISTRATION -->
<div class="section-wrapper" id="patient-section">
    <h2><i class="fas fa-procedures"></i> Patient Registration</h2>
    <form method="POST" class="form-card">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="patient_name" placeholder="Patient Name" required>
        </div>
        <div class="form-group">
            <label>DOB</label>
            <input type="date" name="dob" required>
        </div>
        <div class="form-group">
            <label>Gender</label>
            <select name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label>Blood Group</label>
            <select name="blood_group" required>
                <option value="A+">A+</option><option value="A-">A-</option>
                <option value="B+">B+</option><option value="B-">B-</option>
                <option value="O+">O+</option><option value="O-">O-</option>
                <option value="AB+">AB+</option><option value="AB-">AB-</option>
            </select>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" placeholder="Phone Number" required>
        </div>
        <div class="form-group">
            <label>Emergency Contact</label>
            <input type="text" name="emergency" placeholder="Emergency No." required>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" placeholder="Address" required>
        </div>
        <button type="submit" name="add_patient" class="btn" style="width: auto;">Register</button>
    </form>

    <div class="table-scroll-container">
        <table class="cart-table">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Blood</th>
                <th>Phone</th>
                <th>Emergency</th>
                <th>Address</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>
            <?php if (mysqli_num_rows($patients) > 0): ?>
                <?php 
                $patient_array_for_dropdowns = [];
                $patients_rows = [];
                while ($r_pat = mysqli_fetch_assoc($patients)) {
                    $patients_rows[] = $r_pat;
                }
                $patient_array_for_dropdowns = $patients_rows;
                
                foreach ($patients_rows as $row): 
                ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['patient_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['patient_fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['blood_group']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['Emergency_contact']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['registration_date']); ?></td>
                    <td>
                        <a href="patient_history.php?id=<?php echo htmlspecialchars($row['patient_id']); ?>" class="btn" style="padding: 4px 10px; font-size: 12px; text-decoration: none;">View History</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <?php $patient_array_for_dropdowns = []; ?>
                <tr><td colspan="10" class="empty-row">No records found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- 4. APPOINTMENT MANAGEMENT WITH DIAGNOSIS & AUTO-BILLING -->
<div class="section-wrapper" id="appointment-section">
    <h2><i class="fas fa-calendar-check"></i> Appointment & Diagnosis Booking</h2>
    <form method="POST" class="form-card">
        <div class="form-group">
            <label>Patient</label>
            <select name="patient_id" required>
                <option value="">Select Patient</option>
                <?php foreach ($patient_array_for_dropdowns as $p): ?>
                    <option value="<?php echo htmlspecialchars($p['patient_id']); ?>">
                        <?php echo htmlspecialchars($p['patient_id']) . " - " . htmlspecialchars($p['patient_fullname']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Doctor (Includes Consultation Fee)</label>
            <select name="doctor_id" required>
                <option value="">Select Doctor</option>
                <?php 
                $doctors_dropdown = mysqli_query($conn, "SELECT * FROM doctor");
                while ($d = mysqli_fetch_assoc($doctors_dropdown)): 
                ?>
                    <option value="<?php echo htmlspecialchars($d['doctor_id']); ?>">
                        <?php echo htmlspecialchars($d['doctor_fullname']) . " (" . htmlspecialchars($d['specialization']) . ") - Tk " . htmlspecialchars($d['consultation_fee']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Select Diagnosis / Tests</label>
            <div class="checkbox-group">
                <label><input type="checkbox" name="tests[]" value="300.00"> Complete Blood Count (CBC) - Tk 300</label>
                <label><input type="checkbox" name="tests[]" value="500.00"> X-Ray Chest - Tk 500</label>
                <label><input type="checkbox" name="tests[]" value="800.00"> Lipid Profile - Tk 800</label>
                <label><input type="checkbox" name="tests[]" value="1200.00"> Ultrasound Scan - Tk 1200</label>
                <label><input type="checkbox" name="tests[]" value="400.00"> Urine Routine Analysis - Tk 400</label>
            </div>
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="app_date" required>
        </div>
        <div class="form-group">
            <label>Time</label>
            <input type="time" name="app_time" required>
        </div>
        <div class="form-group">
            <label>Purpose</label>
            <input type="text" name="purpose" placeholder="Reason for visit" required>
        </div>
        <button type="submit" name="add_appointment" class="btn" style="width: auto;">Book & Generate Bill</button>
    </form>

    <div class="table-scroll-container">
        <table class="cart-table">
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if (mysqli_num_rows($appointments) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($appointments)): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['appointment_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['patient_fullname'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_fullname'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px; align-items:center;">
                            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($row['appointment_id']); ?>">
                            <select name="appointment_status" style="padding:4px; font-size:12px; border:1px solid #ccc; border-radius:3px;">
                                <option value="Yet to Consult" <?php if($row['status']=='Yet to Consult') echo 'selected'; ?>>Yet to Consult</option>
                                <option value="In Progress" <?php if($row['status']=='In Progress') echo 'selected'; ?>>In Progress</option>
                                <option value="Consulted" <?php if($row['status']=='Consulted') echo 'selected'; ?>>Consulted</option>
                                <option value="Cancelled" <?php if($row['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_appointment_status" class="btn-toggle" style="padding:4px 8px;">Update</button>
                        </form>
                    </td>
                    <td>
                        <?php if ($row['status'] !== 'Cancelled'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($row['appointment_id']); ?>">
                                <button type="submit" name="cancel_appointment" class="btn-action">Cancel</button>
                            </form>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="empty-row">No appointments scheduled.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- 5. ROOM & BED ALLOCATION -->
<div class="section-wrapper" id="room-section">
    <h2><i class="fas fa-bed"></i> Room & Bed Allocation</h2>
    <form method="POST" class="form-card">
        <div class="form-group">
            <label>Room</label>
            <select name="room_id" required>
                <option value="">Select Room</option>
                <?php 
                $rooms_dropdown = mysqli_query($conn, "SELECT * FROM room");
                while ($r = mysqli_fetch_assoc($rooms_dropdown)): 
                    if ($r['availability'] === 'Available'):
                ?>
                        <option value="<?php echo htmlspecialchars($r['room_id']); ?>">
                            Room <?php echo htmlspecialchars($r['room_number']) . " (" . htmlspecialchars($r['room_type']) . ")"; ?>
                        </option>
                <?php 
                    endif;
                endwhile; 
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Patient</label>
            <select name="patient_id" required>
                <option value="">Select Patient</option>
                <?php foreach ($patient_array_for_dropdowns as $p): ?>
                    <option value="<?php echo htmlspecialchars($p['patient_id']); ?>">
                        <?php echo htmlspecialchars($p['patient_id']) . " - " . htmlspecialchars($p['patient_fullname']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Nurse (Active Only)</label>
            <select name="nurse_id" required>
                <option value="">Select Active Nurse</option>
                <?>
                <?php 
                $nurses_dropdown = mysqli_query($conn, "SELECT * FROM nurse WHERE nurse_employment_status = 'Active'");
                while ($n = mysqli_fetch_assoc($nurses_dropdown)): 
                ?>
                    <option value="<?php echo htmlspecialchars($n['nurse_id']); ?>">
                        <?php echo htmlspecialchars($n['nurse_id']) . " - " . htmlspecialchars($n['nurse_fullname']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" name="add_room" class="btn" style="width: auto;">Allocate</button>
    </form>

    <div class="table-scroll-container">
        <table class="cart-table">
            <tr>
                <th>Room No</th>
                <th>Type</th>
                <th>Daily Charge</th>
                <th>Status</th>
                <th>Patient</th>
                <th>Nurse</th>
                <th>Admission Date</th>
                <th>Action</th>
            </tr>
            <?php if (mysqli_num_rows($rooms) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($rooms)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['room_type']); ?></td>
                    <td>Tk <?php echo htmlspecialchars((string)$row['daily_charge']); ?></td>
                    <td><?php echo htmlspecialchars($row['availability']); ?></td>
                    <td><?php echo htmlspecialchars($row['patient_fullname'] ?? 'Unassigned'); ?></td>
                    <td><?php echo htmlspecialchars($row['nurse_fullname'] ?? 'Unassigned'); ?></td>
                    <td><?php echo htmlspecialchars($row['admission_date'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($row['availability'] === 'Occupied'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($row['room_id']); ?>">
                                <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($row['patient_id']); ?>">
                                <input type="hidden" name="admission_date" value="<?php echo htmlspecialchars($row['admission_date']); ?>">
                                <input type="hidden" name="daily_charge" value="<?php echo htmlspecialchars($row['daily_charge']); ?>">
                                <button type="submit" name="discharge_patient" class="btn-action">Discharge</button>
                            </form>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="empty-row">No records found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- 6. BILLING & INVOICING (SEARCH, DUE BILLS & PAID BILLS TABLES) -->
<div class="section-wrapper" id="billing-section">
    <h2><i class="fas fa-file-invoice-dollar"></i> Billing & Invoicing</h2>
    <form method="GET" action="dashboard.php#billing-section" class="form-card">
        <div class="form-group" style="flex: 2;">
            <label>Search Patient Name</label>
            <input type="text" name="search_patient" placeholder="Enter patient name..." value="<?php echo isset($_GET['search_patient']) ? htmlspecialchars($_GET['search_patient']) : ''; ?>">
        </div>
        <button type="submit" class="btn" style="width: auto;">Search Bill</button>
        <?php if(isset($_GET['search_patient'])): ?>
            <a href="dashboard.php#billing-section" class="btn" style="background:#777; width: auto; text-decoration:none;">Reset</a>
        <?php endif; ?>
    </form>

    <h3 style="margin-top: 15px; margin-bottom: 10px; color: #e74c3c; font-size: 16px;">Due Bills (Pending)</h3>
    <div class="table-scroll-container">
        <table class="cart-table">
            <tr>
                <th>Bill ID</th>
                <th>Patient</th>
                <th>Date</th>
                <th>Total (Tk)</th>
                <th>Discount (Tk)</th>
                <th>Tax (10%)</th>
                <th>Total Payable Bill (Tk)</th>
                <th>Method</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if (mysqli_num_rows($bills_due) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($bills_due)): 
                    $subtotal = (float)$row['total_charge'];
                    $discount = (float)$row['discount'];
                    $tax = ($subtotal - $discount) * 0.10;
                    if ($tax < 0) $tax = 0;
                    $final_total = ($subtotal - $discount) + $tax;
                ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['bill_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['patient_fullname'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['bill_date']); ?></td>
                    <td>Tk <?php echo htmlspecialchars(number_format($subtotal, 2)); ?></td>
                    <td>Tk <?php echo htmlspecialchars(number_format($discount, 2)); ?></td>
                    <td>Tk <?php echo htmlspecialchars(number_format($tax, 2)); ?></td>
                    <td><strong>Tk <?php echo htmlspecialchars(number_format($final_total, 2)); ?></strong></td>
                    <td>
                        <form method="POST" id="form_due_<?php echo $row['bill_id']; ?>" style="display:inline;">
                            <input type="hidden" name="bill_id" value="<?php echo htmlspecialchars($row['bill_id']); ?>">
                            <input type="hidden" name="new_status" value="Paid">
                            <select name="payment_method" style="padding:4px; font-size:12px; border:1px solid #ccc; border-radius:3px;">
                                <option value="Cash" <?php if($row['payment_method']=='Cash') echo 'selected'; ?>>Cash</option>
                                <option value="Card" <?php if($row['payment_method']=='Card') echo 'selected'; ?>>Card</option>
                                <option value="Mobile Banking" <?php if($row['payment_method']=='Mobile Banking') echo 'selected'; ?>>Mobile Banking</option>
                                <option value="Insurance" <?php if($row['payment_method']=='Insurance') echo 'selected'; ?>>Insurance</option>
                            </select>
                    </td>
                    <td><strong><?php echo htmlspecialchars($row['payment_status']); ?></strong></td>
                    <td>
                            <button type="submit" name="update_bill_details" class="btn-toggle">Mark Paid</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="empty-row">No pending due bills found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <h3 style="margin-top: 25px; margin-bottom: 10px; color: #27ae60; font-size: 16px;">Paid Bills History</h3>
    <div class="table-scroll-container">
        <table class="cart-table">
            <tr>
                <th>Bill ID</th>
                <th>Patient</th>
                <th>Date</th>
                <th>Total (Tk)</th>
                <th>Discount (Tk)</th>
                <th>Tax (10%)</th>
                <th>Total Payable Bill (Tk)</th>
                <th>Method</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if (mysqli_num_rows($bills_paid) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($bills_paid)): 
                    $subtotal = (float)$row['total_charge'];
                    $discount = (float)$row['discount'];
                    $tax = ($subtotal - $discount) * 0.10;
                    if ($tax < 0) $tax = 0;
                    $final_total = ($subtotal - $discount) + $tax;
                ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['bill_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['patient_fullname'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['bill_date']); ?></td>
                    <td>Tk <?php echo htmlspecialchars(number_format($subtotal, 2)); ?></td>
                    <td>Tk <?php echo htmlspecialchars(number_format($discount, 2)); ?></td>
                    <td>Tk <?php echo htmlspecialchars(number_format($tax, 2)); ?></td>
                    <td><strong>Tk <?php echo htmlspecialchars(number_format($final_total, 2)); ?></strong></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="bill_id" value="<?php echo htmlspecialchars($row['bill_id']); ?>">
                            <input type="hidden" name="new_status" value="Pending">
                            <select name="payment_method" style="padding:4px; font-size:12px; border:1px solid #ccc; border-radius:3px;">
                                <option value="Cash" <?php if($row['payment_method']=='Cash') echo 'selected'; ?>>Cash</option>
                                <option value="Card" <?php if($row['payment_method']=='Card') echo 'selected'; ?>>Card</option>
                                <option value="Mobile Banking" <?php if($row['payment_method']=='Mobile Banking') echo 'selected'; ?>>Mobile Banking</option>
                                <option value="Insurance" <?php if($row['payment_method']=='Insurance') echo 'selected'; ?>>Insurance</option>
                            </select>
                    </td>
                    <td><strong><?php echo htmlspecialchars($row['payment_status']); ?></strong></td>
                    <td>
                            <button type="submit" name="update_bill_details" class="btn-action" style="background:#e67e22;">Revert to Pending</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="empty-row">No paid bills found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-left">
            <h3>City Care Hospital</h3>
            <p>Address: Mirpur-2, Dhaka, Bangladesh</p>
            <p>Phone: +880 1725895998</p>
            <p>Email: support@citycarehospital.com</p>
        </div>
        <div class="footer-right">
            <h3>@citycare</h3>
        </div>
    </div>
</footer>

</body>
</html>