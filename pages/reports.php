<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Dashboard</h1>
      <p class="text-sm text-slate-500">Overview of key metrics, activities, and financial performance</p>
    </div>
    <div class="flex items-center gap-3">
      <!-- Export Reports Button -->
      <button onclick="exportReports()" class="bg-green-500 hover:bg-green-600 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
        <i class="bx bx-file"></i> Export Reports
      </button>
    </div>
  </div>

  <!-- Totals Section -->
  <div id="totalsSection" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="bg-white p-4 rounded-xl shadow border border-slate-200 text-center">
      <h4 class="text-sm text-slate-500">Accounts Payable</h4>
      <p id="apTotal" class="text-2xl font-bold text-blue-600">0</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border border-slate-200 text-center">
      <h4 class="text-sm text-slate-500">Accounts Receivable</h4>
      <p id="arTotal" class="text-2xl font-bold text-green-600">0</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border border-slate-200 text-center">
      <h4 class="text-sm text-slate-500">Budget Allocation</h4>
      <p id="budgetTotal" class="text-2xl font-bold text-purple-600">0</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border border-slate-200 text-center">
      <h4 class="text-sm text-slate-500">Collections</h4>
      <p id="collectionsTotal" class="text-2xl font-bold text-orange-600">0</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border border-slate-200 text-center">
      <h4 class="text-sm text-slate-500">Disbursements</h4>
      <p id="disbursementsTotal" class="text-2xl font-bold text-red-600">0</p>
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

<!-- Boxicons -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
';

adminLayout($children);
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Fetch totals from API
  async function loadReportData() {
    try {
      const res = await fetch("http://localhost/prefect/api/totals_api.php");
      const data = await res.json();

      // Update Totals
      document.getElementById("apTotal").textContent = data.accounts_payable || 0;
      document.getElementById("arTotal").textContent = data.accounts_receivable || 0;
      document.getElementById("budgetTotal").textContent = data.budget_allocation || 0;
      document.getElementById("collectionsTotal").textContent = data.collections || 0;
      document.getElementById("disbursementsTotal").textContent = data.disbursements || 0;

      // Bar Chart
      const ctx = document.getElementById("reportsChart");
      if (ctx) {
        new Chart(ctx, {
          type: "bar",
          data: {
            labels: ["Collections", "Disbursements", "AP", "AR", "Budget"],
            datasets: [{
              label: "Reports Count",
              data: [
                data.collections,
                data.disbursements,
                data.accounts_payable,
                data.accounts_receivable,
                data.budget_allocation
              ],
              backgroundColor: ["#f59e0b","#ef4444","#3b82f6","#10b981","#8b5cf6"],
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

      // Pie Chart
      const pieCtx = document.getElementById("reportsPie");
      if (pieCtx) {
        new Chart(pieCtx, {
          type: "pie",
          data: {
            labels: ["Approved", "Pending", "Rejected"],
            datasets: [{
              label: "Status",
              data: [16, 6, 2], // static unless API provides it
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

  // Export as CSV
  async function exportReports() {
    try {
      const res = await fetch("http://localhost/prefect/api/totals_api.php");
      const data = await res.json();

      let csvContent = "Category,Count\n";
      csvContent += `Collections,${data.collections}\n`;
      csvContent += `Disbursements,${data.disbursements}\n`;
      csvContent += `Accounts Payable,${data.accounts_payable}\n`;
      csvContent += `Accounts Receivable,${data.accounts_receivable}\n`;
      csvContent += `Budget Allocation,${data.budget_allocation}\n`;

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

  // Load data on page load
  window.onload = loadReportData;
</script>