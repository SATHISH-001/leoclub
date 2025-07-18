<?php
// Start session and include files at the VERY TOP
session_start();
require_once './includes/functions.php';
require_once './includes/db.php';

// Initialize variables
$error = '';
$success = false;
$pageTitle = "Participant Registration";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getPDO();
        $pdo->beginTransaction();
        
        // Validate registration type
        $is_team = isset($_POST['registration_type']) && $_POST['registration_type'] === 'team';
        $team_name = $is_team ? (!empty($_POST['team_name']) ? sanitizeInput($_POST['team_name']) : null) : null;
        
        // Validate required fields
        $required_fields = ['name', 'email', 'phone'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }
        
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address");
        }
        
        // Process team registration if selected
        $team_members = [];
        if ($is_team) {
            if (empty($_POST['team_name'])) {
                throw new Exception("Team name is required for team registration");
            }
            
            // Collect team members
            for ($i = 1; $i <= 4; $i++) {
                if (!empty($_POST['member_name_'.$i])) {
                    $team_members[] = [
                        'name' => sanitizeInput($_POST['member_name_'.$i]),
                        'email' => !empty($_POST['member_email_'.$i]) ? filter_var($_POST['member_email_'.$i], FILTER_SANITIZE_EMAIL) : null,
                        'phone' => !empty($_POST['member_phone_'.$i]) ? sanitizeInput($_POST['member_phone_'.$i]) : null
                    ];
                }
            }
            
            if (count($team_members) < 1) {
                throw new Exception("Please add at least one team member");
            }
        }
        
        // Insert team if team registration
        $team_id = null;
        if ($is_team && !empty($team_name)) {
            $stmt = $pdo->prepare("INSERT INTO teams (name, created_at) VALUES (?, NOW())");
            $stmt->execute([$team_name]);
            $team_id = $pdo->lastInsertId();
        }
        
        // Insert main participant
        $stmt = $pdo->prepare("INSERT INTO participants 
                             (name, email, phone, college, department, year, event_id, team_id, registration_date) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            sanitizeInput($_POST['name']),
            filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            sanitizeInput($_POST['phone']),
            !empty($_POST['college']) ? sanitizeInput($_POST['college']) : null,
            !empty($_POST['department']) ? sanitizeInput($_POST['department']) : null,
            !empty($_POST['year']) ? sanitizeInput($_POST['year']) : null,
            !empty($_POST['event_id']) ? (int)$_POST['event_id'] : null,
            $team_id
        ]);
        
        // Insert team members if team registration
        if ($is_team && !empty($team_members)) {
            foreach ($team_members as $member) {
                $stmt = $pdo->prepare("INSERT INTO participants 
                                      (name, email, phone, team_id, registration_date) 
                                      VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $member['name'],
                    $member['email'],
                    $member['phone'],
                    $team_id
                ]);
            }
        }
        
        $pdo->commit();
        $success = true;
        
        // Clear form on success
        $_POST = array();
        
    } catch (Exception $e) {
        if (isset($pdo)) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Get active events for dropdown
$events = [];
try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT id, title FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
}

// Include header after all processing
require_once './includes/header.php';
?>

