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
      <h1 class="text-2xl font-bold">Disbursements</h1>
      <p class="text-sm text-slate-500">Manage and track your company disbursements</p>
    </div>
    <button onclick="openAddModal()" class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Add Disbursement
    </button>
  </div>

  <!-- Disbursement Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <!-- Filter + Export -->
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Disbursement List</h3>
      <div class="flex items-center gap-3">
        <!-- Filter Status -->
        <select id="filterStatus" onchange="filterDisbursements()" class="border px-3 py-2 rounded-lg text-sm">
          <option value="all">All Status</option>
          <option value="Released">Released</option>
          <option value="pending">Pending</option>
        </select>

        
      </div>
    </div>
    <div class="overflow-x-auto">
      <table id="disbursementTable" class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Voucher #</th>
            <th class="px-4 py-3">Vendor</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Amount</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="disbursementBody" class="divide-y divide-slate-200">
          <!-- Rows loaded dynamically -->
        </tbody>
      </table>
    </div>
  </div>

</main>
</div>

<!-- Add/Edit Disbursement Modal -->
<div id="disbursementModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-lg">
    <h2 id="modalTitle" class="text-xl font-semibold mb-4">Add New Disbursement</h2>
    <form id="disbursementForm" class="space-y-4">
      <input type="hidden" id="disbursementId">

      <div>
        <label class="block text-sm font-medium text-slate-600">Vendor</label>
        <select id="vendorInput" class="w-full border rounded-lg px-3 py-2 mt-1">
          <option value="">Select vendor</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Category</label>
        <select id="categoryInput" class="w-full border rounded-lg px-3 py-2 mt-1">
          <option value="">Select category</option>
          <option value="Supplies">Supplies</option>
          <option value="Utilities">Utilities</option>
          <option value="Salaries">Salaries</option>
          <option value="Maintenance">Maintenance</option>
          <option value="Miscellaneous">Miscellaneous</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Amount</label>
        <input id="amountInput" type="number" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="Enter amount">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Status</label>
        <select id="statusInput" class="w-full border rounded-lg px-3 py-2 mt-1">
          <option value="Released">Released</option>
          <option value="Pending">Pending</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Date</label>
        <input id="dateInput" type="date" class="w-full border rounded-lg px-3 py-2 mt-1">
      </div>
      <div class="flex justify-end space-x-2 mt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
    <h2 class="text-xl font-semibold mb-4">Disbursement Details</h2>
    <div id="viewContent" class="space-y-2 text-sm"></div>
    <div class="flex justify-end mt-4">
      <button onclick="closeViewModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Close</button>
    </div>
  </div>
</div>

<!-- Release Confirmation Modal -->
<div id="releaseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
    <h2 class="text-xl font-semibold mb-4">Confirm Release</h2>
    <p class="text-sm text-slate-600 mb-6">
      Are you sure you want to mark this disbursement as <span class="font-semibold text-green-600">Released</span>?
    </p>
    <div class="flex justify-end space-x-2">
      <button onclick="closeReleaseModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">
        Cancel
      </button>
      <button id="confirmReleaseBtn" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
        Confirm
      </button>
    </div>
  </div>
</div>

';

adminLayout($children);
?>

<script>
const apiUrl = "https://financial.health-ease-hospital.com/api/disbursements_api.php";
const vendorApi = "https://financial.health-ease-hospital.com/api/vendors_api.php";
const tableBody = document.getElementById("disbursementBody");
const modal = document.getElementById("disbursementModal");
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
async function loadDisbursements() {
  const res = await fetch(apiUrl);
  const data = await res.json();
  tableBody.innerHTML = data.map(d => `
    <tr>
      <td class="px-4 py-3 font-medium">${d.voucher_no}</td>
      <td class="px-4 py-3">${d.vendor}</td>
      <td class="px-4 py-3">${d.category}</td>
      <td class="px-4 py-3 text-red-600">₱${d.amount}</td>
      <td class="px-4 py-3">
        <span class="${d.status === "Released" ? "text-green-600" : "text-yellow-600"} font-semibold">
          ${d.status}
        </span>
      </td>
      <td class="px-4 py-3">${d.disbursement_date}</td>
      <td class="px-4 py-3 text-right space-x-2">
        ${
          d.status === "Pending"
          ? `<button onclick="releaseDisbursement(${d.id})" 
                    class="text-green-600 hover:text-green-800" 
                    title="Release">
                <i class="bx bx-check-circle text-xl"></i>
              </button>`
          : ""
        }
        <button onclick="editDisbursement(${d.id})" 
                class="text-blue-600 hover:text-blue-800" title="Edit">
          <i class="bx bx-edit-alt text-xl"></i>
        </button>
        <button onclick="viewDisbursement(${d.id})" 
                class="text-gray-600 hover:text-gray-800" title="View">
          <i class="bx bx-show text-xl"></i>
        </button>
        <button onclick="deleteDisbursement(${d.id})" 
                class="text-red-600 hover:text-red-800" title="Delete">
          <i class="bx bx-trash text-xl"></i>
        </button>
      </td>
    </tr>
  `).join("");
}

