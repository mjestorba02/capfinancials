<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Budget Requests</h1>
      <p class="text-sm text-slate-500">Submit and manage budget requests</p>
    </div>
    <button 
      onclick="openModal()" 
      class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      New Request
    </button>
  </div>

  <!-- Requests Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Budgets List</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Request ID</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Purpose</th>
            <th class="px-4 py-3">Amount</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="budgetTableBody" class="divide-y divide-slate-200">
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal for New Request -->
<div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-lg">
    <h2 class="text-xl font-semibold mb-4">New Budget Request</h2>
    <form id="requestForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-600">Category</label>
        <input type="text" id="department" class="w-full border rounded-lg px-3 py-2 mt-1" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Purpose</label>
        <textarea id="purpose" class="w-full border rounded-lg px-3 py-2 mt-1" rows="3" required></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Amount</label>
        <input type="number" id="amount" class="w-full border rounded-lg px-3 py-2 mt-1" required>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Submit</button>
      </div>
    </form>
  </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-lg font-bold mb-4">Approve Budget Request</h2>
    <div id="approveContent" class="space-y-2 text-sm"></div>

    <div class="flex justify-end space-x-2 mt-6">
      <button type="button" onclick="closeApproveModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">
        Cancel
      </button>
      <button id="confirmApproveBtn" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
        Approve
      </button>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
    <div class="p-6">
      <h2 class="text-lg font-semibold text-gray-700">Delete Request</h2>
      <p class="text-sm text-gray-500 mt-2">Are you sure you want to delete this request? This action cannot be undone.</p>
    </div>
    <div class="flex justify-end space-x-2 px-6 py-4 border-t">
      <button onclick="closeDeleteModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Cancel</button>
      <button onclick="confirmDelete()" class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-lg">Delete</button>
    </div>
  </div>
</div>

';

adminLayout($children);
?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
const apiUrl = "http://localhost/prefect/api/budget_requests_api.php";

// Toast function
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

// Load table data
async function loadRequests() {
  const res = await fetch(apiUrl);
  const data = await res.json();
  const tbody = document.getElementById("budgetTableBody");
  tbody.innerHTML = "";

  data.forEach(req => {
    tbody.innerHTML += `
      <tr>
        <td class="px-4 py-3 font-medium">${req.request_id}</td>
        <td class="px-4 py-3">${req.department}</td>
        <td class="px-4 py-3">${req.purpose}</td>
        <td class="px-4 py-3 text-green-600">₱${parseFloat(req.amount).toLocaleString()}</td>
        <td class="px-4 py-3"><span class="font-semibold ${req.status === "Pending" ? "text-yellow-600" : "text-green-600"}">${req.status}</span></td>
        <td class="px-4 py-3">${req.request_date}</td>
        <td class="px-4 py-3 text-right space-x-2">
          ${req.status !== "Approved" ? `
          <button class="text-green-600 hover:text-green-800" onclick="openApproveModal(${req.id})" title="Approve">
            <i class="bx bx-check-circle text-xl"></i>
          </button>` : ''}

          <button class="text-red-600 hover:text-red-800" onclick="openDeleteModal(${req.id})" title="Delete">
            <i class="bx bx-trash text-xl"></i>
          </button>
        </td>
      </tr>
    `;
  });
}

// Handle form submit (New request)
document.getElementById("requestForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const department = document.getElementById("department").value;
  const purpose = document.getElementById("purpose").value;
  const amount = document.getElementById("amount").value;

  const res = await fetch(apiUrl, {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ department, purpose, amount })
  });
  const result = await res.json();
  if (result.success) {
    closeModal();
    showToast(result.message, "success");
    loadRequests();
  } else {
    showToast(result.error, "error");
  }
});

// Approve request
async function approveRequest(id) {
  if (!confirm("Approve this budget request?")) return;

  const res = await fetch(apiUrl);
  const data = await res.json();
  const request = data.find(r => r.id == id);

  const response = await fetch(apiUrl, {
    method: "PUT",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({
      id: request.id,
      department: request.department,
      purpose: request.purpose,
      amount: request.amount,
      status: "Approved"
    })
  });
  const result = await response.json();
  if (result.success) {
    showToast("Request approved and pushed to Planning!", "success");
    loadRequests();
  } else {
    showToast(result.error, "error");
  }
}

// Delete request
async function deleteRequest(id) {
  if (!confirm("Are you sure to delete this request?")) return;
  const res = await fetch(apiUrl, {
    method: "DELETE",
    body: `id=${id}`
  });
  const result = await res.json();
  if (result.success) {
    showToast(result.message, "success");
    loadRequests();
  } else {
    showToast(result.error, "error");
  }
}

// Modal handlers
function openModal() { document.getElementById("requestModal").classList.remove("hidden"); }
function closeModal() { 
  document.getElementById("requestModal").classList.add("hidden"); 
  document.getElementById("requestForm").reset(); 
}

document.addEventListener("DOMContentLoaded", loadRequests);

//Approve
let approveRequestData = null; // store the full request object

// Open modal
function openApproveModal(request) {
  approveRequestData = request;

  document.getElementById("approveContent").innerHTML = `
    <p><b>Category:</b> ${request.department}</p>
    <p><b>Purpose:</b> ${request.purpose}</p>
    <p><b>Amount:</b> ₱${parseFloat(request.amount).toLocaleString()}</p>
    <p><b>Status:</b> Pending → <span class="text-green-600">Approved</span></p>
  `;

  document.getElementById("approveModal").classList.remove("hidden");
  document.getElementById("approveModal").classList.add("flex");
}

// Close modal
function closeApproveModal() {
  document.getElementById("approveModal").classList.add("hidden");
  document.getElementById("approveModal").classList.remove("flex");
  approveRequestData = null;
}

// Confirm approve
document.getElementById("confirmApproveBtn").addEventListener("click", async () => {
  if (!approveRequestData) return;

  try {
    const response = await fetch(apiUrl, {
      method: "PUT",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify({
        id: approveRequestData.id,
        department: approveRequestData.department,
        purpose: approveRequestData.purpose,
        amount: approveRequestData.amount,
        status: "Approved"
      })
    });

    const result = await response.json();
    if (result.success) {
      showToast("Request approved and pushed to Planning!", "success");
      loadRequests();
      closeApproveModal();
    } else {
      showToast(result.error || "Approval failed", "error");
    }
  } catch (err) {
    console.error(err);
    showToast("Server error while approving", "error");
  }
});

//Delete
let deleteId = null;

function openDeleteModal(id) {
  deleteId = id;
  document.getElementById("deleteModal").classList.remove("hidden");
}

function closeDeleteModal() {
  deleteId = null;
  document.getElementById("deleteModal").classList.add("hidden");
}

async function confirmDelete() {
  if (!deleteId) return;

  const res = await fetch(apiUrl, {
    method: "DELETE",
    body: `id=${deleteId}`
  });
  const result = await res.json();

  if (result.success) {
    showToast(result.message, "success");
    loadRequests();
    closeDeleteModal();
  } else {
    showToast(result.error, "error");
  }
}
</script>