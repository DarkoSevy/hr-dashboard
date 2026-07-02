-- =============================================================================
-- PTS HRMS — seed data: roles, permissions, org structure, leave types,
-- holidays, shifts, default admin user, and demo employees.
-- Run after schema.sql.  Default admin: admin / Admin@2026  (change at once!)
-- =============================================================================
USE pts_hrms;

-- Roles -----------------------------------------------------------------------
INSERT INTO roles (name, slug, description) VALUES
('Administrator',      'admin',              'Full system access'),
('HR Manager',         'hr_manager',         'Manages all HR modules'),
('HR Officer',         'hr_officer',         'Day-to-day HR operations'),
('Managing Director',  'managing_director',  'Executive dashboards and approvals'),
('Department Manager', 'department_manager', 'Manages own department staff'),
('Supervisor',         'supervisor',         'First-line leave/attendance approvals'),
('Finance Officer',    'finance_officer',    'Payroll data and exports'),
('Operations Manager', 'operations_manager', 'Operations staff and driver oversight'),
('Fleet Manager',      'fleet_manager',      'Driver and vehicle assignment data'),
('Employee',           'employee',           'Self-service portal'),
('Auditor',            'auditor',            'Read-only access with audit logs'),
('Compliance Officer', 'compliance_officer', 'Compliance, disciplinary, H&S'),
('IT Administrator',   'it_admin',           'User accounts, security, backups');

-- Permissions (module.action) ---------------------------------------------------
INSERT INTO permissions (name, module, description) VALUES
('dashboard.view','dashboard','View executive dashboard'),
('employees.view','employees','View employees'),
('employees.manage','employees','Create/update/delete employees'),
('departments.manage','organization','Manage departments & positions'),
('recruitment.view','recruitment','View recruitment'),
('recruitment.manage','recruitment','Manage vacancies, applicants, offers'),
('leaves.view','leaves','View leave requests'),
('leaves.request','leaves','Request own leave'),
('leaves.approve_supervisor','leaves','Supervisor-level approval'),
('leaves.approve_hr','leaves','HR-level approval'),
('attendance.view','attendance','View attendance'),
('attendance.clock','attendance','Clock in/out'),
('attendance.manage','attendance','Edit attendance records'),
('payroll.view','payroll','View payroll data'),
('payroll.manage','payroll','Prepare and export payroll'),
('drivers.view','drivers','View driver records'),
('drivers.manage','drivers','Manage driver records'),
('performance.view','performance','View performance reviews'),
('performance.manage','performance','Manage reviews & goals'),
('training.view','training','View training'),
('training.manage','training','Manage courses & sessions'),
('documents.view','documents','View document vault'),
('documents.manage','documents','Upload/replace documents'),
('disciplinary.view','disciplinary','View disciplinary cases'),
('disciplinary.manage','disciplinary','Manage disciplinary cases'),
('health.view','health','View health & safety'),
('health.manage','health','Manage checkups & incidents'),
('assets.view','assets','View assets'),
('assets.manage','assets','Assign/return assets'),
('requests.view','requests','View internal requests'),
('requests.create','requests','Submit internal requests'),
('requests.approve','requests','Approve internal requests'),
('reports.view','reports','View & export HR reports'),
('users.manage','security','Manage users & roles'),
('audit.view','security','View audit trail'),
('settings.manage','security','Manage system settings');

-- Administrator & IT admin: everything
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug IN ('admin','it_admin');

-- HR Manager: everything except users/settings
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug = 'hr_manager' AND p.name NOT IN ('users.manage','settings.manage');

-- HR Officer
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug = 'hr_officer' AND p.module IN
('dashboard','employees','recruitment','leaves','attendance','training','documents','requests','reports')
AND p.name <> 'requests.approve';

-- Managing Director: view everything + approvals
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug = 'managing_director' AND
(p.name LIKE '%.view' OR p.name IN ('dashboard.view','requests.approve','leaves.approve_hr'));

