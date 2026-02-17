 <?php
// index.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Orion | Blood Donation Network</title>

  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#FFF5F6] font-sans antialiased">

<header class="bg-[#D1001C] text-white py-2 px-6 md:px-20 flex justify-between items-center text-sm">
  <div class="flex gap-6">
    <span>📞 +1 (555) 123-4567</span>
    <span>✉ help@orion.com</span>
  </div>
</header>

<nav class="bg-white py-4 px-6 md:px-20 flex justify-between items-center shadow-sm">
  <div class="flex items-center gap-2">
    <div class="bg-[#D1001C] p-2 rounded-lg">🩸</div>
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Orion</h1>
      <p class="text-[10px] text-gray-500 uppercase tracking-widest">
        Saving Lives Together
      </p>
    </div>
  </div>

  <div class="hidden md:flex gap-8 text-gray-600 font-medium">
    <a href="#" class="hover:text-red-600">About</a>
    <a href="#" class="hover:text-red-600">How to Donate</a>
    <a href="#" class="hover:text-red-600">Who Can Donate</a>
    <a href="#" class="hover:text-red-600">Contact</a>
  </div>

  <!-- LOGIN DROPDOWN -->
  <div class="relative">
    <button onclick="toggleLoginMenu()"
      class="bg-red-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-red-700">
      🔐 Login
    </button>

    <div id="loginMenu"
      class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-50">
      <a href="auth/login.php?role=admin" class="block px-4 py-2 hover:bg-gray-100">👨‍💼 Admin</a>
     <a href="src/donor/register.php"
   class="block px-4 py-2 hover:bg-gray-100">
   ❤️ Donor
</a>



    <a href="src/receipent/login.php"
   class="block px-4 py-2 hover:bg-gray-100">
   🏥 Recipient
</a>

  </div>
</nav>

<script>
function toggleLoginMenu() {
  document.getElementById("loginMenu").classList.toggle("hidden");
}
</script>

</body>
</html>
