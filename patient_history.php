<?php
session_start();
include 'db.php';

// Get Patient ID from URL parameter (e.g., patient_history.php?id=1)
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$patient = null;
$appointments = false;
$bills = false;

if ($patient_id > 0) {
    // 1. Fetch Patient Info
    $patient_query = mysqli_query($conn, "SELECT * FROM patient WHERE patient_id = '$patient_id'");
    if ($patient_query && mysqli_num_rows($patient_query) > 0) {
        $patient = mysqli_fetch_assoc($patient_query);
    }

    // 2. Fetch Patient Appointments
    $appointments = mysqli_query($conn, "
        SELECT a.*, d.doctor_fullname 
        FROM appointment a 
        LEFT JOIN doctor d ON a.doctor_id = d.doctor_id 
        WHERE a.patient_id = '$patient_id' 
        ORDER BY a.appointment_id DESC
    ");

    // 3. Fetch Patient Bills
    $bills = mysqli_query($conn, "
        SELECT * FROM billing 
        WHERE patient_id = '$patient_id' 
        ORDER BY bill_id DESC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Medical History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* RESET & BASE STYLES */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f7f6;
            color: #333;
            padding: 30px;
            line-height: 1.6;
        }

        /* CONTAINER & CARDS */
        .history-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #4c98a6;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .history-header h2 {
            color: #2c3e50;
            font-size: 24px;
        }

        /* BACK BUTTON STYLES */
        .btn-back {
            background: #2c3e50;
            color: #ffffff;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 5px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s ease;
        }

        .btn-back:hover {
            background: #1a252f;
        }

        /* PATIENT INFO BANNER */
        .patient-card {
            background: #eef7f8;
            border-left: 5px solid #4c98a6;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .patient-info-item {
            font-size: 14px;
            color: #444;
        }

        .patient-info-item strong {
            color: #2c3e50;
        }

        /* SECTION TITLES */
        .section-title {
            color: #2c3e50;
            font-size: 18px;
            margin: 25px 0 12px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #4c98a6;
        }

        /* TABLES */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background: #fff;
            overflow: hidden;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }

        .history-table th {
            background-color: #4c98a6;
            color: #ffffff;
            text-align: left;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: 600;
        }

        .history-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eeeeee;
            font-size: 14px;
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        .history-table tr:nth-child(even) {
            background-color: #f9fbfb;
        }

        .history-table tr:hover {
            background-color: #f1f7f8;
        }

        /* STATUS BADGES */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-paid { background: #e8f8f5; color: #27ae60; }
        .badge-pending { background: #fadbd8; color: #e74c3c; }
        .badge-completed { background: #ebf5fb; color: #2980b9; }

        /* PRINT BUTTON */
        .btn-print {
            background: #4c98a6;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s ease;
        }

        .btn-print:hover {
            background: #3a7985;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .history-container {
                box-shadow: none;
                padding: 0;
                width: 100%;
            }

            .btn-back, .btn-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="history-container">
    <div class="history-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2>Medical History Report</h2>
        </div>
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>

    <?php if ($patient): ?>
        <div class="patient-card">
            <div class="patient-info-item"><strong>Patient ID:</strong> #<?php echo $patient['patient_id']; ?></div>
            <div class="patient-info-item"><strong>Name:</strong> <?php echo htmlspecialchars($patient['patient_fullname']); ?></div>
            <div class="patient-info-item"><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone_number']); ?></div>
            <div class="patient-info-item"><strong>Blood Group:</strong> <?php echo htmlspecialchars($patient['blood_group']); ?></div>
            <div class="patient-info-item"><strong>DOB:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></div>
        </div>

        <!-- APPOINTMENTS TABLE -->
        <h3 class="section-title"><i class="fas fa-calendar-check"></i> Consultations & Appointments</h3>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Purpose</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointments && mysqli_num_rows($appointments) > 0): ?>
                    <?php while($a = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td><?php echo $a['appointment_date']; ?></td>
                        <td><?php echo htmlspecialchars($a['doctor_fullname'] ? $a['doctor_fullname'] : 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($a['purpose']); ?></td>
                        <td><span class="badge badge-completed"><?php echo htmlspecialchars($a['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; color: #777;">No appointments found for this patient.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- BILLING HISTORY TABLE -->
        <h3 class="section-title"><i class="fas fa-file-invoice-dollar"></i> Billing History</h3>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Bill ID</th>
                    <th>Date</th>
                    <th>Total Charge</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bills && mysqli_num_rows($bills) > 0): ?>
                    <?php while($b = mysqli_fetch_assoc($bills)): ?>
                    <tr>
                        <td>#<?php echo $b['bill_id']; ?></td>
                        <td><?php echo $b['bill_date']; ?></td>
                        <td><?php echo $b['total_charge']; ?> Tk</td>
                        <td>
                            <span class="badge <?php echo ($b['payment_status'] == 'Paid') ? 'badge-paid' : 'badge-pending'; ?>">
                                <?php echo htmlspecialchars($b['payment_status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; color: #777;">No billing records found for this patient.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p style="color: red; font-weight: bold; text-align: center; margin: 40px 0;">
            Invalid Patient ID or Patient not found. Please access this page from the dashboard link.
        </p>
    <?php endif; ?>
</div>

</body>
</html>