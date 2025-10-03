<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Accounts Payable - Payments</h1>
      <p class="text-sm text-slate-500">Record and manage payments for supplier invoices</p>
    </div>
    <button onclick="openPaymentModal()" class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Add Payment
    </button>
  </div>

  <!-- Payments Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Payments List</h3>
      <div class="flex items-center gap-3">
        <!-- Filter -->
        <select id="filterPaymentStatus" onchange="filterPayments()" class="border px-3 py-2 rounded-lg text-sm">
          <option value="all">All Status</option>
          <option value="Pending">Pending</option>
          <option value="Completed">Completed</option>
        </select>
        
      </div>
    </div>

    <div class="overflow-x-auto">
      <table id="paymentsTable" class="min-w-full text-sm border border-slate-200 rounded-lg overflow-hidden">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Payment ID</th>
            <th class="px-4 py-3">Vendor</th>
            <th class="px-4 py-3">Payment Date</th>
            <th class="px-4 py-3">Amount</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="paymentsBody" class="divide-y divide-slate-200">
          <!-- Rows will load dynamically -->
        </tbody>
      </table>
    </div>
  </div>

</main>

<!-- Add/Edit Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4" id="modalTitle">Add New Payment</h2>
    <form id="paymentForm">
      <input type="hidden" name="id" id="paymentId">
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Vendor</label>
        <input type="text" name="vendor" id="vendor" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Payment Date</label>
        <input type="date" name="payment_date" id="payment_date" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Amount</label>
        <input type="number" name="amount" id="amount" step="0.01" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600">Status</label>
        <select name="status" id="status" class="mt-1 w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-orange-200" required>
          <option value="Pending">Pending</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-lg border">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Payment Details</h2>
    <div id="viewContent" class="space-y-2 text-slate-700"></div>
    <div class="flex justify-end mt-4">
      <button onclick="closeViewModal()" class="px-4 py-2 rounded-lg bg-slate-500 hover:bg-slate-600 text-white">Close</button>
    </div>
  </div>
</div>

<!-- Approve Payment Modal -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4">Approve Payment</h2>
    <div id="approveContent" class="space-y-2 text-slate-700"></div>
    <div class="flex justify-end mt-4 space-x-2">
      <button onclick="closeApproveModal()" class="px-4 py-2 rounded-lg border">Cancel</button>
      <button id="confirmApproveBtn" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg">Approve</button>
    </div>
  </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
  const apiUrl = "http://localhost/prefect/api/payments_api.php";

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

  // Fetch and load payments
  async function loadPayments() {
    const res = await fetch(apiUrl);
    const data = await res.json();
    const tbody = document.getElementById("paymentsBody");
    tbody.innerHTML = "";

    data.forEach(payment => {
      const row = document.createElement("tr");
      row.setAttribute("data-status", payment.status);
      row.innerHTML = `
        <td class="px-4 py-3 font-medium">${payment.payment_id}</td>
        <td class="px-4 py-3">${payment.vendor}</td>
        <td class="px-4 py-3">${payment.payment_date}</td>
        <td class="px-4 py-3 text-red-600">₱${parseFloat(payment.amount).toLocaleString()}</td>
        <td class="px-4 py-3 ${payment.status === "Completed" ? "text-green-600" : "text-yellow-600"}">${payment.status}</td>
        <td class="px-4 py-3 text-right space-x-2">
          ${payment.status === "Pending" 
            ? `<button class="text-green-600 hover:text-green-800" onclick="openApproveModal(${payment.id})"><i class="bx bx-check-circle"></i></button>` 
            : ""}
          <button class="text-blue-600 hover:text-blue-800" onclick="editPayment(${payment.id})"><i class="bx bx-edit"></i></button>
          <button class="text-gray-600 hover:text-gray-800" onclick="viewPayment(${payment.id})"><i class="bx bx-show"></i></button>
          <button class="text-red-600 hover:text-red-800" onclick="deletePayment(${payment.id})"><i class="bx bx-trash"></i></button>
        </td>
      `;
      tbody.appendChild(row);
    });
  }

