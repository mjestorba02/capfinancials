<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Budget Planning Section -->
  <div class="mb-12">
    <h1 class="text-2xl font-bold">Budget Planning</h1>
    <p class="text-sm text-slate-500 mb-4">Plan, allocate, and track your budgets effectively</p>

    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <h3 class="text-lg font-semibold mb-4">Budget Plans</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
              <th class="px-4 py-3">Request ID</th>
              <th class="px-4 py-3">Category</th>
              <th class="px-4 py-3">Purpose</th>
              <th class="px-4 py-3">Allocated Budget</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Date</th>
            </tr>
          </thead>
          <tbody id="budgetTableBody" class="divide-y divide-slate-200"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Budget Allocation Section -->
  <div class="mb-8">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold">Budget Allocation</h1>
        <p class="text-sm text-slate-500">Allocate budgets across categories and projects</p>
      </div>
      <button onclick="openAddAllocationModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
        <i class="bx bx-plus-circle text-lg"></i> New Allocation
      </button>
    </div>

    <div class="flex items-center space-x-4 mb-4">
      <div>
        <label class="text-sm font-medium text-slate-600">From</label>
        <input type="date" id="filterFrom" class="border rounded-lg px-3 py-1 text-sm" />
      </div>
      <div>
        <label class="text-sm font-medium text-slate-600">To</label>
        <input type="date" id="filterTo" class="border rounded-lg px-3 py-1 text-sm" />
      </div>
      <button onclick="filterRequests()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">
        Filter
      </button>
      <button onclick="clearFilter()" class="bg-gray-300 hover:bg-gray-400 text-sm px-3 py-2 rounded-lg">
        Reset
      </button>
    </div>

    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Allocation List</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
              <th class="px-4 py-3">Category</th>
              <th class="px-4 py-3">Project</th>
              <th class="px-4 py-3">Allocated</th>
              <th class="px-4 py-3">Used</th>
              <th class="px-4 py-3">Remaining</th>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="allocationTableBody" class="divide-y divide-slate-200"></tbody>
        </table>
      </div>
    </div>
  </div>

</main>

<!-- Add Allocation Modal -->
<div id="addAllocationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add Budget Allocation</h2>
    <form id="allocationForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Category</label>
        <select id="departmentSelect" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" required></select>
      </div>
      <div>
        <label class="block text-sm font-medium">Project</label>
        <input type="text" id="project" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" placeholder="Enter project name" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Allocated Budget</label>
        <input type="number" id="allocated" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" placeholder="Enter amount" required />
      </div>
      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" onclick="closeAddAllocationModal()" class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Calculate Used Modal -->
<div id="calculateUsedModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Calculate Used Budget</h2>
    <form id="calculateUsedForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Category</label>
        <input type="text" id="modalDepartment" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" disabled>
      </div>
      <div>
        <label class="block text-sm font-medium">Project</label>
        <input type="text" id="modalProject" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" disabled>
      </div>
      <div>
        <label class="block text-sm font-medium">Allocated</label>
        <input type="number" id="modalAllocated" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" disabled>
      </div>
      <div>
        <label class="block text-sm font-medium">Used</label>
        <input type="number" id="modalUsed" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium">Remaining</label>
        <input type="number" id="modalRemaining" class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" disabled>
      </div>
      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" onclick="closeCalculateUsedModal()" class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Update Used</button>
      </div>
    </form>
  </div>
</div>
';

adminLayout($children);
?>

<!-- Scripts -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
const budgetApi = "https://financial.health-ease-hospital.com/api/budget_requests_api.php";
const allocationApi = "https://financial.health-ease-hospital.com/api/allocation_api.php";
let currentAllocationId = null;

// Store global data
let allRequests = [];
let allAllocations = [];

// Load Budget Planning (store globally)
async function loadRequests() {
  try {
    const res = await fetch(budgetApi);
    const data = await res.json();
    allRequests = data;
    renderRequests(allRequests);
  } catch (error) {
    console.error(error);
    Toastify({ text: "Failed to load requests", style: { background: "red" }, duration: 3000, close: true }).showToast();
  }
}

function renderRequests(data) {
  const tbody = document.getElementById("budgetTableBody");
  tbody.innerHTML = "";
  data.forEach(req => {
    tbody.innerHTML += `
      <tr>
        <td class="px-4 py-3 font-medium">${req.request_id}</td>
        <td class="px-4 py-3">${req.department}</td>
        <td class="px-4 py-3">${req.purpose}</td>
        <td class="px-4 py-3 text-green-600">₱${parseFloat(req.amount).toLocaleString()}</td>
        <td class="px-4 py-3">${req.status}</td>
        <td class="px-4 py-3">${req.request_date}</td>
      </tr>`;
  });
}

// Load Allocations
async function loadAllocations() {
  const res = await fetch(allocationApi);
  const data = await res.json();
  allAllocations = data;
  renderAllocations(allAllocations);
}

function renderAllocations(data) {
  const tbody = document.getElementById("allocationTableBody");
  tbody.innerHTML = "";
  data.forEach(a => {
    tbody.innerHTML += `
    <tr data-id="${a.id}">
      <td class="px-4 py-3 font-medium">${a.department}</td>
      <td class="px-4 py-3">${a.project}</td>
      <td class="px-4 py-3 text-blue-600">₱${parseFloat(a.allocated).toLocaleString()}</td>
      <td class="px-4 py-3 text-red-600">₱${parseFloat(a.used).toLocaleString()}</td>
      <td class="px-4 py-3 text-green-600">₱${parseFloat(a.allocated - a.used).toLocaleString()}</td>
      <td class="px-4 py-3">${a.created_at}</td>
      <td class="px-4 py-3 text-right space-x-2">
        <button onclick="openCalculateUsedModal(${a.id}, '${a.department}', '${a.project}', ${a.allocated}, ${a.used})" class="text-indigo-600 hover:text-indigo-800">
          <i class="bx bx-calculator text-xl"></i>
        </button>
      </td>
    </tr>`;
  });
}

