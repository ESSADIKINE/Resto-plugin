# Le Bon Resto WordPress Plugin

A WordPress plugin for managing restaurants with map integration using OpenStreetMap and Leaflet.js.

## Features

### Core Functionality
- Custom Restaurant post type with comprehensive metadata
- Interactive map with OpenStreetMap integration
- Advanced search and filtering capabilities
- REST API endpoints for restaurant data
- Responsive design for all devices

### Image Support (New in v1.4.0)
- **Principal Image**: Set a main image for each restaurant that appears prominently in popups
- **Gallery Images**: Upload multiple images to create a restaurant photo gallery
- **Auto-sliding Gallery**: Gallery images automatically cycle every 3 seconds with fade effects
- **Interactive Navigation**: Arrow controls and pagination dots for manual gallery navigation
- **Responsive Design**: Images adapt to different screen sizes with proper aspect ratios

### Map Features
- Interactive markers with enhanced popups
- Restaurant information display (name, address, cuisine type, contact details)
- Featured restaurant highlighting
- Distance-based filtering and sorting
- Multiple map layers (Standard, Satellite, Terrain)
- Fullscreen toggle support

### Admin Features
- Comprehensive restaurant management interface
- Media management for images and galleries
- Custom columns for quick overview
- Advanced search and filtering in admin
- Bulk operations support

## Installation

1. Upload the plugin files to `/wp-content/plugins/le-bon-resto/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the shortcode `[lebonresto_map]` to display the restaurant map on any page

## Usage

### Shortcode Options
```php
[lebonresto_map width="100%" height="500px" zoom="12" center_lat="48.8566" center_lng="2.3522"]
```

### Adding Images to Restaurants
1. Go to **Restaurants** → **Add New** or edit an existing restaurant
2. In the **Restaurant Media** section:
   - **Principal Image**: Click "Select Principal Image" to choose the main restaurant photo
   - **Gallery Images**: Click "Select Images" to add multiple photos to the gallery
3. Save the restaurant

### Gallery Slider Features
- **Auto-play**: Images automatically cycle every 3 seconds
- **Pause on Hover**: Slider pauses when mouse is over the gallery
- **Navigation**: Use arrow buttons to manually navigate
- **Pagination**: Click dots to jump to specific images
- **Responsive**: Adapts to different popup sizes

## API Endpoints

### Get All Restaurants
```
GET /wp-json/lebonresto/v1/restaurants
```

### Get Cuisine Types
```
GET /wp-json/lebonresto/v1/cuisine-types
```

### Restaurant Data Structure
```json
{
  "id": 123,
  "title": {"rendered": "Restaurant Name"},
  "link": "https://example.com/restaurant/name",
  "restaurant_meta": {
    "description": "Restaurant description",
    "address": "123 Main St",
    "city": "City Name",
    "latitude": "48.8566",
    "longitude": "2.3522",
    "cuisine_type": "french",
    "phone": "+1234567890",
    "email": "contact@restaurant.com",
    "is_featured": "1",
    "principal_image": {
      "full": "https://example.com/image-full.jpg",
      "medium": "https://example.com/image-medium.jpg",
      "thumbnail": "https://example.com/image-thumb.jpg"
    },
    "gallery_images": [
      {
        "id": 456,
        "full": "https://example.com/gallery1-full.jpg",
        "medium": "https://example.com/gallery1-medium.jpg",
        "thumbnail": "https://example.com/gallery1-thumb.jpg"
      }
    ],
    "video_url": "https://youtube.com/watch?v=...",
    "virtual_tour_url": "https://example.com/tour"
  }
}
```

## Configuration

### Plugin Settings
- Default map center coordinates
- Default zoom level
- Search radius options
- Map layer controls
- Primary color customization

### Customization
The plugin includes extensive CSS variables and classes for easy styling customization.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Modern web browser with JavaScript enabled

## Changelog

### v1.4.0
- Added principal image support for restaurants
- Implemented gallery image management
- Added auto-sliding gallery slider in map popups
- Enhanced popup design with image display
- Updated REST API to include image URLs
- Added image columns to admin interface

### v1.3.0
- Enhanced map functionality
- Improved search and filtering
- Added featured restaurant support
- Better responsive design

### v1.0.0
- Initial release
- Basic restaurant management
- Map integration
- REST API endpoints

## Support

For support and feature requests, please contact the plugin developer.

## License

This plugin is licensed under the GPL v2 or later.
