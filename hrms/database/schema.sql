-- =============================================================================
-- PTS Rwanda — Human Resource Management System (HRMS)
-- MySQL 8.0+ schema. Engine: InnoDB, charset utf8mb4.
-- Load order matters: referenced tables are created before referencing tables.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS pts_hrms
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pts_hrms;

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. RBAC: roles, permissions, users
-- -----------------------------------------------------------------------------

CREATE TABLE roles (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL UNIQUE,      -- Administrator, HR Manager...
  slug          VARCHAR(60)  NOT NULL UNIQUE,      -- admin, hr_manager...
  description   VARCHAR(255) NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL UNIQUE,      -- employees.view, leaves.approve...
  module        VARCHAR(50)  NOT NULL,
  description   VARCHAR(255) NULL,
  INDEX idx_perm_module (module)
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  role_id       INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE users (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id        INT UNSIGNED NULL,            -- FK added after employees exists
  role_id            INT UNSIGNED NOT NULL,
  username           VARCHAR(60)  NOT NULL UNIQUE,
  email              VARCHAR(150) NOT NULL UNIQUE,
  password_hash      VARCHAR(255) NOT NULL,
  api_key            CHAR(64)     NULL UNIQUE,     -- REST API bearer token (hashed)
  two_factor_enabled TINYINT(1)   NOT NULL DEFAULT 0,
  two_factor_code    VARCHAR(255) NULL,            -- hashed OTP
  two_factor_expires DATETIME     NULL,
  password_changed_at DATETIME    NULL,            -- password-expiry policy
  failed_attempts    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until       DATETIME     NULL,
  last_login_at      DATETIME     NULL,
  last_login_ip      VARCHAR(45)  NULL,
  is_active          TINYINT(1)   NOT NULL DEFAULT 1,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at    DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 2. Organization structure
-- -----------------------------------------------------------------------------

CREATE TABLE branches (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  location   VARCHAR(150) NULL,
  phone      VARCHAR(30)  NULL,
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE departments (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id   INT UNSIGNED NULL,
  name        VARCHAR(100) NOT NULL UNIQUE,       -- HR, Operations, Finance...
  code        VARCHAR(20)  NOT NULL UNIQUE,
  manager_id  INT UNSIGNED NULL,                  -- employee heading the department
  parent_id   INT UNSIGNED NULL,                  -- org-chart nesting
  description VARCHAR(255) NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
  CONSTRAINT fk_dept_parent FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE positions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id INT UNSIGNED NOT NULL,
  title         VARCHAR(120) NOT NULL,
  reports_to    INT UNSIGNED NULL,                -- parent position (hierarchy)
  salary_min    DECIMAL(14,2) NULL,
  salary_max    DECIMAL(14,2) NULL,
  description   TEXT NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pos_dept FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_pos_parent FOREIGN KEY (reports_to) REFERENCES positions(id) ON DELETE SET NULL,
  INDEX idx_pos_dept (department_id)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 3. Employees, contracts, documents
-- -----------------------------------------------------------------------------

CREATE TABLE employees (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_no        VARCHAR(20)  NOT NULL UNIQUE,        -- PTS-0001
  photo_path         VARCHAR(255) NULL,
  first_name         VARCHAR(80)  NOT NULL,
  last_name          VARCHAR(80)  NOT NULL,
  national_id        VARCHAR(30)  NULL UNIQUE,
  passport_no        VARCHAR(30)  NULL,
  gender             ENUM('male','female') NOT NULL,
  date_of_birth      DATE NULL,
  marital_status     ENUM('single','married','divorced','widowed') NULL,
  nationality        VARCHAR(60)  NOT NULL DEFAULT 'Rwandan',
  blood_group        ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NULL,
  phone              VARCHAR(30)  NULL,
  email              VARCHAR(150) NULL UNIQUE,
  emergency_name     VARCHAR(120) NULL,
  emergency_phone    VARCHAR(30)  NULL,
  emergency_relation VARCHAR(60)  NULL,
  address            VARCHAR(255) NULL,
  department_id      INT UNSIGNED NULL,
  position_id        INT UNSIGNED NULL,
  branch_id          INT UNSIGNED NULL,
  manager_id         INT UNSIGNED NULL,                   -- direct supervisor
  employment_type    ENUM('permanent','contract','casual','intern','consultant') NOT NULL DEFAULT 'permanent',
  date_hired         DATE NOT NULL,
  probation_end      DATE NULL,
  confirmation_date  DATE NULL,
  status             ENUM('active','suspended','terminated','retired','resigned') NOT NULL DEFAULT 'active',
  status_date        DATE NULL,
  basic_salary       DECIMAL(14,2) NOT NULL DEFAULT 0,
  bank_name          VARCHAR(100) NULL,
  bank_account       VARCHAR(50)  NULL,
  tin_number         VARCHAR(30)  NULL,
  rssb_number        VARCHAR(30)  NULL,
  medical_insurer    VARCHAR(100) NULL,
  medical_policy_no  VARCHAR(50)  NULL,
  medical_expiry     DATE NULL,
  is_driver          TINYINT(1) NOT NULL DEFAULT 0,
  created_by         INT UNSIGNED NULL,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_emp_dept    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_emp_pos     FOREIGN KEY (position_id)   REFERENCES positions(id)   ON DELETE SET NULL,
  CONSTRAINT fk_emp_branch  FOREIGN KEY (branch_id)     REFERENCES branches(id)    ON DELETE SET NULL,
  CONSTRAINT fk_emp_manager FOREIGN KEY (manager_id)    REFERENCES employees(id)   ON DELETE SET NULL,
  INDEX idx_emp_status (status),
  INDEX idx_emp_dept (department_id),
  INDEX idx_emp_name (last_name, first_name),
  INDEX idx_emp_dob (date_of_birth)
) ENGINE=InnoDB;

ALTER TABLE users
  ADD CONSTRAINT fk_users_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL;
ALTER TABLE departments
  ADD CONSTRAINT fk_dept_manager FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL;

CREATE TABLE contracts (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id   INT UNSIGNED NOT NULL,
  contract_type ENUM('permanent','fixed_term','casual','internship','consultancy') NOT NULL,
  start_date    DATE NOT NULL,
  end_date      DATE NULL,                                -- NULL = open-ended
  salary        DECIMAL(14,2) NOT NULL DEFAULT 0,
  file_path     VARCHAR(255) NULL,
  status        ENUM('active','expired','terminated','renewed') NOT NULL DEFAULT 'active',
  notes         TEXT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ct_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_ct_expiry (end_date, status)
) ENGINE=InnoDB;

CREATE TABLE employee_documents (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id  INT UNSIGNED NOT NULL,
  doc_type     ENUM('cv','contract','academic_certificate','police_clearance','passport',
                    'national_id','driving_license','medical_certificate','performance_review',
                    'insurance','permit','other') NOT NULL,
  title        VARCHAR(150) NOT NULL,
  file_path    VARCHAR(255) NOT NULL,
  version      INT UNSIGNED NOT NULL DEFAULT 1,          -- document vault versioning
  replaces_id  INT UNSIGNED NULL,                        -- previous version
  expiry_date  DATE NULL,                                -- drives expiry alerts
  uploaded_by  INT UNSIGNED NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_doc_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  CONSTRAINT fk_doc_prev FOREIGN KEY (replaces_id) REFERENCES employee_documents(id) ON DELETE SET NULL,
  INDEX idx_doc_expiry (expiry_date),
  INDEX idx_doc_emp_type (employee_id, doc_type)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 4. Leave management
-- -----------------------------------------------------------------------------

CREATE TABLE leave_types (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60) NOT NULL UNIQUE,             -- Annual, Sick, Maternity...
  days_per_year DECIMAL(5,1) NOT NULL DEFAULT 0,
  is_paid       TINYINT(1) NOT NULL DEFAULT 1,
  requires_attachment TINYINT(1) NOT NULL DEFAULT 0,     -- e.g. sick note
  gender        ENUM('all','male','female') NOT NULL DEFAULT 'all',
  is_active     TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE leave_balances (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id   INT UNSIGNED NOT NULL,
  leave_type_id INT UNSIGNED NOT NULL,
  year          SMALLINT UNSIGNED NOT NULL,
  entitled      DECIMAL(5,1) NOT NULL DEFAULT 0,
  carried_over  DECIMAL(5,1) NOT NULL DEFAULT 0,
  used          DECIMAL(5,1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_balance (employee_id, leave_type_id, year),
  CONSTRAINT fk_lb_emp  FOREIGN KEY (employee_id)   REFERENCES employees(id)   ON DELETE CASCADE,
  CONSTRAINT fk_lb_type FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE leave_requests (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id        INT UNSIGNED NOT NULL,
  leave_type_id      INT UNSIGNED NOT NULL,
  start_date         DATE NOT NULL,
  end_date           DATE NOT NULL,
  days               DECIMAL(5,1) NOT NULL,              -- working days, auto-calculated
  reason             TEXT NULL,
  attachment_path    VARCHAR(255) NULL,
  status             ENUM('pending_supervisor','pending_hr','approved','rejected','cancelled')
                     NOT NULL DEFAULT 'pending_supervisor',
  supervisor_id      INT UNSIGNED NULL,
  supervisor_at      DATETIME NULL,
  supervisor_comment VARCHAR(255) NULL,
  hr_id              INT UNSIGNED NULL,
  hr_at              DATETIME NULL,
  hr_comment         VARCHAR(255) NULL,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_lr_emp  FOREIGN KEY (employee_id)   REFERENCES employees(id)   ON DELETE CASCADE,
  CONSTRAINT fk_lr_type FOREIGN KEY (leave_type_id) REFERENCES leave_types(id),
  CONSTRAINT fk_lr_sup  FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_lr_hr   FOREIGN KEY (hr_id)         REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_lr_status (status),
  INDEX idx_lr_dates (start_date, end_date)
) ENGINE=InnoDB;

CREATE TABLE holidays (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name     VARCHAR(100) NOT NULL,
  date     DATE NOT NULL UNIQUE,
  is_recurring TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 5. Attendance & shifts
-- -----------------------------------------------------------------------------

CREATE TABLE shifts (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(60) NOT NULL,                      -- Day, Night, Airport Early...
  start_time  TIME NOT NULL,
  end_time    TIME NOT NULL,
  grace_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 15,   -- late threshold
  works_weekends TINYINT(1) NOT NULL DEFAULT 0,
  is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE shift_assignments (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  shift_id    INT UNSIGNED NOT NULL,
  start_date  DATE NOT NULL,
  end_date    DATE NULL,
  CONSTRAINT fk_sa_emp   FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  CONSTRAINT fk_sa_shift FOREIGN KEY (shift_id)    REFERENCES shifts(id)    ON DELETE CASCADE,
  INDEX idx_sa_emp (employee_id, start_date)
) ENGINE=InnoDB;

CREATE TABLE attendance_records (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id  INT UNSIGNED NOT NULL,
  work_date    DATE NOT NULL,
  clock_in     DATETIME NULL,
  clock_out    DATETIME NULL,
  in_latitude  DECIMAL(10,7) NULL,                       -- GPS support
  in_longitude DECIMAL(10,7) NULL,
  out_latitude  DECIMAL(10,7) NULL,
  out_longitude DECIMAL(10,7) NULL,
  source       ENUM('web','mobile','biometric','manual') NOT NULL DEFAULT 'web',
  status       ENUM('present','late','absent','on_leave','holiday','weekend') NOT NULL DEFAULT 'present',
  late_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  early_departure_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  notes        VARCHAR(255) NULL,
  UNIQUE KEY uq_att (employee_id, work_date),
  CONSTRAINT fk_att_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_att_date (work_date),
  INDEX idx_att_status (status, work_date)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 6. Payroll preparation (data handed off to Finance / QuickBooks)
-- -----------------------------------------------------------------------------

CREATE TABLE payroll_periods (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(30) NOT NULL,                       -- "2026-07"
  start_date DATE NOT NULL,
  end_date   DATE NOT NULL,
  status     ENUM('open','processing','locked','exported') NOT NULL DEFAULT 'open',
  exported_at DATETIME NULL,
  UNIQUE KEY uq_period (name)
) ENGINE=InnoDB;

CREATE TABLE payroll_entries (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  period_id      INT UNSIGNED NOT NULL,
  employee_id    INT UNSIGNED NOT NULL,
  basic_salary   DECIMAL(14,2) NOT NULL DEFAULT 0,
  transport_allowance DECIMAL(14,2) NOT NULL DEFAULT 0,
  telephone_allowance DECIMAL(14,2) NOT NULL DEFAULT 0,
  per_diem       DECIMAL(14,2) NOT NULL DEFAULT 0,
  overtime       DECIMAL(14,2) NOT NULL DEFAULT 0,
  bonus          DECIMAL(14,2) NOT NULL DEFAULT 0,
  commission     DECIMAL(14,2) NOT NULL DEFAULT 0,
  gross_salary   DECIMAL(14,2) NOT NULL DEFAULT 0,
  rssb_employee  DECIMAL(14,2) NOT NULL DEFAULT 0,       -- pension/maternity/CBHI employee share
  rssb_employer  DECIMAL(14,2) NOT NULL DEFAULT 0,
  paye           DECIMAL(14,2) NOT NULL DEFAULT 0,
  loan_deduction DECIMAL(14,2) NOT NULL DEFAULT 0,
  advance_deduction DECIMAL(14,2) NOT NULL DEFAULT 0,
  other_deduction DECIMAL(14,2) NOT NULL DEFAULT 0,
  net_salary     DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes          VARCHAR(255) NULL,
  UNIQUE KEY uq_pe (period_id, employee_id),
  CONSTRAINT fk_pe_period FOREIGN KEY (period_id)   REFERENCES payroll_periods(id) ON DELETE CASCADE,
  CONSTRAINT fk_pe_emp    FOREIGN KEY (employee_id) REFERENCES employees(id)       ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 7. Recruitment
-- -----------------------------------------------------------------------------

CREATE TABLE job_vacancies (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  position_id   INT UNSIGNED NULL,
  title         VARCHAR(150) NOT NULL,
  department_id INT UNSIGNED NULL,
  employment_type ENUM('permanent','contract','casual','intern','consultant') NOT NULL DEFAULT 'permanent',
  openings      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  description   TEXT NULL,
  requirements  TEXT NULL,
  deadline      DATE NULL,
  status        ENUM('draft','published','closed','filled') NOT NULL DEFAULT 'draft',
  created_by    INT UNSIGNED NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_jv_pos  FOREIGN KEY (position_id)   REFERENCES positions(id)   ON DELETE SET NULL,
  CONSTRAINT fk_jv_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE applicants (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vacancy_id   INT UNSIGNED NOT NULL,
  full_name    VARCHAR(150) NOT NULL,
  email        VARCHAR(150) NOT NULL,
  phone        VARCHAR(30)  NULL,
  cv_path      VARCHAR(255) NULL,
  cover_letter TEXT NULL,
  stage        ENUM('applied','shortlisted','interview','offer','accepted','hired','rejected','talent_pool')
               NOT NULL DEFAULT 'applied',                -- recruitment funnel
  employee_id  INT UNSIGNED NULL,                         -- set on conversion to employee
  applied_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_vac FOREIGN KEY (vacancy_id)  REFERENCES job_vacancies(id) ON DELETE CASCADE,
  CONSTRAINT fk_app_emp FOREIGN KEY (employee_id) REFERENCES employees(id)     ON DELETE SET NULL,
  INDEX idx_app_stage (vacancy_id, stage)
) ENGINE=InnoDB;

CREATE TABLE interviews (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  applicant_id INT UNSIGNED NOT NULL,
  scheduled_at DATETIME NOT NULL,
  location     VARCHAR(150) NULL,
  panel        VARCHAR(255) NULL,                         -- interviewer names
  score        DECIMAL(5,2) NULL,                         -- 0–100
  outcome      ENUM('pending','passed','failed','no_show') NOT NULL DEFAULT 'pending',
  notes        TEXT NULL,
  CONSTRAINT fk_int_app FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE job_offers (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  applicant_id INT UNSIGNED NOT NULL,
  salary       DECIMAL(14,2) NOT NULL,
  start_date   DATE NULL,
  letter_path  VARCHAR(255) NULL,
  status       ENUM('sent','accepted','declined','withdrawn') NOT NULL DEFAULT 'sent',
  sent_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  responded_at DATETIME NULL,
  CONSTRAINT fk_off_app FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 8. Drivers & fleet-facing HR data
-- -----------------------------------------------------------------------------

CREATE TABLE drivers (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id        INT UNSIGNED NOT NULL UNIQUE,        -- extension of employees
  license_number     VARCHAR(30) NOT NULL,
  license_categories VARCHAR(30) NOT NULL,                -- e.g. "B,C,D"
  license_expiry     DATE NOT NULL,
  permit_expiry      DATE NULL,
  medical_exam_date  DATE NULL,
  medical_exam_expiry DATE NULL,
  rating             DECIMAL(3,2) NOT NULL DEFAULT 0,     -- 0–5
  trips_completed    INT UNSIGNED NOT NULL DEFAULT 0,
  status             ENUM('available','on_trip','suspended','inactive') NOT NULL DEFAULT 'available',
  CONSTRAINT fk_drv_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_drv_license_exp (license_expiry),
  INDEX idx_drv_medical_exp (medical_exam_expiry)
) ENGINE=InnoDB;

CREATE TABLE vehicles (                                    -- light mirror of Fleet module
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plate_number VARCHAR(20) NOT NULL UNIQUE,
  make_model   VARCHAR(100) NULL,
  vehicle_type ENUM('sedan','suv','van','coaster','bus','truck','safari_4x4') NULL,
  is_active    TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE driver_vehicle_assignments (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  driver_id   INT UNSIGNED NOT NULL,
  vehicle_id  INT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  returned_at DATETIME NULL,
  CONSTRAINT fk_dva_drv FOREIGN KEY (driver_id)  REFERENCES drivers(id)  ON DELETE CASCADE,
  CONSTRAINT fk_dva_veh FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE driver_trips (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  driver_id   INT UNSIGNED NOT NULL,
  vehicle_id  INT UNSIGNED NULL,
  trip_date   DATE NOT NULL,
  trip_type   ENUM('airport_transfer','corporate','car_rental','safari','tour','other') NOT NULL DEFAULT 'other',
  route       VARCHAR(255) NULL,
  distance_km DECIMAL(8,1) NULL,
  fuel_liters DECIMAL(8,2) NULL,                          -- fuel consumption tracking
  client_rating TINYINT UNSIGNED NULL,                    -- 1–5
  notes       VARCHAR(255) NULL,
  CONSTRAINT fk_dt_drv FOREIGN KEY (driver_id)  REFERENCES drivers(id)  ON DELETE CASCADE,
  CONSTRAINT fk_dt_veh FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
  INDEX idx_dt_date (driver_id, trip_date)
) ENGINE=InnoDB;

CREATE TABLE driver_incidents (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  driver_id   INT UNSIGNED NOT NULL,
  incident_type ENUM('accident','traffic_fine','warning','suspension','complaint') NOT NULL,
  incident_date DATE NOT NULL,
  description TEXT NULL,
  cost        DECIMAL(14,2) NULL,                          -- fine amount / damage cost
  resolved    TINYINT(1) NOT NULL DEFAULT 0,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_di_drv FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
  INDEX idx_di_type (driver_id, incident_type)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 9. Performance management
-- -----------------------------------------------------------------------------

CREATE TABLE performance_reviews (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id   INT UNSIGNED NOT NULL,
  reviewer_id   INT UNSIGNED NULL,
  review_type   ENUM('quarterly','annual','probation','360') NOT NULL DEFAULT 'quarterly',
  period        VARCHAR(20) NOT NULL,                     -- "2026-Q2", "2026"
  kpi_score     DECIMAL(5,2) NULL,                        -- 0–100
  overall_rating ENUM('outstanding','exceeds','meets','needs_improvement','unsatisfactory') NULL,
  strengths     TEXT NULL,
  improvements  TEXT NULL,
  recommendation ENUM('none','promotion','salary_increase','warning','improvement_plan','termination') NOT NULL DEFAULT 'none',
  status        ENUM('draft','submitted','acknowledged') NOT NULL DEFAULT 'draft',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_prv_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  CONSTRAINT fk_prv_rev FOREIGN KEY (reviewer_id) REFERENCES users(id)     ON DELETE SET NULL,
  INDEX idx_prv_emp (employee_id, period)
) ENGINE=InnoDB;

CREATE TABLE performance_goals (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  review_id   INT UNSIGNED NULL,
  title       VARCHAR(200) NOT NULL,
  kpi_metric  VARCHAR(150) NULL,
  target      VARCHAR(100) NULL,
  due_date    DATE NULL,
  progress    TINYINT UNSIGNED NOT NULL DEFAULT 0,        -- 0–100 %
  status      ENUM('open','on_track','at_risk','achieved','missed') NOT NULL DEFAULT 'open',
  CONSTRAINT fk_pg_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  CONSTRAINT fk_pg_rev FOREIGN KEY (review_id) REFERENCES performance_reviews(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 10. Training
-- -----------------------------------------------------------------------------

CREATE TABLE training_courses (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150) NOT NULL,                      -- Driver Safety, First Aid...
  category    ENUM('driver_safety','customer_service','tour_guide','first_aid','fire_safety',
                   'it_security','compliance','leadership','other') NOT NULL DEFAULT 'other',
  provider    VARCHAR(150) NULL,
  validity_months SMALLINT UNSIGNED NULL,                 -- certificate validity
  description TEXT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE training_sessions (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id  INT UNSIGNED NOT NULL,
  start_date DATE NOT NULL,
  end_date   DATE NULL,
  location   VARCHAR(150) NULL,
  trainer    VARCHAR(150) NULL,
  capacity   SMALLINT UNSIGNED NULL,
  status     ENUM('planned','ongoing','completed','cancelled') NOT NULL DEFAULT 'planned',
  CONSTRAINT fk_ts_course FOREIGN KEY (course_id) REFERENCES training_courses(id) ON DELETE CASCADE,
  INDEX idx_ts_date (start_date)
) ENGINE=InnoDB;

CREATE TABLE training_participants (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id  INT UNSIGNED NOT NULL,
  employee_id INT UNSIGNED NOT NULL,
  attended    TINYINT(1) NOT NULL DEFAULT 0,
  score       DECIMAL(5,2) NULL,
  certificate_path   VARCHAR(255) NULL,
  certificate_expiry DATE NULL,                           -- expiry tracking
  UNIQUE KEY uq_tp (session_id, employee_id),
  CONSTRAINT fk_tp_sess FOREIGN KEY (session_id)  REFERENCES training_sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_tp_emp  FOREIGN KEY (employee_id) REFERENCES employees(id)         ON DELETE CASCADE,
  INDEX idx_tp_cert_exp (certificate_expiry)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 11. Disciplinary management
-- -----------------------------------------------------------------------------

CREATE TABLE disciplinary_cases (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id  INT UNSIGNED NOT NULL,
  case_date    DATE NOT NULL,
  category     VARCHAR(100) NOT NULL,                     -- misconduct, absenteeism...
  description  TEXT NOT NULL,
  action       ENUM('verbal_warning','written_warning','final_warning','suspension','dismissal') NOT NULL,
  action_date  DATE NULL,
  evidence_path VARCHAR(255) NULL,
  status       ENUM('open','under_review','appealed','closed') NOT NULL DEFAULT 'open',
  appeal_notes TEXT NULL,
  appeal_outcome ENUM('upheld','overturned','reduced') NULL,
  raised_by    INT UNSIGNED NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dc_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_dc_emp (employee_id, status)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 12. Health & safety
-- -----------------------------------------------------------------------------

CREATE TABLE medical_checkups (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id  INT UNSIGNED NOT NULL,
  checkup_date DATE NOT NULL,
  checkup_type ENUM('annual','driver_medical','pre_employment','follow_up') NOT NULL DEFAULT 'annual',
  result       ENUM('fit','fit_with_conditions','unfit','pending') NOT NULL DEFAULT 'pending',
  next_due     DATE NULL,
  notes        TEXT NULL,
  CONSTRAINT fk_mc_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_mc_due (next_due)
) ENGINE=InnoDB;

CREATE TABLE incident_reports (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id   INT UNSIGNED NULL,
  incident_date DATETIME NOT NULL,
  incident_type ENUM('workplace_injury','vehicle_accident','near_miss','fatigue','occupational_illness','other') NOT NULL,
  location      VARCHAR(150) NULL,
  description   TEXT NOT NULL,
  severity      ENUM('minor','moderate','severe','fatal') NOT NULL DEFAULT 'minor',
  insurance_claim TINYINT(1) NOT NULL DEFAULT 0,
  claim_status  ENUM('none','submitted','approved','rejected','paid') NOT NULL DEFAULT 'none',
  status        ENUM('reported','investigating','closed') NOT NULL DEFAULT 'reported',
  reported_by   INT UNSIGNED NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ir_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 13. Employee assets
-- -----------------------------------------------------------------------------

CREATE TABLE assets (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_tag   VARCHAR(30) NOT NULL UNIQUE,
  name        VARCHAR(120) NOT NULL,
  category    ENUM('laptop','phone','vehicle','fuel_card','sim_card','uniform','office_keys','other') NOT NULL,
  serial_no   VARCHAR(80) NULL,
  purchase_date DATE NULL,
  value       DECIMAL(14,2) NULL,
  status      ENUM('available','assigned','maintenance','retired','lost') NOT NULL DEFAULT 'available',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE asset_assignments (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_id    INT UNSIGNED NOT NULL,
  employee_id INT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  due_back    DATE NULL,
  returned_at DATETIME NULL,
  condition_out VARCHAR(150) NULL,
  condition_in  VARCHAR(150) NULL,
  CONSTRAINT fk_aa_asset FOREIGN KEY (asset_id)    REFERENCES assets(id)    ON DELETE CASCADE,
  CONSTRAINT fk_aa_emp   FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_aa_open (asset_id, returned_at)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 14. Internal requests (generic approval workflow)
-- -----------------------------------------------------------------------------

CREATE TABLE internal_requests (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id  INT UNSIGNED NOT NULL,
  request_type ENUM('travel','training','salary_advance','equipment','it_support',
                    'vehicle_assignment','document_request','other') NOT NULL,
  subject      VARCHAR(200) NOT NULL,
  details      TEXT NULL,
  amount       DECIMAL(14,2) NULL,                        -- salary advance etc.
  status       ENUM('pending','approved','rejected','fulfilled','cancelled') NOT NULL DEFAULT 'pending',
  approved_by  INT UNSIGNED NULL,
  approved_at  DATETIME NULL,
  response     VARCHAR(255) NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_req_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  CONSTRAINT fk_req_app FOREIGN KEY (approved_by) REFERENCES users(id)     ON DELETE SET NULL,
  INDEX idx_req_status (status)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 15. Notifications, announcements, audit, settings
-- -----------------------------------------------------------------------------

CREATE TABLE notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  type       VARCHAR(50) NOT NULL,                        -- leave_approved, contract_expiry...
  title      VARCHAR(150) NOT NULL,
  body       VARCHAR(500) NULL,
  link       VARCHAR(255) NULL,
  channel    ENUM('system','email','sms') NOT NULL DEFAULT 'system',
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ntf_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_ntf_unread (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE announcements (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title      VARCHAR(200) NOT NULL,
  body       TEXT NOT NULL,
  audience   ENUM('all','department','drivers') NOT NULL DEFAULT 'all',
  department_id INT UNSIGNED NULL,
  publish_at DATE NOT NULL,
  expire_at  DATE NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ann_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NULL,
  action     VARCHAR(50) NOT NULL,                        -- create, update, delete, login...
  entity     VARCHAR(60) NOT NULL,                        -- table / module name
  entity_id  VARCHAR(30) NULL,
  old_values JSON NULL,
  new_values JSON NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,                           -- device + browser
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_log_entity (entity, entity_id),
  INDEX idx_log_user (user_id, created_at),
  INDEX idx_log_date (created_at)
) ENGINE=InnoDB;

CREATE TABLE settings (
  `key`      VARCHAR(80) PRIMARY KEY,
  `value`    TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
