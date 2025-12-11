# Nymia Ebook System

This folder contains all ebook-related functionality for the Nymia WordPress theme.

## Folder Structure

```
ebook/
├── includes/
│   └── ebook-functions.php    # Backend PHP functions for ebook uploads
├── js/
│   └── ebook.js                # Frontend JavaScript for ebook upload UI
├── css/
│   └── ebook.css               # Styles for ebook components
├── templates/                   # Ebook template files (if any)
└── README.md                   # This file
```

## Features

- **Ebook Upload**: Support for PDF, EPUB, MOBI, and TXT files
- **File Validation**: Validates file type and size (max 50MB)
- **Paid Access**: Optional paid access with pricing
- **Progress Tracking**: Real-time upload progress
- **User Collection**: Stores user's ebook uploads in transients

## File Descriptions

### includes/ebook-functions.php
Contains all server-side ebook functionality:
- `nymia_handle_ebook_upload()` - AJAX handler for ebook uploads
- `nymia_get_user_ebook_posts()` - Retrieves user's uploaded ebooks

### js/ebook.js
Handles all client-side ebook interactions:
- File drag & drop
- File validation
- Upload progress display
- Form submission handling

### css/ebook.css
Styles for:
- Ebook upload area
- Progress bars
- Ebook sidebar cards
- Responsive layouts

## Usage

The ebook system is integrated into the "Create" page (`page-create.php`) as a tab alongside "Go Live", "Audio", and "Text".

Users can:
1. Navigate to the Create page
2. Click the "Ebook" tab
3. Fill in ebook title and optional description
4. Set paid access (optional)
5. Upload PDF, EPUB, MOBI, or TXT files
6. View their uploaded ebooks in the sidebar

## File Storage

Uploaded ebooks are stored in:
`/wp-content/uploads/nymia-ebook/`

## API Endpoints

- `wp_ajax_nymia_upload_ebook` - Handles ebook file uploads via AJAX

