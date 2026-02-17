<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

/*
  ✅ FIXED QUERY
  - blood_groups table JOIN করা হয়েছে
  - donors.blood_group_id ব্যবহার করা হচ্ছে
*/
$donors = $conn->query("
  SELECT 
    d.id AS donor_id,
    u.full_name,
    bg.group_name AS blood_group,
    d.location,
    d.total_donations,
    d.last_donation_date
  FROM donors d
  JOIN users u ON d.user_id = u.id
  JOIN donor_verifications dv ON dv.donor_id = d.id
  LEFT JOIN blood_groups bg ON d.blood_group_id = bg.id
  WHERE dv.status = 'approved'
    AND d.is_active = 1
  ORDER BY d.total_donations DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Available Donors</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<!-- HEADER -->
<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
  <h1 class="text-xl font-bold text-red-600">🧑‍🩸 Available Donors</h1>
  <a href="dashboard.php" class="text-sm text-blue-600 font-bold">
    ← Back
  </a>
</nav>

<!-- DONOR LIST -->
<div class="max-w-6xl mx-auto p-6 space-y-4">

<?php if ($donors->num_rows === 0): ?>
  <div class="bg-white p-6 rounded-xl shadow text-center text-gray-500">
    No approved donors available right now.
  </div>
<?php endif; ?>

<?php while ($d = $donors->fetch_assoc()): ?>

  <div class="bg-white p-6 rounded-xl shadow flex justify-between items-center">

    <div>
      <h3 class="font-bold text-gray-800 text-lg">
        <?= htmlspecialchars($d['full_name']) ?>
      </h3>

      <p class="text-sm text-gray-700 font-semibold">
        🩸 <?= htmlspecialchars($d['blood_group'] ?? 'N/A') ?>
      </p>

      <p class="text-sm text-gray-500">
        📍 <?= htmlspecialchars($d['location']) ?>
      </p>

      <p class="text-xs text-gray-400 mt-1">
        Total Donations: <?= (int)$d['total_donations'] ?>
      </p>

      <p class="text-xs text-gray-400">
        Last Donation:
        <?= $d['last_donation_date'] ?? 'Not yet donated' ?>
      </p>
    </div>

    <a href="request-donor.php?donor_id=<?= $d['donor_id'] ?>"
       class="bg-red-600 text-white px-5 py-2 rounded-lg
              hover:bg-red-700 text-sm font-bold">
      📨 Request
    </a>

  </div>

<?php endwhile; ?>

</div>

</body>
</html>
