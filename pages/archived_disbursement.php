<?php
include '../layout/adminLayout.php';

$children = '
<!-- Include Toastify -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Archived Disbursements</h1>
      <p class="text-sm text-slate-500">View and manage archived disbursement records</p>
    </div>
  </div>

  <!-- Archived Disbursement Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <!-- Filter -->
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Archived List</h3>
      <div class="flex items-center gap-3">
        <!-- Filter Status -->
        <select id="filterStatus" onchange="filterArchivedDisbursements()" class="border px-3 py-2 rounded-lg text-sm">
          <option value="all">All Status</option>
          <option value="Released">Released</option>
          <option value="pending">Pending</option>
        </select>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table id="archivedDisbursementTable" class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Voucher #</th>
            <th class="px-4 py-3">Vendor</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Amount</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3">Archived On</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="archivedDisbursementBody" class="divide-y divide-slate-200">
          <!-- Rows loaded dynamically -->
        </tbody>
      </table>
    </div>
  </div>

</main>
</div>

<!-- View Modal -->
<div id="viewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl p-6 w-full max-w-md max-h-screen overflow-y-auto shadow-lg">
    <h2 class="text-xl font-semibold mb-4">Archived Disbursement Details</h2>
    <div id="viewContent" class="space-y-2 text-sm"></div>
    <div class="flex justify-end mt-4">
      <button onclick="closeViewModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Close</button>
    </div>
  </div>
</div>

<!-- Retrieve Confirmation Modal -->
<div id="retrieveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl p-6 w-full max-w-md max-h-screen overflow-y-auto shadow-lg">
    <h2 class="text-xl font-semibold mb-4">Confirm Retrieval</h2>
    <p class="text-sm text-slate-600 mb-6">
      Are you sure you want to retrieve this disbursement from the archive? It will be restored to active disbursements.
    </p>
    <div class="flex justify-end space-x-2">
      <button onclick="closeRetrieveModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">
        Cancel
      </button>
      <button id="confirmRetrieveBtn" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
        Confirm
      </button>
    </div>
  </div>
</div>

<!-- Permanent Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl p-6 w-full max-w-md max-h-screen overflow-y-auto shadow-lg">
    <h2 class="text-xl font-semibold mb-4 text-red-600">Confirm Permanent Deletion</h2>
    <p class="text-sm text-slate-600 mb-6">
      <strong>Warning:</strong> This action will permanently delete this record from the archive. This cannot be undone!
    </p>
    <div class="flex justify-end space-x-2">
      <button onclick="closeDeleteModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">
        Cancel
      </button>
      <button id="confirmDeleteBtn" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">
        Delete Permanently
      </button>
    </div>
  </div>
</div>

';

adminLayout($children);
?>

<script>
const apiUrl = "https://financial.health-ease-hospital.com/api/archived_disbursements_api.php";
const tableBody = document.getElementById("archivedDisbursementBody");
const viewModal = document.getElementById("viewModal");

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

// ================= FETCH & DISPLAY =================
async function loadArchivedDisbursements() {
  try {
    const res = await fetch(apiUrl);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    tableBody.innerHTML = (data || []).map(d => `
      <tr>
        <td class="px-4 py-3 font-medium">${d.voucher_no}</td>
        <td class="px-4 py-3">${d.vendor}</td>
        <td class="px-4 py-3">${d.category}</td>
        <td class="px-4 py-3 text-red-600">₱${parseFloat(d.amount || 0).toLocaleString()}</td>
        <td class="px-4 py-3">
          <span class="${d.status === "Released" ? "text-green-600" : "text-yellow-600"} font-semibold">
            ${d.status}
          </span>
        </td>
        <td class="px-4 py-3">${d.disbursement_date}</td>
        <td class="px-4 py-3 text-xs text-gray-500">${d.archived_at}</td>
        <td class="px-4 py-3 text-right space-x-2">
          <button onclick="viewArchivedDisbursement(${d.id})" class="text-gray-600 hover:text-gray-800" title="View"><i class="bx bx-show text-xl"></i></button>
          <button onclick="openRetrieveModal(${d.id})" class="text-green-600 hover:text-green-800" title="Retrieve"><i class="bx bx-redo text-xl"></i></button>
          <button onclick="openDeleteModal(${d.id})" class="text-red-600 hover:text-red-800" title="Permanently Delete"><i class="bx bx-trash text-xl"></i></button>
        </td>
      </tr>
    `).join("");
  } catch (err) {
    console.error("loadArchivedDisbursements error:", err);
    showToast("Failed to load archived disbursements.", "error");
  }
}

