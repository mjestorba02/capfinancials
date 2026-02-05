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
            <th class="px-4 py-3">Department</th>
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
<div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl p-6 w-full max-w-lg max-h-screen overflow-y-auto shadow-lg">
    <h2 class="text-xl font-semibold mb-4">New Budget Request</h2>
    <form id="requestForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-600">Department</label>
        <select id="department" class="w-full border rounded-lg px-3 py-2 mt-1" required>
          <option value="">Select Department</option>
          <option value="HR">HR</option>
          <option value="IT">IT</option>
          <option value="Finance">Finance</option>
          <option value="Operations">Operations</option>
          <option value="Marketing">Marketing</option>
          <option value="Sales">Sales</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Purpose</label>
        <textarea id="purpose" class="w-full border rounded-lg px-3 py-2 mt-1" rows="3" required></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Amount</label>
        <input type="number" id="amount" class="w-full border rounded-lg px-3 py-2 mt-1" min="0" max="100000" required>
        <p class="text-xs text-slate-500 mt-1">Maximum limit: ₱100,000</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Attendance Required</label>
        <select id="attendance_required" class="w-full border rounded-lg px-3 py-2 mt-1">
          <option value="No">No</option>
          <option value="Yes">Yes</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Item List</label>
        <textarea id="item_list" class="w-full border rounded-lg px-3 py-2 mt-1" rows="2" placeholder="List items to be purchased"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Approval Required</label>
        <select id="approval_required" class="w-full border rounded-lg px-3 py-2 mt-1" required>
          <option value="No">No</option>
          <option value="Yes">Yes</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Requesting Account</label>
        <select id="requesting_account" class="w-full border rounded-lg px-3 py-2 mt-1">
          <option value="">Select account for requesting</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600">Approval Account</label>
        <select id="approval_account" class="w-full border rounded-lg px-3 py-2 mt-1">
          <option value="">Select account for approval</option>
        </select>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Submit</button>
      </div>
    </form>
  </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-screen overflow-y-auto p-6 relative">
    <h2 class="text-lg font-bold mb-4">Approve Budget Request</h2>
    <div id="approveContent" class="space-y-2 text-sm"></div>
    
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-slate-700">
      <p><strong>Note:</strong> Approval can be from either:</p>
      <p>• Approver Account</p>
      <p>• Finance/Admin Account</p>
    </div>

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
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-screen overflow-y-auto">
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
<<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
const apiUrl = "http://localhost/financial2/api/budget_requests_api.php";

// Toast helper
function showToast(message, type) {
  Toastify({
    text: message,
    duration: 4000,
    close: true,
    style: {
      background: type === "success"
        ? "linear-gradient(to right, #00b09b, #96c93d)"
        : "linear-gradient(to right, #ff5f6d, #ffc371)"
    }
  }).showToast();
}

// Load table data
async function loadRequests() {
  try {
    const res = await fetch(apiUrl);
    const data = await res.json();
    const tbody = document.getElementById("budgetTableBody");
    tbody.innerHTML = "";

    data.forEach(req => {
      // build action buttons based on session account_type
      let actions = `<button class="text-blue-600 hover:text-blue-800" onclick="openApproveModal(${req.id})" title="View Request"><i class="bx bx-show text-xl"></i></button>`;
      if (window.currentUser && parseInt(window.currentUser.account_type) === 1 && req.status !== 'Approved') {
        actions += ` <button class="text-green-600 hover:text-green-800 ml-2" onclick="openApproveModal(${req.id})" title="Approve"><i class="bx bx-check text-xl"></i></button>`;
      }

      tbody.innerHTML += `
        <tr>
          <td class="px-4 py-3 font-medium">${req.request_id}</td>
          <td class="px-4 py-3">${req.department}</td>
          <td class="px-4 py-3">${req.purpose}</td>
          <td class="px-4 py-3 text-green-600">₱${parseFloat(req.amount).toLocaleString()}</td>
          <td class="px-4 py-3">${req.status || "Pending"}</td>
          <td class="px-4 py-3">${req.request_date || "-"}</td>
          <td class="px-4 py-3 text-right">${actions}</td>
        </tr>
      `;
    });
  } catch (err) {
    console.error("Fetch error:", err);
    showToast("Failed to load budget requests.", "error");
  }
}

// load session info (user + account_type) so we can control approve access in UI
function loadSession() {
  return fetch('../api/session.php')
    .then(res => res.json())
    .then(data => {
      if (data.logged_in && data.user) {
        window.currentUser = data.user;
      } else {
        window.currentUser = null;
      }
    })
    .catch(err => {
      console.error('Failed to load session', err);
      window.currentUser = null;
    });
}

