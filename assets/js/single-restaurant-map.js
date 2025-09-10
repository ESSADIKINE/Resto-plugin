(function($) {
    "use strict";

    // Global variables
    let map;
    let markersLayer;
    let allRestaurants = [];
    let filteredRestaurants = [];
    let currentRestaurantId;
    let currentFilters = {};

    // Initialize when document is ready
    $(document).ready(function() {
        initializeSingleRestaurantPage();
    });

    /**
     * Initialize the single restaurant page
     */
    function initializeSingleRestaurantPage() {
        // Get current restaurant data
        const restaurantDataElement = document.getElementById('current-restaurant-data');
        if (restaurantDataElement) {
            window.currentRestaurantData = JSON.parse(restaurantDataElement.textContent);
            currentRestaurantId = window.currentRestaurantData.id;
        }

        // Initialize map
        initializeMap();
        
        // Initialize filters
        initializeFilters();
        
        // Load all restaurants
        loadAllRestaurants();
    }

    /**
     * Initialize the map
     */
    function initializeMap() {
        const mapContainer = document.getElementById('restaurants-map');
        if (!mapContainer) return;

        // Get map center from current restaurant or default
        const center = lebonrestoSingle?.mapCenter || { lat: 48.8566, lng: 2.3522 };
        
        // Initialize map
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
            }
        });
    }

    /**
     * Initialize filter functionality
     */
    function initializeFilters() {
        // Search input
        $('#restaurant-search').on('input', debounce(handleFilterChange, 300));
        
        // Filter dropdowns and inputs
        $('#city-filter').on('input', debounce(handleFilterChange, 300));
        $('#cuisine-filter').on('change', handleFilterChange);
        $('#distance-filter').on('change', handleFilterChange);
        
        // Sort dropdown
        $('#sort-restaurants').on('change', handleSortChange);
        
        // Search button
        $('#search-restaurants').on('click', handleFilterChange);
        
        // Clear filters button
        $('#clear-filters').on('click', clearAllFilters);
    }

    /**
     * Handle filter changes
     */
    function handleFilterChange() {
        // Build filters object
        currentFilters = {};

        const searchText = $('#restaurant-search').val().trim();
        if (searchText) {
            currentFilters.name = searchText;
        }

        const cityFilter = $('#city-filter').val().trim();
        if (cityFilter) {
            currentFilters.city = cityFilter;
        }

        const cuisineFilter = $('#cuisine-filter').val();
        if (cuisineFilter) {
            currentFilters.cuisine = cuisineFilter;
        }

        const distanceFilter = $('#distance-filter').val();
        if (distanceFilter && window.userLocation) {
            currentFilters.distance = distanceFilter;
            currentFilters.lat = window.userLocation.lat;
            currentFilters.lng = window.userLocation.lng;
        }

        // Apply filters and reload
        loadAllRestaurants(currentFilters);
    }

    /**
     * Handle sort changes
     */
    function handleSortChange() {
        const sortOrder = $('#sort-restaurants').val();
        currentFilters.sort = sortOrder;
        
        // Re-apply current filters with new sort
        loadAllRestaurants(currentFilters);
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        $('#restaurant-search').val('');
        $('#city-filter').val('');
        $('#cuisine-filter').val('');
        $('#distance-filter').val('');
        $('#sort-restaurants').val('featured');
        
        currentFilters = {};
        loadAllRestaurants();
    }

    /**
     * Load all restaurants from API
     */
    function loadAllRestaurants(filters = {}) {
        if (!lebonrestoSingle?.apiUrl) {
            console.error('API URL not available');
            return;
        }

        // Show loading state
        updateResultsCount('Chargement...', true);
        showRestaurantListLoading(true);

        // Build query parameters
        const queryParams = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            if (filters[key] !== undefined && filters[key] !== '') {
                queryParams.append(key, filters[key]);
            }
        });

        const apiUrl = lebonrestoSingle.apiUrl + (queryParams.toString() ? '?' + queryParams.toString() : '');

        // Fetch restaurants
        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(restaurants => {
                allRestaurants = Array.isArray(restaurants) ? restaurants : [];
                filteredRestaurants = allRestaurants;
                
                // Update map markers
                updateMapMarkers();
                
                // Update restaurant list
                updateRestaurantList();
                
                // Update results count
                updateResultsCount(`${allRestaurants.length} restaurant${allRestaurants.length !== 1 ? 's' : ''} found`);
                
                showRestaurantListLoading(false);
            })
            .catch(error => {
                console.error('Error loading restaurants:', error);
                updateResultsCount('Erreur lors du chargement des restaurants', true);
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

        const bounds = [];

        allRestaurants.forEach(restaurant => {
            const meta = restaurant.restaurant_meta || {};
            const lat = parseFloat(meta.latitude);
            const lng = parseFloat(meta.longitude);

            if (isNaN(lat) || isNaN(lng)) return;

            bounds.push([lat, lng]);

            // Create marker icon
            const isCurrentRestaurant = restaurant.id === currentRestaurantId;
            const isFeatured = meta.is_featured === '1';
            
            let markerIcon;
            if (isCurrentRestaurant) {
                markerIcon = L.divIcon({
                    className: 'current-restaurant-marker',
                    html: `<div class="w-8 h-8 bg-yellow-400 border-4 border-white rounded-full shadow-lg flex items-center justify-center">
                             <i class="fas fa-star text-xs text-gray-800"></i>
                           </div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
            } else if (isFeatured) {
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
            const popupContent = createMarkerPopup(restaurant);
            marker.bindPopup(popupContent, {
                maxWidth: 300,
                className: 'restaurant-popup'
            });

            // Add click handler
            marker.on('click', function() {
                highlightRestaurantInList(restaurant.id);
            });

            markersLayer.addLayer(marker);
        });

        // Fit map to show all markers if we have results
        if (bounds.length > 0) {
            const group = new L.featureGroup(markersLayer.getLayers());
            map.fitBounds(group.getBounds().pad(0.1));
        }
    }

    /**
     * Create marker popup content
     */
    function createMarkerPopup(restaurant) {
        const meta = restaurant.restaurant_meta || {};
        const title = restaurant.title?.rendered || 'Restaurant';
        const isFeatured = meta.is_featured === '1';
        const isCurrentRestaurant = restaurant.id === currentRestaurantId;
        const principalImage = meta.principal_image || {};

        // Debug logging
        console.log('🚀 SINGLE-RESTAURANT-MAP.JS IS RUNNING!');
        console.log('Creating popup for:', title);
        console.log('Principal image data:', principalImage);

        let content = `<div class="restaurant-popup-content">`;
        
        // Two-column layout: Info on left, Image on right
        content += `<div style="display: flex; gap: 12px; align-items: flex-start;">`;
        
        // Left column: Restaurant information
        content += `<div style="flex: 1; min-width: 0;">`;
        
        // Title with badges
        content += `<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">`;
        content += `<h3 style="font-size: 16px; font-weight: 600; color: #1f2937; margin: 0; flex: 1;">${escapeHtml(title)}</h3>`;
        content += `<div style="display: flex; gap: 4px;">`;
        if (isFeatured) {
            content += `<span style="padding: 2px 6px; background-color: #fedc00; color: #fedc00; font-size: 10px; border-radius: 12px; font-weight: bold;">★</span>`;
        }
        if (isCurrentRestaurant) {
            content += `<span style="padding: 2px 6px; background-color: #fee2e2; color: #991b1b; font-size: 10px; border-radius: 12px; font-weight: bold;">Current</span>`;
        }
        content += `</div>`;
        content += `</div>`;

        // Address
        if (meta.address || meta.city) {
            content += `<p style="margin: 0 0 4px 0; font-size: 13px; color: #4b5563; display: flex; align-items: center; gap: 6px;">`;
            content += `<i class="fas fa-map-marker-alt" style="width: 12px; color: #6b7280;"></i>`;
            content += `${escapeHtml(meta.address || '')}${meta.city ? ', ' + escapeHtml(meta.city) : ''}`;
            content += `</p>`;
        }

        // Cuisine
        if (meta.cuisine_type) {
            content += `<p style="margin: 0 0 4px 0; font-size: 13px; color: #4b5563; display: flex; align-items: center; gap: 6px;">`;
            content += `<i class="fas fa-utensils" style="width: 12px; color: #6b7280;"></i>`;
            content += `${escapeHtml(meta.cuisine_type.charAt(0).toUpperCase() + meta.cuisine_type.slice(1))}`;
            content += `</p>`;
        }

        // Distance
        if (restaurant.distance) {
            content += `<p style="margin: 0 0 8px 0; font-size: 13px; color: #059669; display: flex; align-items: center; gap: 6px;">`;
            content += `<i class="fas fa-route" style="width: 12px; color: #059669;"></i>`;
            content += `${restaurant.distance} km de distance`;
            content += `</p>`;
        }

        // View details button
        content += `<div style="margin-top: 8px;">`;
        if (isCurrentRestaurant) {
            content += `<span style="display: inline-block; padding: 6px 12px; background-color: #d1d5db; color: #4b5563; font-size: 12px; font-weight: 500; border-radius: 6px; cursor: not-allowed;">`;
            content += `Current Restaurant`;
            content += `</span>`;
        } else {
            content += `<a href="${restaurant.link}" style="display: inline-block; padding: 6px 12px; background-color: #fedc00; color: #1f2937; font-size: 12px; font-weight: 500; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;">`;
            content += `Voir Détails`;
            content += `</a>`;
        }
        content += `</div>`;
        
        content += `</div>`; // End left column
        
        // Right column: Principal image
        content += `<div style="flex-shrink: 0;">`;
        
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
        
        // Show image or placeholder
        if (imageUrl) {
            content += `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(title)}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />`;
            content += `<div style="display: none; width: 80px; height: 80px; background-color: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 2px solid #d1d5db;">`;
        } else {
            content += `<div style="width: 80px; height: 80px; background-color: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 2px solid #d1d5db;">`;
        }
        
        // Fallback SVG icon
        content += `<svg style="width: 24px; height: 24px; color: #9ca3af;" fill="currentColor" viewBox="0 0 24 24">`;
        content += `<path d="M8.1 13.34l2.83-2.83L3.91 3.5c-1.56 1.56-1.56 4.09 0 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 12l1.47-1.47z"/>`;
        content += `</svg>`;
        content += `</div>`;
        
        content += `</div>`; // End right column
        content += `</div>`; // End flex container
        content += `</div>`; // End popup content
        
        return content;
    }

    /**
     * Update restaurant list
     */
    function updateRestaurantList() {
        const listContainer = $('#restaurants-list');
        listContainer.empty();

        if (allRestaurants.length === 0) {
            listContainer.html(`
                <div class="text-center py-8">
                    <i class="fas fa-search text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">${lebonrestoSingle.strings?.noRestaurants || 'Aucun restaurant trouvé'}</p>
                </div>
            `);
            return;
        }

        allRestaurants.forEach(restaurant => {
            const card = createRestaurantCard(restaurant);
            listContainer.append(card);
        });
    }

    /**
     * Create restaurant card
     */
    function createRestaurantCard(restaurant) {
        const meta = restaurant.restaurant_meta || {};
        const title = restaurant.title?.rendered || 'Restaurant';
        const isFeatured = meta.is_featured === '1';
        const isCurrentRestaurant = restaurant.id === currentRestaurantId;

        const card = $(`
            <div class="restaurant-card bg-white rounded-lg shadow-md hover:shadow-lg transition duration-300 p-4 border ${isCurrentRestaurant ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200'}" data-restaurant-id="${restaurant.id}">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-utensils text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-lg font-semibold text-gray-900 truncate ${isCurrentRestaurant ? 'text-yellow-700' : ''}">${escapeHtml(title)}</h3>
                            <div class="flex space-x-1">
                                ${isFeatured ? '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">★</span>' : ''}
                                ${isCurrentRestaurant ? '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Current</span>' : ''}
                            </div>
                        </div>
                        
                        ${meta.city ? `<p class="text-sm text-gray-600 mb-1"><i class="fas fa-map-marker-alt mr-1"></i>${escapeHtml(meta.city)}</p>` : ''}
                        ${meta.cuisine_type ? `<p class="text-sm text-gray-600 mb-2"><i class="fas fa-utensils mr-1"></i>${escapeHtml(meta.cuisine_type.charAt(0).toUpperCase() + meta.cuisine_type.slice(1))}</p>` : ''}
                        ${restaurant.distance ? `<p class="text-sm text-green-600 mb-2"><i class="fas fa-route mr-1"></i>${restaurant.distance} km de distance</p>` : ''}
                        
                        <div class="flex items-center space-x-2 mt-3">
                            <a href="${restaurant.link}" class="flex-1 px-3 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 text-sm font-medium rounded transition duration-200 text-center">
                                ${lebonrestoSingle.strings?.viewDetails || 'View Details'}
                            </a>
                            ${meta.phone ? `<a href="tel:${escapeHtml(meta.phone)}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded transition duration-200" title="${lebonrestoSingle.strings?.phoneTitle || 'Call'}"><i class="fas fa-phone text-sm"></i></a>` : ''}
                            ${meta.email ? `<a href="mailto:${escapeHtml(meta.email)}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded transition duration-200" title="${lebonrestoSingle.strings?.emailTitle || 'Email'}"><i class="fas fa-envelope text-sm"></i></a>` : ''}
                        </div>
                    </div>
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
        
        // Scroll to the card
        const card = $(`.restaurant-card[data-restaurant-id="${restaurantId}"]`);
        if (card.length) {
            const container = $('#restaurants-list');
            const cardTop = card.position().top;
            const containerHeight = container.height();
            const cardHeight = card.outerHeight();
            
            if (cardTop < 0 || cardTop + cardHeight > containerHeight) {
                container.animate({
                    scrollTop: container.scrollTop() + cardTop - (containerHeight / 2) + (cardHeight / 2)
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
        const listContainer = $('#restaurants-list');
        
        if (show) {
            listContainer.html(`
                <div class="text-center py-8">
                    <div class="loading-spinner mx-auto mb-2"></div>
                    <p class="text-gray-500">Chargement des restaurants...</p>
                </div>
            `);
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
