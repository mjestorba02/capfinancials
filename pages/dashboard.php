<?php
include '../layout/adminLayout.php';


$children = '<!-- Main -->
    <main class="flex-1 p-8">

   

      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-2xl font-bold">Schedule</h1>
          <p class="text-sm text-slate-500">Aug 5, 2025</p>
        </div>
        <input type="text" placeholder="Search..." class="border border-slate-200 px-4 py-2 rounded-lg text-sm shadow w-60" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Schedule List + Progress -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Schedule Tabs & Cards -->
          <div>
            <div class="flex space-x-4 text-sm mb-4 font-medium text-slate-400">
              <button class="text-black underline">Recent Added</button>
              <button>In Progress</button>
              <button>In Review</button>
              <button>Completed</button>
            </div>

            <div class="space-y-4">
              <div class="bg-black text-white px-6 py-4 rounded-xl flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium">Illustration Templates</p>
                  <p class="text-xs text-slate-400">8:00 AM – 4:00 PM</p>
                </div>
                <div class="flex -space-x-2">
                  <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=3" />
                  <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=5" />
                </div>
              </div>

              <div class="bg-slate-100 px-6 py-4 rounded-xl flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium">UI Design</p>
                  <p class="text-xs text-slate-500">4:00 PM – 5:00 PM</p>
                </div>
                <div class="flex -space-x-2">
                  <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=6" />
                  <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=7" />
                </div>
              </div>

              <div class="bg-slate-100 px-6 py-4 rounded-xl flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium">UX Design</p>
                  <p class="text-xs text-slate-500">5:00 PM – 6:00 PM</p>
                </div>
                <div class="flex -space-x-2">
                  <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=8" />
                  <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=9" />
                </div>
              </div>
            </div>
          </div>

          <!-- Project Progress -->
          <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
            <h3 class="text-lg font-semibold mb-4">Project Progress</h3>
            <svg viewBox="0 0 36 36" class="w-24 h-24 mx-auto">
              <path
                class="text-slate-200"
                d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831"
                fill="none" stroke="currentColor" stroke-width="3.8"
              />
              <path
                class="text-orange-500"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831"
                fill="none" stroke="currentColor" stroke-width="3.8" stroke-dasharray="65, 100"
              />
            </svg>
            <div class="mt-4 text-center text-sm">
              <p><span class="text-orange-500 font-semibold">65%</span> Illustration Template</p>
              <p class="text-slate-500 text-xs mt-1">Cartoon & Abstract Patterns</p>
            </div>
          </div>

        </div>

        <!-- Right Column: Meeting & Files -->
        <div class="space-y-6">

          <!-- Meeting Calendar -->
          <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
            <div class="flex justify-between mb-4">
              <h3 class="text-lg font-semibold">Meeting</h3>
              <p class="text-sm text-slate-500">Aug 3 – Aug 7</p>
            </div>
            <div class="grid grid-cols-5 gap-2 text-center text-xs font-semibold text-slate-500">
              <div class="p-2 rounded bg-slate-50">Mon<br>3</div>
              <div class="p-2 rounded bg-slate-50">Tue<br>4</div>
              <div class="p-2 rounded bg-slate-50">Wed<br>5</div>
              <div class="p-2 rounded bg-orange-100 border border-orange-500 text-orange-700">Thu<br>6</div>
              <div class="p-2 rounded bg-slate-50">Fri<br>7</div>
            </div>
          </div>

          <!-- Files -->
          <div class="bg-white p-6 rounded-xl border border-slate-200 shadow">
            <div class="flex justify-between mb-4">
              <h3 class="text-lg font-semibold">Files</h3>
              <a href="#" class="text-sm text-blue-600 hover:underline">View all →</a>
            </div>
            <div class="space-y-4 text-sm">
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-medium">User flow.fig</p>
                  <p class="text-xs text-slate-500">Aug 5, 2025 at 9:45 AM</p>
                </div>
                <span class="text-xs text-slate-400">2.5 MB</span>
              </div>
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-medium">Design system.fig</p>
                  <p class="text-xs text-slate-500">Aug 5, 2025 at 8:30 AM</p>
                </div>
                <span class="text-xs text-slate-400">4.2 MB</span>
              </div>
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-medium">Animation.json</p>
                  <p class="text-xs text-slate-500">Aug 4, 2025 at 11:15 PM</p>
                </div>
                <span class="text-xs text-slate-400">890 KB</span>
              </div>
            </div>
          </div>

        </div>

      </div>
    </main>
  </div>
<!-- Footer -->
 <footer class="bg-slate-900 text-white py-12 mt-0">
  <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10 text-sm">

    <!-- Logo and description -->
    <div>
      <h2 class="text-xl font-bold mb-4">MediTrack</h2>
      <p class="text-slate-300 mb-4">Empowering your care, one record at a time.</p>
      <div class="flex space-x-4 text-xl">
        <a href="#" class="hover:text-blue-400"><i class="fab fa-facebook"></i></a>
        <a href="#" class="hover:text-blue-400"><i class="fab fa-twitter"></i></a>
        <a href="#" class="hover:text-blue-400"><i class="fab fa-instagram"></i></a>
        <a href="#" class="hover:text-blue-400"><i class="fab fa-tiktok"></i></a>
      </div>
    </div>

    <!-- Services -->
    <div>
      <h3 class="text-lg font-semibold mb-4">Services</h3>
      <ul class="space-y-2 text-slate-400">
        <li><a href="#" class="hover:text-white">Outpatient Care</a></li>
        <li><a href="#" class="hover:text-white">Emergency</a></li>
        <li><a href="#" class="hover:text-white">Pharmacy</a></li>
        <li><a href="#" class="hover:text-white">Online Consultation</a></li>
      </ul>
    </div>

    <!-- Quick Links -->
    <div>
      <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
      <ul class="space-y-2 text-slate-400">
        <li><a href="#" class="hover:text-white">Home</a></li>
        <li><a href="#" class="hover:text-white">Appointments</a></li>
        <li><a href="#" class="hover:text-white">Doctors</a></li>
        <li><a href="#" class="hover:text-white">Contact Us</a></li>
      </ul>
    </div>

    <!-- Support -->
    <div>
      <h3 class="text-lg font-semibold mb-4">Support</h3>
      <ul class="space-y-2 text-slate-400">
        <li><a href="#" class="hover:text-white">FAQs</a></li>
        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
        <li><a href="#" class="hover:text-white">Terms of Use</a></li>
        <li><a href="#" class="hover:text-white">Live Chat</a></li>
      </ul>
    </div>

  </div>
  <div class="mt-10 border-t border-slate-700 pt-6 text-center text-xs text-slate-500">
    &copy; 2025 MediTrack. All rights reserved.
  </div>
</footer>

';

adminLayout($children);
?>