<?php
include '../layout/adminLayout.php';

// Server-side: fetch Paid collections and render rows directly
require_once __DIR__ . '/../api/db.php';
$rowsHtml = '';
$sql = "SELECT * FROM collections WHERE LOWER(TRIM(status)) LIKE '%paid%' ORDER BY date DESC";
$res = $conn->query($sql);
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $invoice = htmlspecialchars($r['invoice_no'] ?? '');
    $customer = htmlspecialchars($r['customer'] ?? '');
    $department = htmlspecialchars($r['department'] ?? '');
    $amount = isset($r['amount']) ? number_format((float)$r['amount'],2) : '0.00';
    $status = htmlspecialchars($r['status'] ?? '');
    $date = htmlspecialchars($r['date'] ?? '');
  $rowsHtml .= "<tr>\n" .
         "<td class=\"px-4 py-3 font-medium\">{$invoice}</td>\n" .
         "<td class=\"px-4 py-3\">{$customer}</td>\n" .
         "<td class=\"px-4 py-3\">{$department}</td>\n" .
         "<td class=\"px-4 py-3\">₱{$amount}</td>\n" .
         "<td class=\"px-4 py-3\"><span class=\"font-semibold text-green-600\">{$status}</span></td>\n" .
         "<td class=\"px-4 py-3\">{$date}</td>\n" .
         "<td class=\"px-4 py-3 text-right\">\n" .
         "  <div class=\"inline-flex items-center gap-2 justify-end\">\n" .
         "    <button type=\"button\" class=\"text-green-600 hover:text-green-800 btn-edit\" data-invoice=\"{$invoice}\" data-customer=\"{$customer}\" data-department=\"{$department}\" data-amount=\"{$amount}\" data-status=\"{$status}\" data-date=\"{$date}\">\n" .
         "      <i class=\"bx bx-edit text-xl\"></i>\n" .
         "    </button>\n" .
         "    <button type=\"button\" class=\"text-gray-600 hover:text-gray-800 btn-print\" data-invoice=\"{$invoice}\">\n" .
         "      <i class=\"bx bx-printer text-xl\"></i>\n" .
         "    </button>\n" .
         "  </div>\n" .
         "</td>\n" .
         "</tr>\n";
  }
} else {
  $rowsHtml = "<tr><td colspan=\"7\" class=\"px-4 py-6 text-center text-slate-600\">No paid invoices found or DB error.</td></tr>";
}

$children = '
<main class="flex-1 p-8 overflow-y-auto max-h-screen">
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">Accounts Receivable - Sales Invoices</h1>
      <p class="text-sm text-slate-500">Manage customer invoices and track payments</p>
    </div>
  </div>

  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <div class="overflow-x-auto">
      <!-- Debug panel (hidden by default) -->
      <div id="siDebug" class="mb-4 text-sm text-slate-500" style="display:none;"></div>
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Sales Invoices List</h3>
      </div>

  <table id="collectionsTable" class="min-w-full text-sm">
  <thead>
    <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
      <th class="px-4 py-3">Invoice #</th>
      <th class="px-4 py-3">Customer</th>
      <th class="px-4 py-3">Department</th>
      <th class="px-4 py-3">Amount</th>
      <th class="px-4 py-3">Status</th>
      <th class="px-4 py-3">Date</th>
      <th class="px-4 py-3 text-right">Actions</th>
    </tr>
  </thead>
  <tbody>
    ' . $rowsHtml . '
  </tbody>
</table>
      <div class="mt-4 flex justify-end">
        <button id="printAllBtn" class="px-4 py-2 bg-gray-800 text-white rounded">Print All</button>
      </div>
    </div>
  </div>
</main>

