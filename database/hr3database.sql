CREATE DATABASE IF NOT EXISTS hr3database;

USE hr3database;

-- Existing users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    admin_username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Insert default admin users into the admin_users table
INSERT INTO admin_users (admin_username, password) VALUES 
('defaultAdmin', 'admin123'), 
('admin', '$2y$10$0TMicWNiVKVxPEzKAFjzx.tENlPT10Sfcxv/gKjkBVPsn6VdT0Q3m');

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
('John Doe', 1, 'HR Manager', '1990-05-15', '1234567890', 'john.doe@example.com', '123 Main St, City', '2020-01-15', 'Full-time'),
('Jane Smith', 2, 'Sales Executive', '1985-03-22', '0987654321', 'jane.smith@example.com', '456 Elm St, City', '2018-07-10', 'Part-time');


CREATE TABLE IF NOT EXISTS leave_types (
    leave_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    leave_type VARCHAR(100) NOT NULL
);

INSERT INTO leave_types (leave_id, leave_type) VALUES 
(1, 'Sick Leave'),
(2, 'Emergency Leave');





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

-- Create the leave_credits table
CREATE TABLE IF NOT EXISTS leave_credits (
    credit_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    total_credits INT NOT NULL CHECK (total_credits >= 0) -- Ensure total_credits is non-negative
);



CREATE TABLE IF NOT EXISTS employee_credit_balance (
    balance_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    credit_id INT(11) NOT NULL,
    used_credits INT NOT NULL CHECK (used_credits >= 0),  -- Ensure used_credits is non-negative
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (credit_id) REFERENCES leave_credits(credit_id) ON DELETE CASCADE
);




CREATE TABLE IF NOT EXISTS employee_shifts (
    shift_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL, -- Foreign key referencing employee_info
    department_id INT(11) NOT NULL, -- Foreign key for department
    position VARCHAR(50) NOT NULL, -- Position of employee
    shift_start DATETIME NOT NULL, -- Shift start time
    shift_end DATETIME NOT NULL, -- Shift end time
    reason VARCHAR(255) DEFAULT NULL, -- Reason for shift, can be NULL
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id), -- Reference employee_info
    FOREIGN KEY (department_id) REFERENCES departments(department_id) -- Reference departments
);


CREATE TABLE IF NOT EXISTS employee_timesheet (
    timesheet_id INT AUTO_INCREMENT PRIMARY KEY,
    shift_id INT(11) NOT NULL, -- Foreign key to employee_shifts
    employee_id INT(11) NOT NULL, -- Redundant for ease, but can be fetched via shift_id
    time_in DATETIME NOT NULL, -- Actual time-in of the employee
    time_out DATETIME NOT NULL, -- Actual time-out of the employee
    hours_worked DECIMAL(5, 2) NOT NULL, -- Total hours worked (calculated as time_out - time_in)
    overtime_hours DECIMAL(5, 2) DEFAULT 0.00, -- Overtime hours (if applicable)
    status VARCHAR(20) NOT NULL, -- Status (e.g., Present, Overtime, Late)
    FOREIGN KEY (shift_id) REFERENCES employee_shifts(shift_id), -- Reference to employee_shifts
    FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id) -- Reference employee_info
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
(1, '$2y$10$0TMicWNiVKVxPEzKAFjzx.tENlPT10Sfcxv/gKjkBVPsn6VdT0Q3m', 1), -- John Doe's employee_id is 1
(2, '$2y$10$0TMicWNiVKVxPEzKAFjzx.tENlPT10Sfcxv/gKjkBVPsn6VdT0Q3m', 1);  -- Jane Smith's employee_id is 2

