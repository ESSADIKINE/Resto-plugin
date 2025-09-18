# All Restaurants Page - Implementation Guide

## 📋 Overview

The new "All Restaurants" page provides a comprehensive listing of all restaurants with advanced filtering, sorting, and interactive features. This page offers a different layout compared to the single restaurant page, focusing on browsing and discovering restaurants.

## 🔗 URL Structure

### Single Restaurant Page
- **URL Pattern**: `{domain}/restaurant/{restaurant-name}`
- **Layout**: Map + Virtual Tour + Restaurant List
- **Purpose**: Interactive exploration with current restaurant highlighted

### All Restaurants Page  
- **URL Pattern**: `{domain}/all`
- **Layout**: Sidebar Filters (30%) + Restaurant Cards (70%)
- **Purpose**: Comprehensive restaurant browsing and discovery

## 🎯 Key Features

### 📱 Layout Differences

| Feature | Single Restaurant | All Restaurants |
|---------|------------------|-----------------|
| **Layout** | 50/50 Map+VR / Restaurant List | 30/70 Sidebar / Restaurant Cards |
| **Focus** | Current restaurant context | All restaurants browsing |
| **Filters** | Header filters | Comprehensive sidebar |
| **Cards** | Horizontal list with basic info | Detailed cards with image sliders |
| **Sorting** | Basic | Advanced (Name, Reviews, Date) |

### 🎨 Restaurant Cards Features

Each restaurant card includes:

- **Image Slider**: Auto-playing gallery with navigation controls
- **Detailed Information**: 
  - Restaurant name and featured badge
  - Address, city, cuisine type
  - Phone number with direct calling
  - Price range display
  - Google ratings with star display
  - Description preview
- **Action Buttons**:
  - Call restaurant
  - WhatsApp contact
  - View on Google Maps
  - Virtual tour (if available)
  - View details link

### 🔍 Advanced Filtering System

#### Sidebar Filters Include:
1. **Search Filter**: Restaurant name search
2. **Location Filter**: City selection
3. **Cuisine Filter**: Cuisine type selection
4. **Price Range**: 4-tier pricing (Budget, Moderate, Expensive, Luxury)
5. **Rating Filter**: Minimum rating selection (3.0+ to 4.5+)
6. **Features Filter**:
   - Featured restaurants only
   - Virtual tour available
   - Video available

### 📊 Sorting Options

- **Name**: Alphabetical (A-Z, Z-A)
- **Rating**: By Google rating (High to Low, Low to High)
- **Date**: By creation date (Newest, Oldest)
- **Featured**: Featured restaurants first

### 🖼️ Image Slider Functionality

- **Auto-play**: Images change every 4 seconds
- **Pause on Hover**: Stops auto-play when hovering
- **Navigation Controls**: Previous/Next arrows
- **Dot Indicators**: Click to jump to specific image
- **Responsive**: Adapts to different screen sizes

## 🚀 Implementation Details

### Files Created/Modified

1. **`templates/all-restaurants.php`**: Main template file
2. **`assets/css/all-restaurants.css`**: Comprehensive styling
3. **`assets/js/all-restaurants.js`**: Interactive functionality
4. **`le-bon-resto.php`**: Added URL rewrite rules
5. **`includes/shortcodes.php`**: Added shortcode support

### URL Routing

The plugin automatically handles the `/all` URL through WordPress rewrite rules:

```php
// Rewrite rule added to le-bon-resto.php
add_rewrite_rule(
    '^all/?$',
    'index.php?all_restaurants=1',
    'top'
);
```

### Shortcode Support

You can also use the page via shortcode:

```php
[lebonresto_all_restaurants_new]
```

**Shortcode Parameters:**
- `per_page="12"`: Items per page
- `show_pagination="true"`: Show/hide pagination
- `show_sorting="true"`: Show/hide sorting
- `show_filters="true"`: Show/hide filters

## 📱 Responsive Design

### Desktop (1024px+)
- 70/30 layout: Restaurant cards + Sidebar filters
- Horizontal restaurant cards with large images
- Full feature set available

### Tablet (768px-1023px)
- Stacked layout: Filters on top, cards below
- Adjusted card sizes
- Touch-friendly controls

### Mobile (< 768px)
- Single column layout
- Vertical restaurant cards
- Compact filter interface
- Touch-optimized interactions

