<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* ==========================
   DONOR VERIFICATION DATA
========================== */
$donors = $conn->query("
    SELECT
        d.id AS donor_id,
        u.full_name,
        u.email,
        d.location,
        a.area_name,
        bg.group_name AS blood_group,
        dv.status AS verification_status
    FROM donors d
    JOIN users u ON d.user_id = u.id
    LEFT JOIN areas a ON d.area_id = a.id
    LEFT JOIN blood_groups bg ON d.blood_group_id = bg.id
    JOIN donor_verifications dv ON dv.donor_id = d.id
    ORDER BY dv.status ASC, d.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-slate-50 min-h-screen">

<!-- HEADER -->
<header class="bg-white border-b sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
    <div class="flex items-center gap-3">
      <div class="bg-red-600 p-2 rounded-xl">
        <i data-lucide="droplet" class="text-white w-5 h-5"></i>
      </div>
      <span class="text-xl font-bold text-gray-800">Orion Admin</span>
    </div>

    <a href="../auth/logout.php"
       class="text-sm text-gray-500 hover:text-red-600 flex items-center gap-2">
      <i data-lucide="log-out" class="w-4 h-4"></i> Logout
    </a>
  </div>
</header>

<main class="max-w-7xl mx-auto p-6">

<!-- ==========================
     DONOR VERIFICATION
========================== -->
<h1 class="text-2xl font-bold text-gray-800 mb-6">
  🧑‍🩸 Donor Verification
</h1>

<div class="space-y-6">
<?php while ($d = $donors->fetch_assoc()): ?>
<?php
  $pending = $d['verification_status'] === 'pending';

  // Blood group color map
  $bgColor = match($d['blood_group']) {
    'A+', 'A-'  => 'bg-blue-100 text-blue-700',
    'B+', 'B-'  => 'bg-green-100 text-green-700',
    'O+', 'O-'  => 'bg-red-100 text-red-700',
    'AB+', 'AB-' => 'bg-purple-100 text-purple-700',
    default     => 'bg-slate-100 text-slate-700'
  };
?>
<div class="bg-white border rounded-2xl p-6 shadow-sm">
  <div class="flex justify-between items-start">

    <!-- LEFT -->
    <div>
      <h3 class="font-bold text-gray-800">
        <?= htmlspecialchars($d['full_name']) ?>
        <span class="text-xs text-gray-400">
          (Donor ID <?= $d['donor_id'] ?>)
        </span>
      </h3>

      <p class="text-sm text-gray-500">
        <?= htmlspecialchars($d['email']) ?>
      </p>

      <div class="flex flex-wrap gap-2 mt-3">

        <!-- BLOOD GROUP -->
        <span class="px-2 py-1 rounded text-xs font-bold <?= $bgColor ?>">
          🩸 <?= $d['blood_group'] ?? 'N/A' ?>
        </span>

        <!-- VERIFICATION STATUS -->
        <span class="px-2 py-1 rounded text-xs font-bold
          <?= $pending
              ? 'bg-yellow-100 text-yellow-700'
              : 'bg-green-100 text-green-700' ?>">
          <?= strtoupper($d['verification_status']) ?>
        </span>

        <!-- AREA -->
        <?php if ($d['area_name']): ?>
        <span class="px-2 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700">
          📍 <?= htmlspecialchars($d['area_name']) ?>
        </span>
        <?php endif; ?>

      </div>
    </div>

    <!-- RIGHT -->
    <div class="text-right">
      <p class="text-xs text-gray-400 mb-3">
        <?= htmlspecialchars($d['location']) ?>
      </p>

      <?php if ($pending): ?>
        <a href="approve-donor.php?id=<?= $d['donor_id'] ?>"
           class="inline-block bg-emerald-600 text-white px-4 py-2 rounded-xl
                  text-sm font-bold hover:bg-emerald-700">
          ✅ Approve
        </a>
      <?php else: ?>
        <span class="text-green-600 font-bold text-sm">
          ✔ Verified
        </span>
      <?php endif; ?>
    </div>

  </div>
</div>
<?php endwhile; ?>
</div>

</main>

<script>
  lucide.createIcons();
</script>

</body>
</html>
