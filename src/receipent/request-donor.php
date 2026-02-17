<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

/* fetch areas */
$areas = [];
$res = $conn->query("SELECT id, area_name, city FROM areas ORDER BY area_name");
while ($row = $res->fetch_assoc()) {
    $areas[] = $row;
}

/* fetch blood groups */
$bloodGroups = [];
$bgRes = $conn->query("SELECT id, group_name FROM blood_groups ORDER BY group_name");
while ($bg = $bgRes->fetch_assoc()) {
    $bloodGroups[] = $bg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recipient_id     = $_SESSION['recipient_id'];
    $area_id          = (int)$_POST['area_id'];
    $blood_group_id   = (int)$_POST['blood_group_id'];

    /* get area name */
    $areaRes  = $conn->query("SELECT area_name FROM areas WHERE id = $area_id");
    $areaRow  = $areaRes->fetch_assoc();
    $area_name = $areaRow['area_name'];

    /* get blood group name */
    $bgNameRes = $conn->query("SELECT group_name FROM blood_groups WHERE id = $blood_group_id");
    $bgRow     = $bgNameRes->fetch_assoc();
    $blood_group_name = $bgRow['group_name'];

    $stmt = $conn->prepare("
        INSERT INTO blood_requests
        (
            recipient_id,
            patient_name,
            blood_group,
            blood_group_id,
            units_required,
            hospital_name,
            hospital_location,
            area_id,
            area_name,
            contact_phone,
            urgency_level,
            is_emergency,
            admin_approved,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 'pending')
    ");

    $stmt->bind_param(
        "isssississs",
        $recipient_id,
        $_POST['patient_name'],
        $blood_group_name,
        $blood_group_id,
        $_POST['units'],
        $_POST['hospital'],
        $area_name,     // hospital_location
        $area_id,
        $area_name,
        $_POST['phone'],
        $_POST['urgency']
    );

    $stmt->execute();

    header("Location: dashboard.php?request=submitted");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Create Blood Request</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-10">

<form method="POST" class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">

  <h2 class="text-xl font-bold text-blue-600 mb-4">
    🩸 Create Blood Request
  </h2>

  <input name="patient_name" placeholder="Patient Name" required
         class="w-full mb-3 p-2 border rounded">

  <!-- ✅ BLOOD GROUP FROM DATABASE -->
  <select name="blood_group_id" required
          class="w-full mb-3 p-2 border rounded bg-white">
    <option value="">Select Blood Group</option>
    <?php foreach ($bloodGroups as $bg): ?>
      <option value="<?= $bg['id'] ?>">
        <?= htmlspecialchars($bg['group_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <input name="units" type="number" placeholder="Units Required" required
         class="w-full mb-3 p-2 border rounded">

  <input name="hospital" placeholder="Hospital Name" required
         class="w-full mb-3 p-2 border rounded">

  <!-- ✅ AREA DROPDOWN -->
  <select name="area_id" required
          class="w-full mb-3 p-2 border rounded bg-white">
    <option value="">Select Hospital Area</option>
    <?php foreach ($areas as $a): ?>
      <option value="<?= $a['id'] ?>">
        <?= htmlspecialchars($a['area_name']) ?>, <?= htmlspecialchars($a['city']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <input name="phone" placeholder="Contact Phone" required
         class="w-full mb-3 p-2 border rounded">

  <label class="block text-sm text-gray-600 mb-2">
    Urgency Level
  </label>

  <select name="urgency" required
          class="w-full mb-3 p-2 border rounded bg-white">
    <option value="low">Low</option>
    <option value="medium">Medium</option>
    <option value="high">High</option>
    <option value="critical">Critical</option>
  </select>

  <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
    Submit Request
  </button>

  <p class="text-sm text-gray-500 mt-4">
    Your request will be reviewed and approved by an admin before it becomes active.
  </p>

</form>

</body>
</html>