-- Department Manager & Supervisor
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug IN ('department_manager','supervisor') AND p.name IN
('dashboard.view','employees.view','leaves.view','leaves.request','leaves.approve_supervisor',
 'attendance.view','attendance.clock','performance.view','performance.manage',
 'requests.view','requests.create','requests.approve','reports.view','training.view');

-- Finance Officer
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug = 'finance_officer' AND p.name IN
('dashboard.view','employees.view','payroll.view','payroll.manage','reports.view',
 'attendance.view','leaves.view','requests.view','requests.approve');

-- Operations / Fleet managers
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug IN ('operations_manager','fleet_manager') AND p.name IN
('dashboard.view','employees.view','drivers.view','drivers.manage','attendance.view',
 'leaves.view','leaves.approve_supervisor','training.view','reports.view','health.view',
 'requests.view','requests.approve');

-- Employee (self service)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug = 'employee' AND p.name IN
('leaves.request','attendance.clock','requests.create');

-- Auditor / Compliance
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug = 'auditor' AND (p.name LIKE '%.view' OR p.name = 'audit.view');
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.slug = 'compliance_officer' AND
(p.name LIKE '%.view' OR p.name IN ('disciplinary.manage','health.manage','audit.view'));

-- Organization ------------------------------------------------------------------
INSERT INTO branches (name, location) VALUES
('Head Office - Kigali', 'KG 7 Ave, Kigali'),
('Kigali International Airport Desk', 'Kanombe, Kigali');

INSERT INTO departments (branch_id, name, code) VALUES
(1,'Human Resources','HR'),
(1,'Operations','OPS'),
(1,'Finance','FIN'),
(1,'Sales & Marketing','SLM'),
(1,'Procurement','PRC'),
(1,'IT','IT'),
(1,'Administration','ADM'),
(1,'Compliance','CMP'),
(1,'Managing Director Office','MDO');

INSERT INTO positions (department_id, title) VALUES
(9,'Managing Director'),
(1,'HR Manager'),(1,'HR Officer'),
(2,'Operations Manager'),(2,'Fleet Supervisor'),(2,'Senior Driver'),(2,'Driver'),(2,'Tour Guide'),
(3,'Finance Manager'),(3,'Accountant'),
(4,'Sales Manager'),(4,'Reservations Officer'),
(5,'Procurement Officer'),
(6,'IT Administrator'),
(7,'Administration Officer'),(7,'Receptionist'),
(8,'Compliance Officer');

-- Leave types (Rwanda labour code aligned) --------------------------------------
INSERT INTO leave_types (name, days_per_year, is_paid, requires_attachment, gender) VALUES
('Annual',        18, 1, 0, 'all'),
('Sick',          15, 1, 1, 'all'),
('Maternity',     84, 1, 1, 'female'),
('Paternity',      4, 1, 0, 'male'),
('Compassionate',  3, 1, 0, 'all'),
('Study',          5, 1, 1, 'all'),
('Unpaid',         0, 0, 0, 'all'),
('Special Leave',  2, 1, 0, 'all');

-- Rwanda public holidays 2026 ----------------------------------------------------
INSERT INTO holidays (name, date, is_recurring) VALUES
('New Year''s Day',            '2026-01-01', 1),
('National Heroes Day',        '2026-02-01', 1),
('Genocide Memorial Day',      '2026-04-07', 1),
('Good Friday',                '2026-04-03', 0),
('Labour Day',                 '2026-05-01', 1),
('Independence Day',           '2026-07-01', 1),
('Liberation Day',             '2026-07-04', 1),
('Assumption Day',             '2026-08-15', 1),
('Christmas Day',              '2026-12-25', 1),
('Boxing Day',                 '2026-12-26', 1);

-- Shifts -------------------------------------------------------------------------
INSERT INTO shifts (name, start_time, end_time, grace_minutes, works_weekends) VALUES
('Office Day',      '08:00:00','17:00:00',15,0),
('Airport Early',   '04:00:00','13:00:00',10,1),
('Airport Late',    '13:00:00','22:00:00',10,1),
('Night Duty',      '22:00:00','06:00:00',10,1);

