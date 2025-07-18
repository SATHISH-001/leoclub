<?php
// Ensure no output before headers
require_once  'config.php';
require_once  'functions.php';

// require_once __DIR__ . '/../../includes/auth.php';

// Check admin permissions
// if (!hasPermission('admin')) {
//     header('Location: /login.php');
//     exit;
// }

$pdo = getPDO();
$pageTitle = "Manage Events";

// Handle all form submissions first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleEventFormSubmission($pdo);
}

// Get all events for display
$events = getAllEvents();

// Now include header which will start output
require_once 'heading.php';

?>


<!-- Main Content -->

<div class="admin-container">
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'admin-sidebar.php'; ?>
            
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Events</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal">
                            <i class="fas fa-plus me-1"></i> Add Event
                        </button>
                    </div>
                </div>
                
                <!-- Status Messages -->
                <?php displayStatusMessages(); ?>
                
                <!-- Events Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($event['title']) ?></td>
                                        <td><?= formatDate($event['event_date']) ?></td>
                                        <td><?= htmlspecialchars($event['location']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-event" 
                                                    data-id="<?= $event['id'] ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#eventModal">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                <button type="submit" name="delete_event" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this event?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Event Modal (for Add/Edit) -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="eventForm">
                <input type="hidden" name="event_id" id="eventId">
                <input type="hidden" name="existing_image" id="existingImage">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Event Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="event_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="event_time" class="form-label">Time *</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="event_image" class="form-label">Event Image</label>
                                <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
                                <div class="form-text">Max size: 2MB. Allowed types: JPG, PNG, GIF</div>
                                <div id="imagePreview" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="save_event">Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
// JavaScript for handling the modal and form
document.addEventListener('DOMContentLoaded', function() {
    const eventModal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    const modalTitle = document.getElementById('eventModalLabel');
    const eventIdInput = document.getElementById('eventId');
    const existingImageInput = document.getElementById('existingImage');
    const imagePreview = document.getElementById('imagePreview');
    
    // Edit buttons
    document.querySelectorAll('.edit-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.getAttribute('data-id');
            // Fetch event data via AJAX or from data attributes
            fetchEventData(eventId);
        });
    });
    
    // File input preview
    document.getElementById('event_image').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                imagePreview.innerHTML = `
                    <img src="${event.target.result}" class="img-thumbnail" style="max-height: 150px;">
                    <div class="mt-1">${file.name}</div>
                `;
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // Reset form when modal is closed
    eventModal.addEventListener('hidden.bs.modal', function() {
        form.reset();
        imagePreview.innerHTML = '';
        modalTitle.textContent = 'Add New Event';
        eventIdInput.value = '';
        existingImageInput.value = '';
    });
    
    // Function to fetch event data (simplified - implement AJAX in production)
    function fetchEventData(eventId) {
        // In a real app, you would fetch this via AJAX
        modalTitle.textContent = 'Edit Event';
        eventIdInput.value = eventId;
        
        // For demo, we'll assume the data is available in the page
        // You should implement proper AJAX call to fetch event details
    }
});
</script>



