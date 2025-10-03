<?php
function adminLayout($children) {
    $currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hospital Management System: Financials</title>

<!-- Favicon -->
<link rel="icon" type="image/jpeg" href="../images/logo.jpg">
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Boxicons CDN -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css" rel="stylesheet">
  <style>
    /* Disable scroll */
    html, body {
      height: 100%;
      overflow: auto;
    }
  </style>
</head>
<body class="bg-white text-slate-800 font-sans flex flex-col min-h-screen">

  <!-- Content Wrapper: Sidebar + Main -->
  <div class="flex flex-1">
    <!-- Sidebar -->
    <aside class="group w-20 hover:w-60 transition-all duration-300 bg-slate-900 flex flex-col py-6 justify-between text-white">
      <div class="flex flex-col items-center group-hover:items-start px-4 space-y-8">

        <!-- Replace with Logo -->
        <div class="flex justify-center items-center w-12 h-12">
          <img src="../images/logo.jpg" alt="Logo" class="w-12 h-12 object-contain rounded-full">
        </div>
        
        <!-- Navigation -->
        <nav class="flex flex-col space-y-4 text-xl w-full">

         <!-- Collections -->
          <a href="collections.php" class="flex items-center space-x-3 px-2 py-2 rounded <?php echo ($currentPage=='collections.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
            <i class='bx bx-credit-card text-lg'></i>
            <span class="text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200">Collections</span>
          </a>

           <!-- Accounts Receivable with Dropdown -->
          <div class="w-full">
            <button onclick="toggleReceivableDropdown()" class="flex items-center justify-between w-full px-2 py-2 hover:text-orange-400 
              <?php echo ($currentPage=='sales_invoices.php' || $currentPage=='accounts_receivable.php') ? 'text-orange-500' : ''; ?>">
              <span class="flex items-center space-x-3">
                <i class='bx bx-download text-lg'></i>
                <span class="text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200">Accounts Receivable</span>
              </span>
              <i class='bx bx-chevron-down opacity-0 group-hover:opacity-100 transition-opacity duration-200'></i>
            </button>

            <!-- Dropdown -->
            <div id="receivableDropdown" class="ml-8 mt-1 hidden flex-col space-y-2">
              <a href="sales_invoices.php" class="block px-2 py-1 text-sm rounded 
                <?php echo ($currentPage=='sales_invoices.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
                Sales Invoices
              </a>
            </div>
          </div>

          <!-- General Ledger with Dropdown -->
          <div class="w-full">
            <button onclick="toggleDropdown()" class="flex items-center justify-between w-full px-2 py-2 hover:text-orange-400 <?php echo ($currentPage=='general_ledger.php' || $currentPage=='chart_of_accounts.php') ? 'text-orange-500' : ''; ?>">
              <span class="flex items-center space-x-3">
                <i class='bx bx-book text-lg'></i>
                <span class="text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200">General Ledger</span>
              </span>
              <i class='bx bx-chevron-down opacity-0 group-hover:opacity-100 transition-opacity duration-200'></i>
            </button>
            <div id="dropdown" class="ml-8 mt-1 hidden flex-col space-y-2">
              <a href="chart_of_accounts.php" class="block px-2 py-1 text-sm rounded <?php echo ($currentPage=='chart_of_accounts.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
                Chart of Accounts
              </a>
            </div>
          </div>

         <!-- Accounts Payable with Dropdown -->
          <div class="w-full">
            <button onclick="togglePayableDropdown()" class="flex items-center justify-between w-full px-2 py-2 hover:text-orange-400 
              <?php echo ($currentPage=='invoices.php' || $currentPage=='payments.php' || $currentPage=='accounts_payable.php') ? 'text-orange-500' : ''; ?>">
              <span class="flex items-center space-x-3">
                <i class='bx bx-money text-lg'></i>
                <span class="text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200">Accounts Payable</span>
              </span>
              <i class='bx bx-chevron-down opacity-0 group-hover:opacity-100 transition-opacity duration-200'></i>
            </button>

            <!-- Dropdown -->
            <div id="payableDropdown" class="ml-8 mt-1 hidden flex-col space-y-2">
           <!--   <a href="invoices.php" class="block px-2 py-1 text-sm rounded 
                <?php echo ($currentPage=='invoices.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
                Invoices
              </a>-->
              <a href="payments.php" class="block px-2 py-1 text-sm rounded 
                <?php echo ($currentPage=='payments.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
                Payments
              </a>
            </div>
          </div>


         


         

          <!-- Disbursement -->
          <a href="disbursement.php" class="flex items-center space-x-3 px-2 py-2 rounded <?php echo ($currentPage=='disbursement.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
            <i class='bx bx-wallet text-lg'></i>
            <span class="text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200">Disbursement</span>
          </a>

         <!-- Budget Management with Dropdown -->
          <div class="w-full">
            <button onclick="toggleBudgetDropdown()" class="flex items-center justify-between w-full px-2 py-2 hover:text-orange-400 <?php echo ($currentPage=='budget_planning.php' || $currentPage=='budget_allocation.php' || $currentPage=='budget_request.php') ? 'text-orange-500' : ''; ?>">
              <span class="flex items-center space-x-3">
                <i class='bx bx-pie-chart-alt-2 text-lg'></i>
                <span class="text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200">Budget Management</span>
              </span>
              <i class='bx bx-chevron-down opacity-0 group-hover:opacity-100 transition-opacity duration-200'></i>
            </button>
            <div id="budgetDropdown" class="ml-8 mt-1 hidden flex-col space-y-2">
              <a href="budget_request.php" class="block px-2 py-1 text-sm rounded <?php echo ($currentPage=='budget_request.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
                Budget Request
              </a>
              <a href="budget_planning.php" class="block px-2 py-1 text-sm rounded <?php echo ($currentPage=='budget_planning.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
                Budget Planning
              </a>
              <a href="budget_allocation.php" class="block px-2 py-1 text-sm rounded <?php echo ($currentPage=='budget_allocation.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
                Budget Allocation
              </a>
              
            </div>
          </div>

          <a href="journal_entry.php" class="flex items-center space-x-3 px-2 py-2 rounded <?php echo ($currentPage=='journal_entry.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
            <i class='bx bx-notepad text-lg'></i>
            <span class="text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200">Journal Entry</span>
          </a>

          <!-- Reports -->
          <a href="reports.php" class="flex items-center space-x-3 px-2 py-2 rounded <?php echo ($currentPage=='reports.php') ? 'bg-orange-500 text-white' : 'hover:bg-slate-800'; ?>">
            <i class='bx bx-bar-chart text-lg'></i>
            <span class="text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200">Reports</span>
          </a>


        </nav>
      </div>

      <!-- Logout -->
      <button class="mb-4 text-sm opacity-50 text-center group-hover:text-left px-4" onclick="logoutUser()">
        <i class='bx bx-log-out'></i> Logout
      </button>
    </aside>

    <!-- Main Content -->
<main class="p-6 w-full">

  <!-- Top Header (inside main) -->
  <div class="bg-white shadow px-6 py-3 flex justify-between items-center mb-6 relative">
    
    <!-- Left: System Title -->
    <div class="text-lg font-bold text-slate-800">
      Hospital Management System: <span class="text-orange-600">Financials</span>
    </div>

    <!-- Center: Search Bar -->
    <div class="flex-1 mx-6 max-w-md">
      <form method="GET" action="">
        <input 
          type="text" 
          name="search" 
          placeholder="Search..." 
          class="w-full border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
        />
      </form>
    </div>

    <!-- Right: Notifications + Profile -->
    <div class="flex items-center space-x-4 relative">
     <!-- Header -->
<div class="relative">
  <button id="notifBtn" class="relative text-2xl">
    <i class='bx bx-bell'></i>
    <span id="notifCount" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">0</span>
  </button>
  
  <!-- Dropdown -->
  <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-lg border">
    <div class="p-2 flex justify-between items-center border-b">
      <span class="font-bold">Notifications</span>
      <button id="markAllBtn" class="text-blue-500 text-sm">Mark all as read</button>
    </div>
    <ul id="notifList" class="max-h-60 overflow-y-auto"></ul>
  </div>
</div>


    

      <!-- Profile Button with Icon -->
      <button onclick="toggleProfileMenu()" class="flex items-center space-x-2 focus:outline-none relative">
        <i class='bx bx-user-circle text-2xl text-slate-700'></i>
      </button>

      <!-- Profile Dropdown -->
      <div id="profileMenu" 
           class="hidden absolute right-5 mt-10 top-2 w-40 bg-white border rounded-lg shadow-lg py-2 z-50">
        <a href="profile.php" 
     class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 cursor-pointer">
    <i class='bx bx-user text-lg'></i> Profile
  </a>
        <a 
           class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 cursor-pointer" onclick="logoutUser()">
          <i class='bx bx-log-out text-lg'></i> Logout
        </a>
      </div>
    </div>
  </div>

  <!-- Children Content (page-specific content will load here) -->
  <div>
    <?php echo $children; ?>
  </div>
</main>


  </div>

  <script>
document.addEventListener("DOMContentLoaded", () => {
  const notifBtn = document.getElementById("notifBtn");
  const notifDropdown = document.getElementById("notifDropdown");
  const notifList = document.getElementById("notifList");
  const notifCount = document.getElementById("notifCount");
  const markAllBtn = document.getElementById("markAllBtn");

  // Toggle dropdown (guard elements exist)
  if (notifBtn && notifDropdown && typeof notifBtn.addEventListener === 'function') {
    notifBtn.addEventListener("click", () => {
      try { notifDropdown.classList.toggle("hidden"); } catch (e) { console.error(e); }
    });
  }

  // Load notifications
  async function loadNotifications() {
  const res = await fetch("https://financial.health-ease-hospital.com/prefect/api/notifications_api.php?action=get_notifications");
    const data = await res.json();

    if (data.success) {
      notifCount.textContent = data.unread_count;
      notifCount.style.display = data.unread_count > 0 ? "block" : "none";

      notifList.innerHTML = data.notifications.map(n => `
        <li class="p-2 border-b flex justify-between items-center ${n.is_read == 0 ? "bg-gray-100" : ""}">
          <div>
            <p>${n.message}</p>
            <small class="text-gray-500">${n.created_at}</small>
          </div>
          <div class="flex gap-2">
            ${n.is_read == 0 ? `
              <button onclick="markRead(${n.id})" title="Mark as read" class="text-blue-500">
                <i class='bx bx-check-circle text-lg'></i>
              </button>` : ""}
            <button onclick="deleteNotif(${n.id})" title="Delete" class="text-red-500">
              <i class='bx bx-trash text-lg'></i>
            </button>
          </div>
        </li>
      `).join("");
    }
  }

  // Mark as read
  window.markRead = async function(id) {
    try {
      const res = await fetch("https://financial.health-ease-hospital.com/prefect/api/notifications_api.php?action=mark_read", {
        method: "POST",
        body: new URLSearchParams({ id })
      });
      const data = await res.json();
      Toastify({
        text: data.message || "Notification marked as read",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#3b82f6"
      }).showToast();
      loadNotifications();
    } catch (e) {
      console.error('markRead error', e);
    }
  }

  // Delete single notification
  window.deleteNotif = async function(id) {
    try {
      const res = await fetch("https://financial.health-ease-hospital.com/prefect/api/notifications_api.php?action=delete", {
        method: "POST",
        body: new URLSearchParams({ id })
      });
      const data = await res.json();
      Toastify({
        text: data.message || "Notification deleted",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#ef4444"
      }).showToast();
      loadNotifications();
    } catch (e) {
      console.error('deleteNotif error', e);
    }
  }

  // Mark all as read (guard exists)
  if (markAllBtn && typeof markAllBtn.addEventListener === 'function') {
    markAllBtn.addEventListener("click", async () => {
      try {
        await fetch("https://financial.health-ease-hospital.com/prefect/api/notifications_api.php?action=mark_read_all", { method: "POST" });
        Toastify({
          text: "All notifications marked as read",
          duration: 3000,
          gravity: "top",
          position: "right",
          backgroundColor: "#10b981"
        }).showToast();
        loadNotifications();
      } catch (e) {
        console.error('mark all error', e);
      }
    });
  }

  // Auto-refresh notifications every 1s (guard loadNotifications exists)
  try { setInterval(loadNotifications, 1000); loadNotifications(); } catch (e) { console.error(e); }
});


   

  function toggleProfileMenu() {
    const profileMenuEl = document.getElementById("profileMenu");
    const notifMenuEl = document.getElementById("notifMenu");
    if (profileMenuEl) profileMenuEl.classList.toggle("hidden");
    if (notifMenuEl) notifMenuEl.classList.add("hidden"); // hide notif if open
  }

  // Close menus if clicked outside (defensive)
  window.addEventListener("click", function(e) {
    try {
      // If a print popup was just opened, ignore clicks to avoid race conditions
      if (window._printing) return;

      // Normalize target so .closest is safe
      let target = e && e.target ? e.target : null;
      if (target && typeof target.closest !== 'function') {
        target = (target.nodeType === Node.TEXT_NODE) ? target.parentElement : target;
      }
      if (!target) return;

      // notifMenu (dropdown) may not exist on all pages — guard access
      const notifMenuEl = document.getElementById("notifDropdown") || document.getElementById("notifMenu");
      if (!target.closest("#notifDropdown") && !target.closest("#notifMenu") && !target.closest("button[onclick='toggleNotifMenu()']")) {
        if (notifMenuEl) {
          try { notifMenuEl.classList.add("hidden"); } catch (e) { /* ignore */ }
        }
      }

      // profileMenu may not exist in some contexts — guard access
      const profileMenuEl = document.getElementById("profileMenu");
      if (!target.closest("#profileMenu") && !target.closest("button[onclick='toggleProfileMenu()']")) {
        if (profileMenuEl) {
          try { profileMenuEl.classList.add("hidden"); } catch (e) { /* ignore */ }
        }
      }
    } catch (err) {
      console.error('global click handler error', err);
    }
  });
  function toggleDropdown() {
    const dropdown = document.getElementById('dropdown');
    dropdown.classList.toggle('hidden');
  }

  function toggleBudgetDropdown() {
    const dropdown = document.getElementById('budgetDropdown');
    dropdown.classList.toggle('hidden');
  }

  // Auto expand General Ledger dropdown if Chart of Accounts is active
  <?php if ($currentPage == 'chart_of_accounts.php'): ?>
    document.addEventListener("DOMContentLoaded", function() {
      document.getElementById('dropdown').classList.remove('hidden');
    });
  <?php endif; ?>

  // Auto expand Budget Management dropdown if any of its pages are active
  <?php if (in_array($currentPage, ['budget_planning.php', 'budget_allocation.php', 'budget_request.php'])): ?>
    document.addEventListener("DOMContentLoaded", function() {
      document.getElementById('budgetDropdown').classList.remove('hidden');
    });
  <?php endif; ?>

  function togglePayableDropdown() {
  const dropdown = document.getElementById('payableDropdown');
  dropdown.classList.toggle('hidden');
}

// Auto expand Accounts Payable dropdown if active
<?php if (in_array($currentPage, ['invoices.php', 'payments.php'])): ?>
  document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('payableDropdown').classList.remove('hidden');
  });
<?php endif; ?>

function toggleReceivableDropdown() {
  const dropdown = document.getElementById('receivableDropdown');
  dropdown.classList.toggle('hidden');
}

// Auto expand Accounts Receivable dropdown if active
<?php if ($currentPage == 'sales_invoices.php'): ?>
  document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('receivableDropdown').classList.remove('hidden');
  });
<?php endif; ?>

async function logoutUser() {
    try {
  const res = await fetch('https://financial.health-ease-hospital.com/prefect/api/logout.php', { method: 'POST' });
        const data = await res.json();
        Toastify({ text: data.message || 'Logged out', duration: 3000 }).showToast();
        if (data.status === 'success') {
            setTimeout(() => window.location.href = 'https://financial.health-ease-hospital.com/prefect', 1000);
        }
    } catch (e) {
        console.error(e);
    }
}


</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>

</body>
</html>

<?php
}
?>
