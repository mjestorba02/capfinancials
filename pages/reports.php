<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Reports</h1>
      <p class="text-sm text-slate-500">Generate, download, and review financial reports</p>
    </div>
    <div class="flex items-center gap-3">
      <!-- Export Reports Button -->
      <button onclick="exportReports()" class="bg-green-500 hover:bg-green-600 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
        <i class="bx bx-file"></i> Export Reports
      </button>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Bar Chart -->
    <div class="bg-white p-6 rounded-xl shadow border border-slate-200">
      <h3 class="text-lg font-semibold mb-4">Reports Overview (Bar)</h3>
      <div class="h-48">
        <canvas id="reportsChart" class="w-full h-full"></canvas>
      </div>
    </div>

    <!-- Pie Chart -->
    <div class="bg-white p-6 rounded-xl shadow border border-slate-200">
      <h3 class="text-lg font-semibold mb-4">Reports Status Distribution</h3>
      <div class="h-48">
        <canvas id="reportsPie" class="w-full h-full"></canvas>
      </div>
    </div>
  </div>

</main>

<!-- Modal -->
<div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg relative">
    <h2 class="text-xl font-bold mb-4">Add New Report</h2>
    <form class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Report Title</label>
        <input type="text" class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-orange-300">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Type</label>
        <select class="w-full border rounded-lg px-3 py-2">
          <option>Collections</option>
          <option>Disbursement</option>
          <option>Accounts Payable</option>
          <option>Accounts Receivable</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Date</label>
        <input type="date" class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select class="w-full border rounded-lg px-3 py-2">
          <option>Pending</option>
          <option>Approved</option>
          <option>Rejected</option>
        </select>
      </div>
      <div class="flex justify-end gap-3 mt-6">
        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Boxicons -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">


';

adminLayout($children);
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Modal toggle
  function openModal() {
    document.getElementById("reportModal").classList.remove("hidden");
  }
  function closeModal() {
    document.getElementById("reportModal").classList.add("hidden");
  }

  // Dummy export function
  function exportReports() {
    alert("Exporting reports... (implement your logic here)");
  }

  // Fetch totals from API
  async function loadReportData() {
    try {
  const res = await fetch("https://financial.health-ease-hospital.com/prefect/api/totals_api.php");
      const data = await res.json();

      // Bar Chart
      const ctx = document.getElementById("reportsChart");
      if (ctx) {
        new Chart(ctx, {
          type: "bar",
          data: {
            labels: ["Collections", "Disbursements", "AP", "AR"],
            datasets: [{
              label: "Reports Count",
              data: [
                data.collections,
                data.disbursements,
                data.accounts_payable,
                data.accounts_receivable
              ],
              backgroundColor: ["#3b82f6","#10b981","#facc15","#ef4444"],
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
          }
        });
      }

      // Pie Chart (static for now, unless you want dynamic)
      const pieCtx = document.getElementById("reportsPie");
      if (pieCtx) {
        new Chart(pieCtx, {
          type: "pie",
          data: {
            labels: ["Approved", "Pending", "Rejected"],
            datasets: [{
              label: "Status",
              data: [16, 6, 2],
              backgroundColor: ["#10b981","#facc15","#ef4444"],
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: "bottom" }
            }
          }
        });
      }

    } catch (error) {
      console.error("Error loading report data:", error);
    }
  }
  // Export function
async function exportReports() {
  try {
  const res = await fetch("https://financial.health-ease-hospital.com/prefect/api/totals_api.php");
    const data = await res.json();

    // Convert JSON to CSV format
    let csvContent = "Category,Count\n";
    csvContent += `Collections,${data.collections}\n`;
    csvContent += `Disbursements,${data.disbursements}\n`;
    csvContent += `Accounts Payable,${data.accounts_payable}\n`;
    csvContent += `Accounts Receivable,${data.accounts_receivable}\n`;

    // Create a downloadable CSV file
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "financial_reports.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

  } catch (error) {
    console.error("Error exporting reports:", error);
    alert("Failed to export reports. Check console for details.");
  }
}


  // Call function when page loads
  window.onload = loadReportData;
</script>

