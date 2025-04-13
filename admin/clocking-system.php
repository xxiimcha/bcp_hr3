<?php
include '../config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Employee Time Clocking</title>
  <link rel="icon" href="../img/logo.webp">
  <link rel="stylesheet" href="../css/time-clocking.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      background-color: #111;
      color: #fff;
      font-family: Arial, sans-serif;
    }

    .time-display {
      font-size: 20px;
      font-weight: bold;
      color: #fff;
      background: #222;
      padding: 10px;
      text-align: center;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .manual-input input {
      padding: 10px;
      font-size: 16px;
      width: 100%;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .manual-input button {
      margin-top: 10px;
      padding: 10px 20px;
      font-size: 16px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    #employee-details {
      margin-top: 20px;
      display: none;
    }

    .card {
      background: #f9f9f9;
      color: #000;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      margin-bottom: 20px;
    }

    .info-label {
      font-weight: bold;
    }

    #scanner-status {
      color: #00cfff;
      font-weight: 500;
      margin-top: 15px;
    }

    #success-message {
      display: none;
      margin-top: 10px;
      color: #00ff80;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="container">
  <div id="header">
    <button class="back-button" onclick="window.location.href='time-and-attendance-home.php'">
      <i class="fas fa-arrow-left"></i>
    </button>
    <button class="copy-link-button" onclick="copyLinkToClipboard()" title="Copy Link">
      <i class="fas fa-link"></i>
    </button>
  </div>

  <div id="copy-success-message">Link copied to clipboard!</div>

  <div class="info-panel">
    <h2 style="color: #28a745;">Manual Attendance Entry</h2>
    <hr class="hr">
    <div class="time-display" id="realtime-clock">Loading time...</div>

    <div class="manual-input">
      <label for="fingerprint-id">Enter Fingerprint ID:</label>
      <input type="text" id="fingerprint-id" placeholder="Enter fingerprint ID">
      <button onclick="submitFingerprintID()">Submit</button>
    </div>

    <div id="employee-details">
      <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <div class="card" style="flex: 1;">
          <h3>👤 Employee Profile</h3>
          <p><span class="info-label">Employee ID:</span> <span id="employee-id"></span></p>
          <p><span class="info-label">Name:</span> <span id="employee-name"></span></p>
          <p><span class="info-label">Position:</span> <span id="employee-position"></span></p>
          <p><span class="info-label">Department:</span> <span id="employee-department"></span></p>
        </div>

        <div class="card" style="flex: 1;">
          <h3>🕒 Today's Attendance</h3>
          <p><span class="info-label">Shift Type:</span> <span id="shift-name"></span></p>
          <p><span class="info-label">Shift Start:</span> <span id="shift-start"></span></p>
          <p><span class="info-label">Shift End:</span> <span id="shift-end"></span></p>
          <hr>
          <p><span class="info-label">Time In:</span> <span id="attendance-in">-</span></p>
          <p><span class="info-label">Time Out:</span> <span id="attendance-out">-</span></p>
          <p><span class="info-label">Status:</span> <span id="attendance-status">-</span></p>
          <p><span class="info-label">Hours Worked:</span> <span id="attendance-hours">-</span></p>
          <p><span class="info-label">Overtime:</span> <span id="attendance-ot">-</span></p>
          <p><span class="info-label">Late:</span> <span id="attendance-late">-</span></p>
        </div>
      </div>

      <div id="scanner-status"></div>
      <div id="success-message"></div>
    </div>
  </div>
</div>

<script>
function updateClock() {
  const now = new Date();
  const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
  const gmtPlus8 = new Date(utc + (3600000 * 8));
  const timeString = gmtPlus8.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  const dateString = gmtPlus8.toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  document.getElementById('realtime-clock').innerText = `${dateString} ${timeString}`;
}
setInterval(updateClock, 1000);
updateClock();

let clearTimeoutHandle;

function submitFingerprintID() {
  const fingerprintId = document.getElementById('fingerprint-id').value.trim();
  if (!fingerprintId) return alert('Please enter a fingerprint ID');

  fetch(`../controllers/clocking.php?action=lookup&fingerprint_id=${fingerprintId}`)
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        document.getElementById('employee-id').innerText = data.employee_id;
        document.getElementById('employee-details').style.display = 'block';
        document.getElementById('scanner-status').innerText = data.message || '';
        document.getElementById('success-message').innerText = "Attendance recorded!";
        document.getElementById('success-message').style.display = "block";

        fetch('https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs')
          .then(res => res.json())
          .then(apiData => {
            const emp = apiData.data.find(e => e.employee_no === data.employee_id);
            if (emp) {
              document.getElementById('employee-name').innerText = `${emp.firstname} ${emp.lastname}`;
              document.getElementById('employee-position').innerText = emp.position || 'N/A';
              document.getElementById('employee-department').innerText = emp.department_name || 'N/A';
            }
          });

        const shift = data.shift || {};
        document.getElementById('shift-name').innerText = shift.shift_name || 'N/A';
        document.getElementById('shift-start').innerText = shift.shift_start || 'N/A';
        document.getElementById('shift-end').innerText = shift.shift_end || 'N/A';

        fetch(`../controllers/get_today_attendance.php?employee_id=${data.employee_id}`)
          .then(res => res.json())
          .then(res => {
            const a = res.data || {};
            document.getElementById('attendance-in').innerText = a.time_in || '-';
            document.getElementById('attendance-out').innerText = a.time_out !== '0000-00-00 00:00:00' ? a.time_out : '-';
            document.getElementById('attendance-status').innerText = a.status || '-';
            document.getElementById('attendance-hours').innerText = a.hours_worked || '0.00';
            document.getElementById('attendance-ot').innerText = a.overtime_hours || '0.00';
            document.getElementById('attendance-late').innerText = a.late + ' minute(s)' || '0';
          });

        if (clearTimeoutHandle) clearTimeout(clearTimeoutHandle);
        clearTimeoutHandle = setTimeout(() => {
          document.getElementById('employee-details').style.display = 'none';
          document.getElementById('scanner-status').innerText = '';
          document.getElementById('success-message').style.display = 'none';
          document.getElementById('fingerprint-id').value = '';
        }, 15000);
      } else {
        alert(data.message);
      }
    });
}
</script>
</body>
</html>
