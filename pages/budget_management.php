<?php
include '../layout/adminLayout.php';

$children = '
<!-- Main -->
<main class="flex-1 p-8 overflow-y-auto max-h-screen">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-2xl font-bold">Budget Management</h1>
      <p class="text-sm text-slate-500">Plan, allocate, and track your budget</p>
    </div>
    <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg shadow">
      + Create Budget
    </button>
  </div>



  <!-- Budget Table -->
  <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
    <h3 class="text-lg font-semibold mb-4">Budget Allocation</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600 uppercase text-xs">
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Allocated</th>
            <th class="px-4 py-3">Spent</th>
            <th class="px-4 py-3">Remaining</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr>
            <td class="px-4 py-3 font-medium">Operations</td>
            <td class="px-4 py-3">₱200,000</td>
            <td class="px-4 py-3 text-red-600">₱120,000</td>
            <td class="px-4 py-3 text-green-600">₱80,000</td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:underline">Edit</button>
              <button class="text-red-600 hover:underline">Delete</button>
            </td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium">Marketing</td>
            <td class="px-4 py-3">₱100,000</td>
            <td class="px-4 py-3 text-red-600">₱70,000</td>
            <td class="px-4 py-3 text-green-600">₱30,000</td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:underline">Edit</button>
              <button class="text-red-600 hover:underline">Delete</button>
            </td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium">Research & Development</td>
            <td class="px-4 py-3">₱50,000</td>
            <td class="px-4 py-3 text-red-600">₱20,000</td>
            <td class="px-4 py-3 text-green-600">₱30,000</td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-blue-600 hover:underline">Edit</button>
              <button class="text-red-600 hover:underline">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</main>
</div>
';

adminLayout($children);
?>
