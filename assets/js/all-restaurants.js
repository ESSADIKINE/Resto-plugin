/**
 * All Restaurants Redesigned - TripAdvisor-Inspired JavaScript
 * Enhanced functionality with modern UI patterns
 */

(function($) {
    'use strict';

    // Global state
    let allRestaurants = [];
    let filteredRestaurants = [];
    let currentFilters = {};
    let currentSort = 'featured';
    let currentPage = 1;
    let itemsPerPage = 20;
    let isLoading = false;
    let hasMoreResults = true;
    let userLocation = null;
    let googleMapsLoaded = false;

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('All Restaurants Redesigned: Initializing...');
        initializeApp();
    });

    /**
     * Initialize the application
     */
    function initializeApp() {
        initializeFilters();
        initializeSort();
        initializeMobileFilters();
        initializeGeolocation();
        loadGoogleMapsAPI();
        loadRestaurants();
        
        console.log('All Restaurants Redesigned: Initialized successfully');
    }

    /**
     * Initialize filter functionality
     */
    function initializeFilters() {
        // Filter toggle functionality
        $('.filter-toggle').on('click', function() {
            const $this = $(this);
            const $content = $this.closest('.filter-group').find('.filter-content');
            const isExpanded = $this.attr('aria-expanded') === 'true';
            
            $this.attr('aria-expanded', !isExpanded);
            
            if (isExpanded) {
                $content.slideUp(200);
            } else {
                $content.slideDown(200);
            }
        });

        // Search input with debounce
        let searchTimeout;
        $('#restaurant-search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });
        
        // Filter option changes
        $('.filter-checkbox, .filter-radio').on('change', function() {
            applyFilters();
        });
        
        // Price input changes
        $('#min-price, #max-price').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });

        // Show more/less functionality
        $('.show-more-btn').on('click', function() {
            const $this = $(this);
            const $options = $this.closest('.filter-content').find('.filter-options');
            const isExpanded = $this.attr('aria-expanded') === 'true';
            
            if (isExpanded) {
                $options.find('.filter-option:nth-child(n+5)').slideUp(200);
                $this.find('.show-more-text').text(lebonrestoAll.strings.showMore);
                $this.attr('aria-expanded', 'false');
            } else {
                $options.find('.filter-option:nth-child(n+5)').slideDown(200);
                $this.find('.show-more-text').text(lebonrestoAll.strings.showLess);
                $this.attr('aria-expanded', 'true');
            }
        });

        // Clear filters
        $('.clear-filters-btn').on('click', function() {
            clearAllFilters();
        });
        
        // Distance filter click (request location if needed)
        $('input[name="distance"]').on('change', function() {
            if ($(this).is(':checked') && !userLocation) {
                requestLocation();
            } else {
                applyFilters();
            }
        });

        // Initially hide extra options
        $('.filter-options .filter-option:nth-child(n+5)').hide();
    }

    /**
     * Initialize sorting functionality
     */
    function initializeSort() {
        // Sort dropdown toggle
        $('.sort-dropdown-btn').on('click', function(e) {
            e.stopPropagation();
            const $this = $(this);
            const isExpanded = $this.attr('aria-expanded') === 'true';
            
            $this.attr('aria-expanded', !isExpanded);
            
            if (!isExpanded) {
                // Close dropdown when clicking outside
                $(document).one('click', function() {
                    $this.attr('aria-expanded', 'false');
                });
            }
        });

        // Sort option selection
        $('.sort-option').on('click', function() {
            const $this = $(this);
            const sortValue = $this.data('sort');
            const sortText = $this.text();
            
            // Update UI
            $('.sort-option').removeClass('active');
            $this.addClass('active');
            $('.sort-text').text(sortText);
            $('.sort-dropdown-btn').attr('aria-expanded', 'false');
            
            // Apply sort
            currentSort = sortValue;
            currentPage = 1;
            applyFilters();
        });
    }


    /**
     * Initialize mobile filters
     */
    function initializeMobileFilters() {
        $('#mobile-filter-btn').on('click', function() {
            showMobileFilters();
        });

        $('#mobile-filter-overlay').on('click', function() {
            hideMobileFilters();
        });
    }

    /**
     * Initialize geolocation for distance filtering
     */
    function initializeGeolocation() {
        const $locationStatus = $('#location-status');
        const $distanceOptions = $('.distance-option');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    $locationStatus.removeClass('disabled').addClass('enabled');
                    $locationStatus.find('.location-text').text('Position détectée');
                    $distanceOptions.addClass('enabled');
                    
                    console.log('User location detected:', userLocation);
                },
                function(error) {
                    console.log('Geolocation error:', error);
                    $locationStatus.removeClass('enabled').addClass('disabled');
                    $locationStatus.find('.location-text').text('Position non disponible - Cliquez pour activer');
                    
                    // Make location status clickable to retry
                    $locationStatus.css('cursor', 'pointer').on('click', requestLocation);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        } else {
            $locationStatus.removeClass('enabled').addClass('disabled');
            $locationStatus.find('.location-text').text('Géolocalisation non supportée');
        }
    }

    /**
     * Request user location
     */
    function requestLocation() {
        const $locationStatus = $('#location-status');
        const $distanceOptions = $('.distance-option');
        
        $locationStatus.find('.location-text').text('Demande d\'autorisation...');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    $locationStatus.removeClass('disabled').addClass('enabled');
                    $locationStatus.find('.location-text').text('Position détectée');
                    $distanceOptions.addClass('enabled');
                    $locationStatus.off('click');
                    
                    console.log('User location granted:', userLocation);
                    
                    // Apply filters if distance is selected
                    if ($('input[name="distance"]:checked').length > 0) {
                        applyFilters();
                    }
                },
                function(error) {
                    console.log('Location permission denied:', error);
                    $locationStatus.removeClass('enabled').addClass('disabled');
                    $locationStatus.find('.location-text').text('Permission refusée - Cliquez pour réessayer');
                }
            );
        }
    }

    /**
     * Load Google Maps API
     */
    function loadGoogleMapsAPI() {
        if (window.google && window.google.maps) {
            googleMapsLoaded = true;
            return;
        }
        
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${lebonrestoAll.settings.googleApiKey || 'AIzaSyDXSSijLxRtL9tz7FbYqvnB3eWwTojpNlI'}&libraries=places`;
        script.async = true;
        script.defer = true;
        script.onload = function() {
            googleMapsLoaded = true;
            console.log('Google Maps API loaded successfully');
        };
        script.onerror = function() {
            console.error('Failed to load Google Maps API');
        };
        document.head.appendChild(script);
    }

    /**
     * Load restaurants from API
     */
    function loadRestaurants(append = false) {
        if (isLoading) return;
        
        showLoadingState(!append);
        isLoading = true;

        const queryParams = new URLSearchParams({
            per_page: 100, // Load all restaurants for client-side filtering
            page: 1
        });

        const apiUrl = `${lebonrestoAll.apiUrl}?${queryParams.toString()}`;
        
        console.log('Loading restaurants from:', apiUrl);

        fetch(apiUrl, {
            headers: {
                'X-WP-Nonce': lebonrestoAll.nonce
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(restaurants => {
            allRestaurants = Array.isArray(restaurants) ? restaurants : [];
            console.log(`Loaded ${allRestaurants.length} restaurants`);
            
            applyFilters();
        })
        .catch(error => {
            console.error('Error loading restaurants:', error);
            showErrorState();
        })
        .finally(() => {
            isLoading = false;
            hideLoadingState();
        });
    }

    /**
     * Apply current filters and sorting
     */
    function applyFilters() {
        console.log('Applying filters...');
        
        // Start with all restaurants
        filteredRestaurants = [...allRestaurants];
        
        // Build current filters
        buildCurrentFilters();
        
        // Apply filters
        filteredRestaurants = filterRestaurants(filteredRestaurants, currentFilters);
        
        // Apply sorting
        filteredRestaurants = sortRestaurants(filteredRestaurants, currentSort);
        
        // Reset pagination
        currentPage = 1;
        hasMoreResults = filteredRestaurants.length > itemsPerPage;
        
        // Update UI
        updateResultsCount();
        displayRestaurants();
        
        console.log(`Filtered to ${filteredRestaurants.length} restaurants`);
    }

    /**
     * Build current filters object from UI
     */
    function buildCurrentFilters() {
        currentFilters = {};

        // Search by name
        const searchTerm = $('#restaurant-search').val().trim();
        if (searchTerm) {
            currentFilters.search = searchTerm;
        }

        // Distance filter
        const selectedDistance = $('input[name="distance"]:checked').val();
        if (selectedDistance && userLocation) {
            currentFilters.distance = parseFloat(selectedDistance);
            currentFilters.userLat = userLocation.lat;
            currentFilters.userLng = userLocation.lng;
        }

        // Price range (custom inputs)
        const minPrice = $('#min-price').val();
        const maxPrice = $('#max-price').val();
        if (minPrice || maxPrice) {
            currentFilters.priceRange = {
                min: minPrice ? parseFloat(minPrice) : 0,
                max: maxPrice ? parseFloat(maxPrice) : Infinity
            };
        }

        // Price categories
        const priceCategories = [];
        $('input[name="price_category"]:checked').each(function() {
            priceCategories.push($(this).val());
        });
        if (priceCategories.length > 0) {
            currentFilters.price_category = priceCategories;
        }


        // Cuisine type
        const cuisineTypes = [];
        $('input[name="cuisine_type"]:checked').each(function() {
            cuisineTypes.push($(this).val());
        });
        if (cuisineTypes.length > 0) {
            currentFilters.cuisine_type = cuisineTypes;
        }

    }

    /**
     * Filter restaurants based on criteria
     */
    function filterRestaurants(restaurants, filters) {
        return restaurants.filter(restaurant => {
            const meta = restaurant.restaurant_meta || {};
            const title = restaurant.title?.rendered || '';
            
            // Search by name filter
            if (filters.search) {
                const searchTerm = filters.search.toLowerCase();
                if (!title.toLowerCase().includes(searchTerm) && 
                    !(meta.description || '').toLowerCase().includes(searchTerm) &&
                    !(meta.city || '').toLowerCase().includes(searchTerm)) {
                    return false;
                }
            }
            
            // Distance filter
            if (filters.distance && filters.userLat && filters.userLng) {
                const restaurantLat = parseFloat(meta.latitude);
                const restaurantLng = parseFloat(meta.longitude);
                
                if (restaurantLat && restaurantLng) {
                    const distance = calculateDistance(
                        filters.userLat, filters.userLng,
                        restaurantLat, restaurantLng
                    );
                    
                    if (distance > filters.distance) {
                        return false;
                    }
                    
                    // Add distance to restaurant object for display
                    restaurant.calculatedDistance = distance;
                }
            }
            
            // Price range filter (custom inputs)
            if (filters.priceRange) {
                const minPrice = parseFloat(meta.min_price) || 0;
                const maxPrice = parseFloat(meta.max_price) || 0;
                const avgPrice = maxPrice > 0 ? (minPrice + maxPrice) / 2 : minPrice;
                
                if (avgPrice < filters.priceRange.min || avgPrice > filters.priceRange.max) {
                    return false;
                }
            }
            
            // Price category filter
            if (filters.price_category && filters.price_category.length > 0) {
                const minPrice = parseFloat(meta.min_price) || 0;
                const maxPrice = parseFloat(meta.max_price) || 0;
                const avgPrice = maxPrice > 0 ? (minPrice + maxPrice) / 2 : minPrice;
                
                let matchesCategory = false;
                filters.price_category.forEach(category => {
                    switch (category) {
                        case 'budget':
                            if (avgPrice <= 100) matchesCategory = true;
                            break;
                        case 'moderate':
                            if (avgPrice > 100 && avgPrice <= 300) matchesCategory = true;
                            break;
                        case 'expensive':
                            if (avgPrice > 300 && avgPrice <= 500) matchesCategory = true;
                            break;
                        case 'very_expensive':
                            if (avgPrice > 500) matchesCategory = true;
                            break;
                    }
                });
                
                if (!matchesCategory) {
                    return false;
                }
            }
            
            
            // Cuisine type filter
            if (filters.cuisine_type && filters.cuisine_type.length > 0) {
                const cuisineType = meta.cuisine_type;
                if (!cuisineType || !filters.cuisine_type.some(type => 
                    cuisineType.toLowerCase().includes(type.toLowerCase())
                )) {
                    return false;
                }
            }
            
            
            return true;
        });
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in kilometers
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    /**
     * Sort restaurants based on criteria
     */
    function sortRestaurants(restaurants, sortBy) {
        const sorted = [...restaurants];
        
        switch (sortBy) {
            case 'featured':
                return sorted.sort((a, b) => {
                    const aFeatured = a.restaurant_meta?.is_featured === '1' ? 1 : 0;
                    const bFeatured = b.restaurant_meta?.is_featured === '1' ? 1 : 0;
                    return bFeatured - aFeatured;
                });
                
            case 'rating':
                return sorted.sort((a, b) => {
                    const aRating = parseFloat(a.restaurant_meta?.average_rating) || 0;
                    const bRating = parseFloat(b.restaurant_meta?.average_rating) || 0;
                    return bRating - aRating;
                });
                
            case 'reviews':
                return sorted.sort((a, b) => {
                    const aReviews = parseInt(a.restaurant_meta?.review_count) || 0;
                    const bReviews = parseInt(b.restaurant_meta?.review_count) || 0;
                    return bReviews - aReviews;
                });
                
            case 'name':
                return sorted.sort((a, b) => {
                    const aName = a.title?.rendered || '';
                    const bName = b.title?.rendered || '';
                    return aName.localeCompare(bName);
                });
                
            case 'distance':
                return sorted.sort((a, b) => {
                    const distanceA = a.calculatedDistance || Infinity;
                    const distanceB = b.calculatedDistance || Infinity;
                    return distanceA - distanceB;
                });
                
            case 'price_low':
                return sorted.sort((a, b) => {
                    const priceA = parseFloat(a.restaurant_meta?.min_price) || 0;
                    const priceB = parseFloat(b.restaurant_meta?.min_price) || 0;
                    return priceA - priceB;
                });
                
            case 'price_high':
                return sorted.sort((a, b) => {
                    const priceA = parseFloat(a.restaurant_meta?.max_price) || 0;
                    const priceB = parseFloat(b.restaurant_meta?.max_price) || 0;
                    return priceB - priceA;
                });
                
            default:
                return sorted;
        }
    }

    /**
     * Display restaurants in the grid
     */
    function displayRestaurants() {
        const $container = $('#restaurants-list');
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const restaurantsToShow = filteredRestaurants.slice(0, endIndex);
        
        if (restaurantsToShow.length === 0) {
            showNoResults();
            return;
        }
        
        // Clear container
        $container.empty();
        
        // Add restaurants with staggered animation
        restaurantsToShow.forEach((restaurant, index) => {
            const $card = createRestaurantCard(restaurant, startIndex + index + 1);
            $card.css({
                opacity: 0,
                transform: 'translateY(20px)'
            });
            $container.append($card);
            
            // Animate in with delay
            setTimeout(() => {
                $card.animate({
                    opacity: 1,
                    transform: 'translateY(0)'
                }, 300);
            }, index * 50);
        });
        
        // Update load more button
        updateLoadMoreButton();
    }

    /**
     * Create a restaurant card element
     */
    function createRestaurantCard(restaurant, ranking) {
        const meta = restaurant.restaurant_meta || {};
        const title = restaurant.title?.rendered || 'Restaurant';
        
        // Use Google rating if available, fallback to local rating
        const googleRating = parseFloat(meta.google_rating) || 0;
        const localRating = parseFloat(meta.average_rating) || 0;
        const rating = googleRating > 0 ? googleRating : localRating;
        
        // Use Google review count if available, fallback to local count
        const googleReviewCount = parseInt(meta.google_review_count) || 0;
        const localReviewCount = parseInt(meta.review_count) || 0;
        const reviewCount = googleReviewCount > 0 ? googleReviewCount : localReviewCount;
        
        // Get Google reviews if available (from API reviews stored in database)
        let googleReviews = meta.google_api_reviews || [];
        
        
        const cuisineType = meta.cuisine_type || '';
        const isOpen = meta.is_open === '1';
        const isFeatured = meta.is_featured === '1';
        const link = restaurant.link || '#';
        
        // Get Google opening hours data
        const openingHours = meta.google_opening_hours || {};
        const currentOpeningHours = meta.google_current_opening_hours || {};
        
        // Determine if restaurant is currently open
        let isCurrentlyOpen = false;
        let statusText = 'Fermé';
        let statusClass = 'status-closed';
        
        if (currentOpeningHours.open_now !== undefined) {
            isCurrentlyOpen = currentOpeningHours.open_now;
            statusText = isCurrentlyOpen ? 'Ouvert' : 'Fermé';
            statusClass = isCurrentlyOpen ? 'status-open' : 'status-closed';
        } else if (openingHours.open_now !== undefined) {
            isCurrentlyOpen = openingHours.open_now;
            statusText = isCurrentlyOpen ? 'Ouvert' : 'Fermé';
            statusClass = isCurrentlyOpen ? 'status-open' : 'status-closed';
        }
        
        // Build Google Maps URL (prefer place_id, then lat/lng, then address)
        const placeId = meta.google_place_id;
        const latitude = parseFloat(meta.latitude);
        const longitude = parseFloat(meta.longitude);
        const address = meta.address || '';
        let mapsUrl = '';
        if (placeId) {
            mapsUrl = `https://www.google.com/maps/search/?api=1&query_place_id=${encodeURIComponent(placeId)}&query=${encodeURIComponent(title)}`;
        } else if (latitude && longitude) {
            mapsUrl = `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`;
        } else if (address) {
            mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
        }
        
        // Calculate price range
        const minPrice = parseFloat(meta.min_price) || 0;
        const maxPrice = parseFloat(meta.max_price) || 0;
        const priceRange = getPriceRangeDisplay(minPrice, maxPrice);
        const priceSymbols = getPriceRangeSymbol(minPrice, maxPrice);
        
        // Distance display
        const distance = restaurant.calculatedDistance;
        
        // Get primary image
        let imageUrl = 'data:image/svg+xml;base64,' + btoa(`
            <svg width="240" height="160" xmlns="http://www.w3.org/2000/svg">
                <rect width="100%" height="100%" fill="#f3f4f6"/>
                <text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#9ca3af" font-family="Arial" font-size="14">${title}</text>
            </svg>
        `);
        
        if (meta.principal_image && meta.principal_image.full) {
            imageUrl = meta.principal_image.full;
        } else if (meta.gallery_images && meta.gallery_images.length > 0) {
            imageUrl = meta.gallery_images[0].full;
        }

        const $card = $(`
            <div class="restaurant-card" data-restaurant-id="${restaurant.id}">
                <div class="card-layout">
                    <div class="card-image">
                        <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(title)}" class="restaurant-image" loading="lazy">
                        <div class="image-overlay">
                            <button class="save-btn" aria-label="Ouvrir la carte">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.25 9.799c0-4.247 3.488-7.707 7.75-7.707s7.75 3.46 7.75 7.707c0 2.28-1.138 4.477-2.471 6.323-1.31 1.813-2.883 3.388-3.977 4.483l-.083.083-.002.002-1.225 1.218-1.213-1.243-.03-.03-.012-.013c-1.1-1.092-2.705-2.687-4.035-4.53-1.324-1.838-2.452-4.024-2.452-6.293M12 3.592c-3.442 0-6.25 2.797-6.25 6.207 0 1.796.907 3.665 2.17 5.415 1.252 1.736 2.778 3.256 3.886 4.357l.043.042.16.164.148-.149.002-.002.061-.06c1.103-1.105 2.605-2.608 3.843-4.322 1.271-1.76 2.187-3.64 2.187-5.445 0-3.41-2.808-6.207-6.25-6.207m1.699 5.013a1.838 1.838 0 1 0-3.397 1.407A1.838 1.838 0 0 0 13.7 8.605m-2.976-2.38a3.338 3.338 0 1 1 2.555 6.168 3.338 3.338 0 0 1-2.555-6.169"></path>
                                </svg>
                            </button>
                            ${meta.virtual_tour_url ? `
                                <button class="vr-btn" aria-label="Visite virtuelle">
                                    <svg viewBox="0 0 24 24" width="16" height="16">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM7 9c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm5 9c-4 0-6-3-6-3s2-3 6-3 6 3 6 3-2 3-6 3zm5-9c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1z"/>
                                    </svg>
                                </button>
                            ` : ''}
                            ${isFeatured ? `
                                <div class="award-badge">
                                    <svg viewBox="0 0 24 24" width="16" height="16">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="#fedc00"/>
                                    </svg>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="card-content">
                        <div class="restaurant-header">
                            <div class="restaurant-info">
                                <h3 class="restaurant-name">
                                    <a href="${escapeHtml(link)}">${escapeHtml(title)}</a>
                                </h3>
                                
                                <div class="rating-section">
                                    ${rating > 0 ? `
                                        <span class="rating-value">${rating.toFixed(1)}</span>
                                        <div class="rating-bubbles">
                                            ${generateRatingBubbles(rating)}
                                        </div>
                                        ${reviewCount > 0 ? `
                                            <a href="${escapeHtml(link)}#reviews" class="review-count">(${reviewCount} avis Google)</a>
                                        ` : ''}
                                    ` : ''}
                                </div>
                                
                                
                            </div>
                        </div>
                        
                        <div class="restaurant-details">
                            <div class="detail-row">
                                <svg viewBox="0 0 24 24" width="16" height="16" class="detail-icon">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.051 6.549v.003l1.134 1.14 3.241-3.25.003-.002 1.134 1.136-3.243 3.252 1.134 1.14a1 1 0 0 0 .09-.008c.293-.05.573-.324.72-.474l.005-.006 2.596-2.603L22 8.016l-2.597 2.604a3.73 3.73 0 0 1-1.982 1.015 4.3 4.3 0 0 1-3.162-.657l-.023-.016-.026-.018-1.366 1.407 8.509 8.512L20.219 22l-.002-.002-6.654-6.663-2.597 2.76-7.3-7.315C1.967 8.948 1.531 6.274 2.524 4.198c.241-.504.566-.973.978-1.386l8.154 8.416 1.418-1.423-.039-.045c-.858-1.002-1.048-2.368-.62-3.595a4.15 4.15 0 0 1 .983-1.561L16 2l1.135 1.138-2.598 2.602-.047.045c-.16.151-.394.374-.433.678zM3.809 5.523c-.362 1.319-.037 2.905 1.06 4.103L10.93 15.7l1.408-1.496zM2.205 20.697 3.34 21.84l4.543-4.552-1.135-1.143z"></path>
                                </svg>
                                <div class="cuisine-price">
                                    <span>${escapeHtml(cuisineType)}</span>
                                    ${priceRange ? `<span class="price-range">${priceRange}</span>` : ''}
                                </div>
                            </div>
                            
                            <a ${mapsUrl ? `href="${mapsUrl}" target="_blank" rel="noopener"` : ''} class="detail-row" ${mapsUrl ? '' : 'style="pointer-events: none;"'}>
                                <svg viewBox="0 0 24 24" width="16" height="16" class="detail-icon">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 6.75c0-.414.336-.75.75-.75h.243l4.716 1.886 5.5-2.2.051-.02H15c.081 0 .161.013.236.038l4.514 1.505c.304.101.5.384.5.702v9.75a.75.75 0 0 1-.514.712l-4.486 1.495a.75.75 0 0 1-.472 0l-5.528-1.992-4.756 1.902A.75.75 0 0 1 3.5 18.75zM9 7.89v9.22l5 1.8V9.69zM8 7.89 5 6.75v9.36l3-.12zm11 1.16-3-1v9.36l3-1.02z"></path>
                                </svg>
                                <span>Voir sur Google Maps</span>
                            </a>
                            
                            
                            ${meta.city ? `
                                <div class="detail-row">
                                    <svg viewBox="0 0 24 24" width="16" height="16" class="detail-icon">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.25 9.799c0-4.247 3.488-7.707 7.75-7.707s7.75 3.46 7.75 7.707c0 2.28-1.138 4.477-2.471 6.323-1.31 1.813-2.883 3.388-3.977 4.483l-.083.083-.002.002-1.225 1.218-1.213-1.243-.03-.03-.012-.013c-1.1-1.092-2.705-2.687-4.035-4.53-1.324-1.838-2.452-4.024-2.452-6.293"></path>
                                    </svg>
                                    <span>${escapeHtml(meta.city)}</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <div class="action-buttons">
                            <a href="${escapeHtml(link)}" class="action-btn primary">
                                Voir les détails
                            </a>
                        </div>
                        <div class="action-icons-vertical">
                            ${meta.phone ? `
                                <a href="tel:${escapeHtml(meta.phone)}" class="action-btn" title="Appeler">
                                    <svg viewBox="0 0 24 24" width="16" height="16">
                                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"></path>
                                    </svg>
                                </a>
                            ` : ''}
                            ${meta.phone ? `
                                <a href="https://wa.me/${meta.phone.replace(/[^0-9]/g, '')}" class="action-btn" target="_blank" rel="noopener" title="WhatsApp">
                                    <svg viewBox="0 0 24 24" width="16" height="16">
                                        <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"></path>
                                    </svg>
                                </a>
                            ` : ''}
                            ${meta.email ? `
                                <a href="mailto:${escapeHtml(meta.email)}" class="action-btn" title="Email">
                                    <svg viewBox="0 0 24 24" width="16" height="16">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"></path>
                                    </svg>
                                </a>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `);

        // Map popup on save icon click (prevent card click)
        $card.find('.save-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openRestaurantMapPopup(restaurant);
        });

        // VR popup on VR icon click (prevent card click)
        $card.find('.vr-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openVirtualTourPopup(restaurant);
        });

        return $card;
    }

    /**
     * Get price range symbol based on min/max prices
     */
    function getPriceRangeSymbol(minPrice, maxPrice) {
        const avgPrice = maxPrice > 0 ? (minPrice + maxPrice) / 2 : minPrice;
        
        if (avgPrice <= 100) return '€';
        if (avgPrice <= 300) return '€€';
        if (avgPrice <= 500) return '€€€';
        return '€€€€';
    }
    
    /**
     * Get price range display text
     */
    function getPriceRangeDisplay(minPrice, maxPrice) {
        if (minPrice > 0 && maxPrice > 0) {
            return `${minPrice}-${maxPrice} MAD`;
        } else if (minPrice > 0) {
            return `À partir de ${minPrice} MAD`;
        } else if (maxPrice > 0) {
            return `Jusqu'à ${maxPrice} MAD`;
        }
        return '';
    }
    
    /**
     * Get price category from price range
     */
    function getPriceCategory(minPrice, maxPrice) {
        const avgPrice = maxPrice > 0 ? (minPrice + maxPrice) / 2 : minPrice;
        
        if (avgPrice <= 100) return 'Économique';
        if (avgPrice <= 300) return 'Modéré';
        if (avgPrice <= 500) return 'Cher';
        return 'Très cher';
    }

    /**
     * Generate rating bubbles HTML
     */
    function generateRatingBubbles(rating) {
        const fullBubbles = Math.floor(rating);
        const hasHalfBubble = rating % 1 >= 0.5;
        const totalBubbles = 5;
        let html = '';

        for (let i = 0; i < totalBubbles; i++) {
            if (i < fullBubbles) {
                html += '<div class="rating-bubble"></div>';
            } else if (i === fullBubbles && hasHalfBubble) {
                html += '<div class="rating-bubble half"></div>';
            } else {
                html += '<div class="rating-bubble empty"></div>';
            }
        }

        return html;
    }

    /**
     * Get price range symbol
     */
    function getPriceRangeSymbol(priceRange) {
        switch (priceRange) {
            case 'budget': return '€';
            case 'moderate': return '€€';
            case 'expensive': return '€€€';
            case 'very_expensive': return '€€€€';
            default: return '€€';
        }
    }

    /**
     * Update results count
     */
    function updateResultsCount() {
        $('#results-total').text(filteredRestaurants.length);
    }

    /**
     * Update load more button
     */
    function updateLoadMoreButton() {
        const $btn = $('#load-more-btn');
        const $container = $('.load-more-container');
        const displayedCount = currentPage * itemsPerPage;
        
        if (displayedCount >= filteredRestaurants.length) {
            $container.hide();
        } else {
            $container.show();
            $btn.off('click').on('click', function() {
                currentPage++;
                displayRestaurants();
            });
        }
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        $('.filter-checkbox, .filter-radio').prop('checked', false);
        $('#restaurant-search').val('');
        $('#min-price, #max-price').val('');
        currentFilters = {};
        currentSort = 'featured';
        currentPage = 1;
        
        // Reset sort UI
        $('.sort-option').removeClass('active');
        $('.sort-option[data-sort="featured"]').addClass('active');
        $('.sort-text').text('En vedette');
        
        applyFilters();
    }

    /**
     * Show loading state
     */
    function showLoadingState(replace = true) {
        const $container = $('#restaurants-list');
        
        if (replace) {
            $container.html(`
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <p class="loading-text">${lebonrestoAll.strings.loading}</p>
                </div>
            `);
        }
    }

    /**
     * Hide loading state
     */
    function hideLoadingState() {
        $('.loading-state').remove();
    }

    /**
     * Show error state
     */
    function showErrorState() {
        const $container = $('#restaurants-list');
        $container.html(`
            <div class="error-state text-center py-12">
                <div class="error-icon mb-4">
                    <svg viewBox="0 0 24 24" width="48" height="48" class="text-red-400">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">${lebonrestoAll.strings.error}</h3>
                <p class="text-gray-500 mb-6">Une erreur est survenue lors du chargement des restaurants.</p>
                <button onclick="location.reload()" class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200">
                    ${lebonrestoAll.strings.tryAgain}
                </button>
            </div>
        `);
    }

    /**
     * Show no results state
     */
    function showNoResults() {
        const $container = $('#restaurants-list');
        $container.html(`
            <div class="no-results-state text-center py-12">
                <div class="no-results-icon mb-4">
                    <svg viewBox="0 0 24 24" width="48" height="48" class="text-gray-400">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">${lebonrestoAll.strings.noResults}</h3>
                <p class="text-gray-500 mb-6">Aucun restaurant ne correspond à vos critères de recherche.</p>
                <button class="clear-filters-btn px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200">
                    ${lebonrestoAll.strings.clearFilters}
                </button>
            </div>
        `);
    }

    /**
     * Show mobile filters
     */
    function showMobileFilters() {
        // In a real implementation, this would show a mobile filter modal
        console.log('Show mobile filters');
    }

    /**
     * Hide mobile filters
     */
    function hideMobileFilters() {
        $('#mobile-filter-overlay').addClass('hidden');
    }


    /**
     * Escape HTML characters
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Make functions globally available for debugging
    window.lebonrestoAllRedesigned = {
        loadRestaurants,
        applyFilters,
        clearAllFilters,
        showMobileFilters,
        hideMobileFilters
    };

    /**
     * Fetch Google Places data for a restaurant
     */

    /**
     * Enhanced load restaurants with Google Places integration
     */

    // Make functions globally available for debugging
    window.lebonrestoAllRedesigned = {
        loadRestaurants,
        applyFilters,
        clearAllFilters,
        showMobileFilters,
        hideMobileFilters,
        requestLocation,
        userLocation: () => userLocation
    };

    /**
     * Generate rating bubbles HTML
     */
    function generateRatingBubbles(rating) {
        const fullBubbles = Math.floor(rating);
        const hasHalfBubble = rating % 1 >= 0.5;
        const totalBubbles = 5;
        let html = '';

        for (let i = 0; i < totalBubbles; i++) {
            if (i < fullBubbles) {
                html += '<div class="rating-bubble"></div>';
            } else if (i === fullBubbles && hasHalfBubble) {
                html += '<div class="rating-bubble half"></div>';
            } else {
                html += '<div class="rating-bubble empty"></div>';
            }
        }

        return html;
    }

    // Global function for review navigation
    window.navigateReview = function(restaurantId, direction) {
        const reviewContainer = document.querySelector(`[data-restaurant-id="${restaurantId}"]`);
        if (!reviewContainer) return;
        
        const reviews = reviewContainer.querySelectorAll('.review-item');
        const counter = reviewContainer.querySelector('.review-counter');
        const currentIndex = Array.from(reviews).findIndex(review => review.classList.contains('active'));
        
        if (currentIndex === -1) return;
        
        let newIndex = currentIndex + direction;
        
        // Handle wrapping
        if (newIndex < 0) newIndex = reviews.length - 1;
        if (newIndex >= reviews.length) newIndex = 0;
        
        // Update active review
        reviews.forEach((review, index) => {
            review.classList.toggle('active', index === newIndex);
        });
        
        // Update counter
        if (counter) {
            counter.textContent = `${newIndex + 1}/${reviews.length}`;
        }
    };

    // Popup Map Functionality
    let popupMap = null;
    let popupMarkers = [];
    let currentPopupRestaurant = null;

    // Virtual Tour Popup Functionality
    let currentVirtualTourRestaurant = null;

    /**
     * Open restaurant map popup
     */
    function openRestaurantMapPopup(restaurant) {
        currentPopupRestaurant = restaurant;
        const popup = document.getElementById('restaurant-map-popup');
        if (popup) {
            popup.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Initialize map after popup is shown
            setTimeout(() => {
                initializePopupMap();
            }, 100);
        }
    }

    /**
     * Close restaurant map popup
     */
    function closeRestaurantMapPopup() {
        const popup = document.getElementById('restaurant-map-popup');
        if (popup) {
            popup.classList.remove('show');
            document.body.style.overflow = '';
            
            // Clean up map
            if (popupMap) {
                popupMap.remove();
                popupMap = null;
            }
            popupMarkers = [];
            currentPopupRestaurant = null;
        }
    }

    /**
     * Initialize popup map
     */
    function initializePopupMap() {
        const mapContainer = document.getElementById('popup-restaurants-map');
        if (!mapContainer || popupMap) return;

        // Default center (Casablanca)
        let centerLat = 33.5731;
        let centerLng = -7.5898;
        let zoom = 12;

        // Use current restaurant location if available
        if (currentPopupRestaurant && currentPopupRestaurant.restaurant_meta) {
            const lat = parseFloat(currentPopupRestaurant.restaurant_meta.latitude);
            const lng = parseFloat(currentPopupRestaurant.restaurant_meta.longitude);
            if (lat && lng) {
                centerLat = lat;
                centerLng = lng;
                zoom = 14;
            }
        }

        // Initialize map
        popupMap = L.map('popup-restaurants-map').setView([centerLat, centerLng], zoom);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(popupMap);

        // Add markers for all restaurants
        addRestaurantMarkersToPopup();

        // Update results counter and button text
        updatePopupResultsCount();
        updateCenterButtonText();
    }

    /**
     * Add restaurant markers to popup map
     */
    function addRestaurantMarkersToPopup() {
        if (!popupMap) return;

        // Clear existing markers
        popupMarkers.forEach(marker => popupMap.removeLayer(marker));
        popupMarkers = [];

        // Add markers for filtered restaurants
        filteredRestaurants.forEach(restaurant => {
        const meta = restaurant.restaurant_meta || {};
            const lat = parseFloat(meta.latitude);
            const lng = parseFloat(meta.longitude);
            
            if (lat && lng) {
                const isCurrent = currentPopupRestaurant && restaurant.id === currentPopupRestaurant.id;
                
                // Get rating info for display
                const googleRating = parseFloat(meta.google_rating) || 0;
                const localRating = parseFloat(meta.average_rating) || 0;
                const rating = googleRating > 0 ? googleRating : localRating;
                const googleReviewCount = parseInt(meta.google_review_count) || 0;
                const localReviewCount = parseInt(meta.review_count) || 0;
                const reviewCount = googleReviewCount > 0 ? googleReviewCount : localReviewCount;
                
                // Generate stars for rating
                const generateStars = (rating) => {
                    const stars = [];
                    for (let i = 1; i <= 5; i++) {
                        if (i <= rating) {
                            stars.push('<span style="color: #fbbf24; font-size: 0.7rem;">★</span>');
                        } else {
                            stars.push('<span style="color: #d1d5db; font-size: 0.7rem;">★</span>');
                        }
                    }
                    return stars.join('');
                };
                
                // Create custom icon with name and rating below
                const iconHtml = `
                    <div class="marker-with-label">
                        <div class="marker-icon ${isCurrent ? 'current' : 'regular'}">
                            <div class="marker-content">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="40" height="40" x="0" y="0" viewBox="0 0 713.343 713.343" style="enable-background:new 0 0 512 512" xml:space="preserve" class="marker-svg">
                                    <g>
                                        <path fill="#ff5252" d="M646.467 289.796c1.226 76.016-30.317 152.811-89.168 211.774L356.672 702.197 156.044 501.569C97.193 442.607 65.65 365.811 66.876 289.796c1.226-70.108 30.651-139.548 84.932-193.717 56.499-56.622 130.742-84.932 204.863-84.932s148.353 28.311 204.863 84.932c54.282 54.169 83.707 123.608 84.933 193.717zm-66.876 11.146c0-123.163-99.757-222.92-222.92-222.92s-222.92 99.757-222.92 222.92 99.757 222.92 222.92 222.92 222.92-99.757 222.92-222.92z" opacity="1" data-original="#ff5252" class=""></path>
                                        <path fill="#323232" d="M490.312 234.066c1.783 88.834-33.438 89.168-33.438 89.168V178.336s32.658 15.381 33.438 55.73zM378.964 312.088c0-21.289-33.438-47.259-33.438-78.022s14.936-55.73 33.438-55.73 33.438 24.967 33.438 55.73-33.438 56.064-33.438 78.022z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#ffd438" d="M378.964 312.088c0-21.958 33.438-47.259 33.438-78.022s-14.936-55.73-33.438-55.73-33.438 24.967-33.438 55.73 33.438 56.733 33.438 78.022zm77.91 11.146s35.221-.334 33.438-89.168c-.78-40.348-33.438-55.73-33.438-55.73zM356.672 78.022c123.163 0 222.92 99.757 222.92 222.92s-99.757 222.92-222.92 222.92-222.92-99.757-222.92-222.92 99.757-222.92 222.92-222.92z" opacity="1" data-original="#ffd438" class=""></path>
                                        <path fill="#323232" d="M356.672 713.343a11.145 11.145 0 0 1-7.881-3.264L148.163 509.451c-60.028-60.142-93.715-140.266-92.431-219.835 1.301-74.434 32.626-145.964 88.204-201.427C200.675 31.326 276.232 0 356.672 0 437.1 0 512.657 31.325 569.423 88.205c55.563 55.448 86.886 126.977 88.188 201.397 1.283 79.585-32.404 159.709-92.424 219.842l-.007.008-200.627 200.627a11.145 11.145 0 0 1-7.881 3.264zm0-691.051c-74.476 0-144.429 29-196.973 81.659-51.478 51.372-80.479 117.436-81.678 186.039-1.187 73.561 30.127 147.814 85.912 203.705l192.739 192.739L549.41 493.696c55.784-55.891 87.098-130.144 85.912-203.72-1.199-68.588-30.201-134.653-81.662-186.008-52.57-52.675-122.522-81.676-196.988-81.676zm200.627 479.277h.014z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M356.672 535.007c-129.064 0-234.066-105.001-234.066-234.066S227.608 66.876 356.672 66.876s234.065 105.001 234.065 234.066-105.001 234.065-234.065 234.065zm0-445.839c-116.772 0-211.774 95.001-211.774 211.774s95.001 211.774 211.774 211.774 211.773-95.001 211.773-211.774S473.444 89.168 356.672 89.168z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M267.504 423.548c-6.156 0-11.146-4.991-11.146-11.146V278.65c0-6.156 4.99-11.146 11.146-11.146s11.146 4.99 11.146 11.146v133.752c0 6.155-4.99 11.146-11.146 11.146z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M267.504 289.796c-11.89 0-23.08-4.643-31.511-13.073-8.43-8.429-13.073-19.62-13.073-31.511v-55.73c0-6.156 4.99-11.146 11.146-11.146s11.146 4.99 11.146 11.146v55.73c0 5.936 2.324 11.528 6.543 15.748 4.221 4.221 9.814 6.544 15.749 6.544 12.292 0 22.292-10 22.292-22.292v-55.73c0-6.156 4.99-11.146 11.146-11.146s11.146 4.99 11.146 11.146v55.73c0 24.584-20 44.584-44.584 44.584z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M267.504 289.796c-6.156 0-11.146-4.99-11.146-11.146v-89.168c0-6.156 4.99-11.146 11.146-11.146s11.146 4.99 11.146 11.146v89.168c0 6.156-4.99 11.146-11.146 11.146zM378.963 423.548c-6.155 0-11.146-4.991-11.146-11.146V289.796c0-6.156 4.991-11.146 11.146-11.146s11.146 4.99 11.146 11.146v122.606c0 6.155-4.99 11.146-11.146 11.146z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M378.963 323.234c-6.155 0-11.146-4.99-11.146-11.146 0-6.37-6.421-16.27-12.629-25.845-9.753-15.04-20.808-32.086-20.808-52.177 0-37.501 19.583-66.876 44.583-66.876 25.001 0 44.584 29.375 44.584 66.876 0 19.988-10.961 36.801-20.632 51.636-6.585 10.102-12.806 19.643-12.806 26.386 0 6.156-4.99 11.146-11.146 11.146zm0-133.752c-10.523 0-22.291 19.067-22.291 44.584 0 13.496 8.753 26.994 17.219 40.048 1.701 2.622 3.381 5.213 4.98 7.788 1.716-2.769 3.532-5.556 5.37-8.374 8.365-12.831 17.014-26.099 17.014-39.462 0-25.518-11.769-44.584-22.292-44.584zM456.874 334.38a11.146 11.146 0 0 1-11.146-11.146V178.336a11.144 11.144 0 0 1 15.896-10.083c1.588.748 38.929 18.867 39.833 65.598.867 43.225-6.591 73.282-22.167 89.326-10.251 10.559-20.383 11.185-22.31 11.203h-.106zm11.146-132.397v99.251c6.193-10.788 11.87-31.038 11.149-66.944-.28-14.439-5.417-24.988-11.149-32.307z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M456.874 423.548c-6.155 0-11.146-4.991-11.146-11.146v-89.168c0-6.156 4.991-11.146 11.146-11.146s11.146 4.99 11.146 11.146v89.168c0 6.155-4.991 11.146-11.146 11.146z" opacity="1" data-original="#323232" class=""></path>
                                    </g>
                                </svg>
                            </div>
                        </div>
                        <div class="marker-label">
                            <div class="marker-name">${escapeHtml(restaurant.title?.rendered || 'Restaurant')}</div>
                            ${rating > 0 ? `
                                <div class="marker-rating">
                                    <div class="marker-stars">${generateStars(rating)}</div>
                                    <span class="marker-rating-text">${rating.toFixed(1)}</span>
                                    ${reviewCount > 0 ? `<span class="marker-review-count">(${reviewCount})</span>` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                const customIcon = L.divIcon({
                    html: iconHtml,
                    className: 'custom-marker-with-label',
                    iconSize: [120, 80],
                    iconAnchor: [60, 40]
                });

                const marker = L.marker([lat, lng], { icon: customIcon })
                    .addTo(popupMap)
                    .bindPopup(createRestaurantPopupContent(restaurant));

                popupMarkers.push(marker);

                // Open popup for current restaurant
                if (isCurrent) {
                    marker.openPopup();
                }
            }
        });

        // Fit map to show all markers
        if (popupMarkers.length > 0) {
            const group = new L.featureGroup(popupMarkers);
            popupMap.fitBounds(group.getBounds().pad(0.1));
        }
    }

    /**
     * Create popup content for restaurant
     */
    function createRestaurantPopupContent(restaurant) {
        const meta = restaurant.restaurant_meta || {};
        const title = restaurant.title?.rendered || 'Restaurant';
        const address = meta.address || '';
        const city = meta.city || '';
        const cuisineType = meta.cuisine_type || '';
        const phone = meta.phone || '';
        const email = meta.email || '';
        
        // Build Google Maps URL for popup (prefer admin-provided link)
        const adminMapsLink = meta.restaurant_google_maps_link || meta.google_maps_link || '';
        const placeIdPopup = meta.google_place_id || '';
        const latPopup = parseFloat(meta.latitude);
        const lngPopup = parseFloat(meta.longitude);
        let mapsUrlPopup = '';
        if (adminMapsLink) {
            mapsUrlPopup = adminMapsLink;
        } else if (placeIdPopup) {
            mapsUrlPopup = `https://www.google.com/maps/search/?api=1&query_place_id=${encodeURIComponent(placeIdPopup)}&query=${encodeURIComponent(title)}`;
        } else if (latPopup && lngPopup) {
            mapsUrlPopup = `https://www.google.com/maps/search/?api=1&query=${latPopup},${lngPopup}`;
        } else if (address) {
            mapsUrlPopup = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
        }
        
        // Rating information
        const googleRating = parseFloat(meta.google_rating) || 0;
        const localRating = parseFloat(meta.average_rating) || 0;
        const rating = googleRating > 0 ? googleRating : localRating;
        const googleReviewCount = parseInt(meta.google_review_count) || 0;
        const localReviewCount = parseInt(meta.review_count) || 0;
        const reviewCount = googleReviewCount > 0 ? googleReviewCount : localReviewCount;
        
        // Get Google reviews (not displayed in popup)
        const googleReviews = meta.google_api_reviews || [];
        
        // Price range
        const minPrice = parseFloat(meta.min_price) || 0;
        const maxPrice = parseFloat(meta.max_price) || 0;
        let priceDisplay = '';
        if (minPrice > 0 && maxPrice > 0) {
            priceDisplay = `${minPrice}-${maxPrice} MAD`;
        } else if (minPrice > 0) {
            priceDisplay = `À partir de ${minPrice} MAD`;
        } else if (maxPrice > 0) {
            priceDisplay = `Jusqu'à ${maxPrice} MAD`;
        }
        
        // Generate stars for rating
        const generateStars = (rating) => {
            const stars = [];
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars.push('<span style="color: #fbbf24;">★</span>');
                } else {
                    stars.push('<span style="color: #d1d5db;">★</span>');
                }
            }
            return stars.join('');
        };
        
        // Reviews not displayed in popup per design
        let reviewsHtml = '';
        
        return `
            <div class="restaurant-popup-content" style="min-width: 280px; max-width: 320px;">
                <div style="margin-bottom: 1rem;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 700; color: #1f2937; line-height: 1.3;">
                        ${escapeHtml(title)}
                    </h3>
                    
                    ${rating > 0 ? `
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <div style="display: flex; gap: 1px;">
                                ${generateStars(rating)}
                            </div>
                            <span style="font-weight: 600; color: #1f2937; font-size: 0.9rem;">${rating.toFixed(1)}</span>
                            ${reviewCount > 0 ? `<span style="color: #6b7280; font-size: 0.8rem;">(${reviewCount} avis)</span>` : ''}
                        </div>
                    ` : ''}
                    
                    
                </div>
                
                <div style="margin-bottom: 1rem;">
                    ${cuisineType ? `
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280;">
                                <path fill="currentColor" d="M14.051 6.549v.003l1.134 1.14 3.241-3.25.003-.002 1.134 1.136-3.243 3.252 1.134 1.14a1 1 0 0 0 .09-.008c.293-.05.573-.324.72-.474l.005-.006 2.596-2.603L22 8.016l-2.597 2.604a3.73 3.73 0 0 1-1.982 1.015 4.3 4.3 0 0 1-3.162-.657l-.023-.016-.026-.018-1.366 1.407 8.509 8.512L20.219 22l-.002-.002-6.654-6.663-2.597 2.76-7.3-7.315C1.967 8.948 1.531 6.274 2.524 4.198c.241-.504.566-.973.978-1.386l8.154 8.416 1.418-1.423-.039-.045c-.858-1.002-1.048-2.368-.62-3.595a4.15 4.15 0 0 1 .983-1.561L16 2l1.135 1.138-2.598 2.602-.047.045c-.16.151-.394.374-.433.678zM3.809 5.523c-.362 1.319-.037 2.905 1.06 4.103L10.93 15.7l1.408-1.496zM2.205 20.697 3.34 21.84l4.543-4.552-1.135-1.143z"></path>
                            </svg>
                            <span style="font-size: 0.85rem; color: #374151;">${escapeHtml(cuisineType)}</span>
                        </div>
                    ` : ''}
                    
                    ${priceDisplay ? `
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280;">
                                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"></path>
                            </svg>
                            <span style="font-size: 0.85rem; color: #374151;">${priceDisplay}</span>
                        </div>
                    ` : ''}
                    
                    ${address ? `
                        ${mapsUrlPopup ? `
                            <a href="${escapeHtml(mapsUrlPopup)}" target="_blank" rel="noopener" style="display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.25rem; text-decoration: none;">
                                <svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280; margin-top: 0.1rem;">
                                    <path fill="currentColor" d="M4.25 9.799c0-4.247 3.488-7.707 7.75-7.707s7.75 3.46 7.75 7.707c0 2.28-1.138 4.477-2.471 6.323-1.31 1.813-2.883 3.388-3.977 4.483l-.083.083-.002.002-1.225 1.218-1.213-1.243-.03-.03-.012-.013c-1.1-1.092-2.705-2.687-4.035-4.53-1.324-1.838-2.452-4.024-2.452-6.293"></path>
                                </svg>
                                <span style="font-size: 0.85rem; color: #2563eb; line-height: 1.3;">${escapeHtml(address)}</span>
                            </a>
                        ` : `
                            <div style="display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280; margin-top: 0.1rem;">
                                    <path fill="currentColor" d="M4.25 9.799c0-4.247 3.488-7.707 7.75-7.707s7.75 3.46 7.75 7.707c0 2.28-1.138 4.477-2.471 6.323-1.31 1.813-2.883 3.388-3.977 4.483l-.083.083-.002.002-1.225 1.218-1.213-1.243-.03-.03-.012-.013c-1.1-1.092-2.705-2.687-4.035-4.53-1.324-1.838-2.452-4.024-2.452-6.293"></path>
                                </svg>
                                <span style="font-size: 0.85rem; color: #374151; line-height: 1.3;">${escapeHtml(address)}</span>
                            </div>
                        `}
                    ` : ''}
                    
                    ${phone ? `
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280;">
                                <path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"></path>
                            </svg>
                            <a href="tel:${escapeHtml(phone)}" style="font-size: 0.85rem; color: #3b82f6; text-decoration: none;">${escapeHtml(phone)}</a>
                        </div>
                    ` : ''}
                </div>
                
                ${reviewsHtml}
                
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <a href="${escapeHtml(restaurant.link)}" class="popup-link" style="display: inline-block; width: 100%; text-align: center;">
                        Voir tous les détails
                    </a>
                </div>
            </div>
        `;
    }

    /**
     * Update popup results count
     */
    function updatePopupResultsCount() {
        const counter = document.getElementById('popup-results-count');
        if (counter) {
            counter.textContent = `${popupMarkers.length} restaurants affichés`;
        }
    }

    /**
     * Update center button text based on current context
     */
    function updateCenterButtonText() {
        const centerBtn = document.getElementById('popup-center-current');
        if (centerBtn) {
            const buttonText = centerBtn.querySelector('.control-icon').nextSibling;
            if (currentPopupRestaurant) {
                buttonText.textContent = ' Centrer sur restaurant';
            } else {
                buttonText.textContent = ' Voir tous les restaurants';
            }
        }
    }

    /**
     * Center popup map on current restaurant or fit all restaurants
     */
    function centerPopupOnCurrent() {
        if (!popupMap) return;
        
        if (currentPopupRestaurant) {
            // Center on specific restaurant
            const meta = currentPopupRestaurant.restaurant_meta || {};
            const lat = parseFloat(meta.latitude);
            const lng = parseFloat(meta.longitude);
            
            if (lat && lng) {
                popupMap.setView([lat, lng], 16);
                
                // Find and open popup for current restaurant
                const currentMarker = popupMarkers.find(marker => {
                    const markerLat = marker.getLatLng().lat;
                    const markerLng = marker.getLatLng().lng;
                    return Math.abs(markerLat - lat) < 0.0001 && Math.abs(markerLng - lng) < 0.0001;
                });
                
                if (currentMarker) {
                    currentMarker.openPopup();
                }
            }
        } else {
            // Fit all restaurants in view
            if (popupMarkers.length > 0) {
                const group = new L.featureGroup(popupMarkers);
                popupMap.fitBounds(group.getBounds().pad(0.1));
            }
        }
    }

    /**
     * Open map with all restaurants (no specific restaurant selected)
     */
    function openMapWithAllRestaurants() {
        currentPopupRestaurant = null; // No specific restaurant selected
        const popup = document.getElementById('restaurant-map-popup');
        if (popup) {
            popup.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Initialize map after popup is shown
            setTimeout(() => {
                initializePopupMapWithAllRestaurants();
            }, 100);
        }
    }

    /**
     * Initialize popup map with all restaurants view
     */
    function initializePopupMapWithAllRestaurants() {
        const mapContainer = document.getElementById('popup-restaurants-map');
        if (!mapContainer || popupMap) return;

        // Default center (Casablanca)
        let centerLat = 33.5731;
        let centerLng = -7.5898;
        let zoom = 11;

        // Initialize map
        popupMap = L.map('popup-restaurants-map').setView([centerLat, centerLng], zoom);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(popupMap);

        // Add markers for all restaurants
        addAllRestaurantMarkersToPopup();

        // Update results counter and button text
        updatePopupResultsCount();
        updateCenterButtonText();
    }

    /**
     * Add all restaurant markers to popup map (no current restaurant highlighting)
     */
    function addAllRestaurantMarkersToPopup() {
        if (!popupMap) return;

        // Clear existing markers
        popupMarkers.forEach(marker => popupMap.removeLayer(marker));
        popupMarkers = [];

        // Add markers for filtered restaurants (all treated as regular)
        filteredRestaurants.forEach(restaurant => {
            const meta = restaurant.restaurant_meta || {};
            const lat = parseFloat(meta.latitude);
            const lng = parseFloat(meta.longitude);
            
            if (lat && lng) {
                // Get rating info for display
                const googleRating = parseFloat(meta.google_rating) || 0;
                const localRating = parseFloat(meta.average_rating) || 0;
                const rating = googleRating > 0 ? googleRating : localRating;
                const googleReviewCount = parseInt(meta.google_review_count) || 0;
                const localReviewCount = parseInt(meta.review_count) || 0;
                const reviewCount = googleReviewCount > 0 ? googleReviewCount : localReviewCount;
                
                // Generate stars for rating
                const generateStars = (rating) => {
                    const stars = [];
                    for (let i = 1; i <= 5; i++) {
                        if (i <= rating) {
                            stars.push('<span style="color: #fbbf24; font-size: 0.7rem;">★</span>');
                        } else {
                            stars.push('<span style="color: #d1d5db; font-size: 0.7rem;">★</span>');
                        }
                    }
                    return stars.join('');
                };
                
                // Create custom icon with name and rating below (all regular markers)
                const iconHtml = `
                    <div class="marker-with-label">
                        <div class="marker-icon regular">
                            <div class="marker-content">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="40" height="40" x="0" y="0" viewBox="0 0 713.343 713.343" style="enable-background:new 0 0 512 512" xml:space="preserve" class="marker-svg">
                                    <g>
                                        <path fill="#ff5252" d="M646.467 289.796c1.226 76.016-30.317 152.811-89.168 211.774L356.672 702.197 156.044 501.569C97.193 442.607 65.65 365.811 66.876 289.796c1.226-70.108 30.651-139.548 84.932-193.717 56.499-56.622 130.742-84.932 204.863-84.932s148.353 28.311 204.863 84.932c54.282 54.169 83.707 123.608 84.933 193.717zm-66.876 11.146c0-123.163-99.757-222.92-222.92-222.92s-222.92 99.757-222.92 222.92 99.757 222.92 222.92 222.92 222.92-99.757 222.92-222.92z" opacity="1" data-original="#ff5252" class=""></path>
                                        <path fill="#323232" d="M490.312 234.066c1.783 88.834-33.438 89.168-33.438 89.168V178.336s32.658 15.381 33.438 55.73zM378.964 312.088c0-21.289-33.438-47.259-33.438-78.022s14.936-55.73 33.438-55.73 33.438 24.967 33.438 55.73-33.438 56.064-33.438 78.022z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#ffd438" d="M378.964 312.088c0-21.958 33.438-47.259 33.438-78.022s-14.936-55.73-33.438-55.73-33.438 24.967-33.438 55.73 33.438 56.733 33.438 78.022zm77.91 11.146s35.221-.334 33.438-89.168c-.78-40.348-33.438-55.73-33.438-55.73zM356.672 78.022c123.163 0 222.92 99.757 222.92 222.92s-99.757 222.92-222.92 222.92-222.92-99.757-222.92-222.92 99.757-222.92 222.92-222.92z" opacity="1" data-original="#ffd438" class=""></path>
                                        <path fill="#323232" d="M356.672 713.343a11.145 11.145 0 0 1-7.881-3.264L148.163 509.451c-60.028-60.142-93.715-140.266-92.431-219.835 1.301-74.434 32.626-145.964 88.204-201.427C200.675 31.326 276.232 0 356.672 0 437.1 0 512.657 31.325 569.423 88.205c55.563 55.448 86.886 126.977 88.188 201.397 1.283 79.585-32.404 159.709-92.424 219.842l-.007.008-200.627 200.627a11.145 11.145 0 0 1-7.881 3.264zm0-691.051c-74.476 0-144.429 29-196.973 81.659-51.478 51.372-80.479 117.436-81.678 186.039-1.187 73.561 30.127 147.814 85.912 203.705l192.739 192.739L549.41 493.696c55.784-55.891 87.098-130.144 85.912-203.72-1.199-68.588-30.201-134.653-81.662-186.008-52.57-52.675-122.522-81.676-196.988-81.676zm200.627 479.277h.014z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M356.672 535.007c-129.064 0-234.066-105.001-234.066-234.066S227.608 66.876 356.672 66.876s234.065 105.001 234.065 234.066-105.001 234.065-234.065 234.065zm0-445.839c-116.772 0-211.774 95.001-211.774 211.774s95.001 211.774 211.774 211.774 211.773-95.001 211.773-211.774S473.444 89.168 356.672 89.168z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M267.504 423.548c-6.156 0-11.146-4.991-11.146-11.146V278.65c0-6.156 4.99-11.146 11.146-11.146s11.146 4.99 11.146 11.146v133.752c0 6.155-4.99 11.146-11.146 11.146z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M267.504 289.796c-11.89 0-23.08-4.643-31.511-13.073-8.43-8.429-13.073-19.62-13.073-31.511v-55.73c0-6.156 4.99-11.146 11.146-11.146s11.146 4.99 11.146 11.146v55.73c0 5.936 2.324 11.528 6.543 15.748 4.221 4.221 9.814 6.544 15.749 6.544 12.292 0 22.292-10 22.292-22.292v-55.73c0-6.156 4.99-11.146 11.146-11.146s11.146 4.99 11.146 11.146v55.73c0 24.584-20 44.584-44.584 44.584z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M267.504 289.796c-6.156 0-11.146-4.99-11.146-11.146v-89.168c0-6.156 4.99-11.146 11.146-11.146s11.146 4.99 11.146 11.146v89.168c0 6.156-4.99 11.146-11.146 11.146zM378.963 423.548c-6.155 0-11.146-4.991-11.146-11.146V289.796c0-6.156 4.991-11.146 11.146-11.146s11.146 4.99 11.146 11.146v122.606c0 6.155-4.99 11.146-11.146 11.146z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M378.963 323.234c-6.155 0-11.146-4.99-11.146-11.146 0-6.37-6.421-16.27-12.629-25.845-9.753-15.04-20.808-32.086-20.808-52.177 0-37.501 19.583-66.876 44.583-66.876 25.001 0 44.584 29.375 44.584 66.876 0 19.988-10.961 36.801-20.632 51.636-6.585 10.102-12.806 19.643-12.806 26.386 0 6.156-4.99 11.146-11.146 11.146zm0-133.752c-10.523 0-22.291 19.067-22.291 44.584 0 13.496 8.753 26.994 17.219 40.048 1.701 2.622 3.381 5.213 4.98 7.788 1.716-2.769 3.532-5.556 5.37-8.374 8.365-12.831 17.014-26.099 17.014-39.462 0-25.518-11.769-44.584-22.292-44.584zM456.874 334.38a11.146 11.146 0 0 1-11.146-11.146V178.336a11.144 11.144 0 0 1 15.896-10.083c1.588.748 38.929 18.867 39.833 65.598.867 43.225-6.591 73.282-22.167 89.326-10.251 10.559-20.383 11.185-22.31 11.203h-.106zm11.146-132.397v99.251c6.193-10.788 11.87-31.038 11.149-66.944-.28-14.439-5.417-24.988-11.149-32.307z" opacity="1" data-original="#323232" class=""></path>
                                        <path fill="#323232" d="M456.874 423.548c-6.155 0-11.146-4.991-11.146-11.146v-89.168c0-6.156 4.991-11.146 11.146-11.146s11.146 4.99 11.146 11.146v89.168c0 6.155-4.991 11.146-11.146 11.146z" opacity="1" data-original="#323232" class=""></path>
                                    </g>
                                </svg>
                            </div>
                        </div>
                        <div class="marker-label">
                            <div class="marker-name">${escapeHtml(restaurant.title?.rendered || 'Restaurant')}</div>
                            ${rating > 0 ? `
                                <div class="marker-rating">
                                    <div class="marker-stars">${generateStars(rating)}</div>
                                    <span class="marker-rating-text">${rating.toFixed(1)}</span>
                                    ${reviewCount > 0 ? `<span class="marker-review-count">(${reviewCount})</span>` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                const customIcon = L.divIcon({
                    html: iconHtml,
                    className: 'custom-marker-with-label',
                    iconSize: [120, 80],
                    iconAnchor: [60, 40]
                });

                const marker = L.marker([lat, lng], { icon: customIcon })
                    .addTo(popupMap)
                    .bindPopup(createRestaurantPopupContent(restaurant));

                popupMarkers.push(marker);
            }
        });

        // Fit map to show all markers
        if (popupMarkers.length > 0) {
            const group = new L.featureGroup(popupMarkers);
            popupMap.fitBounds(group.getBounds().pad(0.1));
        }
    }

    /**
     * Open virtual tour popup
     */
    function openVirtualTourPopup(restaurant) {
        currentVirtualTourRestaurant = restaurant;
        const meta = restaurant.restaurant_meta || {};
        const virtualTourUrl = meta.virtual_tour_url;
        
        if (!virtualTourUrl) return;
        
        // Create popup HTML if it doesn't exist
        let popup = document.getElementById('virtual-tour-popup');
        if (!popup) {
            popup = document.createElement('div');
            popup.id = 'virtual-tour-popup';
            popup.className = 'restaurant-popup-modal';
            popup.innerHTML = `
                <div class="popup-overlay"></div>
                <div class="popup-container">
                    <div class="popup-header">
                        <h3>Visite Virtuelle - ${escapeHtml(restaurant.title?.rendered || 'Restaurant')}</h3>
                        <button id="close-virtual-tour" class="popup-close" aria-label="Fermer">
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="popup-content">
                        <iframe id="virtual-tour-iframe" src="" frameborder="0" allowfullscreen style="width: 100%; height: 500px; border-radius: 8px;"></iframe>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
            
            // Add event listeners
            const closeBtn = document.getElementById('close-virtual-tour');
            const overlay = popup.querySelector('.popup-overlay');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', closeVirtualTourPopup);
            }
            
            if (overlay) {
                overlay.addEventListener('click', closeVirtualTourPopup);
            }
        }
        
        // Set iframe source and show popup
        const iframe = document.getElementById('virtual-tour-iframe');
        const title = popup.querySelector('.popup-header h3');
        
        if (iframe) {
            iframe.src = virtualTourUrl;
        }
        
        if (title) {
            title.textContent = `Visite Virtuelle - ${restaurant.title?.rendered || 'Restaurant'}`;
        }
        
        popup.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Close virtual tour popup
     */
    function closeVirtualTourPopup() {
        const popup = document.getElementById('virtual-tour-popup');
        const iframe = document.getElementById('virtual-tour-iframe');
        
        if (popup) {
            popup.classList.remove('show');
            document.body.style.overflow = '';
        }
        
        if (iframe) {
            iframe.src = ''; // Stop the iframe
        }
        
        currentVirtualTourRestaurant = null;
    }

    // Event listeners for popup
    document.addEventListener('DOMContentLoaded', function() {
        // Close popup events
        const closeBtn = document.getElementById('close-popup');
        const popup = document.getElementById('restaurant-map-popup');
        const overlay = popup?.querySelector('.popup-overlay');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closeRestaurantMapPopup);
        }
        
        if (overlay) {
            overlay.addEventListener('click', closeRestaurantMapPopup);
        }
        
        // Center button
        const centerBtn = document.getElementById('popup-center-current');
        if (centerBtn) {
            centerBtn.addEventListener('click', centerPopupOnCurrent);
        }
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (popup?.classList.contains('show')) {
                    closeRestaurantMapPopup();
                }
                const vtPopup = document.getElementById('virtual-tour-popup');
                if (vtPopup?.classList.contains('show')) {
                    closeVirtualTourPopup();
                }
            }
        });

        // Map toggle button functionality
        const mapToggleBtn = document.querySelector('.map-toggle-btn');
        if (mapToggleBtn) {
            mapToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Open popup with all restaurants (no specific restaurant selected)
                openMapWithAllRestaurants();
            });
        }

        // Enhanced sticky sidebar behavior
        const filtersSidebar = document.querySelector('.filters-sidebar');
        if (filtersSidebar) {
            let isSticky = false;
            
            const handleScroll = () => {
                const rect = filtersSidebar.getBoundingClientRect();
                const shouldBeSticky = rect.top <= 50;
                
                if (shouldBeSticky && !isSticky) {
                    filtersSidebar.classList.add('sticky');
                    isSticky = true;
                } else if (!shouldBeSticky && isSticky) {
                    filtersSidebar.classList.remove('sticky');
                    isSticky = false;
                }
            };
            
            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll(); // Check initial state
        }
    });

})(jQuery);
