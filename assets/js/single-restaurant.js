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
        
        // Add global event listener for VR popup
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const vtPopup = document.getElementById('virtual-tour-popup');
                if (vtPopup?.classList.contains('show')) {
                    closeVirtualTourPopup();
                }
            }
        });
        
    });

    /**
     * Initialize the updated single restaurant page
     */
    function initializeSingleRestaurantUpdated() {
        
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
        
    }

    /**
     * Initialize the map centered on current restaurant
     */
    function initializeMap() {
        
        const mapContainer = document.getElementById('restaurants-map');
        if (!mapContainer) {
            console.error('Map container not found!');
            return;
        }

        // Get map center from current restaurant or default
        const center = lebonrestoSingle?.mapCenter || { lat: 33.5731, lng: -7.5898 }; // Casablanca default
        
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
        // Desktop filter elements
        $('#restaurant-name-filter').on('input', debounce(handleFilterChange, 500));
        $('#city-filter').on('input', debounce(handleFilterChange, 500));
        $('#cuisine-filter').on('change', handleFilterChange);
        $('#distance-filter').on('change', handleFilterChange);
        $('#featured-only').on('change', handleFilterChange);
        $('#sort-restaurants').on('change', handleSortChange);
        $('#search-restaurants').on('click', handleFilterChange);
        $('#clear-filters').on('click', clearAllFilters);
        
        // Mobile filter elements
        initializeMobileFilters();
    }
    
    /**
     * Initialize mobile filter functionality
     */
    function initializeMobileFilters() {
        
        // Mobile filter toggle
        const mobileFilterToggle = document.getElementById('mobile-filter-btn');
        const mobileFilterOverlay = document.getElementById('mobile-filter-overlay');
        const mobileFilterPanel = document.querySelector('.mobile-filter-panel');
        
        
        if (mobileFilterToggle) {
            mobileFilterToggle.addEventListener('click', function() {
                if (mobileFilterOverlay && mobileFilterPanel) {
                    mobileFilterOverlay.style.display = 'block';
                    mobileFilterPanel.classList.remove('-translate-x-full');
                    document.body.style.overflow = 'hidden';
                }
            });
        }
        
        // Close mobile filter
        const closeMobileFilters = document.getElementById('close-mobile-filters');
        if (closeMobileFilters) {
            closeMobileFilters.addEventListener('click', closeMobileFilter);
        }
        
        // Close on overlay click
        if (mobileFilterOverlay) {
            mobileFilterOverlay.addEventListener('click', function(e) {
                if (e.target === mobileFilterOverlay) {
                    closeMobileFilter();
                }
            });
        }
        
        // Mobile filter form elements
        const mobileRestaurantName = document.getElementById('mobile-restaurant-name');
        const mobileCity = document.getElementById('mobile-city');
        const mobileCuisine = document.getElementById('mobile-cuisine');
        const mobileDistance = document.getElementById('mobile-distance');
        const mobileSort = document.getElementById('mobile-sort');
        const mobileFeaturedOnly = document.getElementById('mobile-featured-only');
        
        // Mobile filter inputs with debounce
        if (mobileRestaurantName) {
            mobileRestaurantName.addEventListener('input', debounce(handleMobileFilterChange, 500));
        }
        if (mobileCity) {
            mobileCity.addEventListener('input', debounce(handleMobileFilterChange, 500));
        }
        
        // Mobile filter immediate changes
        if (mobileCuisine) {
            mobileCuisine.addEventListener('change', handleMobileFilterChange);
        }
        if (mobileDistance) {
            mobileDistance.addEventListener('change', function() {
                handleMobileFilterChange();
            });
        }
        if (mobileSort) {
            mobileSort.addEventListener('change', handleMobileSortChange);
        }
        if (mobileFeaturedOnly) {
            mobileFeaturedOnly.addEventListener('change', handleMobileFilterChange);
        }
        
        // Mobile filter buttons
        const mobileApplyFilters = document.getElementById('mobile-apply-filters');
        const mobileClearAll = document.getElementById('mobile-clear-all');
        
        if (mobileApplyFilters) {
            mobileApplyFilters.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Apply the filters first
                handleMobileFilterChange();
                
                // Close the panel after applying filters
                closeMobileFilter();
                
                // Additional direct close as backup
                setTimeout(() => {
                    const panel = document.querySelector('.mobile-filter-panel');
                    const overlay = document.getElementById('mobile-filter-overlay');
                    if (panel && overlay) {
                        panel.classList.add('-translate-x-full');
                        overlay.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                }, 100);
                
            });
        } else {
            console.error('Mobile apply filters button not found!');
        }
        
        if (mobileClearAll) {
            mobileClearAll.addEventListener('click', function() {
                clearMobileFilters();
            });
        }
    }
    
    /**
     * Close mobile filter panel
     */
    function closeMobileFilter() {
        const mobileFilterOverlay = document.getElementById('mobile-filter-overlay');
        const mobileFilterPanel = document.querySelector('.mobile-filter-panel');
        
        
        if (mobileFilterOverlay && mobileFilterPanel) {
            
            // Add the translate class to slide the panel out (this hides it)
            mobileFilterPanel.classList.add('-translate-x-full');
            
            // Hide the overlay after a short delay to allow animation
            setTimeout(() => {
                mobileFilterOverlay.style.display = 'none';
            }, 300); // Match the CSS transition duration
            
            // Restore body scroll
            document.body.style.overflow = '';
            
        } else {
            console.error('Could not find mobile filter elements to close');
        }
    }
    
    /**
     * Handle mobile filter changes
     */
    function handleMobileFilterChange() {
        
        // Get mobile filter values
        const mobileRestaurantName = document.getElementById('mobile-restaurant-name');
        const mobileCity = document.getElementById('mobile-city');
        const mobileCuisine = document.getElementById('mobile-cuisine');
        const mobileDistance = document.getElementById('mobile-distance');
        const mobileFeaturedOnly = document.getElementById('mobile-featured-only');
        
        
        // Update desktop filter values to match mobile
        if (mobileRestaurantName) {
            const desktopName = document.getElementById('restaurant-name-filter');
            if (desktopName) {
                desktopName.value = mobileRestaurantName.value;
            }
        }
        
        if (mobileCity) {
            const desktopCity = document.getElementById('city-filter');
            if (desktopCity) {
                desktopCity.value = mobileCity.value;
            }
        }
        
        if (mobileCuisine) {
            const desktopCuisine = document.getElementById('cuisine-filter');
            if (desktopCuisine) {
                desktopCuisine.value = mobileCuisine.value;
            }
        }
        
        if (mobileDistance) {
            const desktopDistance = document.getElementById('distance-filter');
            if (desktopDistance) {
                desktopDistance.value = mobileDistance.value;
            }
        }
        
        if (mobileFeaturedOnly) {
            const desktopFeatured = document.getElementById('featured-only');
            if (desktopFeatured) {
                desktopFeatured.checked = mobileFeaturedOnly.checked;
            }
        }
        
        // Apply the filters
        handleFilterChange();
    }
    
    /**
     * Handle mobile sort changes
     */
    function handleMobileSortChange() {
        const mobileSort = document.getElementById('mobile-sort');
        const desktopSort = document.getElementById('sort-restaurants');
        
        if (mobileSort && desktopSort) {
            desktopSort.value = mobileSort.value;
            handleSortChange();
        }
    }
    
    /**
     * Clear all mobile filters
     */
    function clearMobileFilters() {
        
        // Clear mobile filter inputs
        const mobileRestaurantName = document.getElementById('mobile-restaurant-name');
        const mobileCity = document.getElementById('mobile-city');
        const mobileCuisine = document.getElementById('mobile-cuisine');
        const mobileDistance = document.getElementById('mobile-distance');
        const mobileSort = document.getElementById('mobile-sort');
        const mobileFeaturedOnly = document.getElementById('mobile-featured-only');
        
        if (mobileRestaurantName) mobileRestaurantName.value = '';
        if (mobileCity) mobileCity.value = '';
        if (mobileCuisine) mobileCuisine.value = '';
        if (mobileDistance) mobileDistance.value = '';
        if (mobileSort) mobileSort.value = 'featured';
        if (mobileFeaturedOnly) mobileFeaturedOnly.checked = false;
        
        
        // Clear desktop filters too
        clearAllFilters();
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
                    
                    // Enable mobile distance filter
                    const mobileDistance = document.getElementById('mobile-distance');
                    if (mobileDistance) {
                        mobileDistance.disabled = false;
                    }
                    
                },
                function(error) {
                    $('#distance-filter').prop('disabled', true);
                    
                    // Disable mobile distance filter
                    const mobileDistance = document.getElementById('mobile-distance');
                    if (mobileDistance) {
                        mobileDistance.disabled = true;
                    }
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
        console.log('🗺️ Updating map markers...');
        console.log('All restaurants count:', allRestaurants.length);
        
        // Clear existing markers
        markersLayer.clearLayers();

        if (allRestaurants.length === 0) {
            console.log('No restaurants to display on map');
            return;
        }

        allRestaurants.forEach(restaurant => {
            const meta = restaurant.restaurant_meta || {};
            const lat = parseFloat(meta.latitude);
            const lng = parseFloat(meta.longitude);

            // Debug logging for coordinates
            console.log(`Restaurant: ${restaurant.title?.rendered}, Lat: ${lat}, Lng: ${lng}, Valid: ${!isNaN(lat) && !isNaN(lng)}`);

            if (isNaN(lat) || isNaN(lng)) {
                console.warn(`Invalid coordinates for restaurant ${restaurant.title?.rendered}: lat=${meta.latitude}, lng=${meta.longitude}`);
                return;
            }

            // Create marker icon based on restaurant type
            const isCurrentRestaurant = restaurant.id === currentRestaurantId;
            const isFeatured = meta.is_featured === '1';
            
            let markerIcon;
            if (isCurrentRestaurant) {
                // Current restaurant - location pin icon
                markerIcon = L.divIcon({
                    className: 'current-restaurant-marker',
                    html: `<div style="display: flex; align-items: center; justify-content: center; position: relative; width: 32px; height: 32px;">
                             <svg style="width: 32px; height: 32px;" fill="currentColor" viewBox="0 0 64 64">
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
                    html: `<div style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px;">
                             <svg style="width: 24px; height: 24px;" fill="currentColor" viewBox="0 0 64 64">
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
                    html: `<div style="display: flex; align-items: center; justify-content: center; width: 20px; height: 20px;">
                             <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 64 64">
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
            console.log(`Created marker for ${restaurant.title?.rendered} at [${lat}, ${lng}] with icon:`, markerIcon);
            
            // Create popup content
            const popupContent = createMarkerPopup(restaurant, isCurrentRestaurant);
            marker.bindPopup(popupContent, {
                maxWidth: window.innerWidth <= 768 ? 280 : 320,
                minWidth: 280,
                className: `restaurant-popup ${isCurrentRestaurant ? 'current-popup' : ''}`,
                closeButton: true,
                autoClose: false,
                keepInView: true,
                offset: [0, -10]
            });

            // Add click handler
            marker.on('click', function() {
                console.log(`Marker clicked for ${restaurant.title?.rendered}`);
                // Highlight functionality removed
            });

            markersLayer.addLayer(marker);
            console.log(`Added marker for ${restaurant.title?.rendered} at [${lat}, ${lng}]`);
        });

        console.log(`Total markers added: ${markersLayer.getLayers().length}`);

        // Highlight current restaurant
        highlightCurrentRestaurant();
    }

    /**
     * Fit map to show all visible markers
     */
    function fitMapToMarkers() {
        console.log('🎯 Fitting map to markers...');
        console.log('Map exists:', !!map);
        console.log('Markers layer exists:', !!markersLayer);
        console.log('Markers count:', markersLayer ? markersLayer.getLayers().length : 0);
        
        if (!map || !markersLayer || markersLayer.getLayers().length === 0) {
            console.log('Cannot fit map - missing map, markers layer, or no markers');
            return;
        }

        // Get all marker positions
        const bounds = L.latLngBounds();
        markersLayer.eachLayer(function(marker) {
            const latLng = marker.getLatLng();
            console.log('Marker position:', latLng);
            bounds.extend(latLng);
        });

        console.log('Bounds:', bounds);

        // Fit map to show all markers with some padding
        if (bounds.isValid() && bounds.getNorth() !== bounds.getSouth()) {
            console.log('Fitting bounds to show all markers');
            map.fitBounds(bounds, {
                padding: [20, 20], // Add padding around the bounds
                maxZoom: 15 // Don't zoom in too much
            });
        } else if (markersLayer.getLayers().length === 1) {
            // If only one marker, center on it with a reasonable zoom level
            const singleMarker = markersLayer.getLayers()[0];
            const latLng = singleMarker.getLatLng();
            console.log('Centering on single marker:', latLng);
            map.setView(latLng, 13);
        } else {
            console.log('No valid bounds found, using default view');
            // Fallback to default view
            const center = lebonrestoSingle?.mapCenter || { lat: 33.5731, lng: -7.5898 }; // Casablanca
            map.setView([center.lat, center.lng], 10);
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
        console.log('Creating popup for restaurant:', restaurant);

        // Create restaurant slug for URL
        const restaurantSlug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/-+/g, '-') // Replace multiple hyphens with single
            .trim();
        
        // Get home URL from WordPress localization
        const homeUrl = window.lebonrestoSingle?.homeUrl || window.location.origin;
        const restaurantUrl = `${homeUrl}details/${restaurantSlug}`;
        
        // Start popup content with proper structure
        let content = `<div class="restaurant-popup-content" style="min-width: 280px; max-width: 320px;">`;
        
        // Header section with restaurant name
        content += `<div style="margin-bottom: 1rem;">`;
        content += `<h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 700; color: #1f2937; line-height: 1.3;">${escapeHtml(title)}</h3>`;
        
        // Rating section (if available) - use same logic as restaurant cards
        const googleRating = parseFloat(meta.google_rating) || 0;
        const localRating = parseFloat(meta.average_rating) || 0;
        const rating = googleRating > 0 ? googleRating : localRating;
        
        const googleReviewCount = parseInt(meta.google_review_count) || 0;
        const localReviewCount = parseInt(meta.review_count) || 0;
        const reviewCount = googleReviewCount > 0 ? googleReviewCount : localReviewCount;
        
        console.log('Rating data for popup:', {
            googleRating: googleRating,
            localRating: localRating,
            finalRating: rating,
            googleReviewCount: googleReviewCount,
            localReviewCount: localReviewCount,
            finalReviewCount: reviewCount,
            meta: meta
        });
        
        // Show rating if we have actual data
        if (rating && rating > 0) {
            content += `<div class="rating-section" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">`;
            content += `<div class="rating-stars" style="display: flex; gap: 1px;">`;
            
            // Generate star rating
            for (let i = 1; i <= 5; i++) {
                const starColor = i <= Math.floor(rating) ? '#fbbf24' : '#d1d5db';
                content += `<span style="color: ${starColor}; font-size: 0.9rem;">★</span>`;
            }
            
            content += `</div>`;
            content += `<span class="rating-value" style="font-weight: 600; color: #1f2937; font-size: 0.9rem;">${rating.toFixed(1)}</span>`;
            if (reviewCount && reviewCount > 0) {
                content += `<span class="review-count" style="color: #6b7280; font-size: 0.8rem;">(${reviewCount} avis)</span>`;
            }
            content += `</div>`;
        } else {
            // Show "No rating available" message
            content += `<div class="rating-section" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">`;
            content += `<span style="color: #6b7280; font-size: 0.85rem; font-style: italic;">Aucune note disponible</span>`;
            content += `</div>`;
        }
        
        content += `</div>`; // End header section
        
        // Details section
        content += `<div style="margin-bottom: 1rem;">`;
        
        // Cuisine type
        if (meta.cuisine_type) {
            content += `<div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">`;
            content += `<svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280;">`;
            content += `<path fill="currentColor" d="M14.051 6.549v.003l1.134 1.14 3.241-3.25.003-.002 1.134 1.136-3.243 3.252 1.134 1.14a1 1 0 0 0 .09-.008c.293-.05.573-.324.72-.474l.005-.006 2.596-2.603L22 8.016l-2.597 2.604a3.73 3.73 0 0 1-1.982 1.015 4.3 4.3 0 0 1-3.162-.657l-.023-.016-.026-.018-1.366 1.407 8.509 8.512L20.219 22l-.002-.002-6.654-6.663-2.597 2.76-7.3-7.315C1.967 8.948 1.531 6.274 2.524 4.198c.241-.504.566-.973.978-1.386l8.154 8.416 1.418-1.423-.039-.045c-.858-1.002-1.048-2.368-.62-3.595a4.15 4.15 0 0 1 .983-1.561L16 2l1.135 1.138-2.598 2.602-.047.045c-.16.151-.394.374-.433.678zM3.809 5.523c-.362 1.319-.037 2.905 1.06 4.103L10.93 15.7l1.408-1.496zM2.205 20.697 3.34 21.84l4.543-4.552-1.135-1.143z"></path>`;
            content += `</svg>`;
            content += `<span style="font-size: 0.85rem; color: #374151;">${escapeHtml(meta.cuisine_type)}</span>`;
        content += `</div>`;
        }
        
        // Price range
        if (meta.price_range) {
            content += `<div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">`;
            content += `<svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280;">`;
            content += `<path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"></path>`;
            content += `</svg>`;
            content += `<span style="font-size: 0.85rem; color: #374151;">${escapeHtml(meta.price_range)}</span>`;
            content += `</div>`;
        }
        
        // Address
        if (meta.address) {
            content += `<a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(meta.address)}" target="_blank" rel="noopener" style="display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.25rem; text-decoration: none;">`;
            content += `<svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280; margin-top: 0.1rem;">`;
            content += `<path fill="currentColor" d="M4.25 9.799c0-4.247 3.488-7.707 7.75-7.707s7.75 3.46 7.75 7.707c0 2.28-1.138 4.477-2.471 6.323-1.31 1.813-2.883 3.388-3.977 4.483l-.083.083-.002.002-1.225 1.218-1.213-1.243-.03-.03-.012-.013c-1.1-1.092-2.705-2.687-4.035-4.53-1.324-1.838-2.452-4.024-2.452-6.293"></path>`;
            content += `</svg>`;
            content += `<span style="font-size: 0.85rem; color: #2563eb; line-height: 1.3;">${escapeHtml(meta.address)}</span>`;
            content += `</a>`;
        }
        
        // Phone number
        if (meta.phone) {
            content += `<div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">`;
            content += `<svg viewBox="0 0 24 24" width="14" height="14" style="color: #6b7280;">`;
            content += `<path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"></path>`;
            content += `</svg>`;
            content += `<a href="tel:${escapeHtml(meta.phone)}" style="font-size: 0.85rem; color: #3b82f6; text-decoration: none;">${escapeHtml(meta.phone)}</a>`;
            content += `</div>`;
        }
        
        content += `</div>`; // End details section
        
        // Action section
        content += `<div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">`;
        content += `<a href="${restaurantUrl}" class="popup-link" style="display: inline-block; width: 100%; text-align: center; background-color: #fedc00; color: #1f2937; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; font-weight: 600; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f59e0b'" onmouseout="this.style.backgroundColor='#fedc00'">`;
        content += `Voir tous les détails`;
        content += `</a>`;
        content += `</div>`;
        
        content += `</div>`; // End popup content
        
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
     * Create compact restaurant card for the list - Redesigned to match all restaurants page
     */
    function createCompactRestaurantCard(restaurant) {
        const meta = restaurant.restaurant_meta || {};
        const title = restaurant.title?.rendered || 'Restaurant';
        const isFeatured = meta.is_featured === '1';
        const isCurrentRestaurant = restaurant.id === currentRestaurantId;
        const link = restaurant.link || '#';
        
        // Use Google rating if available, fallback to local rating
        const googleRating = parseFloat(meta.google_rating) || 0;
        const localRating = parseFloat(meta.average_rating) || 0;
        const rating = googleRating > 0 ? googleRating : localRating;
        
        // Use Google review count if available, fallback to local count
        const googleReviewCount = parseInt(meta.google_review_count) || 0;
        const localReviewCount = parseInt(meta.review_count) || 0;
        const reviewCount = googleReviewCount > 0 ? googleReviewCount : localReviewCount;
        
        const cuisineType = meta.cuisine_type || '';
        
        // Calculate price range
        const minPrice = parseFloat(meta.min_price) || 0;
        const maxPrice = parseFloat(meta.max_price) || 0;
        const priceRange = getPriceRangeDisplay(minPrice, maxPrice);
        
        // Build Google Maps URL
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
            <div class="restaurant-card ${isCurrentRestaurant ? 'current-restaurant' : ''}" data-restaurant-id="${restaurant.id}">
                <div class="card-layout">
                    <div class="card-image">
                        <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(title)}" class="restaurant-image" loading="lazy">
                        <div class="image-overlay">
                            <a href="#" class="save-btn" aria-label="Ouvrir la carte">
                                <div>
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.25 9.799c0-4.247 3.488-7.707 7.75-7.707s7.75 3.46 7.75 7.707c0 2.28-1.138 4.477-2.471 6.323-1.31 1.813-2.883 3.388-3.977 4.483l-.083.083-.002.002-1.225 1.218-1.213-1.243-.03-.03-.012-.013c-1.1-1.092-2.705-2.687-4.035-4.53-1.324-1.838-2.452-4.024-2.452-6.293M12 3.592c-3.442 0-6.25 2.797-6.25 6.207 0 1.796.907 3.665 2.17 5.415 1.252 1.736 2.778 3.256 3.886 4.357l.043.042.16.164.148-.149.002-.002.061-.06c1.103-1.105 2.605-2.608 3.843-4.322 1.271-1.76 2.187-3.64 2.187-5.445 0-3.41-2.808-6.207-6.25-6.207m1.699 5.013a1.838 1.838 0 1 0-3.397 1.407A1.838 1.838 0 0 0 13.7 8.605m-2.976-2.38a3.338 3.338 0 1 1 2.555 6.168 3.338 3.338 0 0 1-2.555-6.169"></path>
                                </svg>
                                </div>
                            </a>
                            ${meta.virtual_tour_url ? `
                                <a href="#" class="vr-btn" aria-label="Visite virtuelle">
                                    <div>
                                    <svg viewBox="0 0 24 24" width="16" height="16">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM7 9c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm5 9c-4 0-6-3-6-3s2-3 6-3 6 3 6 3-2 3-6 3zm5-9c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1z"/>
                                    </svg>
                                    </div>
                                </a>
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
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.25 9.799c0-4.247 3.488-7.707 7.75-7.707s7.75 3.46 7.75 7.707c0 2.28-1.138 4.477-2.471 6.323-1.31 1.813-2.883 3.388-3.977 4.483l-.083.083-.002.002-1.225 1.218-1.213-1.243-.03-.30-.012-.013c-1.1-1.092-2.705-2.687-4.035-4.53-1.324-1.838-2.452-4.024-2.452-6.293"></path>
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


        return $card;
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
        // Highlight functionality removed
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
     * Open virtual tour popup
     */
    function openVirtualTourPopup(restaurant) {
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
        
        if (iframe) {
            iframe.src = virtualTourUrl;
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

