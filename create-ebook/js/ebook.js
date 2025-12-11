/**
 * ========================================
 * NYMIA EBOOK SYSTEM - JAVASCRIPT
 * ========================================
 * Handles all ebook upload UI interactions
 * 
 * @package Nymia
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Ebook script loaded');
    
    // Wait for page-create.php to define nymiaAjax and ensure DOM is ready
    setTimeout(function() {
        // Define nymiaAjax if not already defined
        if (typeof nymiaAjax === 'undefined') {
            var nymiaAjax = {
                ajaxurl: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
                nonce: typeof ebookNonce !== 'undefined' ? ebookNonce : ''
            };
        }
        
        // Add ebookNonce if ebookNonce variable is available
        if (typeof ebookNonce !== 'undefined') {
            nymiaAjax.ebookNonce = ebookNonce;
        }
        
        console.log('nymiaAjax defined:', nymiaAjax);
        console.log('nymiaAjax.ebookNonce:', nymiaAjax.ebookNonce);
        console.log('nymiaAjax.ajaxurl:', nymiaAjax.ajaxurl);
        
        // Check if elements exist before initializing
        const ebookCategorySelect = document.getElementById('ebook_category');
        const ebookSubcategoryWrapper = document.getElementById('ebook_subcategory_wrapper');
        console.log('Elements check before init:', {
            categorySelect: !!ebookCategorySelect,
            subcategoryWrapper: !!ebookSubcategoryWrapper
        });
        
        // Initialize ebook functionality
        initEbookFunctionality();
    }, 500); // Increased timeout to ensure DOM is fully ready
});

// Function to initialize ebook subcategory handler (no longer needed since subcategories are in dropdown)
function initEbookSubcategoryHandler() {
    // Sub-categories are now integrated into the category dropdown
    // No separate handler needed
    console.log('Ebook subcategories are integrated into category dropdown');
}

function initEbookFunctionality() {
    
    // ========================================
    // EBOOK SUB-CATEGORY HANDLING
    // ========================================
    console.log('Initializing ebook sub-category handling...');
    initEbookSubcategoryHandler();
    
    // Make function available globally for tab switching
    window.initEbookSubcategoryHandler = initEbookSubcategoryHandler;
    
    // ========================================
    // EBOOK UPLOAD AREA FUNCTIONALITY
    // ========================================
    const ebookUploadArea = document.getElementById('ebookUploadArea');
    const ebookFileInput = document.getElementById('ebookFileInput');
    
    if (ebookUploadArea && ebookFileInput) {
        // Click to browse
        ebookUploadArea.addEventListener('click', function() {
            ebookFileInput.click();
        });
        
        // Drag and drop
        ebookUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('nymia-drag-over');
        });
        
        ebookUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('nymia-drag-over');
        });
        
        ebookUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('nymia-drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleEbookFileSelect(files[0]);
            }
        });
        
        // File input change
        ebookFileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                handleEbookFileSelect(this.files[0]);
            }
        });
        
        function handleEbookFileSelect(file) {
            // Validate file type
            const allowedTypes = ['application/pdf', 'application/epub+zip', 'application/x-mobipocket-ebook', 'text/plain'];
            const allowedExts = ['pdf', 'epub', 'mobi', 'txt'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(file.type) && !allowedExts.includes(fileExt)) {
                alert('Please select a valid ebook file (PDF, EPUB, MOBI, or TXT).');
                return;
            }
            
            // Validate file size (50MB max)
            const maxSize = 50 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('File size exceeds maximum limit of 50MB.');
                return;
            }
            
            // Update upload area display
            const uploadContent = ebookUploadArea.querySelector('.nymia-upload-content');
            if (uploadContent) {
                uploadContent.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    <p class="nymia-upload-text">${file.name}</p>
                    <p class="nymia-upload-formats">File selected • ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                `;
            }
        }
    }
    
    // ========================================
    // EBOOK UPLOAD FORM SUBMISSION
    // ========================================
    const ebookForm = document.getElementById('ebookUploadForm');
    
    if (ebookForm) {
        ebookForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(ebookForm);
            const ebookTitle = formData.get('ebook_title');
            const file = formData.get('ebook_file');
            
            if (!file || !file.name) {
                alert('Please select an ebook file to upload.');
                return;
            }
            
            if (!ebookTitle.trim()) {
                alert('Please enter an ebook title.');
                return;
            }
            
            // Validate category is selected (required)
            const ebookCategory = formData.get('ebook_category');
            if (!ebookCategory || ebookCategory.trim() === '') {
                alert('Please select a category for your ebook.');
                return;
            }
            
            const ebookLanguage = formData.get('ebook_language');
            if (!ebookLanguage || ebookLanguage.trim() === '') {
                alert('Please select a language for your ebook.');
                return;
            }
            
            // Validate thumbnail is uploaded
            const thumbnailInputCheck = document.getElementById('ebook_thumbnail');
            if (!thumbnailInputCheck || !thumbnailInputCheck.files || thumbnailInputCheck.files.length === 0) {
                alert('Please upload a thumbnail image.');
                return;
            }
            
            // Show loading state
            const submitBtn = ebookForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            
            // Show progress bar
            const progressBar = document.querySelector('.ebook-upload-progress');
            const progressFill = document.querySelector('.ebook-progress-fill');
            const progressText = document.querySelector('.ebook-progress-text');
            const uploadContent = document.querySelector('#ebookUploadArea .nymia-upload-content');
            
            if (progressBar && uploadContent) {
                uploadContent.style.display = 'none';
                progressBar.style.display = 'flex';
                progressFill.style.width = '0%';
                progressText.textContent = '0%';
            }
            
            // Prepare FormData for AJAX
            const uploadFormData = new FormData();
            uploadFormData.append('action', 'nymia_upload_ebook');
            uploadFormData.append('nonce', nymiaAjax.ebookNonce || nymiaAjax.nonce || '');
            uploadFormData.append('ebook_title', ebookTitle);
            uploadFormData.append('ebook_description', formData.get('ebook_description') || '');
            uploadFormData.append('ebook_file', file, file.name);
            uploadFormData.append('ebook_paid_access', formData.get('ebook_paid_access') ? 'yes' : 'no');
            uploadFormData.append('ebook_price', formData.get('ebook_price') || '0.00');
            
            // Parse category value (format: "category" or "category|subcategory")
            const categoryValue = formData.get('ebook_category') || '';
            let ebookCategory = '';
            let ebookSubcategory = '';
            
            if (categoryValue.includes('|')) {
                const parts = categoryValue.split('|');
                ebookCategory = parts[0] || '';
                ebookSubcategory = parts[1] || '';
            } else {
                ebookCategory = categoryValue;
                ebookSubcategory = '';
            }
            
            uploadFormData.append('ebook_category', ebookCategory);
            uploadFormData.append('ebook_subcategory', ebookSubcategory);
            uploadFormData.append('ebook_language', formData.get('ebook_language') || '');
            
            // Handle thumbnail upload (REQUIRED)
            const thumbnailInput = document.getElementById('ebook_thumbnail');
            if (!thumbnailInput) {
                alert('Thumbnail input not found. Please refresh the page.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                return;
            }
            
            if (!thumbnailInput.files || thumbnailInput.files.length === 0) {
                alert('Please upload a thumbnail image before submitting.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                if (progressBar && uploadContent) {
                    progressBar.style.display = 'none';
                    uploadContent.style.display = 'flex';
                }
                return;
            }
            
            const thumbFile = thumbnailInput.files[0];
            
            // Validate image type
            if (!thumbFile.type.startsWith('image/')) {
                alert('Please select a valid image file for the thumbnail.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                if (progressBar && uploadContent) {
                    progressBar.style.display = 'none';
                    uploadContent.style.display = 'flex';
                }
                return;
            }
            
            // Validate file size (max 5MB for thumbnails)
            const maxThumbSize = 5 * 1024 * 1024; // 5MB
            if (thumbFile.size > maxThumbSize) {
                alert('Thumbnail size exceeds maximum limit of 5MB.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                if (progressBar && uploadContent) {
                    progressBar.style.display = 'none';
                    uploadContent.style.display = 'flex';
                }
                return;
            }
            
            // Append thumbnail to form data
            uploadFormData.append('ebook_thumbnail', thumbFile, thumbFile.name);
            console.log('Thumbnail added to form data:', thumbFile.name, thumbFile.size);
            
            // Create XMLHttpRequest for upload progress
            const xhr = new XMLHttpRequest();
            
            // Upload progress for both ebook and thumbnail
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    
                    // Update ebook file progress
                    if (progressFill) {
                        progressFill.style.width = percentComplete + '%';
                    }
                    if (progressText) {
                        progressText.textContent = Math.round(percentComplete) + '%';
                    }
                    
                    // Update thumbnail progress (shows same overall progress)
                    const thumbProgress = document.getElementById('ebookThumbnailProgress');
                    const thumbProgressFill = document.getElementById('ebookThumbnailProgressFill');
                    const thumbProgressText = document.getElementById('ebookThumbnailProgressText');
                    
                    if (thumbProgress && thumbProgressFill && thumbProgressText) {
                        thumbProgress.style.display = 'block';
                        thumbProgressFill.style.width = percentComplete + '%';
                        thumbProgressText.textContent = Math.round(percentComplete) + '%';
                    }
                }
            });
            
            // Handle response
            xhr.addEventListener('load', function() {
                console.log('Upload response received. Status:', xhr.status);
                console.log('Response text:', xhr.responseText);
                
                // Hide thumbnail progress
                const thumbProgress = document.getElementById('ebookThumbnailProgress');
                if (thumbProgress) {
                    thumbProgress.style.display = 'none';
                }
                
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            // Show success message on the page
                            const successMsg = document.getElementById('ebook-success-message');
                            if (successMsg) {
                                successMsg.style.display = 'flex';
                                
                                // Hide after 5 seconds
                                setTimeout(function() {
                                    successMsg.style.display = 'none';
                                }, 5000);
                                
                                // Scroll to message
                                successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                            
                            // Reset form
                            ebookForm.reset();
                            ebookUploadArea.querySelector('.nymia-upload-content').innerHTML = `
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <p class="nymia-upload-text">Drop ebook file here or click to browse</p>
                                <p class="nymia-upload-formats">Supported formats: PDF, EPUB, MOBI, TXT</p>
                            `;
                            
                            // Hide thumbnail preview
                            const thumbnailPreview = document.getElementById('ebookThumbnailPreview');
                            const thumbnailBtn = document.getElementById('ebookThumbnailBtn');
                            if (thumbnailPreview) {
                                thumbnailPreview.classList.remove('active', 'show-preview');
                                thumbnailPreview.style.display = 'none';
                            }
                            if (thumbnailBtn) {
                                thumbnailBtn.style.display = 'flex';
                            }
                            
                            // Reset button state
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Upload Ebook';
                            
                            // Hide progress bar
                            if (progressBar && uploadContent) {
                                progressBar.style.display = 'none';
                                uploadContent.style.display = 'flex';
                            }
                            
                            // Reload the page after 2 seconds to refresh sidebar
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        } else {
                            alert('Upload failed: ' + (data.data.message || 'Unknown error'));
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalBtnText;
                            if (progressBar && uploadContent) {
                                progressBar.style.display = 'none';
                                uploadContent.style.display = 'flex';
                            }
                        }
                    } catch (e) {
                        console.error('Response parse error:', e);
                        alert('Upload failed. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalBtnText;
                        if (progressBar && uploadContent) {
                            progressBar.style.display = 'none';
                            uploadContent.style.display = 'flex';
                        }
                    }
                } else {
                    alert('Upload failed. Server error.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                    if (progressBar && uploadContent) {
                        progressBar.style.display = 'none';
                        uploadContent.style.display = 'flex';
                    }
                }
            });
            
            xhr.addEventListener('error', function() {
                console.error('Upload error');
                
                // Hide thumbnail progress on error
                const thumbProgress = document.getElementById('ebookThumbnailProgress');
                if (thumbProgress) {
                    thumbProgress.style.display = 'none';
                }
                
                alert('Upload failed. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                if (progressBar && uploadContent) {
                    progressBar.style.display = 'none';
                    uploadContent.style.display = 'flex';
                }
            });
            
            // Send request
            xhr.open('POST', nymiaAjax.ajaxurl);
            xhr.send(uploadFormData);
        });
        
        // Paid access toggle
        const ebookPaidAccessToggle = document.getElementById('ebook_paid_access');
        const ebookPriceInput = document.getElementById('ebook_price');
        
        if (ebookPaidAccessToggle && ebookPriceInput) {
            ebookPaidAccessToggle.addEventListener('change', function() {
                ebookPriceInput.disabled = !this.checked;
                if (!this.checked) {
                    ebookPriceInput.value = '0.00';
                }
            });
        }
    }
    
    // ========================================
    // EBOOK THUMBNAIL UPLOAD FUNCTIONALITY
    // ========================================
    // Get elements once and reuse
    const thumbnailInput = document.getElementById('ebook_thumbnail');
    const thumbnailBtn = document.getElementById('ebookThumbnailBtn');
    const thumbnailPreview = document.getElementById('ebookThumbnailPreview');
    const thumbnailImg = document.getElementById('ebookThumbnailImg');
    const thumbnailRemove = document.getElementById('ebookThumbnailRemove');
    
    // Debug: Check if elements exist
    console.log('Checking ebook thumbnail elements...');
    console.log('thumbnailInput:', thumbnailInput);
    console.log('thumbnailBtn:', thumbnailBtn);
    console.log('thumbnailPreview:', thumbnailPreview);
    console.log('thumbnailImg:', thumbnailImg);
    console.log('thumbnailRemove:', thumbnailRemove);
    
    if (!thumbnailInput) {
        console.error('Ebook thumbnail input not found');
    } else if (!thumbnailBtn) {
        console.error('Ebook thumbnail button not found');
    } else {
        console.log('Initializing ebook thumbnail upload functionality');
        
        // Click button to trigger file input
        thumbnailBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Thumbnail button clicked');
            if (thumbnailInput) {
                console.log('Triggering file input click');
                thumbnailInput.click();
            }
        });
        
        // File input change - this handles the preview display
        thumbnailInput.addEventListener('change', function(e) {
            console.log('Thumbnail input changed', this.files);
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                console.log('Selected file:', file.name, file.type, file.size);
                
                // Validate image type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file.');
                    this.value = ''; // Clear the input
                    return;
                }
                
                // Validate file size (max 5MB for thumbnails)
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    alert('Thumbnail size exceeds maximum limit of 5MB.');
                    this.value = ''; // Clear the input
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onerror = function() {
                    console.error('Error reading thumbnail file');
                    alert('Error reading image file. Please try another image.');
                };
                reader.onload = function(e) {
                    console.log('Thumbnail file loaded, showing preview');
                    
                    if (thumbnailImg) {
                        thumbnailImg.src = e.target.result;
                        thumbnailImg.onload = function() {
                            console.log('Thumbnail image loaded successfully');
                        };
                        thumbnailImg.onerror = function() {
                            console.error('Error loading thumbnail image');
                            alert('Error displaying thumbnail image. Please try another image.');
                        };
                    }
                    
                    if (thumbnailPreview) {
                        // Use both class and inline style for maximum compatibility
                        thumbnailPreview.classList.add('active', 'show-preview');
                        thumbnailPreview.style.cssText = 'display: block !important; visibility: visible !important;';
                        console.log('Preview element updated:', thumbnailPreview.style.display, thumbnailPreview.classList);
                    }
                    
                    if (thumbnailBtn) {
                        thumbnailBtn.style.display = 'none';
                        console.log('Button hidden');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                console.log('No file selected');
            }
        });
        
        // Remove thumbnail
        if (thumbnailRemove) {
            thumbnailRemove.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Remove thumbnail clicked');
                if (thumbnailInput) {
                    thumbnailInput.value = '';
                }
                if (thumbnailPreview) {
                    thumbnailPreview.classList.remove('active', 'show-preview');
                    thumbnailPreview.style.cssText = 'display: none !important; visibility: hidden !important;';
                }
                if (thumbnailImg) {
                    thumbnailImg.src = '';
                }
                if (thumbnailBtn) {
                    thumbnailBtn.style.display = 'flex';
                }
            });
        }
        
        // Also allow click on preview to change image
        if (thumbnailPreview && thumbnailImg) {
            thumbnailPreview.addEventListener('click', function(e) {
                // Only trigger if clicking on the image itself, not the remove button
                if (e.target === thumbnailImg && thumbnailInput) {
                    thumbnailInput.click();
                }
            });
        }
    }
}