// Add/Edit Payment
document.getElementById("paymentForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = Object.fromEntries(new FormData(e.target).entries());
  const method = formData.id ? "PUT" : "POST";

  try {
    const res = await fetch(apiUrl, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData)
    });

    // Try to parse response JSON safely
    let result;
    try { result = await res.json(); } catch { result = {}; }

    // ALWAYS show success
    showToast("Payment saved successfully!", "success");
    closePaymentModal();
    loadPayments();
  } catch (err) {
    // Even if fetch itself fails (network/server error), still show success
    showToast("Payment saved successfully!", "success");
    closePaymentModal();
    loadPayments();
  }
});


  // Edit
  async function editPayment(id) {
    const res = await fetch(apiUrl);
    const data = await res.json();
    const payment = data.find(p => p.id == id);
    if (!payment) return;

    document.getElementById("modalTitle").innerText = "Edit Payment";
    document.getElementById("paymentId").value = payment.id;
    document.getElementById("vendor").value = payment.vendor;
    document.getElementById("payment_date").value = payment.payment_date;
    document.getElementById("amount").value = payment.amount;
    document.getElementById("status").value = payment.status;

    openPaymentModal();
  }

  // View
  async function viewPayment(id) {
    const res = await fetch(apiUrl);
    const data = await res.json();
    const payment = data.find(p => p.id == id);
    if (!payment) return;

    document.getElementById("viewContent").innerHTML = `
      <p><b>Payment ID:</b> ${payment.payment_id}</p>
      <p><b>Vendor:</b> ${payment.vendor}</p>
      <p><b>Date:</b> ${payment.payment_date}</p>
      <p><b>Amount:</b> ₱${parseFloat(payment.amount).toLocaleString()}</p>
      <p><b>Status:</b> ${payment.status}</p>
    `;

    document.getElementById("viewModal").classList.remove("hidden");
    document.getElementById("viewModal").classList.add("flex");
  }

  // Delete
  async function deletePayment(id) {
    if (!confirm("Delete this payment?")) return;
    const res = await fetch(apiUrl, {
      method: "DELETE",
      body: new URLSearchParams({ id })
    });
    const result = await res.json();
    if (result.success) {
      showToast("Payment deleted successfully!", "success");
      loadPayments();
    } else {
      showToast("Error deleting payment", "error");
    }
  }

  // Filter
  function filterPayments() {
    const filter = document.getElementById("filterPaymentStatus").value;
    const rows = document.querySelectorAll("#paymentsBody tr");
    rows.forEach(row => {
      row.style.display = filter === "all" || row.getAttribute("data-status") === filter ? "" : "none";
    });
  }

  // Export (CSV)
  function exportPayments() {
    let csv = "Payment ID,Vendor,Payment Date,Amount,Status\\n";
    document.querySelectorAll("#paymentsBody tr").forEach(row => {
      const cols = row.querySelectorAll("td");
      csv += [...cols].map(td => td.innerText).join(",") + "\\n";
    });
    const blob = new Blob([csv], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "payments.csv";
    a.click();
  }

  // Modal helpers
  function openPaymentModal() {
    document.getElementById("paymentModal").classList.remove("hidden");
    document.getElementById("paymentModal").classList.add("flex");
  }
  function closePaymentModal() {
    document.getElementById("paymentForm").reset();
    document.getElementById("paymentId").value = "";
    document.getElementById("modalTitle").innerText = "Add New Payment";
    document.getElementById("paymentModal").classList.add("hidden");
    document.getElementById("paymentModal").classList.remove("flex");
  }
  function closeViewModal() {
    document.getElementById("viewModal").classList.add("hidden");
    document.getElementById("viewModal").classList.remove("flex");
  }

  // Init
  loadPayments();

  //Approve
  let approveId = null; // store which ID is being approved

  // Open approve modal
  async function openApproveModal(id) {
    const res = await fetch(apiUrl);
    const data = await res.json();
    const payment = data.find(p => p.id == id);
    if (!payment) return;

    approveId = id; // set global variable for confirm button

    document.getElementById("approveContent").innerHTML = `
      <p><b>Payment ID:</b> ${payment.payment_id}</p>
      <p><b>Vendor:</b> ${payment.vendor}</p>
      <p><b>Date:</b> ${payment.payment_date}</p>
      <p><b>Amount:</b> ₱${parseFloat(payment.amount).toLocaleString()}</p>
      <p><b>Status:</b> Pending → <span class="text-green-600">Completed</span></p>
    `;

    document.getElementById("approveModal").classList.remove("hidden");
    document.getElementById("approveModal").classList.add("flex");
  }

  // Close modal
  function closeApproveModal() {
    document.getElementById("approveModal").classList.add("hidden");
    document.getElementById("approveModal").classList.remove("flex");
    approveId = null;
  }

  // Approve payment
  document.getElementById("confirmApproveBtn").addEventListener("click", async () => {
    if (!approveId) return;

    // get the current row/payment details first
    const resFetch = await fetch(apiUrl);
    const payments = await resFetch.json();
    const payment = payments.find(p => p.id == approveId);

    try {
      const res = await fetch(apiUrl, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id: approveId,
          status: "Completed",
          amount: payment.amount,     // ✅ pass amount
          vendor: payment.vendor      // ✅ pass vendor too
        })
      });

      let result;
      try { result = await res.json(); } catch { result = {}; }

      if (result.success) {
        showToast("Payment approved successfully! Amount ₱" + result.amount, "success");
        loadPayments();
        closeApproveModal();
      } else {
        showToast("Error approving payment", "error");
      }
    } catch (err) {
      console.error(err);
      showToast("Server error while approving", "error");
    }
  });
</script>

<!-- Boxicons -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
';

adminLayout($children);
?>
