<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

/* ================= DONOR INFO ================= */
$q = $conn->query("
    SELECT 
        u.full_name,
        u.email,
        COALESCE(bg.group_name, d.blood_group) AS blood_group,
        d.blood_group_id,
        d.location,
        d.is_active,
        d.total_donations,
        d.last_donation_date,
        d.id AS donor_id
    FROM users u
    JOIN donors d ON d.user_id = u.id
    LEFT JOIN blood_groups bg ON d.blood_group_id = bg.id
    WHERE u.id = $uid
");
$donor = $q->fetch_assoc();

if (!$donor) {
    echo 'Donor record not found';
    exit;
}

/* ================= EMERGENCY REQUESTS ================= */
$emergency_requests = $conn->query("
    SELECT 
        br.id,
        u.full_name AS recipient_name,
        br.patient_name,
        bg.group_name AS blood_group,
        br.units_required,
        br.hospital_name,
        br.hospital_location,
        br.contact_phone,
        br.created_at
    FROM blood_requests br
    JOIN recipients r ON br.recipient_id = r.id
    JOIN users u ON r.user_id = u.id
    JOIN blood_groups bg ON br.blood_group_id = bg.id
    WHERE br.is_emergency = 1
      AND br.admin_approved = 1
      AND br.status = 'approved'
      AND br.blood_group_id = {$donor['blood_group_id']}
      AND NOT EXISTS (
          SELECT 1 FROM donation_requests dr
          WHERE dr.blood_request_id = br.id
            AND dr.donor_id = {$donor['donor_id']}
      )
    ORDER BY br.created_at DESC
");

/* ================= DIRECT REQUESTS ================= */
$blood_requests = $conn->query("
    SELECT
        br.id,
        u.full_name AS recipient_name,
        br.patient_name,
        bg.group_name AS blood_group,
        br.units_required,
        br.hospital_name,
        br.hospital_location,
        br.contact_phone,
        br.urgency_level,
        br.created_at,
        dr.id AS donation_request_id
    FROM blood_requests br
    JOIN recipients r ON br.recipient_id = r.id
    JOIN users u ON r.user_id = u.id
    JOIN donation_requests dr ON dr.blood_request_id = br.id
    JOIN blood_groups bg ON br.blood_group_id = bg.id
    WHERE br.is_emergency = 0
      AND br.admin_approved = 1
      AND dr.donor_id = {$donor['donor_id']}
      AND dr.status = 'sent'
    ORDER BY br.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donor Dashboard | Orion</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-50 min-h-screen">

<!-- NAVBAR -->
<nav class="bg-white shadow sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">

    <div class="flex items-center gap-3">
      <div class="bg-red-600 p-2 rounded-xl">
        <i data-lucide="droplet" class="text-white w-6 h-6"></i>
      </div>
      <span class="text-2xl font-semibold text-red-600">Orion</span>
    </div>

    <div class="flex items-center gap-4">
      <span class="text-gray-700 font-medium">
        <?= htmlspecialchars($donor['full_name']) ?>
      </span>

      <span class="px-3 py-1 rounded-full text-sm
        <?= $donor['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= $donor['is_active'] ? 'Active' : 'Inactive' ?>
      </span>

      <a href="../auth/logout.php"
         class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 rounded-lg">
        <i data-lucide="log-out" class="w-4 h-4"></i>
        Logout
      </a>
    </div>

  </div>
</nav>

<!-- MAIN -->
<div class="max-w-7xl mx-auto px-4 py-8">

  <!-- Welcome -->
  <div class="mb-8">
    <h1 class="text-3xl font-semibold text-gray-900 mb-1">
      Welcome back, <?= htmlspecialchars($donor['full_name']) ?> 👋
    </h1>
    <p class="text-gray-600">
      📍 Location: <?= htmlspecialchars($donor['location']) ?>
    </p>
  </div>

  <!-- STATS -->
  <div class="grid md:grid-cols-3 gap-6 mb-12">

    <div class="bg-white border rounded-2xl p-6">
      <h3 class="text-sm text-gray-500">Blood Group</h3>
      <p class="text-3xl font-bold text-red-600">
        <?= htmlspecialchars($donor['blood_group'] ?? 'N/A') ?>
      </p>
    </div>

    <div class="bg-white border rounded-2xl p-6">
      <h3 class="text-sm text-gray-500">Total Donations</h3>
      <p class="text-3xl font-bold text-green-600">
        <?= (int)$donor['total_donations'] ?>
      </p>
    </div>

    <div class="bg-white border rounded-2xl p-6">
      <h3 class="text-sm text-gray-500">Last Donation</h3>
      <p class="text-lg font-semibold text-gray-800">
        <?= $donor['last_donation_date'] ?: 'Not yet donated' ?>
      </p>
    </div>

  </div>

  <!-- ACTION -->
  <div class="bg-white rounded-2xl shadow p-6 flex justify-between items-center">
    <div>
      <h3 class="text-xl font-bold mb-1">Manage Availability</h3>
      <p class="text-gray-600">
        Change your active / inactive status anytime
      </p>
    </div>

    <a href="settings.php"
       class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      <i data-lucide="settings" class="w-4 h-4"></i>
      Settings
    </a>
  </div>

</div>

<script>
  lucide.createIcons();
</script>

</body>
</html>

