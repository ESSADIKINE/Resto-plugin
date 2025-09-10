# Le Bon Resto WordPress Plugin

A comprehensive WordPress plugin for managing restaurants with advanced map integration, interactive galleries, and multiple display layouts using OpenStreetMap and Leaflet.js.

## 🚀 Features

### Core Functionality
- **Custom Restaurant Post Type** with comprehensive metadata
- **Interactive Maps** with OpenStreetMap integration
- **Advanced Search & Filtering** capabilities
- **REST API Endpoints** for restaurant data
- **Fully Responsive Design** for all devices
- **Multiple Display Layouts** via shortcodes

### 🖼️ Media Management
- **Principal Image**: Set a main image for each restaurant
- **Gallery Images**: Upload multiple images to create photo galleries
- **Auto-sliding Gallery**: Images automatically cycle with fade effects
- **Interactive Navigation**: Arrow controls and pagination dots
- **Responsive Design**: Images adapt to different screen sizes

### 🗺️ Map Features
- **Interactive Markers** with enhanced popups
- **Restaurant Information Display** (name, address, cuisine, contact)
- **Featured Restaurant Highlighting**
- **Distance-based Filtering** and sorting
- **Multiple Map Layers** (Standard, Satellite, Terrain)
- **Fullscreen Toggle** support
- **Mobile-optimized** touch controls

### 📱 Mobile Features
- **Mobile Tab Navigation** for switching between map and virtual tour
- **Mobile Filter Panel** with slide-out interface
- **Touch-friendly Controls** and gestures
- **Responsive Grid Layouts**
- **Mobile-optimized Popups**

### 🎨 Display Options
- **7 Different Shortcodes** for various layouts
- **Customizable Templates** for each view
- **Flexible Styling** with Tailwind CSS
- **FontAwesome Icons** integration
- **Hover Effects** and animations

## 📦 Installation

1. Upload the plugin files to `/wp-content/plugins/le-bon-resto/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use any of the available shortcodes to display restaurant content

## 🎯 Shortcodes

### 1. All Restaurants Page
```php
[lebonresto_all_page]
```
**Description**: Complete all restaurants page with filters, search, pagination, and map
**Features**: 
- Restaurant grid layout
- Advanced filtering system
- Search functionality
- Pagination controls
- Interactive map
- Mobile responsive design

### 2. Single Restaurant Page
```php
[lebonresto_single_page]
```
**Description**: Single restaurant with map, virtual tour, and restaurant cards
**Features**:
- Two-column layout (Map + Virtual Tour)
- Mobile tab navigation
- Restaurant filtering
- Interactive map with markers
- Virtual tour integration

### 3. Map Page
```php
[lebonresto_map_page]
```
**Description**: Full-width map page with filter header
**Features**:
- 100% width, 75vh height map
- Filter controls in header
- Restaurant search
- City and cuisine filters
- Results counter

### 4. Restaurant Details Page
```php
[lebonresto_details_page]
```
**Description**: Complete restaurant details with gallery, video, and information
**Features**:
- Hero section with restaurant info
- Photo gallery
- Video section
- Virtual tour
- Contact information
- Interactive map

### 5. Map Only
```php
[lebonresto_map_only]
```
**Description**: Clean map view without filters or additional content
**Features**:
- Customizable width and height
- Restaurant markers
- Interactive map
- Clean, minimal design

### 6. Gallery Only
```php
[lebonresto_gallery_only]
```
**Description**: Restaurant photo gallery only
**Features**:
- Responsive grid layout
- Hover effects
- Image captions (optional)
- Customizable columns
- Lightbox functionality

### 7. Restaurant Detail
```php
[lebonresto_detail]
```
**Description**: Individual restaurant detail view
**Features**:
- Hero section with background image
- Restaurant information cards
- Video and virtual tour sections
- Photo gallery
- Contact details

## ⚙️ Usage Examples

### Basic Usage
```php
[lebonresto_all_page]
[lebonresto_single_page]
[lebonresto_map_page]
[lebonresto_details_page]
[lebonresto_map_only]
[lebonresto_gallery_only]
[lebonresto_detail]
```

### With Parameters
```php
[lebonresto_all_page per_page="6" show_filters="true"]
[lebonresto_single_page restaurant_id="123" show_map="true"]
[lebonresto_map_page height="80vh" zoom="15"]
[lebonresto_details_page restaurant_id="123" show_gallery="true"]
[lebonresto_map_only width="100%" height="600px"]
[lebonresto_gallery_only restaurant_id="123" columns="4"]
[lebonresto_detail restaurant_id="123" show_video="true"]
```

## 🖼️ Adding Images to Restaurants

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

## 🔧 Admin Features

- **Comprehensive Restaurant Management** interface
- **Media Management** for images and galleries
- **Custom Columns** for quick overview
- **Advanced Search** and filtering in admin
- **Bulk Operations** support
- **Import/Export** functionality for restaurant data

## 🌐 API Endpoints

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

## ⚙️ Configuration

### Plugin Settings
- Default map center coordinates
- Default zoom level
- Search radius options
- Map layer controls
- Primary color customization

### Customization
The plugin includes extensive CSS variables and classes for easy styling customization.

## 📱 Mobile Support

All shortcodes are fully responsive and include:
- **Mobile-optimized layouts**
- **Touch-friendly controls**
- **Adaptive filtering**
- **Mobile tab navigation** (where applicable)
- **Swipe gestures** for galleries

## 🎨 Styling

All shortcodes include:
- **Tailwind CSS** integration
- **FontAwesome icons**
- **Custom CSS** for enhanced styling
- **Responsive design**
- **Hover effects** and animations
- **Smooth transitions**

## 📋 Requirements

- **WordPress** 5.0 or higher
- **PHP** 7.4 or higher
- **Modern web browser** with JavaScript enabled
- **Internet connection** for CDN resources (Tailwind CSS, FontAwesome, Leaflet)

## 📝 Changelog

### v1.5.0
- **Added 7 comprehensive shortcodes** for different layouts
- **Enhanced mobile experience** with tab navigation
- **Improved template system** with proper asset loading
- **Fixed shortcode functionality** and template integration
- **Added import/export** functionality for restaurant data
- **Enhanced admin interface** with better media management

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

## 🆘 Support

For support and feature requests, please contact the plugin developer.

## 📄 License

This plugin is licensed under the GPL v2 or later.

---

**Le Bon Resto** - The complete restaurant management solution for WordPress! 🍽️✨