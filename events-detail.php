<?php
ob_start();

require_once './includes/functions.php';
require_once './includes/db.php';

$pageTitle = "Event Details";
$event = null;

try {
    $pdo = getPDO();
    
    if (isset($_GET['id'])) {
        $eventId = $_GET['id'];
        
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    error_log("Database error in events-detail.php: " . $e->getMessage());
}

if (!$event) {
    header("Location: events.php");
    exit();
}

require_once './includes/header.php';
?>

<section class="event-detail-hero py-5" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= !empty($event['image_path']) ? '/assets/uploads/' . htmlspecialchars($event['image_path']) : 'https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/event-bg.webp' ?>') center/cover;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-white">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="events.php" class="text-white">Events</a></li>
                        <li class="breadcrumb-item active text-white-50" aria-current="page">Details</li>
                    </ol>
                </nav>
                <h1 class="display-4 fw-bold mb-3"><?= !empty($event['title']) ? htmlspecialchars($event['title']) : 'Event' ?></h1>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-day me-2"></i>
                        <span><?= !empty($event['event_date']) ? date('F j, Y', strtotime($event['event_date'])) : 'Date not set' ?></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock me-2"></i>
                        <span><?= !empty($event['event_time']) ? date('h:i A', strtotime($event['event_time'])) : 'Time not set' ?></span>
                    </div>
                    <?php if (!empty($event['location'])): ?>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span><?= htmlspecialchars($event['location']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($event['registration_link'])): ?>
                <a href="<?= htmlspecialchars($event['registration_link']) ?>" class="btn btn-primary btn-lg px-4 me-2" target="_blank">
                    Register Now
                </a>
                <?php endif; ?>
                <?php if (!empty($event['gallery'])): ?>
                <a href="#event-gallery" class="btn btn-outline-light btn-lg px-4">
                    View Photos
                </a>
                <?php endif; ?>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card shadow-sm bg-white bg-opacity-10 text-white border-0">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-4">Event Summary</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-day me-3"></i>
                                    <div>
                                        <small class="text-white-50 d-block">Date</small>
                                        <span><?= !empty($event['event_date']) ? date('F j, Y', strtotime($event['event_date'])) : 'Not set' ?></span>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock me-3"></i>
                                    <div>
                                        <small class="text-white-50 d-block">Time</small>
                                        <span><?= !empty($event['event_time']) ? date('h:i A', strtotime($event['event_time'])) : 'Not set' ?></span>
                                    </div>
                                </div>
                            </li>
                            <?php if (!empty($event['location'])): ?>
                            <li class="mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt me-3"></i>
                                    <div>
                                        <small class="text-white-50 d-block">Venue</small>
                                        <span><?= htmlspecialchars($event['location']) ?></span>
                                    </div>
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="event-detail-content py-5 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <article class="event-article">
                    <h2 class="mb-4">About This Event</h2>
                    <div class="event-description mb-5">
                        <?= !empty($event['description']) ? nl2br(htmlspecialchars($event['description'])) : 'No description available.' ?>
                    </div>
                    
                    <?php if (!empty($event['agenda'])): ?>
                    <div class="event-agenda mb-5">
                        <h3 class="h4 mb-3">Event Agenda</h3>
                        <div class="accordion" id="agendaAccordion">
                            <?php 
                            $agendaItems = json_decode($event['agenda'], true);
                            if ($agendaItems && is_array($agendaItems)): 
                                foreach ($agendaItems as $index => $item): 
                            ?>
                            <div class="accordion-item border-0 shadow-sm mb-2">
                                <h2 class="accordion-header" id="heading<?= $index ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                                        <span class="me-3"><?= !empty($item['time']) ? htmlspecialchars($item['time']) : '' ?></span>
                                        <?= !empty($item['title']) ? htmlspecialchars($item['title']) : '' ?>
                                    </button>
                                </h2>
                                <div id="collapse<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>" data-bs-parent="#agendaAccordion">
                                    <div class="accordion-body">
                                        <?= !empty($item['description']) ? nl2br(htmlspecialchars($item['description'])) : '' ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($event['registration_link']) && (!empty($event['event_date']) && strtotime($event['event_date']) >= time())): ?>
                    <div class="event-cta text-center py-4 px-3 bg-light rounded-3 mb-5">
                        <h3 class="h4 mb-3">Ready to Join Us?</h3>
                        <a href="<?= htmlspecialchars($event['registration_link']) ?>" class="btn btn-primary btn-lg px-5" target="_blank">
                            Register Now
                        </a>
                    </div>
                    <?php endif; ?>
                </article>
            </div>
            
            <div class="col-lg-4">
                <aside class="event-sidebar">
                    <?php if (!empty($event['image_path'])): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <img src="/assets/uploads/<?= htmlspecialchars($event['image_path']) ?>" 
                             class="card-img-top" 
                             alt="<?= !empty($event['title']) ? htmlspecialchars($event['title']) : 'Event image' ?>">
                    </div>
                    <?php endif; ?>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h3 class="h5 card-title mb-3">Event Details</h3>
                            <ul class="list-unstyled">
                                <li class="mb-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-calendar-day text-primary mt-1 me-3"></i>
                                        <div>
                                            <h4 class="h6 mb-1">Date</h4>
                                            <p class="mb-0"><?= !empty($event['event_date']) ? date('l, F j, Y', strtotime($event['event_date'])) : 'Not set' ?></p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-clock text-primary mt-1 me-3"></i>
                                        <div>
                                            <h4 class="h6 mb-1">Time</h4>
                                            <p class="mb-0"><?= !empty($event['event_time']) ? date('h:i A', strtotime($event['event_time'])) : 'Not set' ?></p>
                                        </div>
                                    </div>
                                </li>
                                <?php if (!empty($event['location'])): ?>
                                <li class="mb-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-map-marker-alt text-primary mt-1 me-3"></i>
                                        <div>
                                            <h4 class="h6 mb-1">Venue</h4>
                                            <p class="mb-0"><?= htmlspecialchars($event['location']) ?></p>
                                        </div>
                                    </div>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($event['gallery'])): ?>
<section id="event-gallery" class="event-gallery py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Event Gallery</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        
        <div class="row g-3">
            <?php 
            $galleryItems = json_decode($event['gallery'], true);
            if ($galleryItems && is_array($galleryItems)): 
                foreach ($galleryItems as $index => $image): 
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/assets/uploads/gallery/<?= htmlspecialchars($image) ?>" data-fancybox="gallery">
                    <img src="/assets/uploads/gallery/<?= htmlspecialchars($image) ?>" 
                         class="img-fluid rounded shadow-sm" 
                         alt="Event Photo <?= $index + 1 ?>"
                         loading="lazy">
                </a>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php 
ob_end_flush();
require_once './includes/footer.php'; 
?>