<?php
session_start();
include '../layout/adminLayout.php';
include '../api/db.php'; // adjust if your db.php path is different


$userId   = $_SESSION['id'];
$username = $_SESSION['name'] ?? "";
$email    = $_SESSION['email'] ?? "";

$message = "";
$success = false;

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? "");
    $newEmail    = trim($_POST['email'] ?? "");
    $newPassword = trim($_POST['password'] ?? "");

    if ($newUsername && $newEmail) {
        if ($newPassword) {
            // Update with password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $newUsername, $newEmail, $hashedPassword, $userId);
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $newUsername, $newEmail, $userId);
        }

        if ($stmt->execute()) {
            $_SESSION['name']  = $newUsername;
            $_SESSION['email'] = $newEmail;
            $username = $newUsername;
            $email    = $newEmail;

            $success = true;
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile.";
        }
        $stmt->close();
    } else {
        $message = "Username and Email are required.";
    }
}

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen bg-gray-50">

 

  <!-- Profile Card -->
  <div class="bg-white shadow-xl rounded-2xl p-8 max-w-xl mx-auto">
    <div class="flex items-center mb-6">
      <div class="w-16 h-16 bg-blue-100 text-blue-600 flex items-center justify-center rounded-full text-2xl font-bold">
        '.strtoupper(substr($username,0,1)).'
      </div>
      <div class="ml-4">
        <h2 class="text-lg font-semibold">'.$username.'</h2>
        <p class="text-sm text-gray-500">'.$email.'</p>
      </div>
    </div>

    <form method="POST" class="space-y-6">
      <!-- Username -->
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
        <input type="text" id="username" name="username" value="'.$username.'" 
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-3" required />
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" name="email" value="'.$email.'" 
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-3" required />
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <div class="relative mt-1">
          <input type="password" id="password" name="password" placeholder="Enter new or current password"
                 class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-3 pr-10" />
          <button type="button" onclick="togglePassword()" 
                  class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
            <i class="bx bx-show text-xl" id="toggleIcon"></i>
          </button>
        </div>
        <p class="text-xs text-gray-500 mt-1">Leave blank if you don\'t want to change password.</p>
      </div>

      <!-- Buttons -->
      <div class="flex justify-end gap-4 pt-4">
        <button type="reset" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md">Save Changes</button>
      </div>
    </form>
  </div>
</main>

<script>
function togglePassword() {
  const pass = document.getElementById("password");
  const icon = document.getElementById("toggleIcon");
  if (pass.type === "password") {
    pass.type = "text";
    icon.classList.remove("bx-show");
    icon.classList.add("bx-hide");
  } else {
    pass.type = "password";
    icon.classList.remove("bx-hide");
    icon.classList.add("bx-show");
  }
}
</script>

'.($message ? '
<script>
Toastify({
  text: "'.$message.'",
  duration: 3000,
  gravity: "top",
  position: "right",
  backgroundColor: "'.($success ? 'green' : 'red').'",
}).showToast();
</script>
' : '').'
';

adminLayout($children);
?>