## 🎛️ JavaScript Functionality

### Core Features
- **Real-time Filtering**: Instant results without page reload
- **Dynamic Sorting**: Client-side sorting for better performance
- **Pagination**: AJAX-style pagination
- **Image Sliders**: Auto-playing galleries with controls
- **Smooth Animations**: Fade-in effects for cards

### Performance Optimizations
- **Debounced Search**: Reduces API calls during typing
- **Lazy Loading**: Images load as needed
- **Efficient DOM Updates**: Minimal re-rendering
- **Memory Management**: Proper cleanup of intervals

## 🎨 CSS Architecture

### Design System
- **CSS Variables**: Consistent theming
- **Responsive Grid**: CSS Grid and Flexbox
- **Modern Animations**: Smooth transitions and hover effects
- **Accessibility**: Focus states and keyboard navigation

### Key Components
- **Restaurant Cards**: Modular card design
- **Filter Sidebar**: Sticky positioned filters
- **Image Sliders**: Custom slider implementation
- **Pagination**: Custom pagination controls

## 🔧 Usage Instructions

### 1. Activation
After plugin update, the `/all` URL should work automatically. If not:
1. Go to WordPress Admin → Settings → Permalinks
2. Click "Save Changes" to flush rewrite rules

### 2. Testing the Page
Visit: `{your-domain}/all`

### 3. Using as Shortcode
Add to any page or post:
```
[lebonresto_all_restaurants_new]
```

### 4. Customization
- **CSS**: Modify `assets/css/all-restaurants.css`
- **Layout**: Edit `templates/all-restaurants.php`
- **Functionality**: Adjust `assets/js/all-restaurants.js`

## 🐛 Troubleshooting

### Common Issues

1. **404 Error on /all**
   - Solution: Flush permalinks in WordPress Admin

2. **Images Not Loading**
   - Check restaurant gallery images are properly uploaded
   - Verify image URLs in restaurant meta

3. **Filters Not Working**
   - Check JavaScript console for errors
   - Ensure jQuery is loaded

4. **Styling Issues**
   - Verify CSS file is loading
   - Check for theme conflicts

### Debug Information

The JavaScript exposes debug functions:
```javascript
// Check filtered results
console.log(AllRestaurants.filteredRestaurants());

// Check all restaurants data
console.log(AllRestaurants.allRestaurants());

// Manually apply filters
AllRestaurants.applyFiltersAndSort();
```

## 🔄 Differences Summary

| Aspect | Single Restaurant | All Restaurants |
|--------|------------------|-----------------|
| **URL** | `/restaurant/{name}` | `/all` |
| **Primary Purpose** | Explore restaurants with map context | Browse and filter all restaurants |
| **Layout** | 50/50 split (Map + List) | 70/30 split (Cards + Filters) |
| **Restaurant Cards** | Simple horizontal cards | Detailed cards with image sliders |
| **Filtering** | Basic header filters | Advanced sidebar filters |
| **Sorting** | Limited | Multiple options (Name, Rating, Date) |
| **Image Display** | Basic thumbnails | Auto-playing image sliders |
| **Information Density** | Focused on current restaurant | Comprehensive restaurant details |
| **Mobile Experience** | Tab navigation (Map/VR) | Scrollable card list |
| **Use Case** | Finding restaurants near a location | Discovering restaurants by criteria |

## 📈 Performance Considerations

- **Client-side Processing**: Filtering and sorting happen in JavaScript for speed
- **Image Optimization**: Images are lazy-loaded and properly sized
- **Minimal Server Requests**: All data loaded once, then processed client-side
- **Responsive Images**: Different sizes for different screen sizes
- **Efficient DOM Updates**: Only visible elements are updated

## 🎯 Future Enhancements

Potential improvements could include:
- **Map Integration**: Add a toggle to show filtered restaurants on map
- **Advanced Search**: Search by specific criteria like opening hours
- **Favorites System**: Allow users to save favorite restaurants
- **Social Features**: Restaurant reviews and ratings from users
- **Export Features**: Export restaurant lists to PDF or CSV

---

The All Restaurants page provides a comprehensive and user-friendly way to browse and discover restaurants, with advanced filtering and sorting capabilities that complement the existing single restaurant page functionality.
