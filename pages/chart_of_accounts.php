<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">
  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Chart of Accounts</h1>
      <p class="text-sm text-slate-500">Manage financial account records and categories.</p>
    </div>
    <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Add Account
    </button>
  </div>

  <!-- Summary Section -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div>
        <p class="text-sm text-slate-600 font-medium">Total Assets</p>
        <p id="totalAssets" class="text-2xl font-bold text-green-600 mt-2">₱0.00</p>
      </div>
      <div>
        <p class="text-sm text-slate-600 font-medium">Total Liabilities</p>
        <p id="totalLiabilities" class="text-2xl font-bold text-red-600 mt-2">₱0.00</p>
      </div>
      <div>
        <p class="text-sm text-slate-600 font-medium">Total Equity</p>
        <p id="totalEquity" class="text-2xl font-bold text-blue-600 mt-2">₱0.00</p>
      </div>
    </div>
  </div>

  <!-- Accounts Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <h3 class="text-lg font-semibold mb-4">List of Accounts</h3>
    <div class="overflow-x-auto">
      <table id="accountsTable" class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs font-semibold">
            <th class="px-4 py-3 border border-slate-200">#</th>
            <th class="px-4 py-3 border border-slate-200">Account Code</th>
            <th class="px-4 py-3 border border-slate-200">Account Name</th>
            <th class="px-4 py-3 border border-slate-200">Account Type</th>
            <th class="px-4 py-3 border border-slate-200">Category</th>
            <th class="px-4 py-3 border border-slate-200">Description</th>
            <th class="px-4 py-3 border border-slate-200 text-center">Action</th>
          </tr>
        </thead>
        <tbody id="accountsBody" class="divide-y divide-slate-200">
          <!-- Dynamic rows load here -->
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-screen overflow-y-auto p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add New Account</h2>
    <form id="addAccountForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-600">Account Code</label>
        <input type="text" name="account_code" required class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Account Name</label>
        <input type="text" name="account_name" required class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Account Type</label>
        <select name="account_type" required class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">-- Select --</option>
          <option value="Asset">Asset</option>
          <option value="Liability">Liability</option>
          <option value="Equity">Equity</option>
          <option value="Revenue">Revenue</option>
          <option value="Expense">Expense</option>
          <option value="Income">Income</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Category</label>
        <select name="category" required class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">-- Select --</option>
          <option value="Current Asset">Current Asset</option>
          <option value="Fixed Asset">Fixed Asset</option>
          <option value="Current Liability">Current Liability</option>
          <option value="Long-Term Liability">Long-Term Liability</option>
          <option value="Owner\'s Equity">Owner\'s Equity</option>
          <option value="Operating Revenue">Operating Revenue</option>
          <option value="Operating Expense">Operating Expense</option>
          <option value="COGS">COGS</option>
          <option value="Non-operating Expense">Non-operating Expense</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Description</label>
        <textarea name="description" rows="2" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Balance</label>
        <input type="number" step="0.01" name="balance" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00" value="0.00">
      </div>
      <div class="flex justify-end space-x-2 pt-4">
        <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-screen overflow-y-auto p-6 relative">
    <h2 class="text-xl font-bold mb-4">Edit Account</h2>
    <form id="editAccountForm" class="space-y-4">
      <input type="hidden" name="id">
      <div>
        <label class="block text-sm font-medium text-slate-600">Account Code</label>
        <input type="text" name="account_code" required class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Account Name</label>
        <input type="text" name="account_name" required class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Account Type</label>
        <select name="account_type" required class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Asset">Asset</option>
          <option value="Liability">Liability</option>
          <option value="Equity">Equity</option>
          <option value="Revenue">Revenue</option>
          <option value="Expense">Expense</option>
          <option value="Income">Income</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Category</label>
        <select name="category" required class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Current Asset">Current Asset</option>
          <option value="Fixed Asset">Fixed Asset</option>
          <option value="Current Liability">Current Liability</option>
          <option value="Long-Term Liability">Long-Term Liability</option>
          <option value="Owner\'s Equity">Owner\'s Equity</option>
          <option value="Operating Revenue">Operating Revenue</option>
          <option value="Operating Expense">Operating Expense</option>
          <option value="COGS">COGS</option>
          <option value="Non-operating Expense">Non-operating Expense</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Description</label>
        <textarea name="description" rows="2" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Balance</label>
        <input type="number" step="0.01" name="balance" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00">
      </div>
      <div class="flex justify-end space-x-2 pt-4">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-screen overflow-y-auto p-6 relative">
    <h2 class="text-lg font-bold mb-4 text-red-600">Delete Account</h2>
    <p id="deleteMessage" class="text-sm text-slate-600 mb-6">Are you sure you want to delete this account?</p>
    <div class="flex justify-end space-x-2">
      <button onclick="closeDeleteModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Cancel</button>
      <button onclick="deleteAccount()" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
    </div>
  </div>
</div>

';

adminLayout($children);
?>

