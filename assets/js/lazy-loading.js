/**
 * Lazy Loading Implementation for Le Bon Resto Plugin
 * 
 * @package LeBonResto
 */

(function() {
    'use strict';
    
    class LazyLoader {
        constructor() {
            this.observer = null;
            this.images = [];
            this.init();
        }
        
        init() {
            // Check if Intersection Observer is supported
            if ('IntersectionObserver' in window) {
                this.initIntersectionObserver();
            } else {
                // Fallback for older browsers
                this.initFallback();
            }
            
            // Handle dynamic content
            this.observeDynamicContent();
        }
        
        initIntersectionObserver() {
            const options = {
                root: null,
                rootMargin: '50px',
                threshold: 0.1
            };
            
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        this.observer.unobserve(entry.target);
                    }
                });
            }, options);
            
            // Observe all lazy images
            this.observeImages();
        }
        
        initFallback() {
            // Load all images immediately for older browsers
            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => this.loadImage(img));
        }
        
        observeImages() {
            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => {
                this.observer.observe(img);
            });
        }
        
        loadImage(img) {
            if (img.dataset.src) {
                // Create a new image to preload
                const imageLoader = new Image();
                
                imageLoader.onload = () => {
                    img.src = img.dataset.src;
                    img.classList.add('lazy-loaded');
                    img.classList.remove('lazy-loading');
                    
                    // Remove data-src attribute
                    delete img.dataset.src;
                    
                    // Trigger custom event
                    img.dispatchEvent(new CustomEvent('lazyLoaded', {
                        detail: { image: img }
                    }));
                };
                
                imageLoader.onerror = () => {
                    img.classList.add('lazy-error');
                    img.classList.remove('lazy-loading');
                    
                    // Show placeholder or error image
                    this.showErrorImage(img);
                };
                
                // Add loading class
                img.classList.add('lazy-loading');
                
                // Start loading
                imageLoader.src = img.dataset.src;
            }
        }
        
        showErrorImage(img) {
            // Create a placeholder or use a default error image
            const placeholder = document.createElement('div');
            placeholder.className = 'lazy-placeholder';
            placeholder.innerHTML = `
                <div class="lazy-error-content">
                    <i class="fas fa-image"></i>
                    <p>Image non disponible</p>
                </div>
            `;
            
            img.parentNode.replaceChild(placeholder, img);
        }
        
        observeDynamicContent() {
            // Use MutationObserver to watch for dynamically added content
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            const lazyImages = node.querySelectorAll ? 
                                node.querySelectorAll('img[data-src]') : [];
                            
                            lazyImages.forEach(img => {
                                if (this.observer) {
                                    this.observer.observe(img);
                                } else {
                                    this.loadImage(img);
                                }
                            });
                        }
                    });
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
        
        // Public method to manually load an image
        loadImageNow(img) {
            this.loadImage(img);
        }
        
        // Public method to refresh lazy loading
        refresh() {
            if (this.observer) {
                this.observer.disconnect();
                this.observeImages();
            }
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.lazyLoader = new LazyLoader();
        });
    } else {
        window.lazyLoader = new LazyLoader();
    }
    
    // Add CSS for lazy loading states
    const style = document.createElement('style');
    style.textContent = `
        .lazy-loading {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .lazy-loaded {
            opacity: 1;
        }
        
        .lazy-placeholder {
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
            border-radius: 8px;
        }
        
        .lazy-error-content {
            text-align: center;
            color: #666;
        }
        
        .lazy-error-content i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .lazy-error-content p {
            margin: 0;
            font-size: 0.9rem;
        }
        
        /* Restaurant card specific styles */
        .restaurant-card .lazy-loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }
    `;
    document.head.appendChild(style);
    
})();