-- Default admin user (password: Admin@2026) --------------------------------------
INSERT INTO users (role_id, username, email, password_hash, password_changed_at, is_active)
SELECT id, 'admin', 'admin@pts.rw',
'$2y$12$3vtBAQfK/GzRRKgemo4f5OnCnlLDEa/g.Zas5l260Pfht9/hHTAjS',
NOW(), 1 FROM roles WHERE slug='admin';

-- Demo employees ------------------------------------------------------------------
INSERT INTO employees (employee_no, first_name, last_name, gender, date_of_birth, nationality,
  phone, email, department_id, position_id, branch_id, employment_type, date_hired,
  status, basic_salary, rssb_number, is_driver) VALUES
('PTS-0001','Yves','Butera','male','1988-03-14','Rwandan','+250788111111','yves.butera@pts.rw',9,1,1,'permanent','2018-01-15','active',3500000,'RSSB-0001',0),
('PTS-0002','Claudine','Mukamana','female','1990-07-22','Rwandan','+250788222222','claudine.m@pts.rw',1,2,1,'permanent','2019-05-02','active',1800000,'RSSB-0002',0),
('PTS-0003','Eric','Nshimiyimana','male','1992-11-05','Rwandan','+250788333333','eric.n@pts.rw',2,4,1,'permanent','2020-02-10','active',1500000,'RSSB-0003',0),
('PTS-0004','Jean Bosco','Habimana','male','1994-06-18','Rwandan','+250788444444','jb.habimana@pts.rw',2,7,1,'contract','2021-09-01','active',450000,'RSSB-0004',1),
('PTS-0005','Aline','Uwase','female','1996-01-30','Rwandan','+250788555555','aline.u@pts.rw',3,10,1,'permanent','2022-03-14','active',900000,'RSSB-0005',0),
('PTS-0006','Patrick','Mugisha','male','1991-09-09','Rwandan','+250788666666','patrick.m@pts.rw',2,7,2,'contract','2023-01-09','active',450000,'RSSB-0006',1);

-- Supervisor links
UPDATE employees SET manager_id = 1 WHERE id IN (2,3,5);
UPDATE employees SET manager_id = 3 WHERE id IN (4,6);
UPDATE departments SET manager_id = 2 WHERE code='HR';
UPDATE departments SET manager_id = 3 WHERE code='OPS';

-- Driver extensions
INSERT INTO drivers (employee_id, license_number, license_categories, license_expiry,
  medical_exam_date, medical_exam_expiry, rating, trips_completed) VALUES
(4,'RW-DL-88231','B,D','2027-04-20','2026-01-10','2027-01-10',4.60,312),
(6,'RW-DL-90417','B,C,D','2026-09-12','2025-11-02','2026-11-02',4.30,187);

INSERT INTO vehicles (plate_number, make_model, vehicle_type) VALUES
('RAE 123 A','Toyota Land Cruiser V8','suv'),
('RAF 456 B','Mercedes-Benz S-Class','sedan'),
('RAG 789 C','Toyota Coaster','coaster');

INSERT INTO driver_vehicle_assignments (driver_id, vehicle_id) VALUES (1,2),(2,1);

-- Leave balances for current year
INSERT INTO leave_balances (employee_id, leave_type_id, year, entitled)
SELECT e.id, lt.id, YEAR(CURDATE()), lt.days_per_year
FROM employees e JOIN leave_types lt
WHERE lt.gender = 'all' OR lt.gender = e.gender;

-- Settings ------------------------------------------------------------------------
INSERT INTO settings (`key`,`value`) VALUES
('company_name','Premier Transport & Tour Services Ltd'),
('company_email','hr@pts.rw'),
('password_expiry_days','90'),
('session_timeout_minutes','30'),
('alert_days_before_expiry','30'),
('rssb_employee_rate','0.06'),
('rssb_employer_rate','0.08');
