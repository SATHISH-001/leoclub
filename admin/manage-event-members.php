<?php
// Use absolute paths with __DIR__
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Verify admin access
// require_admin();

// Get event ID from URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Get PDO instance
$pdo = getPDO();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE id = ? AND event_id = ?");
    $stmt->execute([$id, $event_id]);
    
    $_SESSION['message'] = "Registration deleted successfully";
    header("Location: manage-event-members.php?event_id=$event_id");
    exit();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ./login.php');
    exit;
}

// Get event details
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: ../events.php");
    exit();
}

// Get all registrations with member count
$stmt = $pdo->prepare("
    SELECT r.*, COUNT(m.id) as member_count 
    FROM event_registrations r
    LEFT JOIN event_team_members m ON m.registration_id = r.id
    WHERE r.event_id = ?
    GROUP BY r.id
    ORDER BY r.created_at DESC
");
$stmt->execute([$event_id]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin header
require_once __DIR__ . '/heading.php';
?>
 
<div class="container mt-4">
    <?php include_once __DIR__ . '/admin-sidebar.php'; ?>
    <h1>Manage Registrations: <?= htmlspecialchars($event['title']) ?></h1>

        <?php include_once __DIR__ . '/admin-sidebar.php'; ?>
        
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-info"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between">
            <span>Registrations</span>
            <a href="export-registrations.php?event_id=<?= $event_id ?>" class="btn btn-sm btn-success">Export</a>
        </div>
        
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <?php if ($event['registration_type'] === 'team'): ?>
                            <th>Team</th>
                            <th>Members</th>
                        <?php endif; ?>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $reg): ?>
                    <tr>
                        <td><?= $reg['id'] ?></td>
                        <td><?= htmlspecialchars($reg['contact_person']) ?></td>
                        <td><?= htmlspecialchars($reg['contact_email']) ?></td>
                        <td><?= htmlspecialchars($reg['contact_phone']) ?></td>
                        <?php if ($event['registration_type'] === 'team'): ?>
                            <td><?= htmlspecialchars($reg['team_name']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info view-members" 
                                    data-id="<?= $reg['id'] ?>">
                                    View (<?= $reg['member_count'] ?>)
                                </button>
                            </td>
                        <?php endif; ?>
                        <td><?= date('M j, Y g:i A', strtotime($reg['created_at'])) ?></td>
                        <td>
                            <a href="edit-registration.php?id=<?= $reg['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?action=delete&id=<?= $reg['id'] ?>&event_id=<?= $event_id ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Delete this registration?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Members Modal -->
<div class="modal fade" id="membersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Team Members</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalMembersContent">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.view-members').forEach(btn => {
    btn.addEventListener('click', function() {
        const regId = this.getAttribute('data-id');
        fetch(`get-members.php?registration_id=${regId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalMembersContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('membersModal')).show();
            });
    });
});
</script>

