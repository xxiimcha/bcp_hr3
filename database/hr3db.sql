

-- Create the admin_users table with an email column
CREATE TABLE IF NOT EXISTS admin_users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    admin_username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL DEFAULT 'example@example.com'  -- Add email column
);

-- Insert default admin users into the admin_users table with email
INSERT INTO admin_users (admin_username, password, email) VALUES 
('defaultAdmin', 'admin123', 'defaultAdmin@example.com'), 
('admin', '$2y$10$0TMicWNiVKVxPEzKAFjzx.tENlPT10Sfcxv/gKjkBVPsn6VdT0Q3m', 'admin@example.com');

-- New departments table
CREATE TABLE IF NOT EXISTS departments (
    department_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL
);

INSERT INTO departments (department_name) VALUES
('Human Resources'),
('Sales and Marketing'),
('Accounting/Finance'),
('Front Desk'),
('Housekeeping'),
('Food and Beverage'),
('Maintenance'),
('Security'),
('Kitchen'),
('Purchasing/Inventory'),
('Bar'),
('Events/Convention Services');



CREATE TABLE IF NOT EXISTS employee_info (
    employee_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_name VARCHAR(100) NOT NULL,
    department_id INT(11) NOT NULL, -- Foreign key column
    position VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    contact_no VARCHAR(15) NOT NULL,
    email_address VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    date_hired DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(department_id)-- Foreign key constraint
);
-- Insert default employee records
INSERT INTO employee_info (employee_name, department_id, position, date_of_birth, contact_no, email_address, address, date_hired, status) VALUES
('Rheniel Marzan', 1, 'HR Manager', '1990-05-15', '1234567890', 'niel.m@example.com', '123 Main St, City', '2020-01-15', 'Full-time'),
('Juan Dela Cruz', 2, 'Sales Executive', '1985-03-22', '0987654321', 'juan.d@example.com', '456 Elm St, City', '2018-07-10', 'Part-time'),
('Alice Johnson', 1, 'HR Assistant', '1992-11-30', '5551234567', 'alice.johnson@example.com', '789 Maple St, City', '2021-03-05', 'Full-time'),
('Bob Brown', 2, 'Sales Manager', '1988-08-19', '5557654321', 'bob.brown@example.com', '321 Oak St, City', '2019-09-15', 'Full-time');


CREATE TABLE IF NOT EXISTS leave_types (
    leave_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    leave_code VARCHAR(10) NOT NULL UNIQUE,
    leave_type VARCHAR(50) NOT NULL,
    DefaultCredit INT(11) NOT NULL
);
CREATE TABLE IF NOT EXISTS employee_leave_balances (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    leave_code VARCHAR(10) NOT NULL,
    balance INT(11) NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id),  -- Assuming employee_info has an 'id' field
    FOREIGN KEY (leave_code) REFERENCES leave_types(leave_code)  -- Links to leave_types using leave_code
);

-- Example data insertion for leave types
INSERT INTO leave_types (leave_code, leave_type, DefaultCredit) VALUES
('AL', 'Annual Leave', 30),
('SL', 'Sick Leave', 30),
('EL', 'Emergency Leave', 30),
('ML', 'Maternity Leave', 30),
('PL', 'Paternity Leave', 30);








CREATE TABLE IF NOT EXISTS employee_leave_requests (
    employee_id INT(11) NOT NULL,
    leave_id INT(11) NOT NULL,  
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    date_submitted DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  -- New column for the date submitted
    status VARCHAR(20) NOT NULL,
    remarks VARCHAR(255), 
    PRIMARY KEY (employee_id, start_date),
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id),
    FOREIGN KEY (leave_id) REFERENCES leave_types(leave_id)  -- Foreign key constraint
);

-- Create the employee_leave_records table
CREATE TABLE IF NOT EXISTS employee_leave_records (
    record_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    leave_id INT(11) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL CHECK (total_days > 0),  -- Ensure total_days is positive
    approval_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    remarks VARCHAR(255),
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (leave_id) REFERENCES leave_types(leave_id) ON DELETE CASCADE
);













CREATE TABLE IF NOT EXISTS employee_logins (
    login_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL, -- Foreign key column
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id) -- Foreign key constraint
);

