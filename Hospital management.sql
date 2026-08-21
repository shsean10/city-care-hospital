-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 12, 2026 at 08:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital_management`
--
CREATE DATABASE IF NOT EXISTS `hospital_management`;
USE `hospital_management`;

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointment_id` int(7) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `purpose` varchar(300) NOT NULL,
  `status` enum('Yet to Consult','Consulted') NOT NULL,
  `consultation_note` varchar(500) DEFAULT NULL,
  `patient_id` int(6) DEFAULT NULL,
  `doctor_id` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `appointment_date`, `appointment_time`, `purpose`, `status`, `consultation_note`, `patient_id`, `doctor_id`) VALUES
(7000001, '2026-08-05', '10:00:00', 'Heart Checkup', 'Consulted', 'Blood pressure is normal.', 100001, 10001),
(7000002, '2026-08-06', '11:30:00', 'Headache', 'Yet to Consult', NULL, 100002, 10002),
(7000003, '2026-08-07', '09:15:00', 'Bone Pain', 'Consulted', 'X-ray recommended.', 100003, 10003),
(7000004, '2026-08-08', '10:30:00', 'Skin Allergy', 'Yet to Consult', NULL, 100004, 10004),
(7000005, '2026-08-09', '11:00:00', 'Child Fever', 'Consulted', 'Patient advised to take adequate rest.', 100005, 10005),
(7000006, '2026-08-10', '09:00:00', 'General Checkup', 'Consulted', 'General health condition is satisfactory.', 100006, 10006),
(7000007, '2026-08-11', '12:15:00', 'Pregnancy Checkup', 'Yet to Consult', NULL, 100007, 10007),
(7000008, '2026-08-12', '15:00:00', 'Ear Pain', 'Consulted', 'Ear drops prescribed.', 100008, 10008),
(7000009, '2026-08-13', '16:00:00', 'Eye Examination', 'Yet to Consult', NULL, 100009, 10009);

--
-- Triggers `appointment`
--
DELIMITER $$
CREATE TRIGGER `trg_log_deleted_appointment` AFTER DELETE ON `appointment` FOR EACH ROW BEGIN
    INSERT INTO Appointment_Log
    (
        appointment_id,
        appointment_date,
        appointment_time,
        purpose,
        status,
        consultation_note,
        patient_id,
        doctor_id,
        deletion_datetime
    )
    VALUES
    (
        OLD.appointment_id,
        OLD.appointment_date,
        OLD.appointment_time,
        OLD.purpose,
        OLD.status,
        OLD.consultation_note,
        OLD.patient_id,
        OLD.doctor_id,
        NOW()
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_log`
--

CREATE TABLE `appointment_log` (
  `log_id` int(11) NOT NULL,
  `appointment_id` int(7) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `purpose` varchar(300) NOT NULL,
  `status` enum('Yet to Consult','Consulted') NOT NULL,
  `consultation_note` varchar(500) DEFAULT NULL,
  `patient_id` int(6) DEFAULT NULL,
  `doctor_id` int(5) DEFAULT NULL,
  `deletion_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_log`
--

INSERT INTO `appointment_log` (`log_id`, `appointment_id`, `appointment_date`, `appointment_time`, `purpose`, `status`, `consultation_note`, `patient_id`, `doctor_id`, `deletion_datetime`) VALUES
(1, 7000010, '2026-08-14', '14:30:00', 'Mental Health Consultation', 'Consulted', 'Counseling recommended.', 100010, 10010, '2026-08-12 02:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `bill_id` int(11) NOT NULL,
  `bill_date` date NOT NULL,
  `total_charge` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card','Mobile Banking','Online') NOT NULL,
  `payment_status` enum('Paid','Pending') NOT NULL,
  `patient_id` int(6) DEFAULT NULL,
  `final_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`bill_id`, `bill_date`, `total_charge`, `discount`, `tax`, `payment_method`, `payment_status`, `patient_id`, `final_amount`) VALUES
