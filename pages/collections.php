<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Collections</h1>
      <p class="text-sm text-slate-500">Manage and track your collections</p>
    </div>
    <button onclick="openAddModal()" class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Add Collection
    </button>
  </div>

  <!-- Collections Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <div class="overflow-x-auto">
      <!-- Filter -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Collections List</h3>
        <div class="flex gap-2 items-center mb-4">
          <select id="filterStatus" class="border px-2 py-1">
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
          </select>

          <input type="text" id="filterDepartment" placeholder="Filter by Department" class="border px-2 py-1">

          <input type="date" id="filterDate" class="border px-2 py-1">

          <!-- Reset Button -->
          <button id="resetFilters" class="bg-red-500 text-white px-3 py-1 rounded">
            Reset Filters
          </button>
        </div>
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
        <tbody class="divide-y divide-slate-200"></tbody>
      </table>
    </div>
  </div>
</main>
</div>

<!-- Add Collection Modal -->
<div id="addCollectionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add New Collection</h2>
    <form id="addForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Customer</label>
        <input name="customer" type="text" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" placeholder="Enter customer name" />
      </div>
      <div>
        <label class="block text-sm font-medium">Department</label>
        <input name="department" type="text" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" placeholder="Enter department" />
      </div>
      <div>
        <label class="block text-sm font-medium">Amount</label>
        <input name="amount" type="number" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" placeholder="Enter amount" />
      </div>
      <div>
        <!-- Status is set automatically to Pending on creation -->
        <input type="hidden" name="status" value="Pending" />
      </div>
      <div>
        <label class="block text-sm font-medium">Date</label>
        <input name="date" type="date" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" />
      </div>
      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-orange-500 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- View Collection Modal -->
<div id="viewCollectionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h2 class="text-xl font-bold mb-4">Collection Details</h2>
    <div id="viewDetails" class="space-y-2 text-sm"></div>
    <div class="flex justify-end mt-6">
      <button type="button" onclick="closeViewModal()" class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Close</button>
    </div>
  </div>
</div>

    <!-- Approve Confirmation Modal -->
    <div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h2 class="text-xl font-bold mb-4">Confirm Approval</h2>
        <p class="text-sm text-slate-600">Are you sure you want to mark collection <span id="approveInvoice" class="font-medium"></span> as <span class="font-semibold">Paid</span>?</p>
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" onclick="closeApproveModal()" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
          <button id="confirmApproveBtn" type="button" onclick="approveCollectionFromModal()" class="px-4 py-2 bg-green-600 text-white rounded">Confirm</button>
        </div>
      </div>
    </div>

<!-- Boxicons + Toastify -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
';

adminLayout($children);
?>

<script>
const apiUrl = "https://financial.health-ease-hospital.com/api/collections.php";

// ===================== LOAD COLLECTIONS =====================
async function loadCollections() {
  const res = await fetch(apiUrl);
  const data = await res.json();
  const tbody = document.querySelector("#collectionsTable tbody");
  tbody.innerHTML = "";

  data.forEach(item => {
    const row = `
      <tr>
        <td class="px-4 py-3 font-medium">${item.invoice_no}</td>
        <td class="px-4 py-3">${item.customer}</td>
        <td class="px-4 py-3">${item.department}</td>
        <td class="px-4 py-3 ${item.status === 'Paid' ? 'text-green-600' : item.status === 'Pending' ? 'text-yellow-600' : 'text-red-600'}">₱${parseFloat(item.amount).toLocaleString()}</td>
        <td class="px-4 py-3"><span class="font-semibold ${item.status === 'Paid' ? 'text-green-600' : item.status === 'Pending' ? 'text-yellow-600' : 'text-red-600'}">${item.status}</span></td>
        <td class="px-4 py-3">${item.date}</td>
        <td class="px-4 py-3 text-right">
          <div class="inline-flex items-center gap-2">
            <button onclick="openViewModal('${item.invoice_no}', '${item.customer}', '${item.department}', ${item.amount}, '${item.status}', '${item.date}')" class="text-indigo-600 hover:text-indigo-800" title="View"><i class="bx bx-show text-xl"></i></button>
            ${item.status === 'Pending' ? `<button onclick="showApproveModal('${item.invoice_no}')" class="text-green-600 hover:text-green-800" title="Approve"><i class="bx bx-check text-xl"></i></button>` : ''}
          </div>
        </td>
      </tr>
    `;
    tbody.innerHTML += row;
  });
}

// ===================== ADD COLLECTION =====================
document.getElementById("addForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const newCollection = Object.fromEntries(formData.entries());

  try {
    const res = await fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(newCollection)
    });

    const result = await res.json();

    if (result && result.success) {
      showToast(`Collection ${result.invoice_no ? "#" + result.invoice_no + " " : ""}added successfully!`, "success");
      e.target.reset();
      closeAddModal();
      loadCollections();
    } else {
      const msg = (result && (result.error || result.message)) ? (result.error || result.message) : "Failed to save collection.";
      showToast("Error: " + msg, "error");
    }
  } catch (err) {
    showToast("Error: Unable to reach server.", "error");
    console.error(err);
  }
});

