/**
 * WebP Support Detection and Fallback
 * 
 * @package LeBonResto
 */

(function() {
    'use strict';
    
    class WebPSupport {
        constructor() {
            this.supportsWebP = false;
            this.init();
        }
        
        init() {
            this.checkWebPSupport();
            this.optimizeImages();
        }
        
        checkWebPSupport() {
            // Check if WebP is supported
            const canvas = document.createElement('canvas');
            canvas.width = 1;
            canvas.height = 1;
            
            this.supportsWebP = canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
            
            // Add class to body for CSS targeting
            if (this.supportsWebP) {
                document.body.classList.add('webp-supported');
            } else {
                document.body.classList.add('webp-not-supported');
            }
        }
        
        optimizeImages() {
            const images = document.querySelectorAll('img[data-webp="true"]');
            
            images.forEach(img => {
                if (this.supportsWebP) {
                    this.convertToWebP(img);
                } else {
                    this.ensureFallback(img);
                }
            });
        }
        
        convertToWebP(img) {
            const src = img.src;
            const webpSrc = src.replace(/\.(jpg|jpeg|png)$/i, '.webp');
            
            // Check if WebP version exists
            this.checkImageExists(webpSrc).then(exists => {
                if (exists) {
                    img.src = webpSrc;
                    img.classList.add('webp-loaded');
                }
            });
        }
        
        ensureFallback(img) {
            // Ensure fallback image is loaded
            img.classList.add('fallback-loaded');
        }
        
        checkImageExists(url) {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => resolve(true);
                img.onerror = () => resolve(false);
                img.src = url;
            });
        }
        
        // Public method to convert specific image
        convertImageToWebP(img) {
            if (this.supportsWebP) {
                this.convertToWebP(img);
            }
        }
        
        // Public method to check support
        isWebPSupported() {
            return this.supportsWebP;
        }
    }
    
    // Initialize WebP support
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.webpSupport = new WebPSupport();
        });
    } else {
        window.webpSupport = new WebPSupport();
    }
    
    // Add CSS for WebP support
    const style = document.createElement('style');
    style.textContent = `
        /* WebP Support Styles */
        .webp-supported img[data-webp="true"] {
            transition: opacity 0.3s ease;
        }
        
        .webp-supported img[data-webp="true"]:not(.webp-loaded) {
            opacity: 0.8;
        }
        
        .webp-supported img[data-webp="true"].webp-loaded {
            opacity: 1;
        }
        
        /* Fallback styles */
        .webp-not-supported img[data-webp="true"] {
            opacity: 1;
        }
        
        
        img[data-webp="true"].webp-loaded,
        img[data-webp="true"].fallback-loaded {
            background: none;
            animation: none;
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
