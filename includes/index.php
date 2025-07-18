<?php 
$pageTitle = "Home";
require_once 'header.php'; 
require_once 'functions.php';

// Get counts from database
$pdo = getPDO();
$bearerCount = $pdo->query("SELECT COUNT(*) FROM office_bearers")->fetchColumn();
$coordinatorCount = $pdo->query("SELECT COUNT(*) FROM office_bearers WHERE position LIKE '%Coordinator%'")->fetchColumn();
?>

<section class="hero-section mb-5">
    <div class="hero-image">
        <div class="hero-text text-center">
            <h1>WELCOME TO THE LEO CLUB WEBSITE OF ACGCET</h1>
            <img src="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" width="400px" height="400px" alt="leo ">
            <br>
            <!-- <a href="about.php" class="btn btn-secondary">Learn More</a> -->
        </div>
    </div>
</section>

<!-- Count Display Section -->
<section class="counters-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <!-- Office Bearers Count -->
            <div class="col-md-3 col-6 mb-4">
                <div class="counter-circle mx-auto">
                    <div class="counter-number"><?= $bearerCount ?></div>
                    <div class="counter-label">Office Bearers</div>
                </div>
            </div>
            
            <!-- Coordinators Count -->
            <div class="col-md-3 col-6 mb-4">
                <div class="counter-circle mx-auto">
                    <div class="counter-number"><?= $coordinatorCount ?></div>
                    <div class="counter-label">Coordinators</div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Counter Circle Styles */
.counter-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3498db, #2c3e50);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.counter-circle:hover {
    transform: scale(1.05);
}

.counter-number {
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
}

.counter-label {
    font-size: 1rem;
    text-transform: uppercase;
    margin-top: 5px;
}

/* Hero Section Styles */
.hero-section {
    position: relative;
    height: 80vh;
    background: url('path/to/your/hero-image.jpg') no-repeat center center;
    background-size: cover;
    color: white;
}

.hero-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.hero-text h1 {
    font-size: 2.5rem;
    font-weight: bold;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .counter-circle {
        width: 120px;
        height: 120px;
    }
    
    .counter-number {
        font-size: 2rem;
    }
    
    .hero-text h1 {
        font-size: 2rem;
    }
}
</style>

<?php require_once 'footer.php'; ?>