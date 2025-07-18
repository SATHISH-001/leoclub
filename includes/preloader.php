<?php
/**
 * Ultra-Professional Preloader Component
 * Multiple premium style options with advanced animations
 */
?>
<link rel="icon" href="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" >
<style>
    /* Preloader Base Styles */
    #preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(2px);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        transition: all 0.8s cubic-bezier(0.645, 0.045, 0.355, 1);
        overflow: hidden;
        opacity: 1;
        visibility: visible;
    }
    
    #preloader.fade-out {
        opacity: 0;
        visibility: hidden;
        transform: translateY(-20px);
    }
    
    /* Style 1: Premium Dual-Ring Spinner */
    .preloader-style-1 {
        --primary-color: #2C3E50; /* Professional dark blue */
        --secondary-color: #E74C3C; /* Accent red */
        --highlight-color: #FFC107; /* LEO yellow */
    }
    
    .preloader-style-1 .loader {
        position: relative;
        width: 200px;
        height: 200px;
    }
    
    .preloader-style-1 .loader-dual-ring {
        position: absolute;
        width: 100%;
        height: 100%;
        border: 5px solid transparent;
        border-radius: 50%;
        border-top-color: var(--primary-color);
        border-bottom-color: var(--secondary-color);
        animation: dualSpin 1.8s linear infinite;
    }
    
    .preloader-style-1 .loader-inner-ring {
        position: absolute;
        width: 70%;
        height: 70%;
        top: 15%;
        left: 15%;
        border: 3px solid transparent;
        border-radius: 50%;
        border-left-color: var(--highlight-color);
        border-right-color: var(--highlight-color);
        animation: reverseSpin 1.4s linear infinite;
    }
    
    .preloader-style-1 .loader-logo {
        position: absolute;
        width: 40%;
        height: 40%;
        top: 30%;
        left: 30%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s ease-in-out infinite;
    }
    
    /* Style 2: Elegant Wave Animation */
    .preloader-style-2 {
        --wave-color: #2C3E50;
    }
    
    .preloader-style-2 .loader {
        display: flex;
        align-items: flex-end;
        height: 60px;
        gap: 5px;
    }
    
    .preloader-style-2 .wave-bar {
        width: 8px;
        background-color: var(--wave-color);
        border-radius: 4px;
        animation: wave 1.6s ease-in-out infinite;
    }
    
    .preloader-style-2 .wave-bar:nth-child(1) {
        height: 20%;
        animation-delay: 0.1s;
    }
    
    .preloader-style-2 .wave-bar:nth-child(2) {
        height: 40%;
        animation-delay: 0.2s;
    }
    
    .preloader-style-2 .wave-bar:nth-child(3) {
        height: 60%;
        animation-delay: 0.3s;
    }
    
    .preloader-style-2 .wave-bar:nth-child(4) {
        height: 80%;
        animation-delay: 0.4s;
    }
    
    .preloader-style-2 .wave-bar:nth-child(5) {
        height: 100%;
        animation-delay: 0.5s;
    }
    
    .preloader-style-2 .wave-bar:nth-child(6) {
        height: 80%;
        animation-delay: 0.6s;
    }
    
    .preloader-style-2 .wave-bar:nth-child(7) {
        height: 60%;
        animation-delay: 0.7s;
    }
    
    .preloader-style-2 .wave-bar:nth-child(8) {
        height: 40%;
        animation-delay: 0.8s;
    }
    
    .preloader-style-2 .wave-bar:nth-child(9) {
        height: 20%;
        animation-delay: 0.9s;
    }
    
    /* Style 3: Sophisticated Percentage Loader */
    .preloader-style-3 {
        --track-color: #f0f0f0;
        --progress-color: #2C3E50;
        --text-color: #333;
    }
    
    .preloader-style-3 .loader-container {
        width: 200px;
    }
    
    .preloader-style-3 .loader-track {
        height: 6px;
        background-color: var(--track-color);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 15px;
    }
    
    .preloader-style-3 .loader-progress {
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, var(--progress-color), #E74C3C);
        transition: width 0.4s ease;
    }
    
    .preloader-style-3 .loader-percentage {
        text-align: center;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-color);
        font-family: 'Segoe UI', sans-serif;
    }
    
    /* Animations */
    @keyframes dualSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes reverseSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(-360deg); }
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.9; }
    }
    
    @keyframes wave {
        0%, 100% { height: 20%; }
        50% { height: 100%; }
    }
    
    /* Typography Animation */
    .loader-text {
        margin-top: 30px;
        color: #2C3E50;
        font-size: 1.2rem;
        font-weight: 500;
        text-align: center;
        max-width: 80%;
        font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;
    }
    
    .loader-text span {
        display: inline-block;
        opacity: 0;
        transform: translateY(15px);
        animation: fadeUp 0.6s cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
    }
    
    @keyframes fadeUp {
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .preloader-style-1 .loader {
            width: 180px;
            height: 180px;
        }
        
        .loader-text {
            font-size: 1rem;
            margin-top: 20px;
        }
        
        .preloader-style-3 .loader-container {
            width: 160px;
        }
    }