// ===================== APPROVE COLLECTION =====================
let _pendingApproveInvoice = null;

function showApproveModal(invoice_no) {
  _pendingApproveInvoice = invoice_no;
  document.getElementById('approveInvoice').innerText = invoice_no;
  document.getElementById('approveModal').classList.remove('hidden');
  document.getElementById('approveModal').classList.add('flex');
}

function closeApproveModal() {
  _pendingApproveInvoice = null;
  document.getElementById('approveModal').classList.add('hidden');
  document.getElementById('approveModal').classList.remove('flex');
}

async function approveCollectionFromModal() {
  const invoice_no = _pendingApproveInvoice;
  if (!invoice_no) return;

  const btn = document.getElementById('confirmApproveBtn');
  btn.disabled = true;

  try {
    // Fetch the full collection record first
    const fetchRes = await fetch(`${apiUrl}?invoice_no=${invoice_no}`);
    const collection = await fetchRes.json();

    if (!collection || !collection.data) {
      showToast('Collection not found.', 'error');
      btn.disabled = false;
      return;
    }

    const { customer, department, amount, date } = collection.data;

    // Now send PUT request with all required fields
    const res = await fetch(apiUrl, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        invoice_no,
        customer,
        department,
        amount,
        date,
        status: 'Paid'
      })
    });

    const resultText = await res.text();
    console.log('Raw response:', resultText);

    let result;
    try {
      result = JSON.parse(resultText);
    } catch {
      throw new Error('Invalid JSON from server');
    }

    if (result.success) {
      showToast(`Collection ${invoice_no} marked as Paid.`, 'success');
      closeApproveModal();
      loadCollections();
    } else {
      showToast(result.error || 'Failed to approve collection.', 'error');
    }
  } catch (err) {
    console.error('Approve error:', err);
    showToast('Error: Unable to reach server.', 'error');
  } finally {
    btn.disabled = false;
  }
}

// ===================== VIEW COLLECTION =====================
function openViewModal(invoice, customer, department, amount, status, date) {
  const details = `
    <p><strong>Invoice #:</strong> ${invoice}</p>
    <p><strong>Customer:</strong> ${customer}</p>
    <p><strong>Department:</strong> ${department}</p>
    <p><strong>Amount:</strong> ₱${parseFloat(amount).toLocaleString()}</p>
    <p><strong>Status:</strong> ${status}</p>
    <p><strong>Date:</strong> ${date}</p>
  `;
  document.getElementById("viewDetails").innerHTML = details;

  document.getElementById("viewCollectionModal").classList.remove("hidden");
  document.getElementById("viewCollectionModal").classList.add("flex");
}

function closeViewModal() {
  document.getElementById("viewCollectionModal").classList.add("hidden");
  document.getElementById("viewCollectionModal").classList.remove("flex");
}

// ===================== MODAL HELPERS =====================
function openAddModal() {
  document.getElementById("addCollectionModal").classList.remove("hidden");
  document.getElementById("addCollectionModal").classList.add("flex");
}
function closeAddModal() {
  document.getElementById("addCollectionModal").classList.add("hidden");
  document.getElementById("addCollectionModal").classList.remove("flex");
}

// ===================== FILTER =====================
function filterCollections() {
  const status = document.getElementById("filterStatus").value.toLowerCase();
  const dept = document.getElementById("filterDepartment").value.toLowerCase();
  const date = document.getElementById("filterDate").value; // YYYY-MM-DD

  const rows = document.querySelectorAll("#collectionsTable tbody tr");

  rows.forEach(row => {
    const rowDept = row.cells[2].innerText.toLowerCase();
    const rowStatus = row.cells[4].innerText.toLowerCase();
    const rowDate = row.cells[5].innerText; // matches your Date column

    const matchesStatus = (status === "all" || rowStatus === status);
    const matchesDept = (!dept || rowDept.includes(dept));
    const matchesDate = (!date || rowDate === date);

    row.style.display = (matchesStatus && matchesDept && matchesDate) ? "" : "none";
  });
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("filterDepartment").addEventListener("input", filterCollections);
  document.getElementById("filterDate").addEventListener("change", filterCollections);
  document.getElementById("filterStatus").addEventListener("change", filterCollections);

  // Reset button logic
  document.getElementById("resetFilters").addEventListener("click", () => {
    document.getElementById("filterStatus").value = "all";
    document.getElementById("filterDepartment").value = "";
    document.getElementById("filterDate").value = "";

    filterCollections(); // Show all rows again
  });
});

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

// ===================== INITIAL LOAD =====================
loadCollections();
</script>