<!-- Edit Collection Modal -->
<div id="editCollectionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h2 class="text-xl font-bold mb-4">Edit Collection</h2>
    <form id="editForm" class="space-y-4">
      <input type="hidden" id="editInvoiceNo" name="invoice_no" />
      <div>
        <label class="block text-sm font-medium">Customer</label>
        <input id="editCustomer" name="customer" type="text" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Department</label>
        <input id="editDepartment" name="department" type="text" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Amount</label>
        <input id="editAmount" name="amount" type="number" step="0.01" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Status</label>
        <select id="editStatus" name="status" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm">
          <option>Paid</option>
          <option>Pending</option>
          <option>Overdue</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">Date</label>
        <input id="editDate" name="date" type="date" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" required />
      </div>
      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-indigo-500 text-white rounded-lg">Update</button>
      </div>
    </form>
  </div>
</div>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
';

adminLayout($children);
?>
<!-- JS -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const apiUrl = "http://localhost/prefect/api/collections.php";
  let _paidCache = [];

  // ===================== PRINT ALL PAID =====================
  function printAll() {
    const table = document.getElementById('collectionsTable');
    if (!table) return showToast('No table to print', 'error');
    const allRows = Array.from(table.querySelectorAll('tr')).filter(r => r.querySelectorAll('td').length > 0);
    if (!allRows.length) return showToast('No paid invoices to print', 'error');

    let rows = '';
    allRows.forEach(r => {
      const tds = r.querySelectorAll('td');
      const cols = Array.from(tds).map(td => td.textContent.trim());
      rows += `<tr>
        <td>${cols[0] || ''}</td>
        <td>${cols[1] || ''}</td>
        <td>${cols[2] || ''}</td>
        <td>${cols[3] || ''}</td>
        <td>${cols[4] || ''}</td>
        <td>${cols[5] || ''}</td>
      </tr>`;
    });

    const html = `<!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Paid Invoices</title>
      <style>
        table { width:100%; border-collapse:collapse }
        th, td { border:1px solid #ccc; padding:8px; text-align:left }
      </style>
    </head>
    <body onload="window.print()">
      <h2>Paid Invoices</h2>
      <table>
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Customer</th>
            <th>Department</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </body>
    </html>`;

    const w = window.open('', '_blank');
    if (!w) {
      return showToast('Popup blocked. Allow popups to print.', 'error');
    }
    w.document.write(html);
    w.document.close();
  }

  // ===================== PRINT SINGLE =====================
  function openPrint(invoice_no) {
    const url = `sales_invoices.php?invoice_no=${encodeURIComponent(invoice_no)}&print=1`;
    window.open(url, '_blank', 'noopener');
  }

  // ===================== HOOK EVENTS =====================
  const printAllBtn = document.getElementById("printAllBtn");
  if (printAllBtn) {
    printAllBtn.addEventListener("click", printAll);
  }

  // Delegation for edit/print per row
  document.body.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.btn-edit');
    if (editBtn) {
      const invoice = editBtn.dataset.invoice || '';
      const customer = editBtn.dataset.customer || '';
      const department = editBtn.dataset.department || '';
      const amount = editBtn.dataset.amount || '';
      const status = editBtn.dataset.status || '';
      const date = editBtn.dataset.date || '';
      openEditModal(invoice, customer, department, amount, status, date);
      return;
    }
    const printBtn = e.target.closest('.btn-print');
    if (printBtn) {
      const invoice = printBtn.dataset.invoice || '';
      openPrint(invoice);
      return;
    }
  });

  // ===================== TOAST =====================
  function showToast(message, type) {
    Toastify({
      text: message,
      duration: 3000,
      close: true,
      style: {
        background: type === "success" 
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)"
      }
    }).showToast();
  }

  // ===================== EDIT MODAL FUNCTIONS =====================
  window.openEditModal = function(invoice_no, customer, department, amount, status, date) {
    const modal = document.getElementById("editCollectionModal");
    if (!modal) return;
    document.getElementById("editInvoiceNo").value = invoice_no;
    document.getElementById("editCustomer").value = customer;
    document.getElementById("editDepartment").value = department;
    document.getElementById("editAmount").value = amount;
    document.getElementById("editStatus").value = status;
    document.getElementById("editDate").value = date;
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  }
  window.closeEditModal = function() {
    const modal = document.getElementById("editCollectionModal");
    if (!modal) return;
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }
});
</script>