// ================= LOAD DROPDOWNS =================
async function loadVendors() {
  try {
    const res = await fetch(vendorApi);
    const vendors = await res.json();

    const vendorSelect = document.getElementById("vendorInput");
    const amountInput = document.getElementById("amountInput");

    vendorSelect.innerHTML = `<option value="">Select vendor</option>`;
    vendors.forEach(v => {
      vendorSelect.innerHTML += `
        <option value="${v.vendor}" data-amount="${v.amount}">
          ${v.vendor} - ₱${parseFloat(v.amount).toLocaleString()}
        </option>`;
    });

    // When user selects a vendor, auto-fill amount
    vendorSelect.addEventListener("change", () => {
      const selected = vendorSelect.options[vendorSelect.selectedIndex];
      amountInput.value = selected.getAttribute("data-amount") || "";
    });
  } catch (err) {
    console.error("Vendor load error:", err);
    showToast("Failed to load vendors.", "error");
  }
}

// ================= ADD / EDIT =================
function openAddModal() {
  document.getElementById("modalTitle").innerText = "Add New Disbursement";
  document.getElementById("disbursementId").value = "";
  document.getElementById("disbursementForm").reset();
  loadVendors();
  modal.classList.remove("hidden");
}
function closeModal() { modal.classList.add("hidden"); }

document.getElementById("disbursementForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const id = document.getElementById("disbursementId").value;
  const payload = {
    vendor: document.getElementById("vendorInput").value,
    category: document.getElementById("categoryInput").value,
    amount: document.getElementById("amountInput").value,
    status: document.getElementById("statusInput").value,
    disbursement_date: document.getElementById("dateInput").value
  };

  let res;
  if (id) {
    payload.id = id;
    res = await fetch(apiUrl, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
  } else {
    res = await fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
  }
  const result = await res.json();
  if (result.success) {
    showToast("Disbursement saved successfully!", "success");
    closeModal();
    loadDisbursements();
  } else {
    showToast("Error: " + result.error, "error");
  }
});

// ================= EDIT =================
async function editDisbursement(id) {
  const res = await fetch(apiUrl + "?id=" + id);
  const d = await res.json();
  loadVendors();
  document.getElementById("modalTitle").innerText = "Edit Disbursement";
  document.getElementById("disbursementId").value = d.id;
  document.getElementById("vendorInput").value = d.vendor;
  document.getElementById("categoryInput").value = d.category;
  document.getElementById("amountInput").value = d.amount;
  document.getElementById("statusInput").value = d.status;
  document.getElementById("dateInput").value = d.disbursement_date;
  modal.classList.remove("hidden");
}

// ================= VIEW =================
async function viewDisbursement(id) {
  const res = await fetch(apiUrl + "?id=" + id);
  const d = await res.json();
  document.getElementById("viewContent").innerHTML = `
    <p><strong>Voucher #:</strong> ${d.voucher_no}</p>
    <p><strong>Vendor:</strong> ${d.vendor}</p>
    <p><strong>Category:</strong> ${d.category}</p>
    <p><strong>Amount:</strong> ₱${d.amount}</p>
    <p><strong>Status:</strong> ${d.status}</p>
    <p><strong>Date:</strong> ${d.disbursement_date}</p>
  `;
  viewModal.classList.remove("hidden");
}
function closeViewModal() { viewModal.classList.add("hidden"); }

// ================= DELETE =================
async function deleteDisbursement(id) {
  if (!confirm("Are you sure you want to delete this record?")) return;
  const res = await fetch(apiUrl, { method: "DELETE", body: "id=" + id });
  const result = await res.json();
  if (result.success) {
    showToast("Disbursement deleted!", "success");
    loadDisbursements();
  } else {
    showToast("Error: " + result.error, "error");
  }
}

// ================= FILTER =================
function filterDisbursements() {
  const status = document.getElementById("filterStatus").value.toLowerCase();
  const rows = document.querySelectorAll("#disbursementBody tr");

  rows.forEach(row => {
    const rowStatus = row.cells[4].textContent.trim().toLowerCase();
    if (status === "all" || rowStatus === status) {
      row.style.display = "";   // ✅ show
    } else {
      row.style.display = "none"; // ✅ hide
    }
  });
}

// Dummy Export
function exportDisbursements() {
  showToast("Export disbursements functionality not implemented yet!", "error");
}

// Release
function releaseDisbursement(id) {
  if (!confirm("Mark this disbursement as Released?")) return;

  fetch("api/disbursements_api.php", {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id: id,
      status: "Released"
    })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert("Disbursement released successfully!");
        loadDisbursements(); // reload your table
      } else {
        alert("Error: " + data.error);
      }
    });
}

let releaseId = null; // store which disbursement to release

function releaseDisbursement(id) {
  releaseId = id;
  document.getElementById("releaseModal").classList.remove("hidden");
}

function closeReleaseModal() {
  releaseId = null;
  document.getElementById("releaseModal").classList.add("hidden");
}

document.getElementById("confirmReleaseBtn").addEventListener("click", async () => {
  if (!releaseId) return;

  const res = await fetch(apiUrl, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id: releaseId,
      status: "Released"
    })
  });
  const result = await res.json();

  if (result.success) {
    showToast("Disbursement released successfully!", "success");
    closeReleaseModal();
    loadDisbursements();
  } else {
    showToast("Error: " + result.error, "error");
  }
});

// Load on page start
loadDisbursements();
</script>
