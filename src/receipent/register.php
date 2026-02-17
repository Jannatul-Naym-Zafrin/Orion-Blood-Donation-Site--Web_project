<?php
include "../../config/db.php";

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = $_POST['name'];
    $email = $_POST['email'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $loc   = $_POST['location'];

    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $msg = "❌ Email already exists";
    } else {

        // users table (recipient = auto approved)
        $conn->query("
            INSERT INTO users (full_name,email,password_hash,role,status)
            VALUES ('$name','$email','$pass','recipient','approved')
        ");
        $user_id = $conn->insert_id;

        // recipients table
        $conn->query("
            INSERT INTO recipients (user_id,location)
            VALUES ($user_id,'$loc')
        ");

        $msg = "✅ Registration successful. You can now log in.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Recipient Registration</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-red-50 to-red-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-8">

  <h2 class="text-3xl font-bold text-center text-red-600 mb-2">
    🏥 Recipient Registration
  </h2>

  <p class="text-center text-sm text-gray-600 mb-6">
    Register to request blood instantly
  </p>

  <?php if ($msg): ?>
    <div class="mb-4 p-3 rounded text-center
        <?= str_contains($msg, '❌') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
      <?= $msg ?>
    </div>
  <?php endif; ?>

  <!-- REGISTRATION FORM -->
  <form method="POST" class="space-y-4">

    <input
      name="name"
      placeholder="Full Name"
      required
      class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-red-200"
    >

    <input
      type="email"
      name="email"
      placeholder="Email Address"
      required
      class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-red-200"
    >

    <input
      type="password"
      name="password"
      placeholder="Password"
      required
      class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-red-200"
    >

    <input
      name="location"
      placeholder="Your Location / Area"
      required
      class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-red-200"
    >

    <button
      class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold
             hover:bg-red-700 transition">
      Register
    </button>
  </form>

  <!-- LOGIN LINK -->
  <hr class="my-6">

  <p class="text-center text-sm text-gray-600">
    Already registered?
    <a href="login.php"
       class="text-red-600 font-semibold hover:underline">
      Login here
    </a>
  </p>

</div>

</body>
</html>
