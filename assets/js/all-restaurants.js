(function($) {
    "use strict";

    // Global variables
    let currentPage = 1;
    let totalPages = 1;
    let restaurantsPerPage = 10;
    let isLoading = false;
    let currentFilters = {};
    let allRestaurants = [];
    let filteredRestaurants = [];

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Page Tous les Restaurants: DOM prêt');
        console.log('lebonrestoAll config:', lebonrestoAll);
        initializeAllRestaurantsPage();
    });

    /**
     * Initialize the All Restaurants page
     */
    function initializeAllRestaurantsPage() {
        console.log('Initialisation de la page Tous les Restaurants...');
        
        // Check if required elements exist
        if (!$('#restaurant-name-filter').length) {
            console.error('Restaurant name filter not found');
        }
        if (!$('#restaurants-list').length) {
            console.error('Liste des restaurants non trouvée');
        }
        
        // Initialize filters
        initializeFilters();
        
        // Initialize sorting
        initializeSorting();
        
        // Initialize pagination
        initializePagination();
        
        // Initialize location detection
        initializeLocationDetection();
        
        // Load initial restaurants
        loadRestaurants();
        
        console.log('Initialisation de la page Tous les Restaurants terminée');
    }

    /**
     * Initialize filter functionality
     */
    function initializeFilters() {
        // Real-time search with debounce
        $('#restaurant-name-filter').on('input', debounce(handleFilterChange, 500));
        $('#city-filter').on('input', debounce(handleFilterChange, 500));
        
        // Immediate filter changes
        $('#cuisine-filter').on('change', handleFilterChange);
        $('#featured-only').on('change', handleFilterChange);
        
        // Distance button clicks
        $('.distance-btn').on('click', function() {
            console.log('Bouton de distance cliqué !');
            const button = $(this);
            const distance = button.data('distance');
            
            console.log('Button distance:', distance);
            console.log('User location available:', !!window.userLocation);
            
            // Check if location is available
            if (!window.userLocation) {
                console.log('No location available, requesting native permission...');
                // Force native browser permission dialog
                forceLocationPermission();
                return;
            }
            
            console.log('Location available, applying filter...');
            
            // Remove active class from all buttons
            $('.distance-btn').removeClass('active');
            
            // Add active class to clicked button
            button.addClass('active');
            
            // Trigger filter change
            handleFilterChange();
        });
        
        // Clear button action
        $('#clear-filters').on('click', clearAllFilters);
        
        // Initialize sort dropdown
        initializeSortDropdown();
    }

    /**
     * Initialize sorting functionality
     */
    function initializeSorting() {
        $('#sort-order').on('change', function() {
            currentFilters.sort = $(this).val();
            currentPage = 1; // Reset to first page
            loadRestaurants(true); // Replace current results
        });
    }

    /**
     * Initialize sort dropdown functionality
     */
    function initializeSortDropdown() {
        // Toggle dropdown visibility
        $('#sort-dropdown-btn').on('click', function(e) {
            e.stopPropagation();
            $('#sort-dropdown-menu').toggleClass('hidden');
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#sort-dropdown-btn, #sort-dropdown-menu').length) {
                $('#sort-dropdown-menu').addClass('hidden');
            }
        });
        
        // Handle select change
        $('#sort-select').on('change', function() {
            const sortValue = $(this).val();
            console.log('Tri changé vers:', sortValue);
            
            // Apply sort
            currentFilters.sort = sortValue;
            loadRestaurants();
            
            // Close dropdown
            $('#sort-dropdown-menu').addClass('hidden');
        });
    }

    /**
     * Initialize pagination
     */
    function initializePagination() {
        // Mobile filter toggle functionality
        initializeMobileFilters();
    }

    /**
     * Initialize mobile filter functionality
     */
    function initializeMobileFilters() {
        const mobileFilterBtn = $('#mobile-filter-btn');
        const mobileFilterPanel = $('#mobile-filter-panel');
        const mobileFilterOverlay = $('#mobile-filter-overlay');
        const closeMobileFilters = $('#close-mobile-filters');

        // Open mobile filter panel
        function openMobileFilterPanel() {
            console.log('Ouverture du panneau de filtres mobile');
            mobileFilterPanel.removeClass('-translate-x-full');
            mobileFilterOverlay.removeClass('hidden');
            $('body').addClass('overflow-hidden');
            // Switch to close icon
            $('.mobile-filter-toggle').addClass('open');
        }

        // Click handler for toggle button
        mobileFilterBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Check if panel is open or closed
            if (mobileFilterPanel.hasClass('-translate-x-full')) {
                openMobileFilterPanel();
            } else {
                closeMobileFilterPanel();
            }
        });

        // Close mobile filter panel
        function closeMobileFilterPanel() {
            console.log('Fermeture du panneau de filtres mobile');
            mobileFilterPanel.addClass('-translate-x-full');
            mobileFilterOverlay.addClass('hidden');
            $('body').removeClass('overflow-hidden');
            // Switch to menu icon
            $('.mobile-filter-toggle').removeClass('open');
        }

        // Close on overlay click
        mobileFilterOverlay.on('click', closeMobileFilterPanel);

        // Close on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && !mobileFilterPanel.hasClass('-translate-x-full')) {
                closeMobileFilterPanel();
            }
        });

        // Sync mobile filters with desktop filters
        syncMobileFilters();

        // Mobile filter change handlers
        $('#mobile-restaurant-name-filter').on('input', function() {
            $('#restaurant-name-filter').val($(this).val());
            handleFilterChange();
        });

        $('#mobile-city-filter').on('input', function() {
            $('#city-filter').val($(this).val());
            handleFilterChange();
        });

        $('#mobile-cuisine-filter').on('change', function() {
            $('#cuisine-filter').val($(this).val());
            handleFilterChange();
        });

        $('#mobile-featured-only').on('change', function() {
            $('#featured-only').prop('checked', $(this).is(':checked'));
            handleFilterChange();
        });

        // Mobile apply filters button
        $('#mobile-search-restaurants').on('click', function() {
            closeMobileFilterPanel();
        });

        // Mobile clear filters button
        $('#mobile-clear-filters').on('click', function() {
            clearAllFilters();
            syncMobileFilters();
            closeMobileFilterPanel();
        });
    }

    /**
     * Sync mobile filters with desktop filters
     */
    function syncMobileFilters() {
        $('#mobile-restaurant-name-filter').val($('#restaurant-name-filter').val());
        $('#mobile-city-filter').val($('#city-filter').val());
        $('#mobile-cuisine-filter').val($('#cuisine-filter').val());
        $('#mobile-featured-only').prop('checked', $('#featured-only').is(':checked'));
    }

    /**
     * Initialize location detection for distance filtering
     */
    function initializeLocationDetection() {
        const distanceButtons = $('.distance-btn');
        const locationStatus = $('#location-status');
        
        // Initially mark buttons as location-disabled
        distanceButtons.addClass('location-disabled');
        
        if (navigator.geolocation) {
            // Try to get location with high accuracy
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    window.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    // Enable distance buttons
                    distanceButtons.removeClass('location-disabled');
                    
                    // Update status indicator
                    locationStatus.html('<i class="fas fa-check-circle text-green-500 mr-1"></i>Location enabled');
                    locationStatus.removeClass('text-gray-500').addClass('text-green-600');
                    
                    console.log('Location enabled for distance filtering:', window.userLocation);
                },
                function(error) {
                    console.log('Location access denied:', error);
                    
                    // Update status indicator based on error type
                    let errorMessage = 'Location denied';
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMessage = 'Location bloquée - Cliquez sur un bouton de distance pour activer';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMessage = 'Localisation indisponible';
                    } else if (error.code === error.TIMEOUT) {
                        errorMessage = 'Délai d\'attente de localisation dépassé';
                    }
                    
                    locationStatus.html('<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>' + errorMessage);
                    locationStatus.removeClass('text-gray-500').addClass('text-red-600');
                    
                    // Keep buttons clickable for permission request
                    distanceButtons.removeClass('location-disabled');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 minutes
                }
            );
        } else {
            console.log('Geolocation not supported by this browser');
            
            // Update status indicator
            locationStatus.html('<i class="fas fa-times-circle text-red-500 mr-1"></i>Geolocation not supported');
            locationStatus.removeClass('text-gray-500').addClass('text-red-600');
        }
    }

    /**
     * Force native browser location permission dialog
     */
    function forceLocationPermission() {
        console.log('Forcing native location permission dialog...');
        const distanceButtons = $('.distance-btn');
        const locationStatus = $('#location-status');
        
        // Update status to show permission request
        locationStatus.html('<i class="fas fa-spinner fa-spin text-blue-500 mr-1"></i>Demande d\'autorisation de localisation...');
        locationStatus.removeClass('text-gray-500 text-red-600').addClass('text-blue-600');
        
        if (navigator.geolocation) {
            // Try to force native dialog by using different approaches
            const options = {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            };
            
            // Method 1: Try getCurrentPosition with different options
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    window.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    // Enable distance buttons
                    distanceButtons.removeClass('location-disabled');
                    
                    // Update status indicator
                    locationStatus.html('<i class="fas fa-check-circle text-green-500 mr-1"></i>Localisation activée');
                    locationStatus.removeClass('text-blue-600').addClass('text-green-600');
                    
                    console.log('Location permission granted:', window.userLocation);
                    
                    // Show success message
                    showLocationMessage('Accès à la localisation accordé ! Vous pouvez maintenant utiliser le filtrage par distance.', 'success');
                },
                function(error) {
                    console.log('Location permission denied:', error);
                    
                    // Method 2: Try watchPosition which sometimes triggers dialog
                    if (error.code === error.PERMISSION_DENIED) {
                        console.log('Trying watchPosition to trigger native dialog...');
                        
                        const watchId = navigator.geolocation.watchPosition(
                            function(position) {
                                // Clear watch
                                navigator.geolocation.clearWatch(watchId);
                                
                                window.userLocation = {
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude
                                };
                                
                                // Enable distance buttons
                                distanceButtons.removeClass('location-disabled');
                                
                                // Update status indicator
                                locationStatus.html('<i class="fas fa-check-circle text-green-500 mr-1"></i>Localisation activée');
                                locationStatus.removeClass('text-blue-600').addClass('text-green-600');
                                
                                console.log('Location permission granted via watchPosition:', window.userLocation);
                                
                                // Show success message
                                showLocationMessage('Accès à la localisation accordé ! Vous pouvez maintenant utiliser le filtrage par distance.', 'success');
                            },
                            function(watchError) {
                                // Clear watch
                                navigator.geolocation.clearWatch(watchId);
                                
                                console.log('WatchPosition also denied:', watchError);
                                
                                // Method 3: Try to reset and request again
                                console.log('Trying to reset permission state...');
                                
                                // Clear any cached location
                                window.userLocation = null;
                                
                                // Try again with a slight delay
                                setTimeout(() => {
                                    navigator.geolocation.getCurrentPosition(
                                        function(position) {
                                            window.userLocation = {
                                                lat: position.coords.latitude,
                                                lng: position.coords.longitude
                                            };
                                            
                                            distanceButtons.removeClass('location-disabled');
                                            locationStatus.html('<i class="fas fa-check-circle text-green-500 mr-1"></i>Localisation activée');
                                            locationStatus.removeClass('text-blue-600').addClass('text-green-600');
                                            
                                            console.log('Location permission granted on retry:', window.userLocation);
                                            showLocationMessage('Accès à la localisation accordé ! Vous pouvez maintenant utiliser le filtrage par distance.', 'success');
                                        },
                                        function(retryError) {
                                            console.log('Retry also failed:', retryError);
                                            
                                            // Show final error message with instructions
                                            locationStatus.html('<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>Localisation bloquée - Cliquez pour activer');
                                            locationStatus.removeClass('text-blue-600').addClass('text-red-600');
                                            
                                            // Show detailed instructions popup
                                                                    showLocationInstructionsPopup();
                                        },
                                        {
                                            enableHighAccuracy: true,
                                            timeout: 10000,
                                            maximumAge: 0
                                        }
                                    );
                                }, 1000);
                            },
                            options
                        );
                        
                        // Clear watch after 3 seconds if no response
                        setTimeout(() => {
                            navigator.geolocation.clearWatch(watchId);
                        }, 3000);
                        
                    } else {
                        // Other errors
                        let errorMessage = 'Erreur de localisation';
                        if (error.code === error.POSITION_UNAVAILABLE) {
                            errorMessage = 'Localisation indisponible - veuillez réessayer';
                        } else if (error.code === error.TIMEOUT) {
                            errorMessage = 'Délai d\'attente de localisation dépassé - veuillez réessayer';
                        }
                        
                        locationStatus.html('<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>' + errorMessage);
                        locationStatus.removeClass('text-blue-600').addClass('text-red-600');
                        
                        showLocationInstructionsPopup();
                    }
                },
                options
            );
        } else {
            locationStatus.html('<i class="fas fa-times-circle text-red-500 mr-1"></i>Géolocalisation non supportée');
            locationStatus.removeClass('text-blue-600').addClass('text-red-600');
            
            showLocationMessage('Votre navigateur ne supporte pas les services de localisation.', 'error');
        }
    }

    /**
     * Request location permission when user clicks distance button
     */
    function requestLocationPermission() {
        console.log('Requesting location permission...');
        const distanceButtons = $('.distance-btn');
        const locationStatus = $('#location-status');
        
        // Update status to show permission request
        locationStatus.html('<i class="fas fa-spinner fa-spin text-blue-500 mr-1"></i>Requesting location permission...');
        locationStatus.removeClass('text-gray-500 text-red-600').addClass('text-blue-600');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    window.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    // Enable distance buttons
                    distanceButtons.removeClass('location-disabled');
                    
                    // Update status indicator
                    locationStatus.html('<i class="fas fa-check-circle text-green-500 mr-1"></i>Location enabled');
                    locationStatus.removeClass('text-blue-600').addClass('text-green-600');
                    
                    console.log('Location permission granted:', window.userLocation);
                    
                    // Show success message
                    showLocationMessage('Location access granted! You can now use distance filtering.', 'success');
                },
                function(error) {
                    console.log('Location permission denied:', error);
                    
                    // Update status indicator
                    let errorMessage = 'Location permission denied';
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMessage = 'Please enable location in your browser settings';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMessage = 'Location unavailable - please try again';
                    } else if (error.code === error.TIMEOUT) {
                        errorMessage = 'Location request timed out - please try again';
                    }
                    
                    locationStatus.html('<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>' + errorMessage);
                    locationStatus.removeClass('text-blue-600').addClass('text-red-600');
                    
                    // Show error message
                    showLocationMessage('Location access is required for distance filtering. Please enable location permissions in your browser.', 'error');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0 // Don't use cached location
                }
            );
        } else {
            locationStatus.html('<i class="fas fa-times-circle text-red-500 mr-1"></i>Geolocation not supported');
            locationStatus.removeClass('text-blue-600').addClass('text-red-600');
            
            showLocationMessage('Your browser does not support location services.', 'error');
        }
    }

    /**
     * Show location permission message
     */
    function showLocationMessage(message, type) {
        // Remove existing message
        $('.location-message').remove();
        
        // Create message element
        const messageClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        const iconClass = type === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500';
        
        const messageHtml = `
            <div class="location-message fixed top-4 right-4 z-50 max-w-sm p-4 border-l-4 rounded shadow-lg ${messageClass}" style="animation: slideInRight 0.3s ease-out;">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 ${type === 'success' ? 'text-green-500 hover:bg-green-100 focus:ring-green-600' : 'text-red-500 hover:bg-red-100 focus:ring-red-600'}" onclick="$(this).closest('.location-message').fadeOut(300, function() { $(this).remove(); });">
                                <span class="sr-only">Dismiss</span>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add message to page
        $('body').append(messageHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $('.location-message').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Show location permission popup (like browser dialog)
     */
    function showLocationPermissionPopup() {
        // Remove existing popup
        $('.location-permission-popup').remove();
        
        const popupHtml = `
            <div class="location-permission-popup fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="animation: fadeIn 0.3s ease-out;">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" style="animation: slideInUp 0.3s ease-out;">
                    <!-- Header -->
                    <div class="flex items-center px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-blue-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Autoriser l'accès à la localisation</h3>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="px-6 py-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 text-lg mr-3 mt-1"></i>
                            <div class="flex-1">
                                <p class="text-sm text-gray-700 mb-4">
                                    Ce site souhaite connaître votre position pour filtrer les restaurants par distance. 
                                    Veuillez autoriser l'accès à la localisation dans les paramètres de votre navigateur.
                                </p>
                                
                                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
                                    <div class="flex">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 mt-0.5"></i>
                                        <div class="text-sm text-yellow-800">
                                            <p class="font-medium">Comment activer la localisation :</p>
                                            <ul class="mt-1 space-y-1 text-xs">
                                                <li>• Cliquez sur l'icône de localisation dans la barre d'adresse</li>
                                                <li>• Ou allez dans Paramètres → Confidentialité → Localisation</li>
                                                <li>• Activez "Autoriser les sites à demander votre position"</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="flex items-center justify-between px-6 py-4 bg-gray-50 rounded-b-lg">
                        <button onclick="closeLocationPermissionPopup()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Annuler
                        </button>
                        <button onclick="retryLocationPermission()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Réessayer
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add popup to page
        $('body').append(popupHtml);
    }

    /**
     * Close location permission popup
     */
    function closeLocationPermissionPopup() {
        $('.location-permission-popup').fadeOut(300, function() {
            $(this).remove();
        });
    }

    /**
     * Retry location permission
     */
    function retryLocationPermission() {
        closeLocationPermissionPopup();
        requestLocationPermission();
    }

    /**
     * Show Google-style location permission popup
     */
    function showLocationInstructionsPopup() {
        // Remove existing popup
        $('.location-instructions-popup').remove();
        
        const popupHtml = `
            <div class="location-instructions-popup fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(0, 0, 0, 0.8); animation: fadeIn 0.3s ease-out;">
                <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4" style="animation: slideInUp 0.3s ease-out; font-family: 'Google Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900">Connaître votre position</h3>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="px-6 py-5">
                        <!-- Remember decision option -->
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium text-gray-700">Mémoriser ma décision</span>
                            <select class="text-sm border border-gray-300 rounded px-2 py-1 bg-white">
                                <option>Jusqu'à ce que je ferme ce site</option>
                                <option>Toujours sur ce site</option>
                                <option>Ne jamais sur ce site</option>
                            </select>
                        </div>
                        
                        <!-- Information message -->
                        <div class="flex items-start mb-4">
                            <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                <span class="text-blue-600 text-xs font-bold">i</span>
                            </div>
                            <div class="text-sm text-gray-700 leading-relaxed">
                                <p>
                                    Ce site souhaite connaître votre <span class="font-semibold text-purple-600">position précise</span>. 
                                    Votre navigateur peut uniquement lui fournir des données de <span class="font-semibold text-purple-600">position générale</span> 
                                    étant donné que la <span class="font-semibold text-purple-600">localisation est désactivée</span>. 
                                    <a href="#" class="text-blue-600 hover:underline">En savoir plus</a>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Instructions -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="w-5 h-5 bg-yellow-100 rounded-full flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                    <span class="text-yellow-600 text-xs">!</span>
                                </div>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-medium mb-2">Pour activer la localisation :</p>
                                    <ol class="space-y-1 text-xs">
                                        <li>1. Cliquez sur l'icône de localisation dans la barre d'adresse</li>
                                        <li>2. Sélectionnez "Autoriser" pour ce site</li>
                                        <li>3. Ou activez la localisation dans les paramètres de votre appareil</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="flex items-center justify-end px-6 py-4 bg-gray-50 rounded-b-lg space-x-3">
                        <button onclick="closeLocationInstructionsPopup()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Bloquer
                        </button>
                        <button onclick="retryLocationPermission()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Autoriser
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add popup to page
        $('body').append(popupHtml);
    }

    /**
     * Close location instructions popup
     */
    function closeLocationInstructionsPopup() {
        $('.location-instructions-popup').fadeOut(300, function() {
            $(this).remove();
        });
    }

    // Make functions globally available
    window.closeLocationPermissionPopup = closeLocationPermissionPopup;
    window.retryLocationPermission = retryLocationPermission;
    window.closeLocationInstructionsPopup = closeLocationInstructionsPopup;

    /**
     * Handle filter changes
     */
    function handleFilterChange() {
        console.log('Filtre modifié, application des filtres...');
        applyFiltersAndSorting();
    }

    /**
     * Handle distance slider changes
     */
    function handleDistanceSliderChange() {
        updateDistanceSliderDisplay();
        
        // Only trigger search if location is available
        if (window.userLocation) {
            handleFilterChange();
        }
    }

    /**
     * Update distance slider display
     */
    function updateDistanceSliderDisplay() {
        const value = $('#distance-slider').val();
        $('#distance-value').text(value + 'km');
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

        // Cuisine (multi-select)
        const cuisine = $('#cuisine-filter').val();
        if (cuisine && cuisine.length > 0) {
            currentFilters.cuisine = Array.isArray(cuisine) ? cuisine : [cuisine];
        }

        // Distance (button selection, only if location is available)
        const selectedDistance = $('.distance-btn.active').data('distance');
        if (selectedDistance && window.userLocation) {
            currentFilters.distance = selectedDistance;
            currentFilters.lat = window.userLocation.lat;
            currentFilters.lng = window.userLocation.lng;
            console.log('Distance filter applied:', selectedDistance + 'km from', window.userLocation);
        } else if (selectedDistance && !window.userLocation) {
            console.log('Distance selected but location not available');
        }

        // Featured only
        if ($('#featured-only').is(':checked')) {
            currentFilters.featured_only = true;
        }

        // Sort order
        const sortOrder = $('#sort-order').val();
        if (sortOrder) {
            currentFilters.sort = sortOrder;
        }

        // Pagination
        currentFilters.page = currentPage;
        if (lebonrestoAll.perPage) {
            currentFilters.per_page = lebonrestoAll.perPage;
        }
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        $('#restaurant-name-filter').val('');
        $('#city-filter').val('');
        $('#cuisine-filter').val([]);
        $('.distance-btn').removeClass('active');
        $('#featured-only').prop('checked', false);
        $('#sort-select').val('featured');
        $('#sort-text').text('Sort by');
        
        // Reset floating labels
        $('.floating-label, .floating-select-label').removeClass('floating-label-active');
        
        // Remove any location messages
        $('.location-message').remove();
        
        currentFilters = {};
        currentPage = 1;
        
        applyFiltersAndSorting();
    }

    // Make clearAllFilters globally available
    window.clearAllFilters = clearAllFilters;

    /**
     * Load restaurants from API
     */
    function loadRestaurants(replace = true) {
        if (isLoading) return;
        
        // Show loading state
        showLoadingState(true);
        
        // Build query parameters - load all restaurants without pagination
        const queryParams = new URLSearchParams();
        queryParams.append('per_page', 100); // Load more to get all restaurants

        const apiUrl = lebonrestoAll.apiUrl + '?' + queryParams.toString();
        
        console.log('Loading all restaurants from API:', apiUrl);
        
        // Fetch restaurants
        fetch(apiUrl, {
            headers: {
                'X-WP-Nonce': lebonrestoAll.nonce
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(restaurants => {
            allRestaurants = Array.isArray(restaurants) ? restaurants : [];
            console.log('Loaded', allRestaurants.length, 'restaurants from API');
            applyFiltersAndSorting();
        })
        .catch(error => {
            console.error('Error loading restaurants:', error);
            showErrorState();
        })
        .finally(() => {
            showLoadingState(false);
        });
    }

    /**
     * Apply filters and sorting to restaurants
     */
    function applyFiltersAndSorting() {
        console.log('Applying filters and sorting...');
        
        // Start with all restaurants
        filteredRestaurants = [...allRestaurants];
        
        // Apply filters
        buildCurrentFilters();
        
        // Filter by restaurant name
        if (currentFilters.restaurant_name) {
            const searchTerm = currentFilters.restaurant_name.toLowerCase();
            filteredRestaurants = filteredRestaurants.filter(restaurant => 
                restaurant.title?.rendered?.toLowerCase().includes(searchTerm)
            );
        }
        
        // Filter by city
        if (currentFilters.city) {
            const cityTerm = currentFilters.city.toLowerCase();
            filteredRestaurants = filteredRestaurants.filter(restaurant => {
                const meta = restaurant.restaurant_meta || {};
                return meta.city?.toLowerCase().includes(cityTerm);
            });
        }
        
        // Filter by cuisine
        if (currentFilters.cuisine && currentFilters.cuisine.length > 0) {
            filteredRestaurants = filteredRestaurants.filter(restaurant => {
                const meta = restaurant.restaurant_meta || {};
                const restaurantCuisine = meta.cuisine_type?.toLowerCase();
                return currentFilters.cuisine.some(cuisine => 
                    restaurantCuisine?.includes(cuisine.toLowerCase())
                );
            });
        }
        
        // Filter by featured
        if (currentFilters.featured_only) {
            filteredRestaurants = filteredRestaurants.filter(restaurant => {
                const meta = restaurant.restaurant_meta || {};
                return meta.is_featured === '1';
            });
        }
        
        // Apply sorting
        const sortValue = $('#sort-select').val() || 'featured';
        filteredRestaurants = sortRestaurants(filteredRestaurants, sortValue);
        
        // Reset to page 1
        currentPage = 1;
        
        // Update UI
        updateRestaurantsList();
        updatePagination();
        
        console.log('Filtered restaurants:', filteredRestaurants.length);
    }
    
    /**
     * Sort restaurants based on selected criteria
     */
    function sortRestaurants(restaurants, sortValue) {
        const sorted = [...restaurants];
        
        switch (sortValue) {
            case 'featured':
                return sorted.sort((a, b) => {
                    const aFeatured = a.restaurant_meta?.is_featured === '1' ? 1 : 0;
                    const bFeatured = b.restaurant_meta?.is_featured === '1' ? 1 : 0;
                    return bFeatured - aFeatured;
                });
            case 'newest':
                return sorted.sort((a, b) => new Date(b.date) - new Date(a.date));
            case 'name':
                return sorted.sort((a, b) => 
                    (a.title?.rendered || '').localeCompare(b.title?.rendered || '')
                );
            case 'distance':
                // For distance sorting, we'd need user location
                // For now, just return as-is
                return sorted;
            default:
                return sorted;
        }
    }

    /**
     * Update restaurants list with pagination
     */
    function updateRestaurantsList() {
        const list = $('#restaurants-list');
            list.empty();
        
        if (filteredRestaurants.length === 0) {
            list.html(`
                <div class="text-center py-12">
                    <i class="fas fa-search text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">Aucun restaurant trouvé</p>
                </div>
            `);
            return;
        }
        
        // Calculate pagination
        totalPages = Math.ceil(filteredRestaurants.length / restaurantsPerPage);
        currentPage = Math.min(currentPage, totalPages);
        
        const startIndex = (currentPage - 1) * restaurantsPerPage;
        const endIndex = startIndex + restaurantsPerPage;
        const restaurantsToShow = filteredRestaurants.slice(startIndex, endIndex);
        
        // Display restaurants for current page
        restaurantsToShow.forEach(restaurant => {
            const card = createRestaurantCard(restaurant);
            list.append(card);
        });
    }
    
    /**
     * Update pagination controls
     */
    function updatePagination() {
        const paginationInfo = $('#pagination-info');
        const paginationControls = $('#pagination-controls');
        
        if (filteredRestaurants.length === 0) {
            paginationInfo.text('Aucun restaurant trouvé');
            paginationControls.empty();
            return;
        }

        const startIndex = (currentPage - 1) * restaurantsPerPage + 1;
        const endIndex = Math.min(currentPage * restaurantsPerPage, filteredRestaurants.length);
        
        paginationInfo.text(`Affichage ${startIndex}-${endIndex} sur ${filteredRestaurants.length} restaurants`);
        
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
        updateRestaurantsList();
        updatePagination();
        
        // Scroll to top of restaurants container
        $('.restaurants-container').scrollTop(0);
    }


    /**
     * Create restaurant card HTML with image slider
     */
    function createRestaurantCard(restaurant) {
        const meta = restaurant.restaurant_meta || {};
        const title = restaurant.title?.rendered || 'Restaurant';
        const isFeatured = meta.is_featured === '1';
        const description = meta.description || '';
        
        // Get images for slider
        const images = [];
        
        // Add principal image if exists
        if (meta.principal_image && meta.principal_image.full) {
            images.push(meta.principal_image.full);
        }
        
        // Add gallery images if exist
        if (meta.gallery_images && Array.isArray(meta.gallery_images)) {
            meta.gallery_images.forEach(img => {
                if (img.full) {
                    images.push(img.full);
                }
            });
        }
        
        // If no images, use placeholder
        if (images.length === 0) {
            images.push('https://via.placeholder.com/400x300?text=' + encodeURIComponent(title));
        }
        
        // Build image slider HTML
        let sliderHtml = '';
        if (images.length > 1) {
            // Multiple images - create slider
            const imagesHtml = images.map((img, index) => 
                `<img src="${escapeHtml(img)}" alt="${escapeHtml(title)}" class="slider-image" data-index="${index}" />`
            ).join('');
            
            const dotsHtml = images.map((_, index) => 
                `<span class="slider-dot ${index === 0 ? 'active' : ''}" data-index="${index}"></span>`
            ).join('');
            
            sliderHtml = `
                <div class="slider-container">
                    <div class="slider-images" data-current="0">
                        ${imagesHtml}
                    </div>
                    <button class="slider-nav prev" data-restaurant-id="${restaurant.id}">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="slider-nav next" data-restaurant-id="${restaurant.id}">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="slider-dots">
                        ${dotsHtml}
                    </div>
                </div>
            `;
        } else {
            // Single image - no slider
            sliderHtml = `
                <div class="slider-container">
                    <div class="slider-images">
                        <img src="${escapeHtml(images[0])}" alt="${escapeHtml(title)}" class="slider-image" />
                    </div>
                </div>
            `;
        }

        const card = $(`
            <div class="restaurant-card" data-restaurant-id="${restaurant.id}">
                <!-- Card Content - Three Columns -->
                <div class="card-content">
                    <!-- Left Column - Image Slider -->
                    <div class="image-slider">
                        ${sliderHtml}
                    </div>
                    
                    <!-- Middle Column - Restaurant Info -->
                    <div class="restaurant-info">
                        <!-- Restaurant Name -->
                        <h3 class="restaurant-name">
                            ${escapeHtml(title)}
                            ${isFeatured ? `<span class="featured-badge">★</span>` : ''}
                        </h3>
                        
                        <!-- Category -->
                        ${meta.cuisine_type ? `<div class="restaurant-category">
                            ${escapeHtml(meta.cuisine_type.charAt(0).toUpperCase() + meta.cuisine_type.slice(1))}
                        </div>` : ''}
                        
                        <!-- Description -->
                        ${description ? `<div class="restaurant-description">
                            ${escapeHtml(description.substring(0, 200))}${description.length > 200 ? '...' : ''}
                        </div>` : ''}
                        
                        <!-- Additional Info -->
                        <div class="restaurant-meta">
                            ${meta.city ? `<div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${escapeHtml(meta.city)}</span>
                            </div>` : ''}
                            ${restaurant.distance ? `<div class="meta-item">
                                <i class="fas fa-route"></i>
                                <span>${restaurant.distance} km de distance</span>
                            </div>` : ''}
                        </div>
                    </div>
                    
                    <!-- Right Column - Action Icons -->
                    <div class="action-icons-column">
                        <div class="action-icons-container">
                            <a href="${restaurant.link}" class="action-icon-btn" title="${lebonrestoAll.strings?.viewDetails || 'View Details'}">
                                <i class="fas fa-eye"></i>
                            </a>
                            ${meta.phone ? `<a href="tel:${escapeHtml(meta.phone)}" class="action-icon-btn" title="${lebonrestoAll.strings?.phoneTitle || 'Call restaurant'}">
                                <i class="fas fa-phone"></i>
                            </a>` : ''}
                            <a href="https://wa.me/${escapeHtml(meta.whatsapp || meta.phone || '1234567890')}" class="action-icon-btn whatsapp-btn" title="${lebonrestoAll.strings?.whatsappTitle || 'WhatsApp restaurant'}" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            ${meta.email ? `<a href="mailto:${escapeHtml(meta.email)}" class="action-icon-btn" title="${lebonrestoAll.strings?.emailTitle || 'Email restaurant'}">
                                <i class="fas fa-envelope"></i>
                            </a>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `);

        // Initialize slider if multiple images
        if (images.length > 1) {
            initializeCardSlider(card, restaurant.id);
        }

        return card;
    }

    /**
     * Initialize image slider for a restaurant card
     */
    function initializeCardSlider(card, restaurantId) {
        const sliderImages = card.find('.slider-images');
        const prevBtn = card.find('.slider-nav.prev');
        const nextBtn = card.find('.slider-nav.next');
        const dots = card.find('.slider-dot');
        
        let currentIndex = 0;
        let direction = 1; // 1 for forward, -1 for backward
        const totalImages = sliderImages.find('.slider-image').length;
        
        // Auto-advance slider every 3 seconds with looping pattern
        const autoAdvance = setInterval(() => {
            if (totalImages > 1) {
                // Update index based on direction
                currentIndex += direction;
                
                // Check if we need to reverse direction
                if (currentIndex >= totalImages - 1) {
                    direction = -1; // Start going backward
                } else if (currentIndex <= 0) {
                    direction = 1; // Start going forward
                }
                
                updateSlider();
            }
        }, 3000);
        
        // Store interval ID for cleanup
        card.data('slider-interval', autoAdvance);
        
        function updateSlider() {
            const translateX = -currentIndex * 100;
            sliderImages.css('transform', `translateX(${translateX}%)`);
            
            // Update dots
            dots.removeClass('active');
            dots.eq(currentIndex).addClass('active');
        }
        
        // Previous button
        prevBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            currentIndex = currentIndex > 0 ? currentIndex - 1 : totalImages - 1;
            direction = -1; // Set direction to backward
            updateSlider();
        });
        
        // Next button
        nextBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            currentIndex = (currentIndex + 1) % totalImages;
            direction = 1; // Set direction to forward
            updateSlider();
        });
        
        // Dot navigation
        dots.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            currentIndex = parseInt($(this).data('index'));
            updateSlider();
        });
        
        // Pause auto-advance on hover
        card.on('mouseenter', function() {
            clearInterval(autoAdvance);
        });
        
        card.on('mouseleave', function() {
            const newInterval = setInterval(() => {
                if (totalImages > 1) {
                    currentIndex = (currentIndex + 1) % totalImages;
                    updateSlider();
                }
            }, 3000);
            card.data('slider-interval', newInterval);
        });
    }


    /**
     * Show loading state
     */
    function showLoadingState(show) {
        isLoading = show;
        
        const loadMoreBtn = $('#load-more-btn');
        const searchBtn = $('#search-restaurants-btn');
        
        if (show) {
            if (currentPage === 1) {
                // First load - show main loading
                $('#restaurants-grid').html(`
                    <div class="col-span-full text-center py-12">
                        <div class="loading-spinner mx-auto mb-4"></div>
                        <p class="text-gray-500">Chargement des restaurants...</p>
                    </div>
                `);
            } else {
                // Loading more - update load more button
                loadMoreBtn.html('<div class="loading-spinner inline-block mr-2"></div>Chargement...');
                loadMoreBtn.prop('disabled', true);
            }
            
            searchBtn.html('<div class="loading-spinner inline-block mr-2"></div>Recherche...');
            searchBtn.prop('disabled', true);
        } else {
            loadMoreBtn.html('<i class="fas fa-plus mr-2"></i>' + (lebonrestoAll.strings?.loadMore || 'Load More'));
            loadMoreBtn.prop('disabled', false);
            
            searchBtn.html('<i class="fas fa-search mr-2"></i>' + (lebonrestoAll.strings?.searchButton || 'Search Restaurants'));
            searchBtn.prop('disabled', false);
        }
    }

    /**
     * Show error state
     */
    function showErrorState() {
        $('#restaurants-grid').html(`
            <div class="col-span-full text-center py-12">
                <div class="text-red-400 mb-4">
                    <i class="fas fa-exclamation-triangle text-5xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Erreur lors du chargement des restaurants</h3>
                <p class="text-gray-500 mb-6">Veuillez réessayer plus tard ou vérifiez votre connexion internet.</p>
                <button 
                    onclick="loadRestaurants(true)"
                    class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition duration-200"
                    style="background-color: #FFC107;"
                >
                    <i class="fas fa-redo mr-2"></i>Try Again
                </button>
            </div>
        `);
        
        updateResultsCount();
    }

    /**
     * Show/hide no results message
     */
    function showNoResults(show) {
        const noResults = $('#no-results');
        const grid = $('#restaurants-grid');
        
        if (show) {
            grid.addClass('hidden');
            noResults.removeClass('hidden');
        } else {
            grid.removeClass('hidden');
            noResults.addClass('hidden');
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
