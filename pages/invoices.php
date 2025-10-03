<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Accounts Payable - Invoices</h1>
      <p class="text-sm text-slate-500">Manage all supplier invoices under Accounts Payable</p>
    </div>
    <button onclick="openInvoiceModal()" class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Add Invoice
    </button>
  </div>

  <!-- Invoices Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Invoices List</h3>
      <div class="flex items-center gap-3">
        <!-- Filter -->
        <select id="filterStatus" onchange="filterInvoices()" class="border px-3 py-2 rounded-lg text-sm">
          <option value="all">All Status</option>
          <option value="Pending">Pending</option>
          <option value="Paid">Paid</option>
        </select>
        <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-2">
          <i class="bx bx-file"></i> Export Invoices
        </button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table id="invoicesTable" class="min-w-full text-sm border border-slate-200 rounded-lg overflow-hidden">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Invoice ID</th>
            <th class="px-4 py-3">Vendor</th>
            <th class="px-4 py-3">Invoice Date</th>
            <th class="px-4 py-3">Due Date</th>
            <th class="px-4 py-3">Amount</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr id="invoice-1" data-status="Pending" data-vendor="ABC Supplies">
            <td class="px-4 py-3 font-medium">INV-001</td>
            <td class="px-4 py-3">ABC Supplies</td>
            <td class="px-4 py-3">2025-09-01</td>
            <td class="px-4 py-3">2025-09-15</td>
            <td class="px-4 py-3 text-red-600">₱25,000</td>
            <td class="px-4 py-3 text-yellow-600">Pending</td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:text-blue-800"><i class="bx bx-edit"></i></button>
              <button class="text-gray-600 hover:text-gray-800"><i class="bx bx-show"></i></button>
            </td>
          </tr>
          <tr id="invoice-2" data-status="Paid" data-vendor="XYZ Traders">
            <td class="px-4 py-3 font-medium">INV-002</td>
            <td class="px-4 py-3">XYZ Traders</td>
            <td class="px-4 py-3">2025-09-05</td>
            <td class="px-4 py-3">2025-09-20</td>
            <td class="px-4 py-3 text-red-600">₱15,000</td>
            <td class="px-4 py-3 text-green-600">Paid</td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:text-blue-800"><i class="bx bx-edit"></i></button>
              <button class="text-gray-600 hover:text-gray-800"><i class="bx bx-show"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</main>

<!-- Add Invoice Modal -->
<div id="invoiceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add New Invoice</h2>
    <form>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Invoice ID</label>
        <input type="text" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Vendor</label>
        <input type="text" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Invoice Date</label>
        <input type="date" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Due Date</label>
        <input type="date" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Amount</label>
        <input type="number" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Status</label>
        <select class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
          <option value="Pending">Pending</option>
          <option value="Paid">Paid</option>
        </select>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeInvoiceModal()" class="px-4 py-2 rounded-lg border">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->
<script>
  function openInvoiceModal() {
    const modal = document.getElementById("invoiceModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  }

  function closeInvoiceModal() {
    const modal = document.getElementById("invoiceModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }

  document.getElementById("invoiceModal").addEventListener("click", function(e) {
    if (e.target === this) closeInvoiceModal();
  });

  function filterInvoices() {
    const filter = document.getElementById("filterStatus").value;
    const rows = document.querySelectorAll("#invoicesTable tbody tr");

    rows.forEach(row => {
      row.style.display = filter === "all" || row.getAttribute("data-status") === filter ? "" : "none";
    });
  }
</script>

<!-- Boxicons -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
';

adminLayout($children);
?>
