(function($) {
    "use strict";

    // Gallery lightbox functionality
    let currentImageIndex = 0;
    let galleryImages = [];

    // Initialize when document is ready
    $(document).ready(function() {
        initializeGalleryLightbox();
    });

    /**
     * Initialize gallery lightbox functionality
     */
    function initializeGalleryLightbox() {
        // Get gallery data
        const galleryDataElement = document.getElementById('gallery-data');
        if (galleryDataElement) {
            try {
                galleryImages = JSON.parse(galleryDataElement.textContent);
                console.log('Images de galerie chargées:', galleryImages.length);
            } catch (error) {
                console.error('Erreur lors de l\'analyse des données de galerie:', error);
                galleryImages = [];
            }
        }

        // Add click handlers to gallery items
        $('.gallery-item').on('click', function() {
            const index = parseInt($(this).data('index'));
            openLightbox(index);
        });

        // Add click handlers to lightbox controls
        $('#close-lightbox').on('click', closeLightbox);
        $('#prev-image').on('click', showPreviousImage);
        $('#next-image').on('click', showNextImage);

        // Close lightbox when clicking outside image
        $('#gallery-lightbox').on('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        // Keyboard navigation
        $(document).on('keydown', function(e) {
            if ($('#gallery-lightbox').hasClass('show')) {
                switch(e.key) {
                    case 'Escape':
                        closeLightbox();
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
    }

    /**
     * Open lightbox with specific image
     */
    function openLightbox(index) {
        if (galleryImages.length === 0) return;
        
        currentImageIndex = index;
        updateLightboxImage();
        $('#gallery-lightbox').addClass('show').removeClass('hidden');
        $('body').addClass('overflow-hidden');
    }

    /**
     * Close lightbox
     */
    function closeLightbox() {
        $('#gallery-lightbox').removeClass('show').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    }

    /**
     * Show previous image
     */
    function showPreviousImage() {
        if (galleryImages.length === 0) return;
        
        currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
        updateLightboxImage();
    }

    /**
     * Show next image
     */
    function showNextImage() {
        if (galleryImages.length === 0) return;
        
        currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
        updateLightboxImage();
    }

    /**
     * Update lightbox image and counter
     */
    function updateLightboxImage() {
        if (galleryImages.length === 0) return;
        
        const image = galleryImages[currentImageIndex];
        if (image) {
            $('#lightbox-image').attr('src', image.url).attr('alt', image.alt || 'Image de galerie');
            $('#image-counter').text(`${currentImageIndex + 1} / ${galleryImages.length}`);
        }
    }

    /**
     * Smooth scroll to sections
     */
    function smoothScrollToSection(sectionId) {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    /**
     * Initialize scroll animations
     */
    function initializeScrollAnimations() {
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards
        $('.bg-white').each(function() {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(30px)',
                'transition': 'opacity 0.6s ease, transform 0.6s ease'
            });
            observer.observe(this);
        });
    }

    /**
     * Initialize lazy loading for images
     */
    function initializeLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            $('.gallery-item img').each(function() {
                imageObserver.observe(this);
            });
        }
    }

    /**
     * Initialize all functionality
     */
    function initializeAll() {
        initializeGalleryLightbox();
        initializeScrollAnimations();
        initializeLazyLoading();
        
        console.log('Page de détail du restaurant initialisée');
    }

    // Initialize when document is ready
    $(document).ready(initializeAll);

})(jQuery);