(5001, '2026-08-05', 6000.00, 500.00, 300.00, 'Cash', 'Paid', 100001, NULL),
(5002, '2026-08-06', 15000.00, 1000.00, 700.00, 'Card', 'Pending', 100002, NULL),
(5003, '2026-08-07', 3500.00, 0.00, 175.00, 'Mobile Banking', 'Paid', 100003, NULL),
(5004, '2026-08-08', 8000.00, 500.00, 400.00, 'Cash', 'Paid', 100004, NULL),
(5005, '2026-08-09', 10000.00, 1000.00, 450.00, 'Card', 'Pending', 100005, NULL),
(5006, '2026-08-10', 7000.00, 200.00, 340.00, 'Online', 'Paid', 100006, NULL),
(5007, '2026-08-11', 25000.00, 2000.00, 1150.00, 'Mobile Banking', 'Paid', 100007, NULL),
(5008, '2026-08-12', 6000.00, 300.00, 285.00, 'Cash', 'Pending', 100008, NULL),
(5009, '2026-08-13', 4500.00, 100.00, 220.00, 'Card', 'Paid', 100009, NULL),
(5010, '2026-08-14', 9000.00, 600.00, 420.00, 'Online', 'Pending', 100010, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(5) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `location` varchar(150) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `head_of_dep` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`, `location`, `contact_number`, `head_of_dep`) VALUES
(101, 'Cardiology', 'Floor 2', '021111111', 'Dr. Ayesha Rahman'),
(102, 'Neurology', 'Floor 3', '022222222', 'Dr. Hasan Ali'),
(103, 'Orthopedics', 'Floor 4', '023333333', 'Dr. Farzana Islam'),
(104, 'Dermatology', 'Floor 5', '024444444', 'Dr. Kamrul Hasan'),
(105, 'Pediatrics', 'Floor 6', '025555555', 'Dr. Sharmin Akter'),
(106, 'Medicine', 'Floor 1', '026666666', 'Dr. Tanvir Ahmed'),
(107, 'Gynecology', 'Floor 7', '027777777', 'Dr. Nasrin Sultana'),
(108, 'ENT', 'Floor 3', '028888888', 'Dr. Imran Hossain'),
(109, 'Ophthalmology', 'Floor 2', '029999999', 'Dr. Sayeed Karim'),
(110, 'Psychiatry', 'Floor 8', '020101010', 'Dr. Liza Rahman');

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `doctor_id` int(5) NOT NULL,
  `doctor_fullname` varchar(50) NOT NULL,
  `specialization` varchar(150) NOT NULL,
  `phone_no` varchar(15) NOT NULL,
  `doctor_email` varchar(100) NOT NULL,
  `consultation_fee` decimal(8,2) NOT NULL,
  `qualification` varchar(150) NOT NULL,
  `joining_date` date NOT NULL,
  `employment_status` enum('Active','On Leave','Resigned','Retired') NOT NULL,
  `department_id` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctor_id`, `doctor_fullname`, `specialization`, `phone_no`, `doctor_email`, `consultation_fee`, `qualification`, `joining_date`, `employment_status`, `department_id`) VALUES
(10001, 'Dr. Ayesha Rahman', 'Cardiology', '01755555555', 'ayesha@hospital.com', 1200.00, 'MBBS, MD', '2020-01-10', 'Active', 101),
(10002, 'Dr. Hasan Ali', 'Neurology', '01766666666', 'hasan@hospital.com', 1500.00, 'MBBS, FCPS', '2019-03-15', 'Active', 102),
(10003, 'Dr. Farzana Islam', 'Orthopedics', '01777777777', 'farzana@hospital.com', 1000.00, 'MBBS, MS', '2021-07-01', 'On Leave', 103),
(10004, 'Dr. Kamrul Hasan', 'Dermatology', '01710101010', 'kamrul@hospital.com', 900.00, 'MBBS, DDV', '2018-04-11', 'Active', 104),
(10005, 'Dr. Sharmin Akter', 'Pediatrics', '01720202020', 'sharmin@hospital.com', 1100.00, 'MBBS, DCH', '2020-08-15', 'Active', 105),
(10006, 'Dr. Tanvir Ahmed', 'Medicine', '01730303030', 'tanvir@hospital.com', 800.00, 'MBBS, FCPS', '2019-01-25', 'Resigned', 106),
(10007, 'Dr. Nasrin Sultana', 'Gynecology', '01740404040', 'nasrin@hospital.com', 1300.00, 'MBBS, FCPS', '2022-03-14', 'Active', 107),
(10008, 'Dr. Imran Hossain', 'ENT', '01750505050', 'imran@hospital.com', 1000.00, 'MBBS, MS', '2021-11-20', 'On Leave', 108),
(10009, 'Dr. Sayeed Karim', 'Ophthalmology', '01760606060', 'sayeed@hospital.com', 950.00, 'MBBS, DO', '2017-05-18', 'Retired', 109),
(10010, 'Dr. Liza Rahman', 'Psychiatry', '01770707070', 'liza@hospital.com', 1400.00, 'MBBS, MD', '2023-02-01', 'Active', 110);

