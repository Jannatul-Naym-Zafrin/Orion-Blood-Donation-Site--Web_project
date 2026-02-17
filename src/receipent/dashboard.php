<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}
/* recipient info */
$accepted_requests = $conn->query("
    SELECT
        br.id AS blood_request_id,
        br.patient_name,
        br.blood_group,
        br.units_required,
        br.hospital_name,
        d.blood_group AS donor_blood,
        u.full_name AS donor_name,
        dr.id AS donation_request_id
    FROM donation_requests dr
    JOIN blood_requests br ON dr.blood_request_id = br.id
    JOIN donors d ON dr.donor_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE br.recipient_id = {$_SESSION['recipient_id']}
      AND dr.status = 'accepted'
");

/* recipient info */
$uid = $_SESSION['user_id'];

$q = $conn->query("
    SELECT u.full_name, r.location, r.total_requests, r.active_requests
    FROM users u
    JOIN recipients r ON r.user_id = u.id
    WHERE u.id = $uid
");

$recipient = $q->fetch_assoc();

/* notifications */
$notifications = $conn->query("
    SELECT title, message, created_at
    FROM notifications
    WHERE user_id = $uid AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 5
");

/* accepted emergency requests */
$accepted_emergencies = $conn->query("
    SELECT 
        br.patient_name,
        br.blood_group,
        br.hospital_name,
        br.contact_phone,
        dr.accepted_at,
        u.full_name AS donor_name,
        u.email AS donor_email
    FROM blood_requests br
    JOIN donation_requests dr ON br.id = dr.blood_request_id
    JOIN donors d ON dr.donor_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE br.recipient_id = {$_SESSION['recipient_id']}
      AND br.is_emergency = 1
      AND dr.status = 'accepted'
    ORDER BY dr.accepted_at DESC
    LIMIT 5
");

/* accepted regular blood requests with donor count */
$accepted_requests = $conn->query("
    SELECT
        br.patient_name,
        br.blood_group,
        br.hospital_name,
        br.contact_phone,
        br.created_at,
        COUNT(dr.id) as donor_count,
        GROUP_CONCAT(u.full_name SEPARATOR ', ') as donor_names
    FROM blood_requests br
    JOIN donation_requests dr ON br.id = dr.blood_request_id
    JOIN donors d ON dr.donor_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE br.recipient_id = {$_SESSION['recipient_id']}
      AND br.is_emergency = 0
      AND dr.status = 'accepted'
    GROUP BY br.id
    ORDER BY br.created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Recipient Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<!-- NAVBAR -->
<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
  <h1 class="text-xl font-bold text-red-600">🏥 Orion – Recipient</h1>

  <a href="../auth/logout.php"
     class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
    Logout
  </a>
</nav>

<!-- MAIN -->
<div class="max-w-6xl mx-auto p-6">

  <!-- Welcome -->
  <div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
      Welcome, <?= htmlspecialchars($recipient['full_name']) ?> 👋
    </h2>
    <p class="text-gray-600">
      Location: <?= htmlspecialchars($recipient['location']) ?>
    </p>
  </div>

  <!-- NOTIFICATIONS -->
  <?php if ($notifications->num_rows > 0): ?>
    <div class="mb-6">
      <h3 class="text-lg font-bold text-gray-800 mb-2">🔔 Notifications</h3>
      <div class="space-y-2">
        <?php while ($n = $notifications->fetch_assoc()): ?>
          <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
            <h4 class="font-semibold text-yellow-800"><?= htmlspecialchars($n['title']) ?></h4>
            <p class="text-yellow-700"><?= htmlspecialchars($n['message']) ?></p>
            <p class="text-xs text-yellow-600 mt-1"><?= $n['created_at'] ?></p>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- ACCEPTED EMERGENCY REQUESTS -->
  <?php if ($accepted_emergencies->num_rows > 0): ?>
    <div class="mb-6">
      <h3 class="text-lg font-bold text-green-800 mb-2">✅ Accepted Emergency Requests</h3>
      <div class="space-y-3">
        <?php while ($ae = $accepted_emergencies->fetch_assoc()): ?>
          <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-green-800">Patient: <?= htmlspecialchars($ae['patient_name']) ?></h4>
                <p class="text-green-700 text-sm">Blood Group: <?= $ae['blood_group'] ?> | Hospital: <?= htmlspecialchars($ae['hospital_name']) ?></p>
                <p class="text-green-700 text-sm">Donor: <?= htmlspecialchars($ae['donor_name']) ?> (<?= htmlspecialchars($ae['donor_email']) ?>)</p>
                <p class="text-green-700 text-sm">Contact: <?= htmlspecialchars($ae['contact_phone']) ?></p>
              </div>
              <div class="text-right">
                <p class="text-xs text-green-600">Accepted: <?= date('M d, Y H:i', strtotime($ae['accepted_at'])) ?></p>
                <span class="px-2 py-1 bg-green-200 text-green-800 text-xs font-bold rounded">ACCEPTED</span>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- ACCEPTED BLOOD REQUESTS -->
  <?php if ($accepted_requests->num_rows > 0): ?>
    <div class="mb-6">
      <h3 class="text-lg font-bold text-blue-800 mb-2">✅ Accepted Blood Requests</h3>
      <div class="space-y-3">
        <?php while ($ar = $accepted_requests->fetch_assoc()): ?>
          <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-blue-800">Patient: <?= htmlspecialchars($ar['patient_name']) ?></h4>
                <p class="text-blue-700 text-sm">Blood Group: <?= $ar['blood_group'] ?> | Hospital: <?= htmlspecialchars($ar['hospital_name']) ?></p>
                <p class="text-blue-700 text-sm">Donors: <?= htmlspecialchars($ar['donor_names']) ?></p>
                <p class="text-blue-700 text-sm">Contact: <?= htmlspecialchars($ar['contact_phone']) ?></p>
              </div>
              <div class="text-right">
                <p class="text-xs text-blue-600">Requested: <?= date('M d, Y H:i', strtotime($ar['created_at'])) ?></p>
                <span class="px-2 py-1 bg-blue-200 text-blue-800 text-xs font-bold rounded">
                  <?= $ar['donor_count'] ?> DONOR<?= $ar['donor_count'] > 1 ? 'S' : '' ?> ACCEPTED
                </span>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- STATUS CARDS -->
  <div class="grid md:grid-cols-3 gap-6 mb-10">

    <div class="bg-white p-6 rounded-xl shadow">
      <p class="text-sm text-gray-500">Total Requests</p>
      <p class="text-3xl font-bold text-blue-600">
        <?= $recipient['total_requests'] ?>
      </p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
      <p class="text-sm text-gray-500">Active Requests</p>
      <p class="text-3xl font-bold text-orange-600">
        <?= $recipient['active_requests'] ?>
      </p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
      <p class="text-sm text-gray-500">Status</p>
      <p class="text-lg font-semibold text-green-600">
        Ready to Request
      </p>
    </div>

  </div>

  <!-- ACTION CARD -->
  <!-- SEE ALL DONORS CARD -->
<div class="bg-white rounded-xl shadow p-6 mt-6">
  <h3 class="text-xl font-bold mb-2">🧑‍🩸 Available Donors</h3>
  <p class="text-gray-600 mb-4">
    View all verified donors and send blood requests directly.
  </p>

  <a href="view-donors.php"
     class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
    👀 See All Donors
  </a>
</div>

<!-- EMERGENCY CARD -->
<div class="bg-red-50 border border-red-200 rounded-xl shadow p-6 mt-6">
  <h3 class="text-xl font-bold text-red-700 mb-2">🚨 Emergency Blood Request</h3>
  <p class="text-red-600 mb-4">
    Critical cases only. Admin approval required.
  </p>

  <a href="emergency-request.php"
     class="inline-block bg-red-700 text-white px-6 py-2 rounded-lg hover:bg-red-800">
    🚑 Post Emergency Request
  </a>
</div>

<!-- BLOOD REQUEST CARD -->


</body>
</html>
