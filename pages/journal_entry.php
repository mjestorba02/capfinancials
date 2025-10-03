<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Journal Entries</h1>
      <p class="text-sm text-slate-500">Manage and track your accounting journal entries</p>
    </div>
  </div>

  <!-- Journal Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <div class="overflow-x-auto">
      <!-- Filter -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Journal Entries List</h3>
        <div class="flex gap-2 items-center mb-4">
          <select id="filterType" class="border px-2 py-1">
            <option value="all">All Types</option>
            <option value="debit">Debit</option>
            <option value="credit">Credit</option>
          </select>

          <input type="text" id="filterAccount" placeholder="Filter by Account" class="border px-2 py-1">

          <input type="date" id="filterDate" class="border px-2 py-1">

          <!-- Reset Button -->
          <button id="resetFilters" class="bg-red-500 text-white px-3 py-1 rounded">
            Reset Filters
          </button>
        </div>
      </div>
      <table id="journalTable" class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Entry #</th>
            <th class="px-4 py-3">Account</th>
            <th class="px-4 py-3">Credit</th>
            <th class="px-4 py-3">Debit</th>
            <th class="px-4 py-3">Description</th>
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

<!-- Add Journal Entry Modal -->
<div id="addJournalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add New Journal Entry</h2>
    <form id="addJournalForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Account</label>
        <input name="account" type="text" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" placeholder="Enter account name" />
      </div>
      <div>
        <label class="block text-sm font-medium">Type</label>
        <select name="type" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm">
          <option value="Debit">Debit</option>
          <option value="Credit">Credit</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">Amount</label>
        <input name="amount" type="number" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" placeholder="Enter amount" />
      </div>
      <div>
        <label class="block text-sm font-medium">Description</label>
        <textarea name="description" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" placeholder="Optional description"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium">Date</label>
        <input name="date" type="date" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" />
      </div>
      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" onclick="closeAddJournalModal()" class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-orange-500 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- View Journal Entry Modal -->
<div id="viewJournalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h2 class="text-xl font-bold mb-4">Journal Entry Details</h2>
    <div id="viewJournalDetails" class="space-y-2 text-sm"></div>
    <div class="flex justify-end mt-6">
      <button type="button" onclick="closeViewJournalModal()" class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Close</button>
    </div>
  </div>
</div>

<!-- Delete Journal Entry Modal -->
<div id="deleteJournalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Delete Journal Entry</h2>
    <p class="text-sm text-slate-600 mb-6">Are you sure you want to delete this journal entry?</p>
    <div class="flex justify-end space-x-3">
      <button type="button" onclick="closeDeleteJournalModal()" class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Cancel</button>
      <button type="button" id="confirmDeleteBtn" class="px-4 py-2 text-sm bg-red-500 text-white rounded-lg">Delete</button>
    </div>
  </div>
</div>

<!-- Toastify -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
';

adminLayout($children);
?>

<script>
const journalApiUrl = "https://financial.health-ease-hospital.com/api/journal.php";

// ===================== LOAD JOURNAL =====================
async function loadJournal() {
  const res = await fetch(journalApiUrl);
  const data = await res.json();
  console.log("Journal API response:", data);
  const tbody = document.querySelector("#journalTable tbody");
  tbody.innerHTML = "";

  data.forEach(item => {
    const row = `
      <tr>
        <td class="px-4 py-3 font-medium">${item.entry_no}</td>
        <td class="px-4 py-3">${item.account}</td>
        <td class="px-4 py-3">₱${parseFloat(item.credit).toLocaleString()}</td>
        <td class="px-4 py-3">₱${parseFloat(item.debit).toLocaleString()}</td>
        <td class="px-4 py-3">${item.description || "-"}</td>
        <td class="px-4 py-3">${item.entry_date}</td>
        <td class="px-4 py-3 text-right space-x-2">
            <button onclick="openDeleteJournalModal(${item.entry_no})" 
                class="text-red-600 hover:text-red-800"><i class="bx bx-trash text-xl"></i></button>
        </td>
      </tr>
    `;
    tbody.innerHTML += row;
  });
}

// ===================== ADD JOURNAL ENTRY =====================
const addJournalFormEl = document.getElementById("addJournalForm");
if (addJournalFormEl) {
  addJournalFormEl.addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const newEntry = Object.fromEntries(formData.entries());

  try {
    const res = await fetch(journalApiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(newEntry)
    });
    const result = await res.json();

    if (result && result.success) {
      showToast(`Journal entry added successfully!`, "success");
      e.target.reset();
      closeAddJournalModal();
      loadJournal();
    } else {
      showToast("Error: " + (result.error || "Failed to save."), "error");
    }
  } catch (err) {
    console.error(err);
    showToast("Error: Unable to reach server.", "error");
  }
  });
} else {
  console.warn('addJournalForm not found on this page');
}

