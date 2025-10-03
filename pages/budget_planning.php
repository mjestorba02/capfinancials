<?php
include '../layout/adminLayout.php';

$children = '
<main class="flex-1 p-8 overflow-y-auto max-h-screen">
  <div class="mb-8">
    <h1 class="text-2xl font-bold">Budget Planning</h1>
    <p class="text-sm text-slate-500">Plan, allocate, and track your budgets effectively</p>
  </div>

  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <h3 class="text-lg font-semibold mb-4">Budget Plans</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Request ID</th>
            <th class="px-4 py-3">Department</th>
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
</main>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
const apiUrl = "https://financial.health-ease-hospital.com/prefect/api/budget_requests_api.php";

// Load table data
async function loadRequests() {
  try {
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
          <td class="px-4 py-3">${req.status}</td>
          <td class="px-4 py-3">${req.request_date}</td>
        </tr>
      `;
    });
  } catch (error) {
    console.error(error);
    Toastify({
      text: "Failed to load requests",
      style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" },
      duration: 3000,
      close: true
    }).showToast();
  }
}

// Load on page load
document.addEventListener("DOMContentLoaded", loadRequests);
</script>
';

adminLayout($children);
?>
