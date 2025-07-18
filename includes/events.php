<?php
require_once './header.php';
require_once './functions.php';
require_once './db.php';

$events = []; // ensure $events is always defined

try {
    $pdo = getPDO();
    
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
        if ($stmt && $stmt instanceof PDOStatement) {
            $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (is_array($fetched)) {
                $events = $fetched;
            }
        }
    }
} catch (PDOException $e) {
    error_log("Database error in events.php: " . $e->getMessage());
}

// Ensure $events is always an array
$validEvents = array_filter($events, function($event) {
    return !empty($event['id']) && !empty($event['title']);
});

$pageTitle = "Events";
?>

<section class="events-section py-5">
    <div class="container">
        <h1 class="text-center mb-5">Upcoming Events</h1>
        
        <?php if (empty($validEvents)): ?>
            <div class="alert alert-info">No upcoming events found.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($validEvents as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($event['image_path'])): ?>
                        <img src="/assets/uploads/<?= htmlspecialchars($event['image_path']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($event['title']) ?>"
                             style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                            <p class="card-text"><?= substr(htmlspecialchars($event['description']), 0, 100) ?>...</p>
                            <p class="text-muted">
                                <i class="fas fa-calendar-alt"></i> <?= date('M j, Y', strtotime($event['event_date'])) ?>
                                <i class="fas fa-clock ms-2"></i> <?= date('h:i A', strtotime($event['event_time'])) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="event-details.php?id=<?= $event['id'] ?>" class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once './footer.php'; ?>