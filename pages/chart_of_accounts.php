<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto">
  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Chart of Accounts</h1>
      <p class="text-sm text-slate-500">Manage your financial accounts overview</p>
    </div>
    <button onclick="openAddModal()" class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Add Account
    </button>
  </div>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow border border-slate-200">
      <h3 class="text-sm font-medium text-slate-600">Total Assets</h3>
      <p id="totalAssets" class="text-2xl font-bold text-green-600 mt-2">₱0</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border border-slate-200">
      <h3 class="text-sm font-medium text-slate-600">Total Liabilities</h3>
      <p id="totalLiabilities" class="text-2xl font-bold text-red-600 mt-2">₱0</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border border-slate-200">
      <h3 class="text-sm font-medium text-slate-600">Owner\'s Equity</h3>
      <p id="totalEquity" class="text-2xl font-bold text-blue-600 mt-2">₱0</p>
    </div>
  </div>

  <!-- Accounts Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Accounts List</h3>
      <div class="flex items-center gap-3">
        <!-- Filter by Account Type -->
        <select id="filterType" onchange="filterAccounts()" class="border px-3 py-2 rounded-lg text-sm">
          <option value="all">All Types</option>
          <option value="Asset">Asset</option>
          <option value="Liability">Liability</option>
          <option value="OwnersEquity">Equity</option>
          <option value="Revenue">Revenue</option>
          <option value="Expense">Expense</option>
        </select>
        
      </div>
    </div>

    <div class="overflow-x-auto">
      <table id="accountsTable" class="min-w-full text-sm border border-slate-200 rounded-lg overflow-hidden">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Account Code</th>
            <th class="px-4 py-3">Account Name</th>
            <th class="px-4 py-3">Account Type</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Description</th>
            <th class="px-4 py-3 text-right">Actions</th>
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
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add New Account</h2>
    <form id="addAccountForm">
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Account Code</label>
        <input type="text" name="account_code" required class="mt-1 w-full border rounded-lg px-3 py-2">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Account Name</label>
        <input type="text" name="account_name" required class="mt-1 w-full border rounded-lg px-3 py-2">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Account Type</label>
        <select name="account_type" required class="mt-1 w-full border rounded-lg px-3 py-2">
          <option value="Asset">Asset</option>
          <option value="Liability">Liability</option>
          <option value="Equity">Equity</option>
          <option value="Revenue">Revenue</option>
          <option value="Expense">Expense</option>
          <option value="Income">Income</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Category</label>
        <select name="category" required class="mt-1 w-full border rounded-lg px-3 py-2">
          <option value="Current Asset">Current Asset</option>
          <option value="Fixed Asset">Fixed Asset</option>
          <option value="Current Liability">Current Liability</option>
          <option value="Long-term Liability">Long-term Liability</option>
          <option value="Owner\'s Equity">Owner\'s Equity</option>
          <option value="Revenue">Revenue</option>
          <option value="COGS">COGS</option>
          <option value="Operating Expense">Operating Expense</option>
          <option value="Non-operating Expense">Non-operating Expense</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full border rounded-lg px-3 py-2"></textarea>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Balance</label>
        <input type="number" step="0.01" name="balance" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="0.00" />
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeAddModal()" class="px-4 py-2 rounded-lg border">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Edit Account</h2>
    <form id="editAccountForm">
      <input type="hidden" name="id">
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Account Code</label>
        <input type="text" name="account_code" required class="mt-1 w-full border rounded-lg px-3 py-2">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Account Name</label>
        <input type="text" name="account_name" required class="mt-1 w-full border rounded-lg px-3 py-2">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Account Type</label>
        <select name="account_type" required class="mt-1 w-full border rounded-lg px-3 py-2">
          <option value="Asset">Asset</option>
          <option value="Liability">Liability</option>
          <option value="Equity">Equity</option>
          <option value="Revenue">Revenue</option>
          <option value="Expense">Expense</option>
          <option value="Income">Income</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Category</label>
        <select name="category" required class="mt-1 w-full border rounded-lg px-3 py-2">
          <option value="Current Asset">Current Asset</option>
          <option value="Fixed Asset">Fixed Asset</option>
          <option value="Current Liability">Current Liability</option>
          <option value="Long-term Liability">Long-term Liability</option>
          <option value="Owner\'s Equity">Owner\'s Equity</option>
          <option value="Revenue">Revenue</option>
          <option value="COGS">COGS</option>
          <option value="Operating Expense">Operating Expense</option>
          <option value="Non-operating Expense">Non-operating Expense</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full border rounded-lg px-3 py-2"></textarea>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Balance</label>
        <input type="number" step="0.01" name="balance" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="0.00" />
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg border">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Account Details</h2>
    <div id="viewContent" class="space-y-2 text-sm"></div>
    <div class="flex justify-end mt-4">
      <button onclick="closeViewModal()" class="px-4 py-2 rounded-lg border">Close</button>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6 relative">
    <h2 class="text-lg font-bold mb-4 text-red-600">Delete Account</h2>
    <p id="deleteMessage" class="text-sm text-slate-600 mb-6">Are you sure you want to delete this account?</p>
    <div class="flex justify-end space-x-2">
      <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border">Cancel</button>
      <button onclick="deleteAccount()" class="px-4 py-2 bg-red-500 text-white rounded-lg">Delete</button>
    </div>
  </div>
