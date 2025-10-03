<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Accounts Payable</h1>
      <p class="text-sm text-slate-500">Monitor and manage outstanding payables</p>
    </div>
    <button onclick="openModal()" 
      class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Add Payable
    </button>
  </div>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <p class="text-sm text-slate-500">Total Payables</p>
      <h2 class="text-xl font-bold text-red-600">₱25,000</h2>
    </div>
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <p class="text-sm text-slate-500">Unpaid Invoices</p>
      <h2 class="text-xl font-bold text-orange-500">₱15,000</h2>
    </div>
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <p class="text-sm text-slate-500">Paid Invoices</p>
      <h2 class="text-xl font-bold text-green-600">₱10,000</h2>
    </div>
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <p class="text-sm text-slate-500">Vendors</p>
      <h2 class="text-xl font-bold text-blue-600">5</h2>
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    
    <!-- Filter + Export -->
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Accounts Payable List</h3>
      <div class="flex items-center gap-3">
        <!-- Filter Status -->
        <select id="filterStatus" onchange="filterAccounts()" class="border px-3 py-2 rounded-lg text-sm">
          <option value="all">All Status</option>
          <option value="Paid">Paid</option>
          <option value="Unpaid">Unpaid</option>
        </select>

        <!-- Export -->
        <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-2">
          <i class="bx bx-file"></i> Export Accounts
        </button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Vendor</th>
            <th class="px-4 py-3">Invoice #</th>
            <th class="px-4 py-3">Amount</th>
            <th class="px-4 py-3">Due Date</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr>
            <td class="px-4 py-3 font-medium">ABC Supplies</td>
            <td class="px-4 py-3">INV-001</td>
            <td class="px-4 py-3 text-red-600">₱8,000</td>
            <td class="px-4 py-3">2025-10-15</td>
            <td class="px-4 py-3"><span class="text-orange-600 font-medium">Unpaid</span></td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:text-blue-800"><i class="bx bx-edit"></i></button>
              <button class="text-green-600 hover:text-green-800"><i class="bx bx-show"></i></button>
            </td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium">XYZ Traders</td>
            <td class="px-4 py-3">INV-002</td>
            <td class="px-4 py-3 text-green-600">₱7,000</td>
            <td class="px-4 py-3">2025-10-20</td>
            <td class="px-4 py-3"><span class="text-green-600 font-medium">Paid</span></td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:text-blue-800"><i class="bx bx-edit"></i></button>
              <button class="text-green-600 hover:text-green-800"><i class="bx bx-show"></i></button>
            </td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium">LMN Co.</td>
            <td class="px-4 py-3">INV-003</td>
            <td class="px-4 py-3 text-red-600">₱10,000</td>
            <td class="px-4 py-3">2025-10-30</td>
            <td class="px-4 py-3"><span class="text-orange-600 font-medium">Unpaid</span></td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:text-blue-800"><i class="bx bx-edit"></i></button>
              <button class="text-green-600 hover:text-green-800"><i class="bx bx-show"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</main>
</div>

<!-- Modal -->
<div id="addPayableModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add Payable</h2>
    <form class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Vendor</label>
        <input type="text" class="w-full border rounded-lg px-3 py-2" placeholder="Enter vendor">
      </div>
      <div>
        <label class="block text-sm font-medium">Invoice #</label>
        <input type="text" class="w-full border rounded-lg px-3 py-2" placeholder="Enter invoice number">
      </div>
      <div>
        <label class="block text-sm font-medium">Amount</label>
        <input type="number" class="w-full border rounded-lg px-3 py-2" placeholder="₱">
      </div>
      <div>
        <label class="block text-sm font-medium">Due Date</label>
        <input type="date" class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Status</label>
        <select class="w-full border rounded-lg px-3 py-2">
          <option>Unpaid</option>
          <option>Paid</option>
        </select>
      </div>
      <div class="flex justify-end space-x-2 mt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Boxicons -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<script>
  function openModal() {
    document.getElementById("addPayableModal").classList.remove("hidden");
    document.getElementById("addPayableModal").classList.add("flex");
  }
  function closeModal() {
    document.getElementById("addPayableModal").classList.add("hidden");
    document.getElementById("addPayableModal").classList.remove("flex");
  }

  // Filtering function (status only)
  function filterAccounts() {
    const status = document.getElementById("filterStatus").value;
    const rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
      const rowStatus = row.querySelector("td:nth-child(5) span").textContent.trim();
      const matchStatus = (status === "all" || rowStatus === status);

      row.style.display = matchStatus ? "" : "none";
    });
  }
</script>
';

adminLayout($children);
?>
