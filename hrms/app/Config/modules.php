<?php
/**
 * Metadata-driven modules handled by ResourceController.
 * Each entry declares its table, permissions, list columns and form fields.
 *
 * Field spec: 'column' => [
 *   'label'    => shown in forms/lists,
 *   'type'     => text|email|number|decimal|date|datetime|time|textarea|select|relation|checkbox|file,
 *   'options'  => [value => label]           (select),
 *   'relation' => [table, displayExpr]       (relation → FK dropdown),
 *   'required' => bool,
 *   'upload_dir' => subdir                    (file),
 * ]
 * Modules with richer workflows (employees, leaves, attendance, payroll,
 * dashboard, reports) have dedicated controllers and are not listed here.
 */

$employeeRel   = ['employees', "CONCAT(first_name,' ',last_name,' (',employee_no,')')"];
$departmentRel = ['departments', 'name'];

return [

    'departments' => [
        'title' => 'Departments', 'table' => 'departments', 'icon' => 'diagram-3',
        'perm_view' => 'departments.manage', 'perm_manage' => 'departments.manage',
        'list' => ['name', 'code', 'manager_id', 'is_active'],
        'fields' => [
            'name'      => ['label' => 'Department Name', 'type' => 'text', 'required' => true],
            'code'      => ['label' => 'Code', 'type' => 'text', 'required' => true],
            'branch_id' => ['label' => 'Branch', 'type' => 'relation', 'relation' => ['branches', 'name']],
            'manager_id'=> ['label' => 'Department Manager', 'type' => 'relation', 'relation' => $employeeRel],
            'parent_id' => ['label' => 'Parent Department', 'type' => 'relation', 'relation' => $departmentRel],
            'description'=> ['label' => 'Description', 'type' => 'textarea'],
            'is_active' => ['label' => 'Active', 'type' => 'checkbox'],
        ],
    ],

    'positions' => [
        'title' => 'Positions', 'table' => 'positions', 'icon' => 'person-badge',
        'perm_view' => 'departments.manage', 'perm_manage' => 'departments.manage',
        'list' => ['title', 'department_id', 'salary_min', 'salary_max', 'is_active'],
        'fields' => [
            'title'        => ['label' => 'Position Title', 'type' => 'text', 'required' => true],
            'department_id'=> ['label' => 'Department', 'type' => 'relation', 'relation' => $departmentRel, 'required' => true],
            'reports_to'   => ['label' => 'Reports To (Position)', 'type' => 'relation', 'relation' => ['positions', 'title']],
            'salary_min'   => ['label' => 'Salary Min (RWF)', 'type' => 'decimal'],
            'salary_max'   => ['label' => 'Salary Max (RWF)', 'type' => 'decimal'],
            'description'  => ['label' => 'Job Description', 'type' => 'textarea'],
            'is_active'    => ['label' => 'Active', 'type' => 'checkbox'],
        ],
    ],

    'branches' => [
        'title' => 'Branches', 'table' => 'branches', 'icon' => 'geo-alt',
        'perm_view' => 'departments.manage', 'perm_manage' => 'departments.manage',
        'list' => ['name', 'location', 'phone', 'is_active'],
        'fields' => [
            'name'     => ['label' => 'Branch Name', 'type' => 'text', 'required' => true],
            'location' => ['label' => 'Location', 'type' => 'text'],
            'phone'    => ['label' => 'Phone', 'type' => 'text'],
            'is_active'=> ['label' => 'Active', 'type' => 'checkbox'],
        ],
    ],

    'vacancies' => [
        'title' => 'Job Vacancies', 'table' => 'job_vacancies', 'icon' => 'megaphone',
        'perm_view' => 'recruitment.view', 'perm_manage' => 'recruitment.manage',
        'list' => ['title', 'department_id', 'employment_type', 'deadline', 'status'],
        'fields' => [
            'title'         => ['label' => 'Job Title', 'type' => 'text', 'required' => true],
            'department_id' => ['label' => 'Department', 'type' => 'relation', 'relation' => $departmentRel],
            'position_id'   => ['label' => 'Position', 'type' => 'relation', 'relation' => ['positions', 'title']],
            'employment_type'=> ['label' => 'Employment Type', 'type' => 'select', 'options' => [
                'permanent'=>'Permanent','contract'=>'Contract','casual'=>'Casual','intern'=>'Intern','consultant'=>'Consultant']],
            'openings'      => ['label' => 'Openings', 'type' => 'number'],
            'deadline'      => ['label' => 'Application Deadline', 'type' => 'date'],
            'description'   => ['label' => 'Job Description', 'type' => 'textarea'],
            'requirements'  => ['label' => 'Requirements', 'type' => 'textarea'],
            'status'        => ['label' => 'Status', 'type' => 'select', 'options' => [
                'draft'=>'Draft','published'=>'Published','closed'=>'Closed','filled'=>'Filled']],
        ],
    ],

    'applicants' => [
        'title' => 'Applicants', 'table' => 'applicants', 'icon' => 'people',
        'perm_view' => 'recruitment.view', 'perm_manage' => 'recruitment.manage',
        'list' => ['full_name', 'vacancy_id', 'email', 'phone', 'stage'],
        'row_actions' => [['convert', 'person-plus', 'Convert to employee']],
        'fields' => [
            'vacancy_id'  => ['label' => 'Vacancy', 'type' => 'relation', 'relation' => ['job_vacancies', 'title'], 'required' => true],
            'full_name'   => ['label' => 'Full Name', 'type' => 'text', 'required' => true],
            'gender'      => ['label' => 'Gender', 'type' => 'select', 'options' => ['male' => 'Male', 'female' => 'Female']],
            'email'       => ['label' => 'Email', 'type' => 'email', 'required' => true],
            'phone'       => ['label' => 'Phone', 'type' => 'text'],
            'cv_path'     => ['label' => 'CV (PDF)', 'type' => 'file', 'upload_dir' => 'recruitment'],
            'cover_letter'=> ['label' => 'Cover Letter', 'type' => 'textarea'],
            'stage'       => ['label' => 'Stage', 'type' => 'select', 'options' => [
                'applied'=>'Applied','shortlisted'=>'Shortlisted','interview'=>'Interview','offer'=>'Offer',
                'accepted'=>'Accepted','hired'=>'Hired','rejected'=>'Rejected','talent_pool'=>'Talent Pool']],
        ],
    ],

    'interviews' => [
        'title' => 'Interviews', 'table' => 'interviews', 'icon' => 'chat-square-text',
        'perm_view' => 'recruitment.view', 'perm_manage' => 'recruitment.manage',
        'list' => ['applicant_id', 'scheduled_at', 'location', 'score', 'outcome'],
        'fields' => [
            'applicant_id'=> ['label' => 'Applicant', 'type' => 'relation', 'relation' => ['applicants', 'full_name'], 'required' => true],
            'scheduled_at'=> ['label' => 'Scheduled At', 'type' => 'datetime', 'required' => true],
            'location'    => ['label' => 'Location', 'type' => 'text'],
            'panel'       => ['label' => 'Interview Panel', 'type' => 'text'],
            'score'       => ['label' => 'Score (0–100)', 'type' => 'decimal'],
            'outcome'     => ['label' => 'Outcome', 'type' => 'select', 'options' => [
                'pending'=>'Pending','passed'=>'Passed','failed'=>'Failed','no_show'=>'No Show']],
            'notes'       => ['label' => 'Notes', 'type' => 'textarea'],
        ],
    ],

    'offers' => [
        'title' => 'Job Offers', 'table' => 'job_offers', 'icon' => 'envelope-check',
        'perm_view' => 'recruitment.view', 'perm_manage' => 'recruitment.manage',
        'list' => ['applicant_id', 'salary', 'start_date', 'status', 'sent_at'],
        'fields' => [
            'applicant_id'=> ['label' => 'Applicant', 'type' => 'relation', 'relation' => ['applicants', 'full_name'], 'required' => true],
            'salary'      => ['label' => 'Offered Salary (RWF)', 'type' => 'decimal', 'required' => true],
            'start_date'  => ['label' => 'Proposed Start Date', 'type' => 'date'],
            'letter_path' => ['label' => 'Offer Letter', 'type' => 'file', 'upload_dir' => 'recruitment'],
            'status'      => ['label' => 'Status', 'type' => 'select', 'options' => [
                'sent'=>'Sent','accepted'=>'Accepted','declined'=>'Declined','withdrawn'=>'Withdrawn']],
        ],
    ],

    'drivers' => [
        'title' => 'Drivers', 'table' => 'drivers', 'icon' => 'truck-front',
        'perm_view' => 'drivers.view', 'perm_manage' => 'drivers.manage',
        'list' => ['employee_id', 'license_number', 'license_categories', 'license_expiry', 'rating', 'status'],
        'fields' => [
            'employee_id'       => ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'license_number'    => ['label' => 'Driving License No.', 'type' => 'text', 'required' => true],
            'license_categories'=> ['label' => 'License Categories (e.g. B,C,D)', 'type' => 'text', 'required' => true],
            'license_expiry'    => ['label' => 'License Expiry', 'type' => 'date', 'required' => true],
            'permit_expiry'     => ['label' => 'Permit Expiry', 'type' => 'date'],
            'medical_exam_date' => ['label' => 'Last Medical Exam', 'type' => 'date'],
            'medical_exam_expiry'=> ['label' => 'Medical Exam Expiry', 'type' => 'date'],
            'rating'            => ['label' => 'Rating (0–5)', 'type' => 'decimal'],
            'status'            => ['label' => 'Status', 'type' => 'select', 'options' => [
                'available'=>'Available','on_trip'=>'On Trip','suspended'=>'Suspended','inactive'=>'Inactive']],
        ],
    ],

    'vehicles' => [
        'title' => 'Vehicles', 'table' => 'vehicles', 'icon' => 'car-front',
        'perm_view' => 'drivers.view', 'perm_manage' => 'drivers.manage',
        'list' => ['plate_number', 'make_model', 'vehicle_type', 'is_active'],
        'fields' => [
            'plate_number'=> ['label' => 'Plate Number', 'type' => 'text', 'required' => true],
            'make_model'  => ['label' => 'Make & Model', 'type' => 'text'],
            'vehicle_type'=> ['label' => 'Type', 'type' => 'select', 'options' => [
                'sedan'=>'Sedan','suv'=>'SUV','van'=>'Van','coaster'=>'Coaster','bus'=>'Bus','truck'=>'Truck','safari_4x4'=>'Safari 4x4']],
            'is_active'   => ['label' => 'Active', 'type' => 'checkbox'],
        ],
    ],

    'trips' => [
        'title' => 'Driver Trips', 'table' => 'driver_trips', 'icon' => 'signpost',
        'perm_view' => 'drivers.view', 'perm_manage' => 'drivers.manage',
        'list' => ['driver_id', 'trip_date', 'trip_type', 'route', 'distance_km', 'client_rating'],
        'fields' => [
            'driver_id'    => ['label' => 'Driver', 'type' => 'relation', 'relation' => ['drivers', "(SELECT CONCAT(first_name,' ',last_name) FROM employees WHERE employees.id = drivers.employee_id)"], 'required' => true],
            'vehicle_id'   => ['label' => 'Vehicle', 'type' => 'relation', 'relation' => ['vehicles', 'plate_number']],
            'trip_date'    => ['label' => 'Trip Date', 'type' => 'date', 'required' => true],
            'trip_type'    => ['label' => 'Trip Type', 'type' => 'select', 'options' => [
                'airport_transfer'=>'Airport Transfer','corporate'=>'Corporate','car_rental'=>'Car Rental','safari'=>'Safari','tour'=>'Tour','other'=>'Other']],
            'route'        => ['label' => 'Route', 'type' => 'text'],
            'distance_km'  => ['label' => 'Distance (km)', 'type' => 'decimal'],
            'fuel_liters'  => ['label' => 'Fuel (litres)', 'type' => 'decimal'],
            'client_rating'=> ['label' => 'Client Rating (1–5)', 'type' => 'number'],
            'notes'        => ['label' => 'Notes', 'type' => 'textarea'],
        ],
    ],

    'driver-incidents' => [
        'title' => 'Driver Incidents', 'table' => 'driver_incidents', 'icon' => 'exclamation-triangle',
        'perm_view' => 'drivers.view', 'perm_manage' => 'drivers.manage',
        'list' => ['driver_id', 'incident_type', 'incident_date', 'cost', 'resolved'],
        'fields' => [
            'driver_id'    => ['label' => 'Driver', 'type' => 'relation', 'relation' => ['drivers', "(SELECT CONCAT(first_name,' ',last_name) FROM employees WHERE employees.id = drivers.employee_id)"], 'required' => true],
            'incident_type'=> ['label' => 'Type', 'type' => 'select', 'options' => [
                'accident'=>'Accident','traffic_fine'=>'Traffic Fine','warning'=>'Warning','suspension'=>'Suspension','complaint'=>'Complaint']],
            'incident_date'=> ['label' => 'Date', 'type' => 'date', 'required' => true],
            'description'  => ['label' => 'Description', 'type' => 'textarea'],
            'cost'         => ['label' => 'Cost / Fine (RWF)', 'type' => 'decimal'],
            'resolved'     => ['label' => 'Resolved', 'type' => 'checkbox'],
        ],
    ],

    'performance' => [
        'title' => 'Performance Reviews', 'table' => 'performance_reviews', 'icon' => 'graph-up-arrow',
        'perm_view' => 'performance.view', 'perm_manage' => 'performance.manage',
        'list' => ['employee_id', 'review_type', 'period', 'kpi_score', 'overall_rating', 'status'],
        'fields' => [
            'employee_id'   => ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'review_type'   => ['label' => 'Review Type', 'type' => 'select', 'options' => [
                'quarterly'=>'Quarterly','annual'=>'Annual','probation'=>'Probation','360'=>'360°']],
            'period'        => ['label' => 'Period (e.g. 2026-Q2)', 'type' => 'text', 'required' => true],
            'kpi_score'     => ['label' => 'KPI Score (0–100)', 'type' => 'decimal'],
            'overall_rating'=> ['label' => 'Overall Rating', 'type' => 'select', 'options' => [
                'outstanding'=>'Outstanding','exceeds'=>'Exceeds Expectations','meets'=>'Meets Expectations',
                'needs_improvement'=>'Needs Improvement','unsatisfactory'=>'Unsatisfactory']],
            'strengths'     => ['label' => 'Strengths', 'type' => 'textarea'],
            'improvements'  => ['label' => 'Areas for Improvement', 'type' => 'textarea'],
            'recommendation'=> ['label' => 'Recommendation', 'type' => 'select', 'options' => [
                'none'=>'None','promotion'=>'Promotion','salary_increase'=>'Salary Increase',
                'warning'=>'Warning Letter','improvement_plan'=>'Improvement Plan','termination'=>'Termination']],
            'status'        => ['label' => 'Status', 'type' => 'select', 'options' => [
                'draft'=>'Draft','submitted'=>'Submitted','acknowledged'=>'Acknowledged']],
        ],
    ],

    'goals' => [
        'title' => 'Goals & KPIs', 'table' => 'performance_goals', 'icon' => 'bullseye',
        'perm_view' => 'performance.view', 'perm_manage' => 'performance.manage',
        'list' => ['employee_id', 'title', 'target', 'due_date', 'progress', 'status'],
        'fields' => [
            'employee_id'=> ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'title'      => ['label' => 'Goal', 'type' => 'text', 'required' => true],
            'kpi_metric' => ['label' => 'KPI Metric', 'type' => 'text'],
            'target'     => ['label' => 'Target', 'type' => 'text'],
            'due_date'   => ['label' => 'Due Date', 'type' => 'date'],
            'progress'   => ['label' => 'Progress %', 'type' => 'number'],
            'status'     => ['label' => 'Status', 'type' => 'select', 'options' => [
                'open'=>'Open','on_track'=>'On Track','at_risk'=>'At Risk','achieved'=>'Achieved','missed'=>'Missed']],
        ],
    ],

    'courses' => [
        'title' => 'Training Courses', 'table' => 'training_courses', 'icon' => 'mortarboard',
        'perm_view' => 'training.view', 'perm_manage' => 'training.manage',
        'list' => ['name', 'category', 'provider', 'validity_months', 'is_active'],
        'fields' => [
            'name'     => ['label' => 'Course Name', 'type' => 'text', 'required' => true],
            'category' => ['label' => 'Category', 'type' => 'select', 'options' => [
                'driver_safety'=>'Driver Safety','customer_service'=>'Customer Service','tour_guide'=>'Tour Guide',
                'first_aid'=>'First Aid','fire_safety'=>'Fire Safety','it_security'=>'IT Security Awareness',
                'compliance'=>'Compliance','leadership'=>'Leadership','other'=>'Other']],
            'provider' => ['label' => 'Provider', 'type' => 'text'],
            'validity_months' => ['label' => 'Certificate Validity (months)', 'type' => 'number'],
            'description' => ['label' => 'Description', 'type' => 'textarea'],
            'is_active'=> ['label' => 'Active', 'type' => 'checkbox'],
        ],
    ],

    'training-sessions' => [
        'title' => 'Training Sessions', 'table' => 'training_sessions', 'icon' => 'calendar-event',
        'perm_view' => 'training.view', 'perm_manage' => 'training.manage',
        'list' => ['course_id', 'start_date', 'end_date', 'location', 'trainer', 'status'],
        'fields' => [
            'course_id'  => ['label' => 'Course', 'type' => 'relation', 'relation' => ['training_courses', 'name'], 'required' => true],
            'start_date' => ['label' => 'Start Date', 'type' => 'date', 'required' => true],
            'end_date'   => ['label' => 'End Date', 'type' => 'date'],
            'location'   => ['label' => 'Location', 'type' => 'text'],
            'trainer'    => ['label' => 'Trainer', 'type' => 'text'],
            'capacity'   => ['label' => 'Capacity', 'type' => 'number'],
            'status'     => ['label' => 'Status', 'type' => 'select', 'options' => [
                'planned'=>'Planned','ongoing'=>'Ongoing','completed'=>'Completed','cancelled'=>'Cancelled']],
        ],
    ],

    'training-participants' => [
        'title' => 'Training Participants', 'table' => 'training_participants', 'icon' => 'person-check',
        'perm_view' => 'training.view', 'perm_manage' => 'training.manage',
        'list' => ['session_id', 'employee_id', 'attended', 'score', 'certificate_expiry'],
        'fields' => [
            'session_id' => ['label' => 'Session', 'type' => 'relation', 'relation' => ['training_sessions', "CONCAT('Session #', id, ' — ', start_date)"], 'required' => true],
            'employee_id'=> ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'attended'   => ['label' => 'Attended', 'type' => 'checkbox'],
            'score'      => ['label' => 'Score', 'type' => 'decimal'],
            'certificate_path'   => ['label' => 'Certificate', 'type' => 'file', 'upload_dir' => 'training'],
            'certificate_expiry' => ['label' => 'Certificate Expiry', 'type' => 'date'],
        ],
    ],

    'documents' => [
        'title' => 'Document Vault', 'table' => 'employee_documents', 'icon' => 'folder2-open',
        'perm_view' => 'documents.view', 'perm_manage' => 'documents.manage',
        'list' => ['employee_id', 'doc_type', 'title', 'version', 'expiry_date', 'created_at'],
        'fields' => [
            'employee_id'=> ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'doc_type'   => ['label' => 'Document Type', 'type' => 'select', 'options' => [
                'cv'=>'CV','contract'=>'Contract','academic_certificate'=>'Academic Certificate',
                'police_clearance'=>'Police Clearance','passport'=>'Passport','national_id'=>'National ID',
                'driving_license'=>'Driving License','medical_certificate'=>'Medical Certificate',
                'performance_review'=>'Performance Review','insurance'=>'Insurance','permit'=>'Permit','other'=>'Other']],
            'title'      => ['label' => 'Title', 'type' => 'text', 'required' => true],
            'file_path'  => ['label' => 'File', 'type' => 'file', 'upload_dir' => 'documents', 'required' => true],
            'expiry_date'=> ['label' => 'Expiry Date (for alerts)', 'type' => 'date'],
        ],
    ],

    'contracts' => [
        'title' => 'Contracts', 'table' => 'contracts', 'icon' => 'file-earmark-text',
        'perm_view' => 'employees.view', 'perm_manage' => 'employees.manage',
        'list' => ['employee_id', 'contract_type', 'start_date', 'end_date', 'salary', 'status'],
        'fields' => [
            'employee_id'  => ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'contract_type'=> ['label' => 'Contract Type', 'type' => 'select', 'options' => [
                'permanent'=>'Permanent','fixed_term'=>'Fixed Term','casual'=>'Casual','internship'=>'Internship','consultancy'=>'Consultancy']],
            'start_date'   => ['label' => 'Start Date', 'type' => 'date', 'required' => true],
            'end_date'     => ['label' => 'End Date (empty = open-ended)', 'type' => 'date'],
            'salary'       => ['label' => 'Salary (RWF)', 'type' => 'decimal'],
            'file_path'    => ['label' => 'Contract File', 'type' => 'file', 'upload_dir' => 'contracts'],
            'status'       => ['label' => 'Status', 'type' => 'select', 'options' => [
                'active'=>'Active','expired'=>'Expired','terminated'=>'Terminated','renewed'=>'Renewed']],
            'notes'        => ['label' => 'Notes', 'type' => 'textarea'],
        ],
    ],

    'disciplinary' => [
        'title' => 'Disciplinary Cases', 'table' => 'disciplinary_cases', 'icon' => 'shield-exclamation',
        'perm_view' => 'disciplinary.view', 'perm_manage' => 'disciplinary.manage',
        'list' => ['employee_id', 'case_date', 'category', 'action', 'status'],
        'fields' => [
            'employee_id' => ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'case_date'   => ['label' => 'Case Date', 'type' => 'date', 'required' => true],
            'category'    => ['label' => 'Category', 'type' => 'text', 'required' => true],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true],
            'action'      => ['label' => 'Action', 'type' => 'select', 'options' => [
                'verbal_warning'=>'Verbal Warning','written_warning'=>'Written Warning',
                'final_warning'=>'Final Warning','suspension'=>'Suspension','dismissal'=>'Dismissal']],
            'action_date' => ['label' => 'Action Date', 'type' => 'date'],
            'evidence_path'=> ['label' => 'Evidence', 'type' => 'file', 'upload_dir' => 'disciplinary'],
            'status'      => ['label' => 'Status', 'type' => 'select', 'options' => [
                'open'=>'Open','under_review'=>'Under Review','appealed'=>'Appealed','closed'=>'Closed']],
            'appeal_notes'=> ['label' => 'Appeal Notes', 'type' => 'textarea'],
            'appeal_outcome'=> ['label' => 'Appeal Outcome', 'type' => 'select', 'options' => [
                'upheld'=>'Upheld','overturned'=>'Overturned','reduced'=>'Reduced']],
        ],
    ],

    'checkups' => [
        'title' => 'Medical Checkups', 'table' => 'medical_checkups', 'icon' => 'heart-pulse',
        'perm_view' => 'health.view', 'perm_manage' => 'health.manage',
        'list' => ['employee_id', 'checkup_date', 'checkup_type', 'result', 'next_due'],
        'fields' => [
            'employee_id' => ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'checkup_date'=> ['label' => 'Checkup Date', 'type' => 'date', 'required' => true],
            'checkup_type'=> ['label' => 'Type', 'type' => 'select', 'options' => [
                'annual'=>'Annual','driver_medical'=>'Driver Medical','pre_employment'=>'Pre-Employment','follow_up'=>'Follow-Up']],
            'result'      => ['label' => 'Result', 'type' => 'select', 'options' => [
                'fit'=>'Fit','fit_with_conditions'=>'Fit with Conditions','unfit'=>'Unfit','pending'=>'Pending']],
            'next_due'    => ['label' => 'Next Due', 'type' => 'date'],
            'notes'       => ['label' => 'Notes', 'type' => 'textarea'],
        ],
    ],

    'incidents' => [
        'title' => 'Incident Reports', 'table' => 'incident_reports', 'icon' => 'cone-striped',
        'perm_view' => 'health.view', 'perm_manage' => 'health.manage',
        'list' => ['employee_id', 'incident_date', 'incident_type', 'severity', 'status'],
        'fields' => [
            'employee_id'  => ['label' => 'Employee Involved', 'type' => 'relation', 'relation' => $employeeRel],
            'incident_date'=> ['label' => 'Date & Time', 'type' => 'datetime', 'required' => true],
            'incident_type'=> ['label' => 'Type', 'type' => 'select', 'options' => [
                'workplace_injury'=>'Workplace Injury','vehicle_accident'=>'Vehicle Accident','near_miss'=>'Near Miss',
                'fatigue'=>'Driver Fatigue','occupational_illness'=>'Occupational Illness','other'=>'Other']],
            'location'     => ['label' => 'Location', 'type' => 'text'],
            'description'  => ['label' => 'Description', 'type' => 'textarea', 'required' => true],
            'severity'     => ['label' => 'Severity', 'type' => 'select', 'options' => [
                'minor'=>'Minor','moderate'=>'Moderate','severe'=>'Severe','fatal'=>'Fatal']],
            'insurance_claim'=> ['label' => 'Insurance Claim Filed', 'type' => 'checkbox'],
            'claim_status' => ['label' => 'Claim Status', 'type' => 'select', 'options' => [
                'none'=>'None','submitted'=>'Submitted','approved'=>'Approved','rejected'=>'Rejected','paid'=>'Paid']],
            'status'       => ['label' => 'Status', 'type' => 'select', 'options' => [
                'reported'=>'Reported','investigating'=>'Investigating','closed'=>'Closed']],
        ],
    ],

    'assets' => [
        'title' => 'Assets', 'table' => 'assets', 'icon' => 'laptop',
        'perm_view' => 'assets.view', 'perm_manage' => 'assets.manage',
        'list' => ['asset_tag', 'name', 'category', 'status', 'value'],
        'fields' => [
            'asset_tag'    => ['label' => 'Asset Tag', 'type' => 'text', 'required' => true],
            'name'         => ['label' => 'Asset Name', 'type' => 'text', 'required' => true],
            'category'     => ['label' => 'Category', 'type' => 'select', 'options' => [
                'laptop'=>'Laptop','phone'=>'Phone','vehicle'=>'Vehicle','fuel_card'=>'Fuel Card',
                'sim_card'=>'SIM Card','uniform'=>'Uniform','office_keys'=>'Office Keys','other'=>'Other']],
            'serial_no'    => ['label' => 'Serial No.', 'type' => 'text'],
            'purchase_date'=> ['label' => 'Purchase Date', 'type' => 'date'],
            'value'        => ['label' => 'Value (RWF)', 'type' => 'decimal'],
            'status'       => ['label' => 'Status', 'type' => 'select', 'options' => [
                'available'=>'Available','assigned'=>'Assigned','maintenance'=>'Maintenance','retired'=>'Retired','lost'=>'Lost']],
        ],
    ],

    'asset-assignments' => [
        'title' => 'Asset Assignments', 'table' => 'asset_assignments', 'icon' => 'box-arrow-right',
        'perm_view' => 'assets.view', 'perm_manage' => 'assets.manage',
        'list' => ['asset_id', 'employee_id', 'assigned_at', 'due_back', 'returned_at'],
        'fields' => [
            'asset_id'    => ['label' => 'Asset', 'type' => 'relation', 'relation' => ['assets', "CONCAT(asset_tag,' — ',name)"], 'required' => true],
            'employee_id' => ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'due_back'    => ['label' => 'Due Back', 'type' => 'date'],
            'returned_at' => ['label' => 'Returned At', 'type' => 'datetime'],
            'condition_out'=> ['label' => 'Condition (out)', 'type' => 'text'],
            'condition_in' => ['label' => 'Condition (returned)', 'type' => 'text'],
        ],
    ],

    'requests' => [
        'title' => 'Internal Requests', 'table' => 'internal_requests', 'icon' => 'inbox',
        'perm_view' => 'requests.view', 'perm_manage' => 'requests.approve',
        'perm_create' => 'requests.create',
        'list' => ['employee_id', 'request_type', 'subject', 'amount', 'status', 'created_at'],
        'fields' => [
            'employee_id' => ['label' => 'Employee', 'type' => 'relation', 'relation' => $employeeRel, 'required' => true],
            'request_type'=> ['label' => 'Request Type', 'type' => 'select', 'options' => [
                'travel'=>'Travel','training'=>'Training','salary_advance'=>'Salary Advance','equipment'=>'Equipment',
                'it_support'=>'IT Support','vehicle_assignment'=>'Vehicle Assignment','document_request'=>'Document Request','other'=>'Other']],
            'subject'     => ['label' => 'Subject', 'type' => 'text', 'required' => true],
            'details'     => ['label' => 'Details', 'type' => 'textarea'],
            'amount'      => ['label' => 'Amount (RWF, if applicable)', 'type' => 'decimal'],
            'status'      => ['label' => 'Status', 'type' => 'select', 'options' => [
                'pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','fulfilled'=>'Fulfilled','cancelled'=>'Cancelled']],
            'response'    => ['label' => 'Response', 'type' => 'text'],
        ],
    ],

    'holidays' => [
        'title' => 'Public Holidays', 'table' => 'holidays', 'icon' => 'calendar-heart',
        'perm_view' => 'leaves.view', 'perm_manage' => 'settings.manage',
        'list' => ['name', 'date', 'is_recurring'],
        'fields' => [
            'name'        => ['label' => 'Holiday Name', 'type' => 'text', 'required' => true],
            'date'        => ['label' => 'Date', 'type' => 'date', 'required' => true],
            'is_recurring'=> ['label' => 'Recurs Annually', 'type' => 'checkbox'],
        ],
    ],

    'shifts' => [
        'title' => 'Shifts', 'table' => 'shifts', 'icon' => 'clock-history',
        'perm_view' => 'attendance.view', 'perm_manage' => 'attendance.manage',
        'list' => ['name', 'start_time', 'end_time', 'grace_minutes', 'works_weekends'],
        'fields' => [
            'name'          => ['label' => 'Shift Name', 'type' => 'text', 'required' => true],
            'start_time'    => ['label' => 'Start Time', 'type' => 'time', 'required' => true],
            'end_time'      => ['label' => 'End Time', 'type' => 'time', 'required' => true],
            'grace_minutes' => ['label' => 'Late Grace (minutes)', 'type' => 'number'],
            'works_weekends'=> ['label' => 'Includes Weekends', 'type' => 'checkbox'],
            'is_active'     => ['label' => 'Active', 'type' => 'checkbox'],
        ],
    ],

    'announcements' => [
        'title' => 'Announcements', 'table' => 'announcements', 'icon' => 'broadcast',
        'perm_view' => 'dashboard.view', 'perm_manage' => 'employees.manage',
        'list' => ['title', 'audience', 'publish_at', 'expire_at'],
        'fields' => [
            'title'     => ['label' => 'Title', 'type' => 'text', 'required' => true],
            'body'      => ['label' => 'Announcement', 'type' => 'textarea', 'required' => true],
            'audience'  => ['label' => 'Audience', 'type' => 'select', 'options' => [
                'all'=>'All Staff','department'=>'Specific Department','drivers'=>'Drivers Only']],
            'department_id' => ['label' => 'Department (if applicable)', 'type' => 'relation', 'relation' => $departmentRel],
            'publish_at'=> ['label' => 'Publish Date', 'type' => 'date', 'required' => true],
            'expire_at' => ['label' => 'Expiry Date', 'type' => 'date'],
        ],
    ],

    'users' => [
        'title' => 'User Accounts', 'table' => 'users', 'icon' => 'person-lock',
        'perm_view' => 'users.manage', 'perm_manage' => 'users.manage',
        'list' => ['username', 'email', 'role_id', 'two_factor_enabled', 'last_login_at', 'is_active'],
        'fields' => [
            'username'    => ['label' => 'Username', 'type' => 'text', 'required' => true],
            'email'       => ['label' => 'Email', 'type' => 'email', 'required' => true],
            'role_id'     => ['label' => 'Role', 'type' => 'relation', 'relation' => ['roles', 'name'], 'required' => true],
            'employee_id' => ['label' => 'Linked Employee', 'type' => 'relation', 'relation' => $employeeRel],
            'password'    => ['label' => 'Password (leave blank to keep)', 'type' => 'password'],
            'two_factor_enabled' => ['label' => 'Two-Factor Authentication', 'type' => 'checkbox'],
            'is_active'   => ['label' => 'Active', 'type' => 'checkbox'],
        ],
    ],
];