</style>

<div id="preloader" class="preloader-style-1">
    <!-- Style 1: Premium Dual-Ring Spinner -->
    <div class="loader">
        <div class="loader-dual-ring"></div>
        <div class="loader-inner-ring"></div>
        <div class="loader-logo">
            <img src="https://accet-site-media-trial.s3.ap-northeast-1.amazonaws.com/cocurricular/clubs/leologo.webp" alt="LEO Club Logo" style="width: 110px;">
        </div>
    </div>
    
    <!-- Style 2: Elegant Wave Animation (Alternative) -->
    <!--
    <div class="loader">
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
    </div>
    -->
    
    <!-- Style 3: Sophisticated Percentage Loader (Alternative) -->
    <!--
    <div class="loader-container">
        <div class="loader-track">
            <div class="loader-progress"></div>
        </div>
        <div class="loader-percentage">0%</div>
    </div>
    -->
    
    <div class="loader-text">
        <span>P</span>
        <span>r</span>
        <span>e</span>
        <span>p</span>
        <span>a</span>
        <span>r</span>
        <span>i</span>
        <span>n</span>
        <span>g</span>
        <span> </span>
        <span>E</span>
        <span>x</span>
        <span>p</span>
        <span>e</span>
        <span>r</span>
        <span>i</span>
        <span>e</span>
        <span>n</span>
        <span>c</span>
        <span>e</span>
        <span>.</span>
        <span>.</span>
        <span>.</span>
    </div>
</div>

<script>
    // Advanced Preloader Logic
    document.addEventListener('DOMContentLoaded', function() {
        const preloader = document.getElementById('preloader');
        let loaded = false;
        
        // For percentage loader (Style 3)
        if (document.querySelector('.preloader-style-3')) {
            let progress = 0;
            const progressBar = document.querySelector('.loader-progress');
            const percentageText = document.querySelector('.loader-percentage');
            
            const progressInterval = setInterval(() => {
                progress += Math.random() * 5;
                const currentProgress = Math.min(progress, 100);
                
                if (progressBar) progressBar.style.width = `${currentProgress}%`;
                if (percentageText) percentageText.textContent = `${Math.floor(currentProgress)}%`;
                
                if (progress >= 100) {
                    clearInterval(progressInterval);
                }
            }, 100);
        }
        
        // Hide when everything is loaded
        function completeLoading() {
            if (!loaded) {
                loaded = true;
                fadeOutPreloader();
            }
        }
        
        window.addEventListener('load', completeLoading);
        
        // Fallback with minimum display time
        setTimeout(completeLoading, 2000);
        
        function fadeOutPreloader() {
            if (preloader) {
                preloader.classList.add('fade-out');
                
                // Remove after animation completes
                setTimeout(() => {
                    if (preloader && preloader.parentNode) {
                        preloader.parentNode.removeChild(preloader);
                    }
                }, 800);
            }
        }
        
        // Add smooth character animation
        const textSpans = document.querySelectorAll('.loader-text span');
        textSpans.forEach((span, index) => {
            span.style.animationDelay = `${index * 0.05}s`;
        });
    });
</script>