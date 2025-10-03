<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Budget Allocation</h1>
      <p class="text-sm text-slate-500">Allocate budgets across departments and projects</p>
    </div>
    <button onclick="openAddAllocationModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
      <i class="bx bx-plus-circle text-lg"></i> New Allocation
    </button>
  </div>

  <!-- Allocation Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Allocation List</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Department</th>
            <th class="px-4 py-3">Project</th>
            <th class="px-4 py-3">Allocated</th>
            <th class="px-4 py-3">Used</th>
            <th class="px-4 py-3">Remaining</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="allocationTableBody" class="divide-y divide-slate-200"></tbody>
      </table>
    </div>
  </div>

</main>

<!-- Add Allocation Modal -->
<div id="addAllocationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h2 class="text-xl font-bold mb-4">Add Budget Allocation</h2>
    <form id="allocationForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Department</label>
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
        <label class="block text-sm font-medium">Department</label>
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
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<script>
const allocationApi = "https://financial.health-ease-hospital.com/prefect/api/allocation_api.php";
const budgetApi = "https://financial.health-ease-hospital.com/prefect/api/budget_requests_api.php";
let currentAllocationId = null;

// Load allocations
async function loadAllocations() {
  const res = await fetch(allocationApi);
  const data = await res.json();
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
    <td class="px-4 py-3 text-right space-x-2">
      <button onclick="openCalculateUsedModal(${a.id}, '${a.department}', '${a.project}', ${a.allocated}, ${a.used})" class="text-indigo-600 hover:text-indigo-800" title="Calculate Used">
        <i class="bx bx-calculator text-xl"></i>
      </button>
    </td>
  </tr>
`;

  });
}

// Calculate remaining
function calculateRemaining() {
  const allocated = parseFloat(document.getElementById("modalAllocated").value) || 0;
  const used = parseFloat(document.getElementById("modalUsed").value) || 0;
  document.getElementById("modalRemaining").value = allocated - used;
}

// Open Calculate Used modal
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

// Close Calculate Used modal
function closeCalculateUsedModal() {
  document.getElementById("calculateUsedModal").classList.add("hidden");
  document.getElementById("calculateUsedModal").classList.remove("flex");
  currentAllocationId = null;
}

// Auto-calculate remaining on input
document.getElementById("modalUsed").addEventListener("input", calculateRemaining);

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

// Load departments in Add Allocation modal
async function loadDepartments() {
  const res = await fetch(budgetApi);
  const data = await res.json();
  const select = document.getElementById("departmentSelect");
  select.innerHTML = "<option value=\"\">Select Department</option>";
  data.forEach(d => {
    select.innerHTML += `<option value="${d.department}" data-amount="${d.amount}">${d.department} - ₱${parseFloat(d.amount).toLocaleString()}</option>`;
  });
}

// Prefill allocated
document.getElementById("departmentSelect").addEventListener("change", function(){
  const amount = this.selectedOptions[0].getAttribute("data-amount");
  document.getElementById("allocated").value = amount || "";
});

// Add allocation
document.getElementById("allocationForm").addEventListener("submit", async function(e){
  e.preventDefault();
  const department = document.getElementById("departmentSelect").value;
  const project = document.getElementById("project").value;
  const allocated = document.getElementById("allocated").value;

  const res = await fetch(allocationApi, {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({ department, project, allocated })
  });
  const result = await res.json();
  if(result.success){
    closeAddAllocationModal();
    loadAllocations();
    alert("Allocation added successfully!");
  } else alert("Error: "+result.error);
});

// Add Allocation modal handlers
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

// Load allocations on page load
document.addEventListener("DOMContentLoaded", loadAllocations);
</script>