<section class="registration-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white py-4">
                        <div class="text-center">
                            <h2 class="mb-0"><i class="fas fa-user-plus me-2"></i> Participant Registration</h2>
                            <p class="mb-0">Join our upcoming events and competitions</p>
                        </div>
                    </div>
                    
                    <div class="card-body p-5">
                        <!-- Error Message -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Success Message -->
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i> Registration successful! Thank you for registering.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="registrationForm" class="needs-validation" novalidate>
                            <!-- Registration Type Toggle -->
                            <div class="d-flex justify-content-center mb-5">
                                <div class="btn-group btn-group-toggle" role="group" data-toggle="buttons">
                                    <input type="radio" class="btn-check" name="registration_type" id="individual" value="individual" autocomplete="off" <?= (!isset($_POST['registration_type']) || $_POST['registration_type'] === 'individual') ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary" for="individual">
                                        <i class="fas fa-user me-2"></i> Individual
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="registration_type" id="team" value="team" autocomplete="off" <?= (isset($_POST['registration_type']) && $_POST['registration_type'] === 'team') ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary" for="team">
                                        <i class="fas fa-users me-2"></i> Team
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Team Fields -->
                            <div id="teamFields" class="mb-5" style="display: <?= (isset($_POST['registration_type']) && $_POST['registration_type'] === 'team') ? 'block' : 'none' ?>;">
                                <div class="section-header mb-4">
                                    <h4 class="section-title"><i class="fas fa-users me-2"></i> Team Information</h4>
                                    <div class="section-divider"></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="team_name" class="form-label">Team Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="team_name" name="team_name" value="<?= isset($_POST['team_name']) ? htmlspecialchars($_POST['team_name']) : '' ?>">
                                    <div class="invalid-feedback">Please provide a team name</div>
                                </div>
                                
                                <h5 class="mb-3"><i class="fas fa-user-friends me-2"></i> Team Members</h5>
                                <p class="text-muted mb-4">At least one team member is required</p>
                                
                                <!-- Team Member 1 (Required) -->
                                <div class="card mb-3 border-primary">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <h6 class="mb-0">Member 1 (Required)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="member_name_1" value="<?= isset($_POST['member_name_1']) ? htmlspecialchars($_POST['member_name_1']) : '' ?>">
                                                <div class="invalid-feedback">Member name is required</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="member_email_1" value="<?= isset($_POST['member_email_1']) ? htmlspecialchars($_POST['member_email_1']) : '' ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control" name="member_phone_1" value="<?= isset($_POST['member_phone_1']) ? htmlspecialchars($_POST['member_phone_1']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Team Member 2 -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Member 2 (Optional)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" name="member_name_2" value="<?= isset($_POST['member_name_2']) ? htmlspecialchars($_POST['member_name_2']) : '' ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="member_email_2" value="<?= isset($_POST['member_email_2']) ? htmlspecialchars($_POST['member_email_2']) : '' ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control" name="member_phone_2" value="<?= isset($_POST['member_phone_2']) ? htmlspecialchars($_POST['member_phone_2']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Team Member 3 -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Member 3 (Optional)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" name="member_name_3" value="<?= isset($_POST['member_name_3']) ? htmlspecialchars($_POST['member_name_3']) : '' ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="member_email_3" value="<?= isset($_POST['member_email_3']) ? htmlspecialchars($_POST['member_email_3']) : '' ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control" name="member_phone_3" value="<?= isset($_POST['member_phone_3']) ? htmlspecialchars($_POST['member_phone_3']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Team Member 4 -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Member 4 (Optional)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" name="member_name_4" value="<?= isset($_POST['member_name_4']) ? htmlspecialchars($_POST['member_name_4']) : '' ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="member_email_4" value="<?= isset($_POST['member_email_4']) ? htmlspecialchars($_POST['member_email_4']) : '' ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control" name="member_phone_4" value="<?= isset($_POST['member_phone_4']) ? htmlspecialchars($_POST['member_phone_4']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Primary Participant Details -->
                            <div class="section-header mb-4">
                                <h4 class="section-title"><i class="fas fa-user me-2"></i> Primary Participant Details</h4>
                                <div class="section-divider"></div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                                    <div class="invalid-feedback">Please provide your full name</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                    <div class="invalid-feedback">Please provide a valid email</div>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                                    <div class="invalid-feedback">Please provide your phone number</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="college" class="form-label">College/Institution</label>
                                    <select class="form-select" id="college" name="college">
                                        <option value="">Select your college</option>
                                        <option value="1" <?= (isset($_POST['college']) && $_POST['college'] == '1') ? 'selected' : '' ?>>ACCET-KARAIKUDI</option>
                                        <option value="2" <?= (isset($_POST['college']) && $_POST['college'] == '2') ? 'selected' : '' ?>>Other College</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="department" name="department" value="<?= isset($_POST['department']) ? htmlspecialchars($_POST['department']) : '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="year" class="form-label">Year of Study</label>
                                    <select class="form-select" id="year" name="year">
                                        <option value="">Select your year</option>
                                        <option value="1" <?= (isset($_POST['year']) && $_POST['year'] == '1') ? 'selected' : '' ?>>First Year</option>
                                        <option value="2" <?= (isset($_POST['year']) && $_POST['year'] == '2') ? 'selected' : '' ?>>Second Year</option>
                                        <option value="3" <?= (isset($_POST['year']) && $_POST['year'] == '3') ? 'selected' : '' ?>>Third Year</option>
                                        <option value="4" <?= (isset($_POST['year']) && $_POST['year'] == '4') ? 'selected' : '' ?>>Final Year</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Event Selection -->
                            <?php if (!empty($events)): ?>
                            <div class="section-header mb-4">
                                <h4 class="section-title"><i class="fas fa-calendar-alt me-2"></i> Event Registration</h4>
                                <div class="section-divider"></div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="event_id" class="form-label">Select Event (Optional)</label>
                                <select class="form-select" id="event_id" name="event_id">
                                    <option value="">Choose an event...</option>
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?= $event['id'] ?>" <?= (isset($_POST['event_id']) && $_POST['event_id'] == $event['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($event['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Submit Button -->
                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-primary btn-lg py-3">
                                    <i class="fas fa-paper-plane me-2"></i> Complete Registration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.registration-section {
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%);
}

.card {
    border-radius: 12px;
    overflow: hidden;
    border: none;
}

.card-header {
    padding: 1.5rem;
}

.card-body {
    padding: 2rem;
}

@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem;
    }
}

.form-control, .form-select {
    padding: 0.75rem 1rem;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.invalid-feedback {
    font-size: 0.85rem;
}

.section-header {
    position: relative;
    margin-bottom: 1.5rem;
}

.section-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.section-divider {
    width: 60px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
    margin: 0.5rem 0 1rem;
}

.btn-group-toggle .btn {
    padding: 0.5rem 1.25rem;
}

@media (max-width: 576px) {
    .btn-group-toggle {
        width: 100%;
    }
    
    .btn-group-toggle .btn {
        width: 50%;
    }
}

.text-danger {
    color: #dc3545;
}

.alert {
    border-radius: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Team/Individual toggle
    const individualRadio = document.getElementById('individual');
    const teamRadio = document.getElementById('team');
    const teamFields = document.getElementById('teamFields');
    const teamNameInput = document.getElementById('team_name');
    const memberName1Input = document.querySelector('[name="member_name_1"]');
    
    function toggleTeamFields() {
        if (teamRadio.checked) {
            teamFields.style.display = 'block';
            teamNameInput.required = true;
            memberName1Input.required = true;
        } else {
            teamFields.style.display = 'none';
            teamNameInput.required = false;
            memberName1Input.required = false;
        }
    }
    
    individualRadio.addEventListener('change', toggleTeamFields);
    teamRadio.addEventListener('change', toggleTeamFields);
    
    // Form validation
    const form = document.getElementById('registrationForm');
    
    form.addEventListener('submit', function(event) {
        if (teamRadio.checked) {
            teamNameInput.required = true;
            memberName1Input.required = true;
        } else {
            teamNameInput.required = false;
            memberName1Input.required = false;
        }
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php require_once './includes/footer.php'; ?>