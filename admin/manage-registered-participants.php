<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getPDO();
$pageTitle = "Manage Registered Participants";

// Handle actions (delete, export, etc.)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // First get team_id if this participant is part of a team
        $stmt = $pdo->prepare("SELECT team_id FROM participants WHERE id = ?");
        $stmt->execute([$id]);
        $participant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $pdo->beginTransaction();
        
        // Delete the participant
        $stmt = $pdo->prepare("DELETE FROM participants WHERE id = ?");
        $stmt->execute([$id]);
        
        // If this was the last participant in a team, delete the team
        if (!empty($participant['team_id'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE team_id = ?");
            $stmt->execute([$participant['team_id']]);
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
                $stmt->execute([$participant['team_id']]);
            }
        }
        
        $pdo->commit();
        
        $_SESSION['success'] = "Participant deleted successfully";
        header("Location: manage-registered-participants.php");
        exit();
    } catch (PDOException $e) {
        if (isset($pdo)) $pdo->rollBack();
        $_SESSION['error'] = "Error deleting participant: " . $e->getMessage();
        header("Location: manage-registered-participants.php");
        exit();
    }
}

// Get all participants with search functionality
$search = $_GET['search'] ?? '';
$query = "SELECT p.*, e.title as event_name, t.name as team_name 
          FROM participants p 
          LEFT JOIN events e ON p.event_id = e.id
          LEFT JOIN teams t ON p.team_id = t.id";

$params = [];
if (!empty($search)) {
    $query .= " WHERE p.name LIKE ? OR p.email LIKE ? OR p.phone LIKE ? OR p.college LIKE ?";
    $searchParam = "%$search%";
    $params = array_fill(0, 4, $searchParam);
}

$query .= " ORDER BY p.registration_date DESC";

$stmt = $pdo->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'heading.php';
?>

<div class="admin-container">
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'admin-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Registered Participants</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="export-participants.php?<?= http_build_query(['search' => $search]) ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i> Export
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by name, email, phone or college" 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                            <div class="col-md-2">
                                <a href="manage-registered-participants.php" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>College</th>
                                        <th>Team</th>
                                        <th>Event</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($participants)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No participants found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($participants as $index => $participant): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <?= htmlspecialchars($participant['name']) ?>
                                                <?php if (!empty($participant['team_id']) && empty($participant['team_name'])): ?>
                                                    <span class="badge bg-info">Team Member</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($participant['email']) ?></td>
                                            <td><?= htmlspecialchars($participant['phone']) ?></td>
                                            <td><?= htmlspecialchars($participant['college']) ?></td>
                                            <td><?= !empty($participant['team_name']) ? htmlspecialchars($participant['team_name']) : 'N/A' ?></td>
                                            <td><?= $participant['event_name'] ? htmlspecialchars($participant['event_name']) : 'N/A' ?></td>
                                            <td><?= date('M j, Y', strtotime($participant['registration_date'])) ?></td>
                                            <td>
                                                <a href="participant-details.php?id=<?= $participant['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="manage-registered-participants.php?delete=<?= $participant['id'] ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure you want to delete this participant?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