</div>

';

adminLayout($children);
?>
<!-- Toastify -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
  const API_URL = "http://localhost/prefect/api/chart_of_accounts_api.php";

  // ===================== TOAST FUNCTION =====================
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
    const res = await fetch(API_URL);
    const data = await res.json();

    const tbody = document.getElementById("accountsBody");
    tbody.innerHTML = "";

    let totalAssets = 0, totalLiabilities = 0, totalEquity = 0;

    data.forEach(acc => {
      if (acc.category === "Asset") totalAssets += parseFloat(acc.balance || 0);
      if (acc.category === "Liability") totalLiabilities += parseFloat(acc.balance || 0);
      if (acc.category === "OwnersEquity") totalEquity += parseFloat(acc.balance || 0);

      tbody.innerHTML += `
        <tr data-type="${acc.account_type || ''}" data-category="${acc.category}">
          <td class="px-4 py-3 font-medium">${acc.account_code}</td>
          <td class="px-4 py-3">${acc.account_name}</td>
          <td class="px-4 py-3">${acc.account_type || ''}</td>
          <td class="px-4 py-3">${acc.category}</td>
          <td class="px-4 py-3">${acc.description || ''}</td>
          <td class="px-4 py-3 text-right space-x-2">
            <button onclick="editAccount(${acc.id})" class="text-blue-600 hover:text-blue-800"><i class="bx bx-edit"></i></button>
            <button onclick="viewAccount(${acc.id})" class="text-gray-600 hover:text-gray-800"><i class="bx bx-show"></i></button>
            <button onclick="confirmDelete(${acc.id})" class="text-red-600 hover:text-red-800"><i class="bx bx-trash"></i></button>
          </td>
        </tr>
      `;
    });

    // Map categories to summary totals (simple heuristic)
    let assets = 0, liabilities = 0, equity = 0;
    data.forEach(acc => {
      const cat = (acc.category || '').toLowerCase();
      const val = parseFloat(acc.balance || 0);
      if (cat.includes('asset')) assets += val;
      else if (cat.includes('liabil')) liabilities += val;
      else if (cat === 'equity' || cat.includes('equity')) equity += val;
    });

    document.getElementById("totalAssets").innerText = "₱" + assets.toLocaleString();
    document.getElementById("totalLiabilities").innerText = "₱" + liabilities.toLocaleString();
    document.getElementById("totalEquity").innerText = "₱" + equity.toLocaleString();
  }

  // Filter
  function filterAccounts() {
    const filterType = document.getElementById("filterType").value;
    document.querySelectorAll("#accountsBody tr").forEach(row => {
      const rowType = (row.getAttribute('data-type') || '').toLowerCase();
      row.style.display = filterType === "all" || rowType === filterType.toLowerCase() ? "" : "none";
    });
  }

  // ===================== ADD =====================
  document.getElementById("addAccountForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(this).entries());

    const res = await fetch(API_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData)
    });

    if (res.ok) {
      showToast("Account added successfully!", "success");
      closeAddModal();
      this.reset();
      loadAccounts();
    } else {
      showToast("Failed to add account", "error");
    }
  });

  // ===================== EDIT =====================
  async function editAccount(id) {
  const res = await fetch(`${API_URL}?id=${id}`);
  const acc = await res.json();

  const form = document.getElementById("editAccountForm");
  form.querySelector("input[name='id']").value = acc.id; // ✅ FIX
  form.account_code.value = acc.account_code;
  form.account_name.value = acc.account_name;
  form.account_type.value = acc.account_type || acc.category || 'Asset';
  form.category.value = acc.category;
  form.description.value = acc.description || '';
  form.balance.value = acc.balance || 0;

  openEditModal();
}


  document.getElementById("editAccountForm").addEventListener("submit", async function(e) {
  e.preventDefault();
  const formData = Object.fromEntries(new FormData(this).entries());

  const res = await fetch(`${API_URL}?id=${formData.id}`, { // ✅ add id in query string
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(formData)
  });

  if (res.ok) {
    showToast("Account updated successfully!", "success");
    closeEditModal();
    loadAccounts();
  } else {
    showToast("Failed to update account", "error");
  }
});


  // ===================== VIEW =====================
  async function viewAccount(id) {
    const res = await fetch(`${API_URL}?id=${id}`);
    const acc = await res.json();

    document.getElementById("viewContent").innerHTML = `
      <p><b>Code:</b> ${acc.account_code}</p>
      <p><b>Name:</b> ${acc.account_name}</p>
      <p><b>Account Type:</b> ${acc.account_type || ''}</p>
      <p><b>Category:</b> ${acc.category}</p>
      <p><b>Description:</b> ${acc.description || ''}</p>
      <p><b>Balance:</b> ₱${parseFloat(acc.balance || 0).toLocaleString()}</p>
    `;

    openViewModal();
    showToast("Viewing account details", "success");
  }

  // ===================== MODALS =====================
  function openAddModal() { document.getElementById("addModal").classList.replace("hidden", "flex"); }
  function closeAddModal() { document.getElementById("addModal").classList.replace("flex", "hidden"); }
  function openEditModal() { document.getElementById("editModal").classList.replace("hidden", "flex"); }
  function closeEditModal() { document.getElementById("editModal").classList.replace("flex", "hidden"); }
  function openViewModal() { document.getElementById("viewModal").classList.replace("hidden", "flex"); }
  function closeViewModal() { document.getElementById("viewModal").classList.replace("flex", "hidden"); }

  // Export
  function exportAccounts() { window.open(API_URL, "_blank"); }

  // Load on page start
  loadAccounts();

  //Delete
  let deleteId = null;

  function confirmDelete(id) {
    deleteId = id;
    document.getElementById("deleteMessage").innerText =
      "Are you sure you want to delete Account ID #" + id + "?";
    openDeleteModal();
  }

  async function deleteAccount() {
    if (!deleteId) return;

    const res = await fetch(`${API_URL}?id=${deleteId}`, {
      method: "DELETE"
    });

    if (res.ok) {
      showToast("Account deleted successfully!", "success");
      closeDeleteModal();
      loadAccounts();
    } else {
      showToast("Failed to delete account", "error");
    }
  }

  function openDeleteModal() { 
    document.getElementById("deleteModal").classList.replace("hidden", "flex"); 
  }
  function closeDeleteModal() { 
    document.getElementById("deleteModal").classList.replace("flex", "hidden"); 
    deleteId = null;
  }
</script>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">