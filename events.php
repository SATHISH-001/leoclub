<?php
require_once './includes/header.php';
require_once './includes/functions.php';
require_once './includes/db.php';

$pageTitle = "Events";
$events = [];
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : null;

try {
    $pdo = getPDO();
    
    if ($pdo) {
        // Base query
        $query = "SELECT * FROM events";
        
        // Add year filter if selected
        if ($selectedYear) {
            $query .= " WHERE YEAR(event_date) = :year";
        }
        
        $query .= " ORDER BY event_date DESC";
        
        $stmt = $pdo->prepare($query);
        
        if ($selectedYear) {
            $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error in events.php: " . $e->getMessage());
}

// Get distinct years from events for the year filter dropdown
$years = [];
try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT DISTINCT YEAR(event_date) as year FROM events ORDER BY year DESC");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    error_log("Error fetching years: " . $e->getMessage());
}

// Filter valid events
$validEvents = array_filter($events, function($event) {
    return !empty($event['id']) && !empty($event['title']);
});

// Separate upcoming and past events
$upcomingEvents = array_filter($validEvents, function($event) {
    return strtotime($event['event_date']) >= time();
});

$pastEvents = array_filter($validEvents, function($event) {
    return strtotime($event['event_date']) < time();
});

// Sort upcoming events chronologically
usort($upcomingEvents, function($a, $b) {
    return strtotime($a['event_date']) - strtotime($b['event_date']);
});

// Sort past events reverse chronologically
usort($pastEvents, function($a, $b) {
    return strtotime($b['event_date']) - strtotime($a['event_date']);
});
?>

<section class="events-hero py-5 bg-light">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Our Events</h1>
        <p class="lead text-light mx-auto " style="max-width: 700px;">
            Discover our upcoming gatherings and relive past memorable moments
        </p>
    </div>
</section>

<section class="featured-events py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Featured Events</h2>
            <div class="section-divider mx-auto"></div>
            <p class="section-subtitle text-muted">Highlighted gatherings you won't want to miss</p>
            
            <!-- Year Filter Dropdown -->
            <div class="year-filter mb-4">
                <form method="get" class="d-flex justify-content-center">
                    <div class="input-group" style="max-width: 300px;">
                        <select class="form-select" name="year" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= $selectedYear === $year ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($selectedYear): ?>
                            <a href="events.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($upcomingEvents)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-calendar-check me-2"></i> No upcoming events scheduled. Check back soon!
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach (array_slice($upcomingEvents, 0, 3) as $event): ?>
                <div class="col-lg-4">
                    <div class="event-card card border-0 shadow-sm h-100">
                        <?php if (!empty($event['image_path'])): ?>
                        <div class="event-card-img">
                            <img src="/assets/uploads/<?= htmlspecialchars($event['image_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($event['title']) ?>"
                                 loading="lazy">
                            <div class="event-date-badge">
                                <span class="event-day"><?= date('d', strtotime($event['event_date'])) ?></span>
                                <span class="event-month"><?= date('M', strtotime($event['event_date'])) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h5 card-title mb-0"><?= htmlspecialchars($event['title']) ?></h3>
                                <?php if (!empty($event['location'])): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($event['location']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <p class="card-text text-muted mb-3">
                                <?= substr(htmlspecialchars($event['description']), 0, 120) ?>...
                            </p>
                            <div class="event-meta d-flex justify-content-between align-items-center">
                                <span class="text-primary">
                                    <i class="fas fa-clock me-1"></i> <?= date('h:i A', strtotime($event['event_time'])) ?>
                                </span>
                                <a href="events-detail.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary stretched-link">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="all-events py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="sticky-top" style="top: 20px;">
                    <div class="section-header mb-4">
                        <h2 class="section-title">Upcoming Events</h2>
                        <div class="section-divider"></div>
                    </div>
                    
                    <?php if (empty($upcomingEvents)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-calendar-plus me-2"></i> No upcoming events scheduled yet.
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($upcomingEvents as $event): ?>
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <?= date('M j', strtotime($event['event_date'])) ?>
                                </div>
                                <div class="timeline-content card shadow-sm">
                                    <?php if (!empty($event['image_path'])): ?>
                                    <img src="/assets/uploads/<?= htmlspecialchars($event['image_path']) ?>" 
                                         class="card-img-top" 
                                         alt="<?= htmlspecialchars($event['title']) ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h3 class="h5 card-title"><?= htmlspecialchars($event['title']) ?></h3>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="text-muted me-3">
                                                <i class="fas fa-clock me-1"></i> <?= date('h:i A', strtotime($event['event_time'])) ?>
                                            </span>
                                            <?php if (!empty($event['location'])): ?>
                                            <span class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($event['location']) ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="card-text"><?= substr(htmlspecialchars($event['description']), 0, 150) ?>...</p>
                                        <a href="events-detail.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-primary">More Info</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="section-header mb-4">
                    <h2 class="section-title">Past Events</h2>
                    <div class="section-divider"></div>
                    <?php if ($selectedYear): ?>
                        <p class="text-muted">Showing events from <?= $selectedYear ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($pastEvents)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-minus me-2"></i> No past events to display.
                    </div>
                <?php else: ?>
                    <div class="past-events-grid">
                        <?php foreach ($pastEvents as $event): ?>
                        <div class="past-event-card card mb-4 border-0 shadow-sm">
                            <div class="row g-0">
                                <?php if (!empty($event['image_path'])): ?>
                                <div class="col-md-4">
                                    <img src="/assets/uploads/<?= htmlspecialchars($event['image_path']) ?>" 
                                         class="img-fluid rounded-start h-100 object-cover" 
                                         alt="<?= htmlspecialchars($event['title']) ?>"
                                         loading="lazy">
                                </div>
                                <?php endif; ?>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h3 class="h5 card-title mb-0"><?= htmlspecialchars($event['title']) ?></h3>
                                            <small class="text-muted"><?= date('M j, Y', strtotime($event['event_date'])) ?></small>
                                        </div>
                                        <?php if (!empty($event['location'])): ?>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($event['location']) ?>
                                        </p>
                                        <?php endif; ?>
                                        <p class="card-text"><?= substr(htmlspecialchars($event['description']), 0, 100) ?>...</p>
                                        <a href="events-detail.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary">View Recap</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section */
.events-hero {
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                url('https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp') center/cover;
    color: white;
    padding: 100px 0;
    text-shadow: white;
}

/* Section Styling */
.section-header {
    position: relative;
    margin-bottom: 2rem;
}

.section-title {
    font-weight: 700;
    position: relative;
    display: inline-block;
    margin-bottom: 1rem;
}

.section-title:after {
    content: "";
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
}

.section-divider {
    width: 80px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
    margin: 1rem 0;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #6c757d;
}

/* Event Cards */
.event-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.event-card-img {
    position: relative;
    overflow: hidden;
    height: 200px;
}

.event-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.event-card:hover .event-card-img img {
    transform: scale(1.05);
}

.event-date-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(0,0,0,0.7);
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 2;
}

.event-day {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.event-month {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline:before {
    content: "";
    position: absolute;
    top: 0;
    left: 10px;
    height: 100%;
    width: 2px;
    background: #3498db;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-date {
    position: absolute;
    left: -30px;
    top: 0;
    width: 60px;
    text-align: center;
    background: #3498db;
    color: white;
    padding: 5px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.timeline-content {
    margin-left: 30px;
    border-radius: 8px;
    overflow: hidden;
}

.timeline-content img {
    height: 180px;
    object-fit: cover;
}

/* Past Events */
.past-event-card {
    transition: all 0.3s ease;
}

.past-event-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}

.past-event-card img {
    height: 100%;
    min-height: 150px;
}

/* Year Filter */
.year-filter {
    margin-top: 1.5rem;
}

/* Responsive */
@media (max-width: 992px) {
    .event-card-img {
        height: 180px;
    }
    
    .timeline:before {
        left: 8px;
    }
    
    .timeline-date {
        left: -25px;
        width: 50px;
    }
}

@media (max-width: 768px) {
    .events-hero {
        padding: 80px 0;
    }
    
    .event-card-img {
        height: 160px;
    }
    
    .timeline {
        padding-left: 25px;
    }
    
    .timeline:before {
        left: 5px;
    }
    
    .timeline-date {
        left: -20px;
        width: 40px;
        font-size: 0.7rem;
    }
    
    .timeline-content {
        margin-left: 25px;
    }
}

@media (max-width: 576px) {
    .events-hero {
        padding: 60px 0;
    }
    
    .event-card-img {
        height: 140px;
    }
    
    .past-event-card .row > div {
        width: 100%;
    }
    
    .past-event-card img {
        height: 150px;
        width: 100%;
    }
}
</style>

<?php require_once './includes/footer.php'; ?>