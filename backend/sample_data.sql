-- Insert sample departments
INSERT INTO departments (name, description) VALUES
('Human Resources', 'Handles employee relations and HR processes'),
('Information Technology', 'Manages technical infrastructure and support'),
('Finance', 'Handles financial operations and accounting'),
('Marketing', 'Manages marketing and promotional activities'),
('Operations', 'Oversees daily business operations');

-- Insert sample positions
INSERT INTO positions (title, department_id, description) VALUES
('HR Manager', 1, 'Oversees HR department operations'),
('HR Specialist', 1, 'Handles employee relations and benefits'),
('IT Manager', 2, 'Manages IT infrastructure and support'),
('Software Developer', 2, 'Develops and maintains software applications'),
('Finance Manager', 3, 'Oversees financial operations'),
('Accountant', 3, 'Handles accounting and financial reporting'),
('Marketing Manager', 4, 'Leads marketing strategies'),
('Marketing Specialist', 4, 'Executes marketing campaigns'),
('Operations Manager', 5, 'Manages daily operations'),
('Operations Coordinator', 5, 'Coordinates operational activities');

-- Insert sample employees with AUTO_INCREMENT starting from 1
ALTER TABLE employees AUTO_INCREMENT = 1;
INSERT INTO employees (name, email, position, department, hire_date, third_party_info) VALUES
('John Smith', 'john.smith@company.com', 'HR Manager', 'Human Resources', '2020-01-15', 
 '{"insurance": {"medical": true, "life": true, "provider": "ABC Insurance", "policyNumber": "INS12345", "expiryDate": "2024-12-31"}, "rssb": {"registered": true, "registrationNumber": "RSSB12345", "contributionRate": 5}}'),
('Sarah Johnson', 'sarah.johnson@company.com', 'HR Specialist', 'Human Resources', '2021-03-20', 
 '{"insurance": {"medical": true, "life": true, "provider": "XYZ Insurance", "policyNumber": "INS67890", "expiryDate": "2024-12-31"}, "rssb": {"registered": true, "registrationNumber": "RSSB67890", "contributionRate": 5}}'),
('Michael Brown', 'michael.brown@company.com', 'IT Manager', 'Information Technology', '2019-05-10', 
 '{"insurance": {"medical": true, "life": true, "provider": "ABC Insurance", "policyNumber": "INS24680", "expiryDate": "2024-12-31"}, "rssb": {"registered": true, "registrationNumber": "RSSB24680", "contributionRate": 5}}'),
('Emily Davis', 'emily.davis@company.com', 'Software Developer', 'Information Technology', '2022-02-15', 
 '{"insurance": {"medical": true, "life": false, "provider": "XYZ Insurance", "policyNumber": "INS13579", "expiryDate": "2024-12-31"}, "rssb": {"registered": true, "registrationNumber": "RSSB13579", "contributionRate": 5}}'),
('David Wilson', 'david.wilson@company.com', 'Finance Manager', 'Finance', '2018-11-01', 
 '{"insurance": {"medical": true, "life": true, "provider": "ABC Insurance", "policyNumber": "INS97531", "expiryDate": "2024-12-31"}, "rssb": {"registered": true, "registrationNumber": "RSSB97531", "contributionRate": 5}}');

-- Wait for a second to ensure employees are inserted
SELECT SLEEP(1);

-- Insert sample tasks (after employees are inserted)
INSERT INTO tasks (title, description, status, priority, due_date, assignee_id) VALUES
('Update Employee Handbook', 'Review and update the employee handbook with new policies', 'pending', 'high', '2024-05-01', 1),
('Conduct Performance Reviews', 'Schedule and conduct quarterly performance reviews', 'in_progress', 'medium', '2024-04-15', 1),
('Implement New HR Software', 'Set up and configure new HR management system', 'pending', 'high', '2024-06-01', 2),
('Server Maintenance', 'Perform routine maintenance on production servers', 'completed', 'medium', '2024-03-20', 3),
('Develop New Feature', 'Implement user authentication system', 'in_progress', 'high', '2024-04-30', 4),
('Fix Bug in Dashboard', 'Resolve issue with data visualization in dashboard', 'pending', 'low', '2024-04-10', 4),
('Prepare Financial Report', 'Generate Q1 financial statements', 'completed', 'high', '2024-04-05', 5),
('Process Payroll', 'Run monthly payroll for all employees', 'pending', 'high', '2024-04-25', 5),
('Update Marketing Strategy', 'Revise marketing plan for Q2', 'in_progress', 'medium', '2024-04-20', 1),
('Create Social Media Campaign', 'Develop content for upcoming product launch', 'pending', 'low', '2024-05-15', 2); 