-- --------------------------------------------------------

--
-- Table structure for table `nurse`
--

CREATE TABLE `nurse` (
  `nurse_id` int(11) NOT NULL,
  `nurse_fullname` varchar(100) NOT NULL,
  `nurse_phone_no` varchar(11) NOT NULL,
  `nurse_email` varchar(100) NOT NULL,
  `nurse_qualification` varchar(100) NOT NULL,
  `nurse_joining_date` date NOT NULL,
  `nurse_employment_status` enum('Active','On Leave','Resigned','Retired') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nurse`
--

INSERT INTO `nurse` (`nurse_id`, `nurse_fullname`, `nurse_phone_no`, `nurse_email`, `nurse_qualification`, `nurse_joining_date`, `nurse_employment_status`) VALUES
(201, 'Shila Akter', '01788888888', 'shila@hospital.com', 'Diploma in Nursing', '2021-05-10', 'Active'),
(202, 'Rina Sultana', '01799999999', 'rina@hospital.com', 'BSc in Nursing', '2022-02-20', 'Active'),
(203, 'Mita Khatun', '01712345678', 'mita@hospital.com', 'Diploma in Nursing', '2020-11-15', 'On Leave'),
(204, 'Jannatul Ferdous', '01711112222', 'jannat@hospital.com', 'BSc in Nursing', '2021-08-01', 'Active'),
(205, 'Nadia Akter', '01722223333', 'nadia@hospital.com', 'Diploma in Nursing', '2020-06-15', 'Active'),
(206, 'Rokeya Begum', '01733334444', 'rokeya@hospital.com', 'BSc in Nursing', '2019-09-12', 'On Leave'),
(207, 'Sonia Islam', '01744445555', 'sonia@hospital.com', 'Diploma in Nursing', '2022-01-20', 'Active'),
(208, 'Taslima Khatun', '01755556666', 'taslima@hospital.com', 'BSc in Nursing', '2018-12-01', 'Retired'),
(209, 'Farzana Yasmin', '01766667777', 'farzana.n@hospital.com', 'MSc in Nursing', '2023-02-10', 'Active'),
(210, 'Ruma Akter', '01777778888', 'ruma@hospital.com', 'Diploma in Nursing', '2021-10-05', 'Resigned');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patient_id` int(6) NOT NULL,
  `patient_fullname` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` varchar(150) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `Emergency_contact` varchar(20) NOT NULL,
  `registration_date` date NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `patient_fullname`, `date_of_birth`, `gender`, `address`, `phone_number`, `Emergency_contact`, `registration_date`, `blood_group`) VALUES
(100001, 'Rahman Uddin', '1998-05-15', 'Male', 'Dhaka', '01711111111', '01811111111', '2026-08-01', 'A+'),
(100002, 'Nusrat Jahan', '2001-09-22', 'Female', 'Chattogram', '01722222222', '01822222222', '2026-08-02', 'O+'),
(100003, 'Sabbir Ahmed', '1996-12-10', 'Male', 'Khulna', '01733333333', '01833333333', '2026-08-03', 'B+'),
(100004, 'Rahman Hasan', '1999-03-12', 'Male', 'Rajshahi', '01744444444', '01844444444', '2026-08-04', 'AB+'),
(100005, 'Tania Sultana', '2000-07-18', 'Female', 'Sylhet', '01755555556', '01855555556', '2026-08-05', 'O-'),
(100006, 'Mahmudul Islam', '1995-11-27', 'Male', 'Barishal', '01766666667', '01866666667', '2026-08-06', 'A-'),
(100007, 'Sadia Akter', '2002-02-09', 'Female', 'Cumilla', '01777777778', '01877777778', '2026-08-07', 'B-'),
(100008, 'Rakib Hossain', '1997-08-21', 'Male', 'Rangpur', '01788888889', '01888888889', '2026-08-08', 'AB-'),
(100009, 'Mim Chowdhury', '2001-06-30', 'Female', 'Mymensingh', '01799999990', '01899999990', '2026-08-09', 'A+'),
(100010, 'Rahman Ahmed', '1998-10-05', 'Male', 'Noakhali', '01611111111', '01911111111', '2026-08-10', 'O+');

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `room_id` int(11) NOT NULL,
  `room_number` varchar(4) NOT NULL,
  `room_type` enum('General','Cabin','ICU','CCU','NICU','Emergency','VIP') NOT NULL,
  `capacity` int(11) NOT NULL,
  `daily_charge` decimal(10,2) NOT NULL,
  `availability` enum('Available','Occupied','Reserved','Maintenance') NOT NULL,
  `patient_id` int(6) DEFAULT NULL,
  `admission_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`room_id`, `room_number`, `room_type`, `capacity`, `daily_charge`, `availability`, `patient_id`, `admission_date`) VALUES
