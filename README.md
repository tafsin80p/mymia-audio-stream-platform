# Nymia WordPress Theme

A modern WordPress theme for audio content platform with dashboard functionality. This theme converts the original React application design into a fully functional WordPress theme.

## Features

- **Modern Dark Theme**: Beautiful dark design with orange accent colors
- **Dashboard Page**: Automatically created dashboard page with static content overview
- **Responsive Design**: Fully responsive layout that works on all devices
- **Static Content Display**: Shows all audio content, live streaming, and suggestions
- **Sidebar Navigation**: Fixed sidebar with navigation menu
- **Search Functionality**: Header search bar
- **User Profile**: Avatar and notification system
- **Filter System**: Content filtering buttons
- **Suggestion Sidebar**: Right sidebar with user suggestions

## Installation

1. **Upload Theme Files**:
   - Copy the `nymia-wp-theme` folder to your WordPress `/wp-content/themes/` directory
   - Rename the folder to `nymia-theme`

2. **Activate Theme**:
   - Go to WordPress Admin → Appearance → Themes
   - Find "Nymia Theme" and click "Activate"

3. **Dashboard Page**:
   - The theme will automatically create a "Dashboard" page
   - The dashboard page will be set as your front page
   - All content is static and displays immediately

## Theme Structure

```
nymia-wp-theme/
├── style.css              # Main stylesheet with all CSS
├── index.php              # Main template file
├── functions.php          # Theme functions and setup
├── header.php             # Header template
├── footer.php             # Footer template
├── template-parts/        # Template parts directory
│   ├── header.php         # Header component
│   ├── sidebar.php        # Left sidebar navigation
│   ├── sidebar-right.php  # Right sidebar suggestions
│   └── dashboard.php      # Dashboard content
└── js/
    └── main.js           # JavaScript functionality
```

## Customization

### Colors
The theme uses CSS custom properties for easy color customization. Edit the `:root` section in `style.css`:

```css
:root {
    --background: hsl(0, 0%, 7%);        /* Dark background */
    --primary: hsl(18, 75%, 58%);       /* Orange accent */
    --foreground: hsl(0, 0%, 98%);      /* White text */
    /* ... more colors */
}
```

### Content
All dashboard content is defined in the `nymia_get_dashboard_data()` function in `functions.php`. You can modify the arrays to change:
- Recent content items
- Live audio streaming content
- Audio books
- Suggestions

### Navigation
The sidebar navigation is defined in `template-parts/sidebar.php`. You can add or modify menu items as needed.

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Modern web browser

## Support

This theme is designed to be a static display of the original React application. All content is hardcoded and displays immediately upon theme activation.

## License

This theme is created for the Nymia project. Please ensure you have the appropriate rights to use the design and content.
# tafsin80p-mymia-audio-stream-platform