<!-- Toastify -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<script>
  const API_URL = "http://localhost/financial2/api/chart_of_accounts_api.php";

  // Toast Function
  function showToast(message, type) {
    Toastify({
      text: message,
      style: {
        background: type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)"
      },
      duration: 3000,
      close: true
    }).showToast();
  }

  // Load Accounts
  async function loadAccounts() {
    try {
      const res = await fetch(API_URL);
      const data = await res.json();

      const tbody = document.getElementById("accountsBody");
      tbody.innerHTML = "";

      let totalAssets = 0, totalLiabilities = 0, totalEquity = 0;
      let rowNum = 1;

      data.forEach(acc => {
        // Calculate totals based on category
        const cat = (acc.category || "").toLowerCase();
        const bal = parseFloat(acc.balance || 0);
        if (cat.includes("asset")) totalAssets += bal;
        else if (cat.includes("liabil")) totalLiabilities += bal;
        else if (cat.includes("equity")) totalEquity += bal;

        tbody.innerHTML += `
          <tr class="hover:bg-slate-50 transition">
            <td class="px-4 py-3 border border-slate-200 text-center text-slate-600">${rowNum}</td>
            <td class="px-4 py-3 border border-slate-200 text-orange-600 font-semibold">${acc.account_code}</td>
            <td class="px-4 py-3 border border-slate-200">${acc.account_name}</td>
            <td class="px-4 py-3 border border-slate-200">${acc.account_type || ""}</td>
            <td class="px-4 py-3 border border-slate-200">${acc.category}</td>
            <td class="px-4 py-3 border border-slate-200">${acc.description || ""}</td>
            <td class="px-4 py-3 border border-slate-200 text-center">
              <button onclick="editAccount(${acc.id})" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                <i class="bx bx-edit text-lg"></i>
              </button>
            </td>
          </tr>
        `;
        rowNum++;
      });

      // Update totals
      document.getElementById("totalAssets").innerText = "₱" + totalAssets.toLocaleString("en-PH", { minimumFractionDigits: 2 });
      document.getElementById("totalLiabilities").innerText = "₱" + totalLiabilities.toLocaleString("en-PH", { minimumFractionDigits: 2 });
      document.getElementById("totalEquity").innerText = "₱" + totalEquity.toLocaleString("en-PH", { minimumFractionDigits: 2 });
    } catch (err) {
      console.error("Error loading accounts:", err);
      showToast("Failed to load accounts", "error");
    }
  }

  // Add Account
  document.getElementById("addAccountForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(this).entries());

    try {
      const res = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
      });

      const result = await res.json();
      if (result.success) {
        showToast("Account added successfully!", "success");
        closeAddModal();
        this.reset();
        loadAccounts();
      } else {
        showToast(result.message || "Failed to add account", "error");
      }
    } catch (err) {
      console.error("Error adding account:", err);
      showToast("Error adding account", "error");
    }
  });

  // Edit Account
  async function editAccount(id) {
    try {
      const res = await fetch(`${API_URL}?id=${id}`);
      const acc = await res.json();

      const form = document.getElementById("editAccountForm");
      form.querySelector("input[name='id']").value = acc.id;
      form.querySelector("input[name='account_code']").value = acc.account_code;
      form.querySelector("input[name='account_name']").value = acc.account_name;
      form.querySelector("select[name='account_type']").value = acc.account_type;
      form.querySelector("select[name='category']").value = acc.category;
      form.querySelector("textarea[name='description']").value = acc.description || "";
      form.querySelector("input[name='balance']").value = acc.balance || 0;

      openEditModal();
    } catch (err) {
      console.error("Error loading account:", err);
      showToast("Failed to load account", "error");
    }
  }

  document.getElementById("editAccountForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(this).entries());

    try {
      const res = await fetch(`${API_URL}?id=${formData.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
      });

      const result = await res.json();
      if (result.success) {
        showToast("Account updated successfully!", "success");
        closeEditModal();
        loadAccounts();
      } else {
        showToast(result.message || "Failed to update account", "error");
      }
    } catch (err) {
      console.error("Error updating account:", err);
      showToast("Error updating account", "error");
    }
  });

  // Delete Account
  let deleteId = null;

  function confirmDelete(id) {
    deleteId = id;
    document.getElementById("deleteMessage").innerText =
      "Are you sure you want to delete Account ID #" + id + "? This action cannot be undone.";
    openDeleteModal();
  }

  async function deleteAccount() {
    if (!deleteId) return;

    try {
      const res = await fetch(`${API_URL}?id=${deleteId}`, {
        method: "DELETE"
      });

      const result = await res.json();
      if (result.success) {
        showToast("Account deleted successfully!", "success");
        closeDeleteModal();
        loadAccounts();
      } else {
        showToast(result.message || "Failed to delete account", "error");
      }
    } catch (err) {
      console.error("Error deleting account:", err);
      showToast("Error deleting account", "error");
    }
  }

  // Modal Functions
  function openAddModal() {
    document.getElementById("addModal").classList.remove("hidden");
    document.getElementById("addModal").classList.add("flex");
  }

  function closeAddModal() {
    document.getElementById("addModal").classList.add("hidden");
    document.getElementById("addModal").classList.remove("flex");
    document.getElementById("addAccountForm").reset();
  }

  function openEditModal() {
    document.getElementById("editModal").classList.remove("hidden");
    document.getElementById("editModal").classList.add("flex");
  }

  function closeEditModal() {
    document.getElementById("editModal").classList.add("hidden");
    document.getElementById("editModal").classList.remove("flex");
  }

  function openDeleteModal() {
    document.getElementById("deleteModal").classList.remove("hidden");
    document.getElementById("deleteModal").classList.add("flex");
  }

  function closeDeleteModal() {
    document.getElementById("deleteModal").classList.add("hidden");
    document.getElementById("deleteModal").classList.remove("flex");
    deleteId = null;
  }

  // Load accounts on page load
  loadAccounts();
</script>