-- Inserting login credentials for the employees
INSERT INTO employee_logins (employee_id, password, is_active) VALUES 
(1, '$2y$10$UCqxt4.lhg3tL/o4hTUguuP4p53I98ol.stPAFM2cRk4A6P0dAGiW', 1), -- John Doe's employee_id is 1
(2, '$2y$10$UCqxt4.lhg3tL/o4hTUguuP4p53I98ol.stPAFM2cRk4A6P0dAGiW', 1);  -- Jane Smith's employee_id is 2




-- Create the shift_types table
CREATE TABLE IF NOT EXISTS shift_types (
    shift_type_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    shift_name VARCHAR(50) NOT NULL, -- Name of the shift type (e.g., 'Morning', 'Afternoon', 'Night')
    shift_start TIME NOT NULL,       -- Start time for this shift type
    shift_end TIME NOT NULL          -- End time for this shift type
);

-- Create the employee_shifts table with employee_shift_id
CREATE TABLE IF NOT EXISTS employee_shifts (
    employee_shift_id INT(11) AUTO_INCREMENT PRIMARY KEY,  -- Changed shift_id to employee_shift_id
    employee_id INT(11) NOT NULL,    -- Foreign key to employee_info table
    shift_type_id INT(11) NOT NULL,  -- Foreign key to shift_types table
    notes VARCHAR(255),              -- Optional notes for the shift
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id),
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(shift_type_id)
);




-- Insert default shift types including weekend shifts
INSERT INTO shift_types (shift_name, shift_start, shift_end) VALUES
('Day Shift', '08:00:00', '18:00:00'),  -- Regular day shift
('Evening Shift', '18:00:00', '04:00:00'), -- Regular evening shift
('Early Morning Shift', '04:00:00', '14:00:00'),  -- Regular early morning shift
('Weekend Shift', '10:00:00', '20:00:00'); -- Weekend shift from 10:00 AM to 08:00 PM


CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT(11) AUTO_INCREMENT PRIMARY KEY,             -- Unique identifier for each attendance record
    employee_id INT(11) NOT NULL,                                 -- Foreign key to employee_info table
    attendance_date DATE NOT NULL,                                -- Date of attendance
    time_in TIME DEFAULT NULL,                                    -- Time the employee clocked in
    time_out TIME DEFAULT NULL,                                   -- Time the employee clocked out
    overtime_in TIME DEFAULT NULL,                                -- Time the employee started overtime, if any
    overtime_out TIME DEFAULT NULL,                               -- Time the employee ended overtime, if any
    status ENUM('Present', 'Absent', 'Leave', 'Undertime', 'Overtime', 'Late', 'Overtime In', 'Overtime Out') DEFAULT 'Present', -- Attendance status
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id), -- Foreign key constraint
    UNIQUE (employee_id, attendance_date)                        -- Unique constraint for employee_id and attendance_date
);

CREATE TABLE IF NOT EXISTS attendance_records (
    record_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    attendance_date DATE NOT NULL,
    time_in TIME DEFAULT NULL,
    time_out TIME DEFAULT NULL,
    overtime_in TIME DEFAULT NULL,
    overtime_out TIME DEFAULT NULL,
    worked_hours DECIMAL(5, 2) DEFAULT 0.00,
    early_arrival_hours DECIMAL(5, 2) DEFAULT 0.00,
    late_departure_hours DECIMAL(5, 2) DEFAULT 0.00,
    total_overtime_hours DECIMAL(5, 2) DEFAULT 0.00,
    status ENUM('Present', 'Absent', 'Leave', 'Undertime', 'Overtime', 'Late', 'Overtime In', 'Overtime Out') DEFAULT 'Present',
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id)
);

CREATE TABLE IF NOT EXISTS attendance_summary (
    summary_id INT(11) AUTO_INCREMENT PRIMARY KEY,           -- Unique identifier for the summary record
    employee_id INT(11) NOT NULL,                             -- Foreign key to employee_info table
    attendance_id INT(11) NOT NULL,                           -- Foreign key to attendance table
    worked_hours DECIMAL(5, 2) NOT NULL,                     -- Total worked hours (regular hours)
    total_overtime_hours DECIMAL(5, 2) NOT NULL,              -- Total overtime hours worked
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id), -- Foreign key constraint to employee_info
    FOREIGN KEY (attendance_id) REFERENCES attendance(attendance_id), -- Foreign key constraint to attendance
    UNIQUE (employee_id, attendance_id)                       -- Unique constraint for employee_id and attendance_id
);