// ===================== VIEW JOURNAL =====================
function openViewJournalModal(entry_no, account, type, credit, debit, description, date) {
  const details = `
    <p><strong>Entry #:</strong> ${entry_no}</p>
    <p><strong>Account:</strong> ${account}</p>
    <p><strong>Type:</strong> ${type}</p>
    <p><strong>Credit:</strong> ₱${parseFloat(credit).toLocaleString()}</p>
    <p><strong>Debit:</strong> ₱${parseFloat(debit).toLocaleString()}</p>
    <p><strong>Description:</strong> ${description || "-"}</p>
    <p><strong>Date:</strong> ${date}</p>
  `;
  document.getElementById("viewJournalDetails").innerHTML = details;
  document.getElementById("viewJournalModal").classList.remove("hidden");
  document.getElementById("viewJournalModal").classList.add("flex");
}

function closeViewJournalModal() {
  document.getElementById("viewJournalModal").classList.add("hidden");
  document.getElementById("viewJournalModal").classList.remove("flex");
}

// ===================== MODAL HELPERS =====================
function openAddJournalModal() {
  document.getElementById("addJournalModal").classList.remove("hidden");
  document.getElementById("addJournalModal").classList.add("flex");
}
function closeAddJournalModal() {
  document.getElementById("addJournalModal").classList.add("hidden");
  document.getElementById("addJournalModal").classList.remove("flex");
}

// ===================== FILTER =====================
function filterJournal() {
  const type = document.getElementById("filterType").value.toLowerCase();
  const account = document.getElementById("filterAccount").value.toLowerCase();
  const date = document.getElementById("filterDate").value;

  const rows = document.querySelectorAll("#journalTable tbody tr");
  rows.forEach(row => {
    const rowType = row.cells[2].innerText.toLowerCase();
    const rowAccount = row.cells[1].innerText.toLowerCase();
    const rowDate = row.cells[5].innerText;

    const matchesType = (type === "all" || rowType === type);
    const matchesAccount = (!account || rowAccount.includes(account));
    const matchesDate = (!date || rowDate === date);

    row.style.display = (matchesType && matchesAccount && matchesDate) ? "" : "none";
  });
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("filterAccount").addEventListener("input", filterJournal);
  document.getElementById("filterDate").addEventListener("change", filterJournal);
  document.getElementById("filterType").addEventListener("change", filterJournal);

  document.getElementById("resetFilters").addEventListener("click", () => {
    document.getElementById("filterType").value = "all";
    document.getElementById("filterAccount").value = "";
    document.getElementById("filterDate").value = "";
    filterJournal();
  });
});

// ===================== TOAST =====================
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

// Delete
let deleteEntryNo = null;

// Open delete modal
function openDeleteJournalModal(id) {
  deleteEntryNo = id;
  document.getElementById("deleteJournalModal").classList.remove("hidden");
  document.getElementById("deleteJournalModal").classList.add("flex");
}

// Close delete modal (was missing - calling this previously threw and caused dual toasts)
function closeDeleteJournalModal() {
  deleteEntryNo = null;
  const modal = document.getElementById("deleteJournalModal");
  if (!modal) return;
  try {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  } catch (e) {
    console.error('closeDeleteJournalModal error', e);
  }
}

// Confirm delete
const confirmDeleteBtnEl = document.getElementById("confirmDeleteBtn");
if (confirmDeleteBtnEl) {
  confirmDeleteBtnEl.addEventListener("click", async () => {
  if (!deleteEntryNo) return;

  try {
    const res = await fetch(journalApiUrl, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: deleteEntryNo })
    });

    let result = null;
    try {
      result = await res.json();
    } catch {
      throw new Error("Invalid JSON response");
    }

    if (result && result.success) {
      showToast("Journal entry deleted successfully!", "success");
      closeDeleteJournalModal();
      loadJournal();
    } else {
      showToast("Error: " + (result.error || "Failed to delete."), "error");
    }
  } catch (err) {
    console.error("Delete error:", err);
    showToast("Error: Unable to reach server.", "error");
  }
  });
} else {
  console.warn('confirmDeleteBtn not present on this page');
}

// ===================== INITIAL LOAD =====================
loadJournal();
</script>