(1, '101', 'General', 4, 1500.00, 'Available', NULL, NULL),
(2, '201', 'Cabin', 1, 5000.00, 'Occupied', 100001, '2026-08-15'),
(3, '301', 'ICU', 1, 12000.00, 'Available', NULL, NULL),
(4, '102', 'General', 4, 1500.00, 'Available', NULL, NULL),
(5, '202', 'Cabin', 1, 5000.00, 'Available', NULL, NULL),
(6, '302', 'ICU', 1, 12000.00, 'Available', NULL, NULL),
(7, '401', 'CCU', 2, 15000.00, 'Available', NULL, NULL),
(8, '501', 'NICU', 3, 18000.00, 'Available', NULL, NULL),
(9, '601', 'VIP', 1, 20000.00, 'Available', NULL, NULL),
(10, '701', 'Emergency', 5, 3000.00, 'Available', NULL, NULL);

--
-- Triggers `room`
--
DELIMITER $$
CREATE TRIGGER `trg_check_room_availability` BEFORE UPDATE ON `room` FOR EACH ROW BEGIN
    IF OLD.availability <> 'Available'
       AND NEW.patient_id IS NOT NULL THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Admission Failed: Room is not available.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `treatments`
--

CREATE TABLE `treatments` (
  `treatment_id` int(8) NOT NULL,
  `diagnosis_details` varchar(500) NOT NULL,
  `prescribed_medicines` varchar(500) NOT NULL,
  `treatment_date` date NOT NULL,
  `follow_up_instructions` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `fk_appointment_patient` (`patient_id`),
  ADD KEY `fk_appointment_doctor` (`doctor_id`);

--
-- Indexes for table `appointment_log`
--
ALTER TABLE `appointment_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `fk_billing_patient` (`patient_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD UNIQUE KEY `contact_number` (`contact_number`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `phone_no` (`phone_no`),
  ADD UNIQUE KEY `doctor_email` (`doctor_email`),
  ADD KEY `fk_doctor_department` (`department_id`);

--
-- Indexes for table `nurse`
--
ALTER TABLE `nurse`
  ADD PRIMARY KEY (`nurse_id`),
  ADD UNIQUE KEY `nurse_phone_no` (`nurse_phone_no`),
  ADD UNIQUE KEY `nurse_email` (`nurse_email`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD UNIQUE KEY `Emergency_contact` (`Emergency_contact`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `fk_room_patient` (`patient_id`);

--
-- Indexes for table `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`treatment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment_log`
--
ALTER TABLE `appointment_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `fk_appointment_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`),
  ADD CONSTRAINT `fk_appointment_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`);

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `fk_billing_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`);

--
-- Constraints for table `doctor`
--
ALTER TABLE `doctor`
  ADD CONSTRAINT `fk_doctor_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`);

--
-- Constraints for table `room`
--
ALTER TABLE `room`
  ADD CONSTRAINT `fk_room_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;