// Filter by date range (affects both tables)
function filterRequests() {
  const from = document.getElementById("filterFrom").value;
  const to = document.getElementById("filterTo").value;

  // Filter requests
  let filteredReqs = allRequests;
  if (from) filteredReqs = filteredReqs.filter(r => new Date(r.request_date) >= new Date(from));
  if (to) filteredReqs = filteredReqs.filter(r => new Date(r.request_date) <= new Date(to));
  renderRequests(filteredReqs);

  // Filter allocations
  let filteredAllocs = allAllocations;
  if (from) filteredAllocs = filteredAllocs.filter(a => new Date(a.created_at) >= new Date(from));
  if (to) filteredAllocs = filteredAllocs.filter(a => new Date(a.created_at) <= new Date(to));
  renderAllocations(filteredAllocs);
}

// Clear filter
function clearFilter() {
  document.getElementById("filterFrom").value = "";
  document.getElementById("filterTo").value = "";
  renderRequests(allRequests);
  renderAllocations(allAllocations);
}

// ---- MODALS (Add, Calculate Used) ----
function calculateRemaining() {
  const allocated = parseFloat(document.getElementById("modalAllocated").value) || 0;
  const used = parseFloat(document.getElementById("modalUsed").value) || 0;
  document.getElementById("modalRemaining").value = allocated - used;
}

function openCalculateUsedModal(id, department, project, allocated, used) {
  currentAllocationId = id;
  document.getElementById("modalDepartment").value = department;
  document.getElementById("modalProject").value = project;
  document.getElementById("modalAllocated").value = allocated;
  document.getElementById("modalUsed").value = used;
  calculateRemaining();
  document.getElementById("calculateUsedModal").classList.remove("hidden");
  document.getElementById("calculateUsedModal").classList.add("flex");
}

function closeCalculateUsedModal() {
  document.getElementById("calculateUsedModal").classList.add("hidden");
  document.getElementById("calculateUsedModal").classList.remove("flex");
  currentAllocationId = null;
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("modalUsed").addEventListener("input", calculateRemaining);
});

// Submit Calculate Used modal
document.getElementById("calculateUsedForm").addEventListener("submit", async function(e){
  e.preventDefault();
  const used = parseFloat(document.getElementById("modalUsed").value) || 0;

  const res = await fetch(allocationApi, {
    method: "PUT",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({ id: currentAllocationId, used })
  });

  const result = await res.json();
  if(result.success){
    closeCalculateUsedModal();
    loadAllocations();
    alert("Used updated successfully!");
  } else {
    alert("Error: "+result.error);
  }
});

// Add Allocation modal
async function loadDepartments() {
  const res = await fetch(budgetApi);
  const data = await res.json();
  const select = document.getElementById("departmentSelect");
  select.innerHTML = "<option value=\"\">Select Department</option>";
  // Only include requests that are not Approved
  data.filter(d => (d.status || '').toString().trim().toLowerCase() !== 'approved')
    .forEach(d => {
      // store the numeric id as the option value and include department/purpose/amount as data attributes
      select.innerHTML += `<option value="${d.id}" data-department="${encodeURIComponent(d.department)}" data-purpose="${encodeURIComponent(d.purpose || '')}" data-amount="${d.amount}">${d.department} - ₱${parseFloat(d.amount).toLocaleString()}</option>`;
    });
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("departmentSelect").addEventListener("change", function(){
    const amount = this.selectedOptions[0].getAttribute("data-amount");
    document.getElementById("allocated").value = amount || "";
  });
});

document.getElementById("allocationForm").addEventListener("submit", async function(e){
  e.preventDefault();
  // departmentSelect now stores the budget_request id as value
  const selectedOption = document.getElementById("departmentSelect").selectedOptions[0];
  const requestId = document.getElementById("departmentSelect").value; // numeric id from budget_requests
  const department = selectedOption ? decodeURIComponent(selectedOption.getAttribute('data-department') || '') : '';
  const project = document.getElementById("project").value;
  const allocated = document.getElementById("allocated").value;

  const res = await fetch(allocationApi, {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({ department, project, allocated })
  });
  const result = await res.json();
  if(result.success){
    // mark the budget request as Approved (if we have a request id)
    if (requestId) {
      try {
        await fetch(budgetApi, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: requestId, department: department, purpose: selectedOption.getAttribute('data-purpose') ? decodeURIComponent(selectedOption.getAttribute('data-purpose')) : '', amount: selectedOption.getAttribute('data-amount'), status: 'Approved' })
        });
      } catch (err) {
        console.error('Failed to mark budget request approved', err);
      }
    }

    closeAddAllocationModal();
    loadAllocations();
    loadRequests();
  } else alert("Error: "+result.error);
});

function openAddAllocationModal() {
  document.getElementById("addAllocationModal").classList.remove("hidden");
  document.getElementById("addAllocationModal").classList.add("flex");
  loadDepartments();
}
function closeAddAllocationModal() {
  document.getElementById("addAllocationModal").classList.add("hidden");
  document.getElementById("addAllocationModal").classList.remove("flex");
  document.getElementById("allocationForm").reset();
}

// Load both tables on page load
document.addEventListener("DOMContentLoaded", () => {
  loadRequests();
  loadAllocations();
});
</script>