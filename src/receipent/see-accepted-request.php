<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    die("Unauthorized");
}

$donation_id = (int)$_GET['donation_id'];

$data = $conn->query("
    SELECT
        u.full_name AS donor_name,
        d.blood_group,
        br.patient_name,
        br.hospital_name,
        br.hospital_location,
        br.contact_phone,
        dr.accepted_at
    FROM donation_requests dr
    JOIN donors d ON dr.donor_id = d.id
    JOIN users u ON d.user_id = u.id
    JOIN blood_requests br ON dr.blood_request_id = br.id
    WHERE dr.id = $donation_id
")->fetch_assoc();

if (!$data) {
    die('Invalid request');
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Accepted Request Details</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow">
  <h1 class="text-2xl font-bold text-green-600 mb-4">
    ✅ Donor Accepted Your Request
  </h1>

  <p><b>Donor Name:</b> <?= htmlspecialchars($data['donor_name']) ?></p>
  <p><b>Blood Group:</b> <?= $data['blood_group'] ?></p>
  <p><b>Patient:</b> <?= htmlspecialchars($data['patient_name']) ?></p>
  <p><b>Hospital:</b> <?= htmlspecialchars($data['hospital_name']) ?></p>
  <p><b>Location:</b> <?= htmlspecialchars($data['hospital_location']) ?></p>
  <p><b>Contact:</b> <?= htmlspecialchars($data['contact_phone']) ?></p>
  <p class="text-sm text-gray-500 mt-2">
    Accepted at: <?= $data['accepted_at'] ?>
  </p>
</div>

</body>
</html>