// Handle modal open/close
function openModal() {
  document.getElementById("requestModal").classList.remove("hidden");
}
function closeModal() {
  document.getElementById("requestModal").classList.add("hidden");
  document.getElementById("requestForm").reset();
}

// Handle form submit
document.addEventListener("DOMContentLoaded", () => {
  // load session first (so we know if user can approve), then load accounts and requests
  loadSession().then(() => {
    loadAccountsForSelects();
    loadRequests();
  });

  // Load accounts to populate requesting/approval account selects
  async function loadAccountsForSelects() {
    try {
      const res = await fetch('http://localhost/financial2/api/chart_of_accounts_api.php');
      const data = await res.json();
      const reqSelect = document.getElementById('requesting_account');
      const aprSelect = document.getElementById('approval_account');
      if (!data || !Array.isArray(data)) return;
      data.forEach(acc => {
        const opt = document.createElement('option');
        opt.value = acc.account_code || acc.id || acc.account_name;
        opt.textContent = `${acc.account_code || ''} ${acc.account_name || ''}`.trim();
        reqSelect.appendChild(opt.cloneNode(true));
        aprSelect.appendChild(opt);
      });
    } catch (err) {
      console.error('Failed to load accounts for selects', err);
    }
  }
  // loadAccountsForSelects will be invoked after session is loaded

  const form = document.getElementById("requestForm");
  if (!form) return console.error("Form not found!");

    form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const department = document.getElementById("department").value.trim();
    const purpose = document.getElementById("purpose").value.trim();
    const amount = document.getElementById("amount").value.trim();
    const attendance_required = document.getElementById("attendance_required").value;
    const item_list = document.getElementById("item_list").value.trim();
    const approval_required = document.getElementById("approval_required").value;
    const requesting_account = document.getElementById("requesting_account").value.trim();
    const approval_account = document.getElementById("approval_account").value.trim();

    if (!department || !purpose || !amount) {
      showToast("Please fill in required fields.", "error");
      return;
    }

    // Validate amount does not exceed 100,000
    if (parseFloat(amount) > 100000) {
      showToast("Amount cannot exceed ₱100,000.", "error");
      return;
    }

    try {
      console.log("Submitting to API:", { department, purpose, amount, attendance_required, item_list, approval_required, requesting_account, approval_account });
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ department, purpose, amount, attendance_required, item_list, approval_required, requesting_account, approval_account })
      });

      const result = await response.json();
      console.log("API response:", result);

      if (result.success) {
        showToast("Request added successfully!", "success");
        closeModal();
        loadRequests();
      } else {
        showToast(result.error || "Submission failed.", "error");
      }
    } catch (error) {
      console.error("Submit error:", error);
      showToast("Could not submit request. Check logs or API.", "error");
    }
  });
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
      // pass through accounts if present
      requesting_account: request.requesting_account || null,
      approval_account: request.approval_account || null,
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

// removed duplicate loadRequests listener; requests are loaded after session via the other DOMContentLoaded handler

//Approve
let approveRequestData = null; // store the full request object

// Open modal (fetch full request by ID)
async function openApproveModal(id) {
  try {
    const res = await fetch(apiUrl);
    const data = await res.json();
    const request = data.find(r => r.id == id);
    if (!request) return showToast('Request not found', 'error');

    approveRequestData = request;
    document.getElementById("approveContent").innerHTML = `
      <p><b>Request ID:</b> ${request.request_id}</p>
      <p><b>Department:</b> ${request.department}</p>
      <p><b>Purpose:</b> ${request.purpose}</p>
      <p><b>Items:</b> ${request.item_list || '-'}</p>
      <p><b>Amount:</b> ₱${parseFloat(request.amount).toLocaleString()}</p>
      <p><b>Requesting Account:</b> ${request.requesting_account || '-'}</p>
      <p><b>Approval Account:</b> ${request.approval_account || '-'}</p>
      <p><b>Status:</b> ${request.status || 'Pending'}</p>
    `;

    document.getElementById("approveModal").classList.remove("hidden");
    document.getElementById("approveModal").classList.add("flex");
  } catch (err) {
    console.error('openApproveModal error', err);
    showToast('Failed to load request details', 'error');
  }
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
        attendance_required: approveRequestData.attendance_required || null,
        item_list: approveRequestData.item_list || null,
        approval_required: approveRequestData.approval_required || null,
        requesting_account: approveRequestData.requesting_account || null,
        approval_account: approveRequestData.approval_account || null,
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