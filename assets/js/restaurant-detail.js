(function($) {
    "use strict";

    // Initialize when document is ready
    $(document).ready(function() {
        initializePropertyDetail();
    });

    /**
     * Initialize all property detail functionality
     */
    function initializePropertyDetail() {
        initializeNavigation();
        initializeTabs();
        initializeGallery();
        // initializeMap(); // Handled by PHP template
        initializeForms();
        initializeModals();
    }

    /**
     * Initialize smooth scrolling navigation
     */
    function initializeNavigation() {
        // Smooth scrolling for property navigation
        $('.property-navigation .target').on('click', function(e) {
            e.preventDefault();
            const targetId = $(this).attr('href').substring(1);
            const targetElement = $('#' + targetId);
            
            if (targetElement.length) {
                $('html, body').animate({
                    scrollTop: targetElement.offset().top - 100
                }, 800);
            }
        });

        // Back to top functionality
        $('.back-top').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 800);
        });

        // Show/hide back to top button based on scroll position
        $(window).on('scroll', function() {
            if ($(window).scrollTop() > 300) {
                $('.back-top').addClass('show');
            } else {
                $('.back-top').removeClass('show');
            }
        });
    }

    /**
     * Initialize tab functionality
     */
    function initializeTabs() {
        // Desktop tabs
        $('.nav-pills .nav-link').on('click', function(e) {
            e.preventDefault();
            
            const targetTab = $(this).attr('href');
            
            // Remove active class from all tabs and panes
            $('.nav-pills .nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');
            
            // Add active class to clicked tab and corresponding pane
            $(this).addClass('active');
            $(targetTab).addClass('show active');
        });

        // Mobile tabs
        $('#pills-tab-mobile .nav-link').on('click', function(e) {
            e.preventDefault();
            
            const targetTab = $(this).attr('href');
            
            // Remove active class from all mobile tabs and panes
            $('#pills-tab-mobile .nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');
            
            // Add active class to clicked tab and corresponding pane
            $(this).addClass('active');
            $(targetTab).addClass('show active');
        });
    }

    /**
     * Initialize gallery functionality
     */
    function initializeGallery() {
        let galleryImages = [];
        let currentImageIndex = 0;

        // Get gallery data
        const galleryDataElement = document.getElementById('gallery-data');
        if (galleryDataElement) {
            try {
                galleryImages = JSON.parse(galleryDataElement.textContent);
                console.log('Gallery images loaded:', galleryImages.length);
            } catch (error) {
                console.error('Error parsing gallery data:', error);
                galleryImages = [];
            }
        }

        // Gallery lightbox functionality
        $('.houzez-trigger-popup-slider-js').on('click', function(e) {
            e.preventDefault();
            
            const sliderNo = parseInt($(this).data('slider-no')) - 1;
            currentImageIndex = sliderNo;
            
            if (galleryImages[sliderNo]) {
                showLightbox(galleryImages[sliderNo], sliderNo);
            }
        });

        // Lightbox controls
        $('#close-lightbox, .modal .close').on('click', function() {
            hideLightbox();
        });

        // Close lightbox when clicking outside
        $('.modal').on('click', function(e) {
            if (e.target === this) {
                hideLightbox();
            }
        });

        // Keyboard navigation
        $(document).on('keydown', function(e) {
            if ($('.modal').hasClass('show')) {
                switch(e.key) {
                    case 'Escape':
                        hideLightbox();
                        break;
                    case 'ArrowLeft':
                        showPreviousImage();
                        break;
                    case 'ArrowRight':
                        showNextImage();
                        break;
                }
            }
        });

        function showLightbox(image, index) {
            const lightboxContent = `
                <div class="lightbox-image-container">
                    <img src="${image.url}" alt="${image.alt || 'Gallery Image'}" class="img-fluid">
                    <div class="lightbox-controls">
                        <button class="lightbox-prev" onclick="showPreviousImage()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="lightbox-next" onclick="showNextImage()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="lightbox-counter">
                        ${index + 1} / ${galleryImages.length}
                    </div>
                </div>
            `;
            
            $('#lightbox-gallery').html(lightboxContent);
            $('#property-lightbox').addClass('show');
            $('body').addClass('modal-open');
        }

        function hideLightbox() {
            $('#property-lightbox').removeClass('show');
            $('body').removeClass('modal-open');
        }

    function showPreviousImage() {
        currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
            showLightbox(galleryImages[currentImageIndex], currentImageIndex);
    }

    function showNextImage() {
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            showLightbox(galleryImages[currentImageIndex], currentImageIndex);
        }

        // Make functions globally available
        window.showPreviousImage = showPreviousImage;
        window.showNextImage = showNextImage;
    }

    /**
     * Map functionality is handled by the PHP template
     * This function is kept for compatibility but does nothing
     */
    function initializeMap() {
        // Map initialization is handled in the PHP template
        // This prevents conflicts with the existing map implementation
        console.log('Map initialization handled by PHP template');
        
        // Additional safety check - ensure map container exists
        const mapContainer = document.getElementById('restaurant-map');
        if (!mapContainer) {
            console.warn('Map container not found - map initialization may fail');
        }
    }

    /**
     * Initialize form functionality
     */
    function initializeForms() {
        // Contact form submission
        $('.property-form form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            
            // Show loading state
            submitBtn.addClass('loading').prop('disabled', true);
            
            // Simulate form submission (replace with actual AJAX call)
            setTimeout(function() {
                // Reset form
                form[0].reset();
                
                // Hide loading state
                submitBtn.removeClass('loading').prop('disabled', false);
                
                // Show success message
                showNotification('Message envoyé avec succès!', 'success');
            }, 2000);
        });

        // Review form submission
        $('#property-review-form form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('#submit-review');
            
            // Show loading state
            submitBtn.addClass('loading').prop('disabled', true);
            
            // Simulate form submission (replace with actual AJAX call)
            setTimeout(function() {
                // Reset form
                form[0].reset();
                
                // Hide loading state
                submitBtn.removeClass('loading').prop('disabled', false);
                
                // Show success message
                showNotification('Avis posté avec succès!', 'success');
            }, 2000);
        });

        // Form validation
        $('.form-control').on('blur', function() {
            validateField($(this));
        });

        function validateField(field) {
            const value = field.val().trim();
            const fieldName = field.attr('name');
            let isValid = true;
            let errorMessage = '';

            // Remove existing error styling
            field.removeClass('is-invalid');
            field.siblings('.invalid-feedback').remove();

            // Validation rules
            switch(fieldName) {
                case 'email':
                case 'review_email':
                    if (value && !isValidEmail(value)) {
                        isValid = false;
                        errorMessage = 'Veuillez entrer une adresse email valide';
                    }
                    break;
                case 'mobile':
                    if (value && !isValidPhone(value)) {
                        isValid = false;
                        errorMessage = 'Veuillez entrer un numéro de téléphone valide';
                    }
                    break;
                case 'name':
                    if (value && value.length < 2) {
                        isValid = false;
                        errorMessage = 'Le nom doit contenir au moins 2 caractères';
                    }
                    break;
            }

            if (!isValid) {
                field.addClass('is-invalid');
                field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
            }

            return isValid;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidPhone(phone) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
            return phoneRegex.test(phone);
        }
    }

    /**
     * Initialize modal functionality
     */
    function initializeModals() {
        // Dropdown functionality
        $('.dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = $(this).siblings('.dropdown-menu');
            
            // Close other dropdowns
            $('.dropdown-menu').not(dropdown).removeClass('show');
            
            // Toggle current dropdown
            dropdown.toggleClass('show');
        });

        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.item-tool').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notification notification-${type}">
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);

        // Add to body
        $('body').append(notification);

        // Show notification
        setTimeout(() => notification.addClass('show'), 100);

        // Auto hide after 5 seconds
        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);

        // Close button
        notification.find('.notification-close').on('click', function() {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 300);
        });
    }

    /**
     * Initialize social sharing
     */
    function initializeSocialSharing() {
        // WhatsApp sharing
        $('.dropdown-item[href*="whatsapp"]').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            window.open(url, '_blank');
        });

        // Other social sharing
        $('.dropdown-item:not([href*="whatsapp"]):not([href*="mailto"])').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            window.open(url, 'mywin', 'left=50,top=50,width=600,height=350,toolbar=0');
        });
    }

    // Initialize social sharing
    initializeSocialSharing();

    // Map initialization is handled by the PHP template
    // No additional JavaScript map handling needed

    // Expose functions globally
    window.lebonrestoDetail = {
        showNotification: showNotification,
        initializePropertyDetail: initializePropertyDetail,
        initializeMap: initializeMap
    };

})(jQuery);