// ================= VIEW =================
async function viewArchivedDisbursement(id) {
  try {
    const res = await fetch(apiUrl + "?id=" + id);
    if (!res.ok) throw new Error("Failed to fetch record");
    const d = await res.json();
    document.getElementById("viewContent").innerHTML = `
      <p><strong>Voucher #:</strong> ${d.voucher_no}</p>
      <p><strong>Vendor:</strong> ${d.vendor}</p>
      <p><strong>Category:</strong> ${d.category}</p>
      <p><strong>Amount:</strong> ₱${parseFloat(d.amount || 0).toLocaleString()}</p>
      <p><strong>Status:</strong> ${d.status}</p>
      <p><strong>Date:</strong> ${d.disbursement_date}</p>
      <p><strong>Archived On:</strong> ${d.archived_at}</p>
      <p><strong>Archived By:</strong> ${d.archived_by || "N/A"}</p>
      <p><strong>Archive Reason:</strong> ${d.archive_reason || "N/A"}</p>
    `;
    viewModal.classList.remove("hidden");
  } catch (err) {
    console.error("viewArchivedDisbursement error:", err);
    showToast("Failed to fetch disbursement details.", "error");
  }
}

function closeViewModal() { 
  viewModal.classList.add("hidden"); 
}

// ================= RETRIEVE FLOW =================
let retrieveId = null;
function openRetrieveModal(id) {
  retrieveId = id;
  document.getElementById("retrieveModal").classList.remove("hidden");
}

function closeRetrieveModal() {
  retrieveId = null;
  document.getElementById("retrieveModal").classList.add("hidden");
}

document.getElementById("confirmRetrieveBtn").addEventListener("click", async () => {
  if (!retrieveId) return;
  try {
    const res = await fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "retrieve", id: retrieveId })
    });
    const result = await res.json();
    if (result.success) {
      showToast("Disbursement retrieved successfully!", "success");
      closeRetrieveModal();
      loadArchivedDisbursements();
    } else {
      showToast("Error: " + result.error, "error");
    }
  } catch (err) {
    console.error("retrieve error:", err);
    showToast("Failed to retrieve disbursement.", "error");
  }
});

// ================= DELETE FLOW (Permanent) =================
let deleteId = null;
function openDeleteModal(id) {
  deleteId = id;
  document.getElementById("deleteModal").classList.remove("hidden");
}

function closeDeleteModal() {
  deleteId = null;
  document.getElementById("deleteModal").classList.add("hidden");
}

document.getElementById("confirmDeleteBtn").addEventListener("click", async () => {
  if (!deleteId) return;
  try {
    const res = await fetch(apiUrl, {
      method: "DELETE",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ id: deleteId }).toString()
    });
    const result = await res.json();
    if (result.success) {
      showToast("Record permanently deleted!", "success");
      closeDeleteModal();
      loadArchivedDisbursements();
    } else {
      showToast("Error: " + result.error, "error");
    }
  } catch (err) {
    console.error("delete error:", err);
    showToast("Failed to delete record.", "error");
  }
});

// ================= FILTER =================
function filterArchivedDisbursements() {
  const status = document.getElementById("filterStatus").value.toLowerCase();
  const rows = document.querySelectorAll("#archivedDisbursementBody tr");

  rows.forEach(row => {
    const rowStatus = row.cells[4].textContent.trim().toLowerCase();
    if (status === "all" || rowStatus === status) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}

// Load on page start
loadArchivedDisbursements();
</script>
