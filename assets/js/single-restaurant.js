(function($) {
    "use strict";

    // Global variables
    let map;
    let markersLayer;
    let allRestaurants = [];
    let currentRestaurantId;
    let currentFilters = {};
    let currentPage = 1;
    let restaurantsPerPage = 10;
    let totalPages = 1;

    // Initialize when document is ready
    $(document).ready(function() {
        initializeSingleRestaurantUpdated();
    });

    /**
     * Initialize the updated single restaurant page
     */
    function initializeSingleRestaurantUpdated() {
        console.log('Initializing single restaurant page...');
        
        // Check if Tailwind is loaded
        if (!document.querySelector('[href*="tailwindcss"]')) {
            console.warn('Tailwind CSS may not be loaded');
        }
        
        // Get current restaurant data
        const restaurantDataElement = document.getElementById('current-restaurant-data');
        if (restaurantDataElement) {
            try {
                window.currentRestaurantData = JSON.parse(restaurantDataElement.textContent);
                currentRestaurantId = window.currentRestaurantData.id;
                console.log('Current restaurant ID:', currentRestaurantId);
            } catch (error) {
                console.error('Error parsing restaurant data:', error);
            }
        } else {
            console.warn('Restaurant data element not found');
        }

        // Initialize map
        initializeMap();
        
        // Initialize filters
        initializeFilters();
        
        // Initialize location detection
        initializeLocationDetection();
        
        // Load all restaurants
        loadAllRestaurants();
        
        console.log('Single restaurant page initialization complete');
    }

    /**
     * Initialize the map centered on current restaurant
     */
    function initializeMap() {
        const mapContainer = document.getElementById('restaurants-map');
        if (!mapContainer) return;

        // Get map center from current restaurant or default
        const center = lebonrestoSingle?.mapCenter || { lat: 48.8566, lng: 2.3522 };
        
        // Initialize map centered on current restaurant
        map = L.map('restaurants-map').setView([center.lat, center.lng], 10);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Create markers layer
        markersLayer = L.layerGroup().addTo(map);

        // Add map controls
        addMapControls();
    }

    /**
     * Add map controls
     */
    function addMapControls() {
        // Center on current restaurant button
        $('#center-current-restaurant').on('click', function() {
            if (window.currentRestaurantData && window.currentRestaurantData.latitude && window.currentRestaurantData.longitude) {
                map.setView([
                    parseFloat(window.currentRestaurantData.latitude),
                    parseFloat(window.currentRestaurantData.longitude)
                ], 13);
                
                // Highlight current restaurant marker
                highlightCurrentRestaurant();
            }
        });
    }

    /**
     * Initialize filter functionality
     */
    function initializeFilters() {
        // Search inputs with debounce
        $('#restaurant-name-filter').on('input', debounce(handleFilterChange, 500));
        $('#city-filter').on('input', debounce(handleFilterChange, 500));
        
        // Immediate filter changes
        $('#cuisine-filter').on('change', handleFilterChange);
        $('#distance-filter').on('change', handleFilterChange);
        $('#featured-only').on('change', handleFilterChange);
        
        // Sort dropdown
        $('#sort-restaurants').on('change', handleSortChange);
        
        // Button actions
        $('#search-restaurants').on('click', handleFilterChange);
        $('#clear-filters').on('click', clearAllFilters);
    }

    /**
     * Initialize location detection for distance filtering
     */
    function initializeLocationDetection() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    window.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    // Enable distance filter
                    $('#distance-filter').prop('disabled', false);
                    console.log('Location enabled for distance filtering');
                },
                function(error) {
                    console.log('Location access denied');
                    $('#distance-filter').prop('disabled', true);
                }
            );
        }
    }

    /**
     * Handle filter changes
     */
    function handleFilterChange() {
        buildCurrentFilters();
        currentPage = 1; // Reset to first page when filters change
        loadAllRestaurants();
    }

    /**
     * Handle sort changes
     */
    function handleSortChange() {
        const sortOrder = $('#sort-restaurants').val();
        currentFilters.sort = sortOrder;
        loadAllRestaurants();
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        $('#restaurant-name-filter').val('');
        $('#city-filter').val('');
        $('#cuisine-filter').val('');
        $('#distance-filter').val('');
        $('#featured-only').prop('checked', false);
        $('#sort-restaurants').val('featured');
        
        currentFilters = {};
        currentPage = 1; // Reset to first page when clearing filters
        loadAllRestaurants();
    }

    /**
     * Build current filters object
     */
    function buildCurrentFilters() {
        currentFilters = {};

        // Restaurant name
        const restaurantName = $('#restaurant-name-filter').val().trim();
        if (restaurantName) {
            currentFilters.name = restaurantName;
        }

        // City
        const city = $('#city-filter').val().trim();
        if (city) {
            currentFilters.city = city;
        }

        // Cuisine
        const cuisine = $('#cuisine-filter').val();
        if (cuisine) {
            currentFilters.cuisine = cuisine;
        }

        // Distance (only if location is available)
        const distance = $('#distance-filter').val();
        if (distance && window.userLocation) {
            currentFilters.distance = distance;
            currentFilters.lat = window.userLocation.lat;
            currentFilters.lng = window.userLocation.lng;
        }

        // Featured only
        if ($('#featured-only').is(':checked')) {
            currentFilters.featured_only = true;
        }

        // Sort order
        const sortOrder = $('#sort-restaurants').val();
        if (sortOrder) {
            currentFilters.sort = sortOrder;
        }
    }

    /**
     * Load all restaurants from API
     */
    function loadAllRestaurants() {
        if (!lebonrestoSingle?.apiUrl) {
            console.error('API URL not available');
            return;
        }

        // Show loading state
        updateResultsCount(lebonrestoSingle.strings?.loadingRestaurants || 'Chargement des restaurants...', true);
        showRestaurantListLoading(true);

        // Build query parameters
        const queryParams = new URLSearchParams();
        Object.keys(currentFilters).forEach(key => {
            if (currentFilters[key] !== undefined && currentFilters[key] !== '') {
                queryParams.append(key, currentFilters[key]);
            }
        });

        const apiUrl = lebonrestoSingle.apiUrl + (queryParams.toString() ? '?' + queryParams.toString() : '');

        // Fetch restaurants
        fetch(apiUrl, {
            headers: {
                'X-WP-Nonce': lebonrestoSingle.nonce
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(restaurants => {
            allRestaurants = Array.isArray(restaurants) ? restaurants : [];
            
            // Debug: Log first restaurant data to see structure
            if (allRestaurants.length > 0) {
                console.log('First restaurant from API:', allRestaurants[0]);
                console.log('Principal image data:', allRestaurants[0].restaurant_meta?.principal_image);
            }
            
            // Update map markers
            updateMapMarkers();
            
            // Fit map to show all visible markers
            fitMapToMarkers();
            
            // Update restaurant list
            updateRestaurantList();
            
            // Update results count
            const count = allRestaurants.length;
            const countText = lebonrestoSingle.strings?.restaurantsFound || '%s restaurants trouvés';
            updateResultsCount(countText.replace('%s', count));
            
            showRestaurantListLoading(false);
        })
        .catch(error => {
            console.error('Error loading restaurants:', error);
            updateResultsCount(lebonrestoSingle.strings?.loadingError || 'Erreur lors du chargement des restaurants', true);
            showRestaurantListLoading(false);
        });
    }

    /**
     * Update map markers
     */
    function updateMapMarkers() {
        // Clear existing markers
        markersLayer.clearLayers();

        if (allRestaurants.length === 0) return;

        allRestaurants.forEach(restaurant => {
            const meta = restaurant.restaurant_meta || {};
            const lat = parseFloat(meta.latitude);
            const lng = parseFloat(meta.longitude);

            if (isNaN(lat) || isNaN(lng)) return;

            // Create marker icon based on restaurant type
            const isCurrentRestaurant = restaurant.id === currentRestaurantId;
            const isFeatured = meta.is_featured === '1';
            
            let markerIcon;
            if (isCurrentRestaurant) {
                // Current restaurant - location pin icon
                markerIcon = L.divIcon({
                    className: 'current-restaurant-marker',
                    html: `<div class="flex items-center justify-center relative">
                             <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 64 64">
                               <path fill="#ff9800" d="M53 24.267C53 42.633 32 61 32 61S11 42.633 11 24.267a21 21 0 1 1 42 0z"/>
                               <circle cx="32" cy="24" r="17" fill="#eeeeee"/>
                               <ellipse cx="39" cy="20" fill="#ff9800" rx="4" ry="5"/>
                               <path d="M32 2a22.16 22.16 0 0 0-22 22.267c0 7.841 3.6 16.542 10.7 25.86a86.428 86.428 0 0 0 10.642 11.626 1 1 0 0 0 1.316 0A86.428 86.428 0 0 0 43.3 50.127C50.4 40.809 54 32.108 54 24.267A22.16 22.16 0 0 0 32 2zm0 57.646c-3.527-3.288-20-19.5-20-35.379a20 20 0 1 1 40 0c0 15.88-16.473 32.091-20 35.379z" fill="#000000"/>
                               <path d="M32 6a18 18 0 1 0 18 18A18.021 18.021 0 0 0 32 6zm0 34a16 16 0 1 1 16-16 16.019 16.019 0 0 1-16 16z" fill="#000000"/>
                               <path d="M30 22c0 .188 0 .382-.582.673L28 23.382V14h-2v9.382l-1.418-.709C24 22.382 24 22.188 24 22v-8h-2v8a2.7 2.7 0 0 0 1.687 2.462l1.948.974a3 3 0 0 0 .365.131V36h2V25.567a3 3 0 0 0 .365-.131l1.947-.974A2.7 2.7 0 0 0 32 22v-8h-2zM39 14c-2.757 0-5 2.691-5 6 0 2.9 1.721 5.321 4 5.879V36h2V25.879c2.279-.558 4-2.981 4-5.879 0-3.309-2.243-6-5-6zm0 10c-1.654 0-3-1.794-3-4s1.346-4 3-4 3 1.794 3 4-1.346 4-3 4z" fill="#000000"/>
                             </svg>
                           </div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                });
            } else if (isFeatured) {
                // Featured restaurant marker - location pin icon
                markerIcon = L.divIcon({
                    className: 'featured-restaurant-marker',
                    html: `<div class="flex items-center justify-center">
                             <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 64 64">
                               <path fill="#ff9800" d="M53 24.267C53 42.633 32 61 32 61S11 42.633 11 24.267a21 21 0 1 1 42 0z"/>
                               <circle cx="32" cy="24" r="17" fill="#eeeeee"/>
                               <ellipse cx="39" cy="20" fill="#ff9800" rx="4" ry="5"/>
                               <path d="M32 2a22.16 22.16 0 0 0-22 22.267c0 7.841 3.6 16.542 10.7 25.86a86.428 86.428 0 0 0 10.642 11.626 1 1 0 0 0 1.316 0A86.428 86.428 0 0 0 43.3 50.127C50.4 40.809 54 32.108 54 24.267A22.16 22.16 0 0 0 32 2zm0 57.646c-3.527-3.288-20-19.5-20-35.379a20 20 0 1 1 40 0c0 15.88-16.473 32.091-20 35.379z" fill="#000000"/>
                               <path d="M32 6a18 18 0 1 0 18 18A18.021 18.021 0 0 0 32 6zm0 34a16 16 0 1 1 16-16 16.019 16.019 0 0 1-16 16z" fill="#000000"/>
                               <path d="M30 22c0 .188 0 .382-.582.673L28 23.382V14h-2v9.382l-1.418-.709C24 22.382 24 22.188 24 22v-8h-2v8a2.7 2.7 0 0 0 1.687 2.462l1.948.974a3 3 0 0 0 .365.131V36h2V25.567a3 3 0 0 0 .365-.131l1.947-.974A2.7 2.7 0 0 0 32 22v-8h-2zM39 14c-2.757 0-5 2.691-5 6 0 2.9 1.721 5.321 4 5.879V36h2V25.879c2.279-.558 4-2.981 4-5.879 0-3.309-2.243-6-5-6zm0 10c-1.654 0-3-1.794-3-4s1.346-4 3-4 3 1.794 3 4-1.346 4-3 4z" fill="#000000"/>
                             </svg>
                           </div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 24]
                });
            } else {
                // Regular restaurant marker - location pin icon
                markerIcon = L.divIcon({
                    className: 'regular-restaurant-marker',
                    html: `<div class="flex items-center justify-center">
                             <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 64 64">
                               <path fill="#ff9800" d="M53 24.267C53 42.633 32 61 32 61S11 42.633 11 24.267a21 21 0 1 1 42 0z"/>
                               <circle cx="32" cy="24" r="17" fill="#eeeeee"/>
                               <ellipse cx="39" cy="20" fill="#ff9800" rx="4" ry="5"/>
                               <path d="M32 2a22.16 22.16 0 0 0-22 22.267c0 7.841 3.6 16.542 10.7 25.86a86.428 86.428 0 0 0 10.642 11.626 1 1 0 0 0 1.316 0A86.428 86.428 0 0 0 43.3 50.127C50.4 40.809 54 32.108 54 24.267A22.16 22.16 0 0 0 32 2zm0 57.646c-3.527-3.288-20-19.5-20-35.379a20 20 0 1 1 40 0c0 15.88-16.473 32.091-20 35.379z" fill="#000000"/>
                               <path d="M32 6a18 18 0 1 0 18 18A18.021 18.021 0 0 0 32 6zm0 34a16 16 0 1 1 16-16 16.019 16.019 0 0 1-16 16z" fill="#000000"/>
                               <path d="M30 22c0 .188 0 .382-.582.673L28 23.382V14h-2v9.382l-1.418-.709C24 22.382 24 22.188 24 22v-8h-2v8a2.7 2.7 0 0 0 1.687 2.462l1.948.974a3 3 0 0 0 .365.131V36h2V25.567a3 3 0 0 0 .365-.131l1.947-.974A2.7 2.7 0 0 0 32 22v-8h-2zM39 14c-2.757 0-5 2.691-5 6 0 2.9 1.721 5.321 4 5.879V36h2V25.879c2.279-.558 4-2.981 4-5.879 0-3.309-2.243-6-5-6zm0 10c-1.654 0-3-1.794-3-4s1.346-4 3-4 3 1.794 3 4-1.346 4-3 4z" fill="#000000"/>
                             </svg>
                           </div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 20]
                });
            }

            const marker = L.marker([lat, lng], { icon: markerIcon });
            
            // Create popup content
            const popupContent = createMarkerPopup(restaurant, isCurrentRestaurant);
            marker.bindPopup(popupContent, {
                maxWidth: window.innerWidth <= 768 ? 250 : 300,
                className: `restaurant-popup ${isCurrentRestaurant ? 'current-popup' : ''}`,
                closeButton: true,
                autoClose: false,
                keepInView: true
            });

            // Add click handler
            marker.on('click', function() {
                highlightRestaurantInList(restaurant.id);
            });

            markersLayer.addLayer(marker);
        });

        // Highlight current restaurant
        highlightCurrentRestaurant();
    }

    /**
     * Fit map to show all visible markers
     */
    function fitMapToMarkers() {
        if (!map || !markersLayer || markersLayer.getLayers().length === 0) {
            return;
        }

        // Get all marker positions
        const bounds = L.latLngBounds();
        markersLayer.eachLayer(function(marker) {
            bounds.extend(marker.getLatLng());
        });

        // Fit map to show all markers with some padding
        if (bounds.isValid() && bounds.getNorth() !== bounds.getSouth()) {
            map.fitBounds(bounds, {
                padding: [20, 20], // Add padding around the bounds
                maxZoom: 15 // Don't zoom in too much
            });
        } else if (markersLayer.getLayers().length === 1) {
            // If only one marker, center on it with a reasonable zoom level
            const singleMarker = markersLayer.getLayers()[0];
            map.setView(singleMarker.getLatLng(), 13);
        }
    }

    /**
     * Create marker popup content
     */
    function createMarkerPopup(restaurant, isCurrentRestaurant) {
        const meta = restaurant.restaurant_meta || {};
        const title = restaurant.title?.rendered || 'Restaurant';
        const isFeatured = meta.is_featured === '1';
        const principalImage = meta.principal_image || {};

        // Debug logging
        console.log('🔥 SINGLE-RESTAURANT-UPDATED.JS IS RUNNING!');
        console.log('Restaurant data for popup:', restaurant);
        console.log('Principal image data:', principalImage);
        console.log('Meta data:', meta);

        // Create restaurant slug for URL
        const restaurantSlug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/-+/g, '-') // Replace multiple hyphens with single
            .trim();
        
        // Get current domain dynamically
        const currentDomain = window.location.origin;
        const restaurantUrl = `${currentDomain}/details/${restaurantSlug}`;
        
        let content = `<div class="restaurant-popup-content" style="padding: 0; margin: 0; cursor: pointer;" onclick="window.open('${restaurantUrl}', '_blank')">`;
        
        // Mobile responsive layout
        content += `<div style="display: flex; gap: 8px; align-items: flex-start; padding: 0; margin: 0;">`;
        
        // Left column: Principal image (responsive size)
        content += `<div style="flex-shrink: 0; padding: 0; margin: 0;">`;
        
        // Try different possible image sources
        let imageUrl = null;
        if (principalImage.thumbnail) {
            imageUrl = principalImage.thumbnail;
        } else if (principalImage.medium) {
            imageUrl = principalImage.medium;
        } else if (principalImage.full) {
            imageUrl = principalImage.full;
        } else if (typeof principalImage === 'string' && principalImage) {
            imageUrl = principalImage;
        }
        
        // Show image or placeholder (smaller on mobile)
        if (imageUrl) {
            content += `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(title)}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 0; margin: 0;" class="popup-image" />`;
        } else {
            content += `<div style="width: 80px; height: 80px; background-color: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 2px solid #d1d5db; padding: 0; margin: 0;" class="popup-placeholder">`;
            content += `<svg style="width: 24px; height: 24px; color: #9ca3af;" fill="currentColor" viewBox="0 0 24 24">`;
            content += `<path d="M8.1 13.34l2.83-2.83L3.91 3.5c-1.56 1.56-1.56 4.09 0 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 12l1.47-1.47z"/>`;
            content += `</svg>`;
            content += `</div>`;
        }
        
        content += `</div>`; // End left column
        
        // Right column: Restaurant information
        content += `<div style="flex: 1; min-width: 0; padding: 0; margin: 0;">`;
        
        // Restaurant name with click indicator
        content += `<h3 style="font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 6px 0; padding: 0; line-height: 1.3;">${escapeHtml(title)}</h3>`;
        
        // Click to view details indicator
        content += `<div style="display: flex; align-items: center; background-color: #fef3c7; border-radius: 4px; padding: 4px 6px; margin-bottom: 6px; font-size: 10px; color: #92400e;">`;
        content += `<i class="fas fa-external-link-alt" style="margin-right: 4px; font-size: 8px;"></i>`;
        content += `<span style="font-weight: 500;">Cliquez pour voir les détails</span>`;
        content += `</div>`;
        
        // Restaurant details as cards (smaller on mobile)
        if (meta.city) {
            content += `<div style="display: flex; align-items: center; background-color: #f9fafb; border-radius: 4px; padding: 4px 6px; margin-bottom: 3px; font-size: 10px;">`;
            content += `<i class="fas fa-map-marker-alt" style="margin-right: 4px; color: #f59e0b; font-size: 8px;"></i>`;
            content += `<span style="color: #374151; font-weight: 500;">${escapeHtml(meta.city)}</span>`;
            content += `</div>`;
        }
        
        if (meta.cuisine_type) {
            content += `<div style="display: flex; align-items: center; background-color: #f9fafb; border-radius: 4px; padding: 4px 6px; margin-bottom: 3px; font-size: 10px;">`;
            content += `<i class="fas fa-utensils" style="margin-right: 4px; color: #f59e0b; font-size: 8px;"></i>`;
            content += `<span style="color: #374151; font-weight: 500;">${escapeHtml(meta.cuisine_type.charAt(0).toUpperCase() + meta.cuisine_type.slice(1))}</span>`;
            content += `</div>`;
        }
        
        if (restaurant.distance) {
            content += `<div style="display: flex; align-items: center; background-color: #f0fdf4; border-radius: 4px; padding: 4px 6px; margin-bottom: 3px; font-size: 10px;">`;
            content += `<i class="fas fa-route" style="margin-right: 4px; color: #10b981; font-size: 8px;"></i>`;
            content += `<span style="color: #065f46; font-weight: 500;">${restaurant.distance} km</span>`;
            content += `</div>`;
        }
        
        content += `</div>`; // End right column
        content += `</div>`; // End flex container
        content += `</div>`; // End popup content
        
        // Debug: Log the complete HTML
        console.log('Complete popup HTML:', content);
        
        return content;
    }

    /**
     * Update restaurant list with pagination
     */
    function updateRestaurantList() {
        const container = $('#restaurants-container');
        container.empty();

        if (allRestaurants.length === 0) {
            container.html(`
                <div class="text-center py-8">
                    <i class="fas fa-search text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">${lebonrestoSingle.strings?.noRestaurants || 'Aucun restaurant trouvé'}</p>
                </div>
            `);
            updatePagination();
            return;
        }

        // Calculate pagination
        totalPages = Math.ceil(allRestaurants.length / restaurantsPerPage);
        currentPage = Math.min(currentPage, totalPages);
        
        const startIndex = (currentPage - 1) * restaurantsPerPage;
        const endIndex = startIndex + restaurantsPerPage;
        const restaurantsToShow = allRestaurants.slice(startIndex, endIndex);

        // Display restaurants for current page
        restaurantsToShow.forEach(restaurant => {
            const card = createCompactRestaurantCard(restaurant);
            container.append(card);
        });

        // Update pagination
        updatePagination();
    }

    /**
     * Create compact restaurant card for the list
     */
    function createCompactRestaurantCard(restaurant) {
        const meta = restaurant.restaurant_meta || {};
        const title = restaurant.title?.rendered || 'Restaurant';
        const isFeatured = meta.is_featured === '1';
        const isCurrentRestaurant = restaurant.id === currentRestaurantId;
        const principalImage = meta.principal_image || {};

        const card = $(`
            <div class="restaurant-card bg-white rounded-lg shadow-sm p-4 ${isFeatured ? 'border-2 border-yellow-400' : 'border-0'} ${isCurrentRestaurant ? 'bg-yellow-50' : ''}" data-restaurant-id="${restaurant.id}" style="min-height: 120px; width: 100%; flex-shrink: 0; position: relative;">
                
                <!-- Two Column Layout: Image Left, Info Right -->
                <div class="flex items-center h-full gap-4" style="display: contents;">
                    <!-- LEFT COLUMN: Restaurant Image -->
                    <div class="flex-shrink-0">
                        ${(() => {
                            let imageUrl = null;
                            if (principalImage.thumbnail) {
                                imageUrl = principalImage.thumbnail;
                            } else if (principalImage.medium) {
                                imageUrl = principalImage.medium;
                            } else if (principalImage.full) {
                                imageUrl = principalImage.full;
                            } else if (typeof principalImage === 'string' && principalImage) {
                                imageUrl = principalImage;
                            }
                            
                            if (imageUrl) {
                                return `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(title)}" class="w-32 h-32 object-cover rounded-lg shadow-sm" />`;
                            } else {
                                return `<div class="w-32 h-32 bg-gray-200 rounded-lg flex items-center justify-center shadow-sm">
                                          <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8.1 13.34l2.83-2.83L3.91 3.5c-1.56 1.56-1.56 4.09 0 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 12l1.47-1.47z"/>
                                          </svg>
                                        </div>`;
                            }
                        })()}
                    </div>
                    
                    <!-- RIGHT COLUMN: Restaurant Information -->
                    <div class="flex-1 min-w-0 h-full flex flex-col justify-between">
                        <!-- Top Section: Title -->
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-lg font-bold text-gray-900 truncate ${isCurrentRestaurant ? 'text-yellow-700' : ''}">${escapeHtml(title)}</h4>
                            </div>
                            
                            <!-- Restaurant Details - Better Structured -->
                            <div class="space-y-2">
                                ${meta.city ? `
                                    <div class="flex items-center bg-gray-50 rounded-lg px-3">
                                        <i class="fas fa-map-marker-alt text-yellow-500 mr-3 text-sm"></i>
                                        <span class="text-sm font-medium text-gray-700 truncate">${escapeHtml(meta.city)}</span>
                        </div>
                                ` : ''}
                                
                                ${meta.cuisine_type ? `
                                    <div class="flex items-center bg-gray-50 rounded-lg px-3">
                                        <i class="fas fa-utensils text-yellow-500 mr-3 text-sm"></i>
                                        <span class="text-sm font-medium text-gray-700 truncate">${escapeHtml(meta.cuisine_type.charAt(0).toUpperCase() + meta.cuisine_type.slice(1))}</span>
                        </div>
                                ` : ''}
                                
                                ${restaurant.distance ? `
                                    <div class="flex items-center bg-green-50 rounded-lg px-3">
                                        <i class="fas fa-route text-green-500 mr-3 text-sm"></i>
                                        <span class="text-sm font-medium text-green-700 truncate">${restaurant.distance} km de distance</span>
                                    </div>
                                ` : ''}
                            </div>
                            
                            <!-- Restaurant Description -->
                            ${meta.description ? `
                                <div class="mt-3">
                                    <p class="description text-xs text-gray-500 leading-relaxed">${escapeHtml(meta.description)}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                
                <!-- Action Icons - Bottom Right -->
                <div class="action-icons-container" style="position: absolute; bottom: 10px; right: 15px; display: flex; gap: 6px; z-index: 1000;">
                    <!-- View Details Icon -->
                    <a href="${restaurant.link}" class="action-icon" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </a>
                    
                    <!-- Phone Icon -->
                    ${meta.phone ? `
                    <a href="tel:${meta.phone}" class="action-icon" title="Téléphone">
                        <i class="fas fa-phone"></i>
                    </a>
                    ` : ''}
                    
                    <!-- Email Icon -->
                    ${meta.email ? `
                    <a href="mailto:${meta.email}" class="action-icon" title="Email">
                        <i class="fas fa-envelope"></i>
                    </a>
                    ` : ''}
                    
                    <!-- WhatsApp Icon -->
                    ${meta.phone ? `
                    <a href="https://wa.me/${meta.phone.replace(/[^0-9]/g, '')}" class="action-icon" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    ` : ''}
                </div>
            </div>
        `);

        // Add click handler to highlight on map
        card.on('click', function(e) {
            if (!$(e.target).closest('a').length) {
                highlightRestaurantOnMap(restaurant.id);
            }
        });

        return card;
    }

    /**
     * Highlight restaurant in list
     */
    function highlightRestaurantInList(restaurantId) {
        $('.restaurant-card').removeClass('ring-2 ring-yellow-400');
        $(`.restaurant-card[data-restaurant-id="${restaurantId}"]`).addClass('ring-2 ring-yellow-400');
        
        // Scroll to the card within the right column
        const card = $(`.restaurant-card[data-restaurant-id="${restaurantId}"]`);
        if (card.length) {
            const rightColumn = $('.right-column');
            const cardOffset = card.offset().top - rightColumn.offset().top;
            const rightColumnHeight = rightColumn.height();
            const cardHeight = card.outerHeight();
            
            if (cardOffset < 0 || cardOffset + cardHeight > rightColumnHeight) {
                rightColumn.animate({
                    scrollTop: rightColumn.scrollTop() + cardOffset - (rightColumnHeight / 2) + (cardHeight / 2)
                }, 300);
            }
        }
    }

    /**
     * Highlight restaurant on map
     */
    function highlightRestaurantOnMap(restaurantId) {
        const restaurant = allRestaurants.find(r => r.id === restaurantId);
        if (!restaurant || !restaurant.restaurant_meta) return;

        const lat = parseFloat(restaurant.restaurant_meta.latitude);
        const lng = parseFloat(restaurant.restaurant_meta.longitude);

        if (!isNaN(lat) && !isNaN(lng)) {
            map.setView([lat, lng], 16);
            
            // Find and open the popup
            markersLayer.eachLayer(function(layer) {
                if (layer.getLatLng().lat === lat && layer.getLatLng().lng === lng) {
                    layer.openPopup();
                }
            });
        }
    }

    /**
     * Highlight current restaurant
     */
    function highlightCurrentRestaurant() {
        if (currentRestaurantId) {
            setTimeout(() => {
                highlightRestaurantInList(currentRestaurantId);
            }, 500);
        }
    }

    /**
     * Update results count
     */
    function updateResultsCount(text, isError = false) {
        const counter = $('#map-results-count');
        counter.text(text);
        
        if (isError) {
            counter.removeClass('text-gray-700').addClass('text-red-600');
        } else {
            counter.removeClass('text-red-600').addClass('text-gray-700');
        }
    }

    /**
     * Show/hide loading state for restaurant list
     */
    function showRestaurantListLoading(show) {
        const container = $('#restaurants-container');
        
        if (show) {
            container.html(`
                <div class="text-center py-8">
                    <div class="loading-spinner mx-auto mb-3"></div>
                    <p class="text-gray-500">${lebonrestoSingle.strings?.loadingRestaurants || 'Loading restaurants...'}</p>
                </div>
            `);
        }
    }

    /**
     * Update pagination controls
     */
    function updatePagination() {
        const paginationInfo = $('#pagination-info');
        const paginationControls = $('#pagination-controls');
        
        if (allRestaurants.length === 0) {
            paginationInfo.text('No restaurants found');
            paginationControls.empty();
            return;
        }

        const startIndex = (currentPage - 1) * restaurantsPerPage + 1;
        const endIndex = Math.min(currentPage * restaurantsPerPage, allRestaurants.length);
        
        paginationInfo.text(`Showing ${startIndex}-${endIndex} of ${allRestaurants.length} restaurants`);
        
        // Clear existing controls
        paginationControls.empty();
        
        if (totalPages <= 1) {
            return;
        }

        // Previous button
        const prevBtn = $(`<button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i>
        </button>`);
        
        if (currentPage > 1) {
            prevBtn.on('click', () => goToPage(currentPage - 1));
        }
        
        paginationControls.append(prevBtn);

        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // First page
        if (startPage > 1) {
            const firstBtn = $(`<button class="pagination-btn">1</button>`);
            firstBtn.on('click', () => goToPage(1));
            paginationControls.append(firstBtn);
            
            if (startPage > 2) {
                paginationControls.append('<span class="text-gray-400">...</span>');
            }
        }

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = $(`<button class="pagination-btn ${i === currentPage ? 'active' : ''}">${i}</button>`);
            if (i !== currentPage) {
                pageBtn.on('click', () => goToPage(i));
            }
            paginationControls.append(pageBtn);
        }

        // Last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationControls.append('<span class="text-gray-400">...</span>');
            }
            
            const lastBtn = $(`<button class="pagination-btn">${totalPages}</button>`);
            lastBtn.on('click', () => goToPage(totalPages));
            paginationControls.append(lastBtn);
        }

        // Next button
        const nextBtn = $(`<button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''}>
            <i class="fas fa-chevron-right"></i>
        </button>`);
        
        if (currentPage < totalPages) {
            nextBtn.on('click', () => goToPage(currentPage + 1));
        }
        
        paginationControls.append(nextBtn);
    }

    /**
     * Go to specific page
     */
    function goToPage(page) {
        if (page < 1 || page > totalPages || page === currentPage) {
            return;
        }
        
        currentPage = page;
        updateRestaurantList();
        
        // Scroll to top of right column
        const rightColumn = $('.right-column');
        rightColumn.scrollTop(0);
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

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

})(jQuery);

