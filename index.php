<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hospital Management System: Financials</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="icon" type="image/jpeg" href="./images/logo.jpg">
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100..900&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; text-decoration: none; list-style: none; }
    body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(90deg, #e2e2e2, #c9d6ff); }
    .container { position: relative; width: 850px; height: 550px; background: #fff; margin: 20px; border-radius: 30px; box-shadow: 0 0 30px rgba(0, 0, 0, .2); overflow: hidden; }
    .container h1 { font-size: 36px; margin: -10px 0; }
    .container p { font-size: 14.5px; margin: 15px 0; }
    form { width: 100%; }
    .form-box { position: absolute; right: 0; width: 50%; height: 100%; background: #fff; display: flex; align-items: center; color: #333; text-align: center; padding: 40px; z-index: 1; transition: .6s ease-in-out 1.2s, visibility 0s 1s; }
    .container.active .form-box { right: 50%; }
    .form-box.register { visibility: hidden; }
    .container.active .form-box.register { visibility: visible; }
    .input-box { position: relative; margin: 30px 0; }
    .input-box input { width: 100%; padding: 13px 50px 13px 20px; background: #eee; border-radius: 8px; border: none; outline: none; font-size: 16px; color: #333; font-weight: 500; }
    .input-box input::placeholder { color: #888; font-weight: 400; }
    .input-box i { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 20px; cursor: pointer; }
    .forgot-link { margin: -15px 0 15px; }
    .forgot-link a { font-size: 14.5px; color: #333; }
    .btn { width: 100%; height: 48px; background: #7494ec; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, .1); border: none; cursor: pointer; font-size: 16px; color: #fff; font-weight: 600; }
    .social-icons { display: flex; justify-content: center; }
    .social-icons a { display: inline-flex; padding: 10px; border: 2px solid #ccc; border-radius: 8px; font-size: 24px; color: #333; margin: 0 8px; }
    .toggle-box { position: absolute; width: 100%; height: 100%; }
    .toggle-box::before { content: ''; position: absolute; left: -250%; width: 300%; height: 100%; background: #7494ec; border-radius: 150px; z-index: 2; transition: 1.8s ease-in-out; }
    .container.active .toggle-box::before { left: 50%; }
    .toggle-panel { position: absolute; width: 50%; height: 100%; color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 2; transition: .6s ease-in-out; }
    .toggle-panel.toggle-left { left: 0; transition-delay: 1.2s; }
    .container.active .toggle-panel.toggle-left { left: -50%; transition-delay: .6s; }
    .toggle-panel.toggle-right { right: -50%; transition-delay: .6s; }
    .container.active .toggle-panel.toggle-right { right: 0; transition-delay: 1.2s; }
    .toggle-panel p { margin-bottom: 20px; }
    .toggle-panel .btn { width: 160px; height: 46px; background: transparent; border: 2px solid #fff; box-shadow: none; }
  </style>
</head>
<body>

  <div class="container">
    <!-- Login Form -->
    <div class="form-box login">
      <form id="loginForm">
        <h1>Login</h1>
        <div class="input-box">
          <input type="email" id="loginEmail" placeholder="Email" required>
          <i class='bx bxs-envelope'></i>
        </div>
        <div class="input-box">
          <input type="password" id="loginPassword" placeholder="Password" required>
          <i class='bx bxs-lock-alt toggle-password'></i>
        </div>
        <div class="forgot-link"><a href="#">Forgot Password?</a></div>
        <button type="submit" class="btn">Login</button>
        <p>or login with social platforms</p>
        <div class="social-icons">
          <a href="#"><i class='bx bxl-google'></i></a>
          <a href="#"><i class='bx bxl-facebook'></i></a>
          <a href="#"><i class='bx bxl-github'></i></a>
          <a href="#"><i class='bx bxl-linkedin'></i></a>
        </div>
      </form>

      <!-- OTP Verification (hidden by default) -->
      <form id="otpForm" style="display:none; margin-top:20px;">
        <h1>Enter OTP</h1>
        <div class="input-box">
          <input type="text" id="otpCode" placeholder="Enter 6-digit OTP" required>
          <i class='bx bxs-key'></i>
        </div>
        <button type="submit" class="btn">Verify OTP</button>
      </form>
    </div>

    <!-- Registration Form -->
    <div class="form-box register">
      <form id="registerForm">
        <h1>Registration</h1>
        <div class="input-box">
          <input type="text" id="regName" placeholder="Username" required>
          <i class='bx bxs-user'></i>
        </div>
        <div class="input-box">
          <input type="email" id="regEmail" placeholder="Email" required>
          <i class='bx bxs-envelope'></i>
        </div>
        <div class="input-box">
          <input type="password" id="regPassword" placeholder="Password" required>
          <i class='bx bxs-lock-alt toggle-password'></i>
        </div>
        <button type="submit" class="btn">Register</button>
        <p>or register with social platforms</p>
        <div class="social-icons">
          <a href="#"><i class='bx bxl-google'></i></a>
          <a href="#"><i class='bx bxl-facebook'></i></a>
          <a href="#"><i class='bx bxl-github'></i></a>
          <a href="#"><i class='bx bxl-linkedin'></i></a>
        </div>
      </form>
    </div>

    <!-- Toggle Panels -->
    <div class="toggle-box">
      <div class="toggle-panel toggle-left">
        <h1>Hello, Welcome!</h1>
        <p>Don't have an account?</p>
        <button class="btn register-btn">Register</button>
      </div>
      <div class="toggle-panel toggle-right">
        <h1>Welcome Back!</h1>
        <p>Already have an account?</p>
        <button class="btn login-btn">Login</button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
  <script>
  const container = document.querySelector('.container');
  const registerBtn = document.querySelector('.register-btn');
  const loginBtn = document.querySelector('.login-btn');

  registerBtn.addEventListener('click', () => container.classList.add('active'));
  loginBtn.addEventListener('click', () => container.classList.remove('active'));

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

  // ===================== PASSWORD TOGGLE =====================
  document.querySelectorAll(".toggle-password").forEach(icon => {
    icon.addEventListener("click", () => {
      const input = icon.previousElementSibling;
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bxs-lock-alt");
        icon.classList.add("bxs-lock-open-alt");
      } else {
        input.type = "password";
        icon.classList.remove("bxs-lock-open-alt");
        icon.classList.add("bxs-lock-alt");
      }
    });
  });

  // ===================== REGISTER FORM =====================
  document.getElementById("registerForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const name = document.getElementById("regName").value.trim();
    const email = document.getElementById("regEmail").value.trim();
    const password = document.getElementById("regPassword").value.trim();

    try {
  const res = await fetch("https://financial.health-ease-hospital.com/api/users.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password })
      });
      const data = await res.json();
      showToast(data.message, data.status);
      if (data.status === "success") {
        container.classList.remove("active");
      }
    } catch (error) {
      console.error("Error:", error);
      showToast("Something went wrong. Please try again.", "error");
    }
  });

  // ===================== LOGIN FORM =====================
  document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const email = document.getElementById("loginEmail").value.trim();
    const password = document.getElementById("loginPassword").value.trim();

    try {
  const res = await fetch("https://financial.health-ease-hospital.com/api/auth.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();

      if (data.status === "otp_required") {
        showToast(data.message, "success");
        document.getElementById("loginForm").style.display = "none";
        document.getElementById("otpForm").style.display = "block";
      } else {
        showToast(data.message, "error");
      }
    } catch (error) {
      console.error("Error:", error);
      showToast("Login failed. Try again.", "error");
    }
  });

  // ===================== OTP FORM =====================
  document.getElementById("otpForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const otp = document.getElementById("otpCode").value.trim();

    try {
  const res = await fetch("https://financial.health-ease-hospital.com/api/verify_otp.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ otp })
      });
      const data = await res.json();

      if (data.status === "success") {
        showToast(data.message, "success");
        setTimeout(() => window.location.href = data.redirect, 1500);
      } else {
        showToast(data.message, "error");
      }
    } catch (error) {
      console.error("Error:", error);
      showToast("OTP verification failed.", "error");
    }
  });
  </script>
</body>
</html>
