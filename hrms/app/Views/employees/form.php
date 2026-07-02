<?php
/** @var ?array $employee  @var array $departments $positions $branches $managers */
$edit = $employee !== null;
$v = fn(string $k) => e($employee[$k] ?? '');
$sel = fn(string $k, string $opt) => ($employee[$k] ?? '') === $opt ? 'selected' : '';
$action = $edit ? url('employees/' . $employee['id']) : url('employees');
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0"><?= $edit ? 'Edit Employee — ' . $v('employee_no') : 'New Employee' ?></h1>
  <a href="<?= url('employees') ?>" class="btn btn-outline-secondary ms-auto"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card mb-3">
        <div class="card-header"><i class="bi bi-person me-2"></i>Personal Information</div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label">First Name *</label>
            <input name="first_name" class="form-control" required value="<?= $v('first_name') ?>"></div>
          <div class="col-md-6"><label class="form-label">Last Name *</label>
            <input name="last_name" class="form-control" required value="<?= $v('last_name') ?>"></div>
          <div class="col-md-6"><label class="form-label">Gender *</label>
            <select name="gender" class="form-select" required>
              <option value="male" <?= $sel('gender', 'male') ?>>Male</option>
              <option value="female" <?= $sel('gender', 'female') ?>>Female</option>
            </select></div>
          <div class="col-md-6"><label class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control" value="<?= $v('date_of_birth') ?>"></div>
          <div class="col-md-6"><label class="form-label">National ID</label>
            <input name="national_id" class="form-control" value="<?= $v('national_id') ?>"></div>
          <div class="col-md-6"><label class="form-label">Passport No.</label>
            <input name="passport_no" class="form-control" value="<?= $v('passport_no') ?>"></div>
          <div class="col-md-4"><label class="form-label">Marital Status</label>
            <select name="marital_status" class="form-select"><option value="">—</option>
              <?php foreach (['single', 'married', 'divorced', 'widowed'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $sel('marital_status', $opt) ?>><?= label($opt) ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-4"><label class="form-label">Nationality</label>
            <input name="nationality" class="form-control" value="<?= $v('nationality') ?: 'Rwandan' ?>"></div>
          <div class="col-md-4"><label class="form-label">Blood Group</label>
            <select name="blood_group" class="form-select"><option value="">—</option>
              <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $sel('blood_group', $opt) ?>><?= $opt ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-12"><label class="form-label">Passport Photo (jpg/png)</label>
            <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png"></div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><i class="bi bi-telephone me-2"></i>Contact</div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label">Phone</label>
            <input name="phone" class="form-control" value="<?= $v('phone') ?>"></div>
          <div class="col-md-6"><label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= $v('email') ?>"></div>
          <div class="col-md-12"><label class="form-label">Physical Address</label>
            <input name="address" class="form-control" value="<?= $v('address') ?>"></div>
          <div class="col-md-4"><label class="form-label">Emergency Contact</label>
            <input name="emergency_name" class="form-control" value="<?= $v('emergency_name') ?>"></div>
          <div class="col-md-4"><label class="form-label">Emergency Phone</label>
            <input name="emergency_phone" class="form-control" value="<?= $v('emergency_phone') ?>"></div>
          <div class="col-md-4"><label class="form-label">Relationship</label>
            <input name="emergency_relation" class="form-control" value="<?= $v('emergency_relation') ?>"></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card mb-3">
        <div class="card-header"><i class="bi bi-briefcase me-2"></i>Employment</div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label">Department</label>
            <select name="department_id" class="form-select"><option value="">—</option>
              <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>" <?= ($employee['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Position</label>
            <select name="position_id" class="form-select"><option value="">—</option>
              <?php foreach ($positions as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($employee['position_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['title']) ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Branch</label>
            <select name="branch_id" class="form-select"><option value="">—</option>
              <?php foreach ($branches as $b): ?>
                <option value="<?= $b['id'] ?>" <?= ($employee['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Manager / Supervisor</label>
            <select name="manager_id" class="form-select"><option value="">—</option>
              <?php foreach ($managers as $m): ?>
                <?php if ($edit && $m['id'] == $employee['id']) continue; ?>
                <option value="<?= $m['id'] ?>" <?= ($employee['manager_id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Employment Type</label>
            <select name="employment_type" class="form-select">
              <?php foreach (['permanent', 'contract', 'casual', 'intern', 'consultant'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $sel('employment_type', $opt) ?>><?= label($opt) ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Status</label>
            <select name="status" class="form-select">
              <?php foreach (['active', 'suspended', 'terminated', 'retired', 'resigned'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $sel('status', $opt) ?>><?= label($opt) ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-4"><label class="form-label">Date Hired *</label>
            <input type="date" name="date_hired" class="form-control" required value="<?= $v('date_hired') ?>"></div>
          <div class="col-md-4"><label class="form-label">Probation Ends</label>
            <input type="date" name="probation_end" class="form-control" value="<?= $v('probation_end') ?>"></div>
          <div class="col-md-4"><label class="form-label">Confirmation Date</label>
            <input type="date" name="confirmation_date" class="form-control" value="<?= $v('confirmation_date') ?>"></div>
          <div class="col-md-12 form-check ms-2">
            <input class="form-check-input" type="checkbox" name="is_driver" id="isDriver"
                   <?= !empty($employee['is_driver']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="isDriver">This employee is a driver</label>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><i class="bi bi-bank me-2"></i>Salary, Statutory &amp; Insurance</div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label">Basic Salary (RWF)</label>
            <input type="number" step="0.01" min="0" name="basic_salary" class="form-control" value="<?= $v('basic_salary') ?>"></div>
          <div class="col-md-6"><label class="form-label">TIN Number</label>
            <input name="tin_number" class="form-control" value="<?= $v('tin_number') ?>"></div>
          <div class="col-md-6"><label class="form-label">Bank</label>
            <input name="bank_name" class="form-control" value="<?= $v('bank_name') ?>"></div>
          <div class="col-md-6"><label class="form-label">Bank Account</label>
            <input name="bank_account" class="form-control" value="<?= $v('bank_account') ?>"></div>
          <div class="col-md-6"><label class="form-label">RSSB Number</label>
            <input name="rssb_number" class="form-control" value="<?= $v('rssb_number') ?>"></div>
          <div class="col-md-6"><label class="form-label">Medical Insurer</label>
            <input name="medical_insurer" class="form-control" value="<?= $v('medical_insurer') ?>"></div>
          <div class="col-md-6"><label class="form-label">Policy No.</label>
            <input name="medical_policy_no" class="form-control" value="<?= $v('medical_policy_no') ?>"></div>
          <div class="col-md-6"><label class="form-label">Insurance Expiry</label>
            <input type="date" name="medical_expiry" class="form-control" value="<?= $v('medical_expiry') ?>"></div>
        </div>
      </div>

      <div class="text-end">
        <button class="btn btn-ocean px-4"><i class="bi bi-check-lg me-1"></i><?= $edit ? 'Save Changes' : 'Create Employee' ?></button>
      </div>
    </div>
  </div>
</form>
