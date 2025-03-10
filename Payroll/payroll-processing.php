<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Processing</title>
    <link rel="stylesheet" href="../css/PAYROLL.css">
</head>
<body>
    <div class="top-nav">
        <ul>
            <h1 class="logopos">Payroll Processing</h1>
            <li class="top">
                <a class="top1" href="../index.html">Home</a>
                <a class="top1" href="time-and-attendance.html">Time and Attendance</a>
                <a class="top1" href="employee-information.html">Employee Information</a>
                <a class="top1" href="payroll-processing.html">Payroll Processing</a>
            </li>
        </ul>
        <button class="logout" title="logout">Log out</button>
    </div>

    <div class="content">
        <h2>Payroll Processing</h2>

        <form id="payroll-processing-form">
            <label for="employee-id">Employee ID:</label>
            <input type="text" id="employee-id" name="employee-id" required><br><br>

            <label for="hours-worked">Hours Worked:</label>
            <input type="number" id="hours-worked" name="hours-worked" required><br><br>

            <label for="overtime-hours">Overtime Hours:</label>
            <input type="number" id="overtime-hours" name="overtime-hours"><br><br>

            <label for="salary">Salary/Compensation:</label>
            <input type="number" id="salary" name="salary" required><br><br>

            <button type="submit">Process Payroll</button>
        </form>
    </div>
</body>
</html>
