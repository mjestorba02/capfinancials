<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Accounts Receivable</h1>
      <p class="text-sm text-slate-500">Track customer invoices and outstanding receivables</p>
    </div>
    <button onclick="openReceivableModal()" 
      class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Add Receivable
    </button>
  </div>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <p class="text-sm text-slate-500">Total Receivables</p>
      <h2 class="text-xl font-bold text-blue-600">₱45,000</h2>
    </div>
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <p class="text-sm text-slate-500">Unpaid Invoices</p>
      <h2 class="text-xl font-bold text-orange-500">₱20,000</h2>
    </div>
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <p class="text-sm text-slate-500">Collected Payments</p>
      <h2 class="text-xl font-bold text-green-600">₱25,000</h2>
    </div>
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <p class="text-sm text-slate-500">Customers</p>
      <h2 class="text-xl font-bold text-purple-600">8</h2>
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">

    <!-- Filter + Export -->
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Accounts Receivable List</h3>
      <div class="flex items-center gap-3">
      <!-- Search -->
      <input id="searchInput" type="text" placeholder="Search customer or invoice" class="border px-3 py-2 rounded-lg text-sm" />
      
      <!-- Date Range -->
      <input id="fromDate" type="date" class="border px-3 py-2 rounded-lg text-sm" />
      <span class="text-slate-500 text-sm">to</span>
      <input id="toDate" type="date" class="border px-3 py-2 rounded-lg text-sm" />
      
      <!-- Filter Status -->
      <select id="filterStatus" onchange="filterReceivables()" class="border px-3 py-2 rounded-lg text-sm">
      <option value="all">All Status</option>
      <option value="Paid">Paid</option>
      <option value="Unpaid">Unpaid</option>
      <option value="Overdue">Overdue</option>
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
            <th class="px-4 py-3">Customer</th>
            <th class="px-4 py-3">Invoice #</th>
            <th class="px-4 py-3">Amount</th>
            <th class="px-4 py-3">Due Date</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr>
            <td class="px-4 py-3 font-medium">Juan Dela Cruz</td>
            <td class="px-4 py-3">AR-001</td>
            <td class="px-4 py-3 text-blue-600">₱10,000</td>
            <td class="px-4 py-3">2025-10-10</td>
            <td class="px-4 py-3"><span class="text-red-600 font-medium">Overdue</span></td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:text-blue-800"><i class="bx bx-edit"></i></button>
              <button class="text-green-600 hover:text-green-800"><i class="bx bx-show"></i></button>
            </td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium">Maria Santos</td>
            <td class="px-4 py-3">AR-002</td>
            <td class="px-4 py-3 text-blue-600">₱15,000</td>
            <td class="px-4 py-3">2025-10-20</td>
            <td class="px-4 py-3"><span class="text-orange-600 font-medium">Unpaid</span></td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:text-blue-800"><i class="bx bx-edit"></i></button>
              <button class="text-green-600 hover:text-green-800"><i class="bx bx-show"></i></button>
            </td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium">Pedro Reyes</td>
            <td class="px-4 py-3">AR-003</td>
            <td class="px-4 py-3 text-green-600">₱20,000</td>
            <td class="px-4 py-3">2025-10-05</td>
            <td class="px-4 py-3"><span class="text-green-600 font-medium">Paid</span></td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-gray-400 cursor-not-allowed" disabled><i class="bx bx-check-shield"></i></button>
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
<div id="addReceivableModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add Receivable</h2>
    <form class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Customer</label>
        <input type="text" class="w-full border rounded-lg px-3 py-2" placeholder="Enter customer">
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
          <option>Overdue</option>
        </select>
      </div>
      <div class="flex justify-end space-x-2 mt-4">
        <button type="button" onclick="closeReceivableModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Boxicons -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<script>
  function openReceivableModal() {
    document.getElementById("addReceivableModal").classList.remove("hidden");
    document.getElementById("addReceivableModal").classList.add("flex");
  }
  function closeReceivableModal() {
    document.getElementById("addReceivableModal").classList.add("hidden");
    document.getElementById("addReceivableModal").classList.remove("flex");
  }

  // Filter Receivables by Status, Search, and Date Range
  function filterReceivables() {
    const status = document.getElementById("filterStatus").value.toLowerCase();
    const searchEl = document.getElementById("searchInput");
    const fromEl = document.getElementById("fromDate");
    const toEl = document.getElementById("toDate");

    const search = searchEl ? searchEl.value.trim().toLowerCase() : "";
    const fromDate = fromEl ? fromEl.value : "";
    const toDate = toEl ? toEl.value : "";
    const rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
      const customer = row.querySelector("td:nth-child(1)")?.textContent.toLowerCase() || "";
      const invoice = row.querySelector("td:nth-child(2)")?.textContent.toLowerCase() || "";
      const due = row.querySelector("td:nth-child(4)")?.textContent.trim() || "";
      const rowStatus = (row.querySelector("td:nth-child(5) span")?.textContent || "").trim().toLowerCase();

      const matchesStatus = status === "all" || rowStatus === status;
      const matchesSearch = !search || customer.includes(search) || invoice.includes(search);

      let inDateRange = true;
      if (fromDate || toDate) {
        const dueTime = new Date(due).getTime();
        if (!Number.isNaN(dueTime)) {
          if (fromDate && new Date(fromDate).getTime() > dueTime) inDateRange = false;
          if (toDate && new Date(toDate).getTime() < dueTime) inDateRange = false;
        }
      }

      row.style.display = (matchesStatus && matchesSearch && inDateRange) ? "" : "none";
    });
  }

  // Hook up live filtering
  const searchInput = document.getElementById("searchInput");
  if (searchInput) searchInput.addEventListener("input", filterReceivables);
  const fromDateInput = document.getElementById("fromDate");
  if (fromDateInput) fromDateInput.addEventListener("change", filterReceivables);
  const toDateInput = document.getElementById("toDate");
  if (toDateInput) toDateInput.addEventListener("change", filterReceivables);
</script>
';

adminLayout($children);
?>