// Additional CSS for notifications
const notificationCSS = `
<style>
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 1rem;
    z-index: 10000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 300px;
}

.notification.show {
    transform: translateX(0);
}

.notification-success {
    border-left: 4px solid #10b981;
}

.notification-info {
    border-left: 4px solid #3b82f6;
}

.notification-error {
    border-left: 4px solid #ef4444;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #374151;
}

.notification-close {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.25rem;
}

.notification-close:hover {
    color: #6b7280;
}

.lightbox-image-container {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
}

.lightbox-image-container img {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
}

.lightbox-controls {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
}

.lightbox-prev,
.lightbox-next {
    background: rgba(0,0,0,0.5);
    border: none;
    color: white;
    padding: 1rem;
    border-radius: 50%;
    cursor: pointer;
    pointer-events: auto;
    transition: background 0.3s ease;
}

.lightbox-prev:hover,
.lightbox-next:hover {
    background: rgba(0,0,0,0.7);
}

.lightbox-prev {
    margin-left: 1rem;
}

.lightbox-next {
    margin-right: 1rem;
}

.lightbox-counter {
    position: absolute;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
}

.back-top.show {
    opacity: 1;
    visibility: visible;
}

.back-top {
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.form-control.is-invalid {
    border-color: #ef4444;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #ef4444;
}
</style>
`;

// Inject notification CSS
document.head.insertAdjacentHTML('beforeend', notificationCSS);