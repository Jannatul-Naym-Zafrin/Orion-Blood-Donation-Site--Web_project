<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

$donor_id = (int)$_GET['donor_id'];
$recipient_id = $_SESSION['recipient_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $conn->prepare("
      INSERT INTO donation_requests
      (donor_id, blood_request_id, status)
      VALUES (?, ?, 'sent')
    ");

    $stmt->bind_param(
      "ii",
      $donor_id,
      $_POST['blood_request_id']
    );

    $stmt->execute();
    header("Location: dashboard.php?request=sent");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Send Request</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-10">

<form method="POST" class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow">

  <h2 class="text-xl font-bold text-red-600 mb-4">
    📨 Send Blood Request
  </h2>

  <label class="text-sm text-gray-600">Select Blood Request</label>
  <select name="blood_request_id" required
          class="w-full p-2 border rounded mb-4">
    <?php
      $req = $conn->query("
        SELECT id, patient_name
        FROM blood_requests
        WHERE recipient_id = $recipient_id
          AND status = 'approved'
      ");
      while ($r = $req->fetch_assoc()):
    ?>
      <option value="<?= $r['id'] ?>">
        <?= htmlspecialchars($r['patient_name']) ?>
      </option>
    <?php endwhile; ?>
  </select>

  <button class="w-full bg-red-600 text-white py-2 rounded">
    Send Request
  </button>

</form>

</body>
</html>
