-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS hr_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE hr_dashboard;

-- Create employees table
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    salary DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive', 'resigned') NOT NULL DEFAULT 'active',
    third_party_info JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_department (department),
    INDEX idx_start_date (start_date),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO employees (name, email, position, department, start_date, salary, status, third_party_info) VALUES
('John Doe', 'john@example.com', 'Software Engineer', 'IT', '2023-01-15', 5000.00, 'active', 
    '{
        "insurance": {
            "medical": true,
            "life": true,
            "provider": "RSSB Insurance",
            "policyNumber": "INS12345",
            "expiryDate": "2024-12-31"
        },
        "rssb": {
            "registered": true,
            "registrationNumber": "RSSB12345",
            "contributionRate": "5"
        }
    }'
),
('Jane Smith', 'jane@example.com', 'HR Manager', 'Human Resources', '2022-06-01', 6000.00, 'active',
    '{
        "insurance": {
            "medical": true,
            "life": true,
            "provider": "RSSB Insurance",
            "policyNumber": "INS12346",
            "expiryDate": "2024-12-31"
        },
        "rssb": {
            "registered": true,
            "registrationNumber": "RSSB12346",
            "contributionRate": "5"
        }
    }'
);

-- Create tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    due_date DATE,
    assignee_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (assignee_id) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date),
    INDEX idx_assignee (assignee_id),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_name (name),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create positions table
CREATE TABLE IF NOT EXISTS positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    department_id INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_title (title),
    INDEX idx_department (department_id),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'leave') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (employee_id, date),
    INDEX idx_date (date),
    INDEX idx_status (status),
    INDEX idx_employee (employee_id),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample departments
INSERT INTO departments (name, description) VALUES
('IT', 'Information Technology Department'),
('HR', 'Human Resources Department'),
('Finance', 'Finance and Accounting Department'),
('Marketing', 'Marketing and Communications Department');

-- Insert sample positions
INSERT INTO positions (title, department_id, description) VALUES
('Software Engineer', 1, 'Develops and maintains software applications'),
('HR Manager', 2, 'Manages human resources operations'),
('Financial Analyst', 3, 'Analyzes financial data and prepares reports'),
('Marketing Specialist', 4, 'Develops and implements marketing strategies');

-- Insert sample employees
INSERT INTO employees (name, email, position, department, start_date, salary, status, third_party_info) VALUES
('John Doe', 'john@example.com', 'Software Engineer', 'IT', '2023-01-15', 5000.00, 'active', 
    '{
        "insurance": {
            "medical": true,
            "life": true,
            "provider": "RSSB Insurance",
            "policyNumber": "INS12345",
            "expiryDate": "2024-12-31"
        },
        "rssb": {
            "registered": true,
            "registrationNumber": "RSSB12345",
            "contributionRate": "5"
        }
    }'
),
('Jane Smith', 'jane@example.com', 'HR Manager', 'Human Resources', '2022-06-01', 6000.00, 'active',
    '{
        "insurance": {
            "medical": true,
            "life": true,
            "provider": "RSSB Insurance",
            "policyNumber": "INS12346",
            "expiryDate": "2024-12-31"
        },
        "rssb": {
            "registered": true,
            "registrationNumber": "RSSB12346",
            "contributionRate": "5"
        }
    }'
);

-- Create user and grant permissions
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON hr_dashboard.* TO 'root'@'%';
FLUSH PRIVILEGES; 