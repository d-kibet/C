@extends('admin_master')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Add New Expense</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                            <li class="breadcrumb-item active">Add Expense</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Category Quick Select -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tags me-2"></i>Quick Category Selection
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="category-grid">
                            @foreach($categories as $category)
                            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                                <div class="category-card" 
                                     data-category-id="{{ $category->id }}"
                                     data-category-name="{{ $category->name }}"
                                     data-requires-approval="{{ $category->requires_approval ? 'true' : 'false' }}"
                                     style="cursor: pointer;">
                                    <div class="card text-center h-100 category-option" 
                                         style="border: 2px solid #e3e6f0; transition: all 0.2s;">
                                        <div class="card-body py-3">
                                            <div class="category-icon mb-2" 
                                                 style="color: {{ $category->color_code }}; font-size: 2rem;">
                                                <i class="{{ $category->icon_class }}"></i>
                                            </div>
                                            <h6 class="category-name mb-0" style="font-size: 0.85rem;">
                                                {{ $category->name }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-receipt me-2"></i>Expense Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row g-3">
                                <!-- Category Selection -->
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Category <span class="text-danger">*</span>
                                    </label>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value="">Select a category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    data-color="{{ $category->color_code }}"
                                                    data-icon="{{ $category->icon_class }}"
                                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Amount -->
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">
                                        <i class="fas fa-money-bill-wave me-1"></i>Amount (KES) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" 
                                               name="amount" 
                                               id="amount" 
                                               class="form-control" 
                                               placeholder="0.00" 
                                               step="0.01" 
                                               min="0.01" 
                                               max="999999.99"
                                               value="{{ old('amount') }}" 
                                               required>
                                    </div>
                                    @error('amount')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-1"></i>Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="description" 
                                              id="description" 
                                              class="form-control" 
                                              rows="2" 
                                              placeholder="What was this expense for?"
                                              maxlength="500" 
                                              required>{{ old('description') }}</textarea>
                                    <div class="form-text">
                                        <span id="description-count">0</span>/500 characters
                                    </div>
                                    @error('description')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Vendor -->
                                <div class="col-md-6">
                                    <label for="vendor_name" class="form-label">
                                        <i class="fas fa-store me-1"></i>Vendor/Supplier <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="vendor_name" 
                                           id="vendor_name" 
                                           class="form-control" 
                                           placeholder="Vendor or supplier name"
                                           list="vendor-suggestions"
                                           value="{{ old('vendor_name') }}" 
                                           required>
                                    <datalist id="vendor-suggestions">
                                        @foreach($recentVendors as $vendor)
                                            <option value="{{ $vendor }}">
                                        @endforeach
                                    </datalist>
                                    @error('vendor_name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Date -->
                                <div class="col-md-6">
                                    <label for="expense_date" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Expense Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           name="expense_date" 
                                           id="expense_date" 
                                           class="form-control" 
                                           max="{{ date('Y-m-d') }}"
                                           value="{{ old('expense_date', date('Y-m-d')) }}" 
                                           required>
                                    @error('expense_date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Payment Method -->
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">
                                        <i class="fas fa-credit-card me-1"></i>Payment Method <span class="text-danger">*</span>
                                    </label>
                                    <select name="payment_method" id="payment_method" class="form-select" required>
                                        <option value="">Select payment method</option>
                                        <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="M-Pesa" {{ old('payment_method') == 'M-Pesa' ? 'selected' : '' }}>M-Pesa</option>
                                        <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Transaction Reference -->
                                <div class="col-md-6">
                                    <label for="transaction_reference" class="form-label">
                                        <i class="fas fa-hashtag me-1"></i>Transaction Reference
                                    </label>
                                    <input type="text" 
                                           name="transaction_reference" 
                                           id="transaction_reference" 
                                           class="form-control" 
                                           placeholder="Receipt number, M-Pesa code, etc."
                                           value="{{ old('transaction_reference') }}">
                                    @error('transaction_reference')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Receipt Upload -->
                                <div class="col-md-6">
                                    <label for="galleryInput" class="form-label">
                                        <i class="fas fa-camera me-1"></i>Receipt Photo
                                    </label>
                                    
                                    <!-- Mobile-First Upload Options -->
                                    <div class="upload-options mb-3">
                                        <!-- Camera Button -->
                                        <button type="button" class="btn btn-primary me-2 mb-2" id="cameraBtn">
                                            <i class="fas fa-camera me-1"></i>Take Photo
                                        </button>
                                        <!-- Gallery Button -->
                                        <button type="button" class="btn btn-outline-primary mb-2" id="galleryBtn">
                                            <i class="fas fa-images me-1"></i>Choose from Gallery
                                        </button>
                                        <!-- Debug Button (temporary) -->
                                        <button type="button" class="btn btn-outline-info mb-2 ms-2" id="debugBtn">
                                            <i class="fas fa-bug me-1"></i>Test Upload
                                        </button>
                                    </div>

                                    <!-- Hidden File Inputs -->
                                    <input type="file" 
                                           id="cameraInput"
                                           name="camera_temp"
                                           accept="image/*,.jpg,.jpeg,.png,.webp,.heic,.heif,.avif,.bmp,.gif" 
                                           capture="environment" 
                                           style="display: none;">
                                    <input type="file" 
                                           id="galleryInput" 
                                           name="receipt_image"
                                           accept="image/*,.jpg,.jpeg,.png,.webp,.heic,.heif,.avif,.bmp,.gif" 
                                           style="display: none;">
                                    
                                    <!-- Preview Area -->
                                    <div id="previewArea" class="d-none">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <img id="imagePreview" src="" alt="Preview" class="img-fluid mb-2" style="max-height: 200px;">
                                                <div>
                                                    <span id="fileName" class="text-muted d-block mb-2"></span>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeImage">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="upload-help mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Max 5MB • All image formats supported (JPEG, PNG, WebP, HEIC, etc.)
                                        </small>
                                    </div>
                                    
                                    @error('receipt_image')
                                        <div class="alert alert-danger mt-2">
                                            <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note me-1"></i>Additional Notes
                                    </label>
                                    <textarea name="notes" 
                                              id="notes" 
                                              class="form-control" 
                                              rows="3" 
                                              placeholder="Any additional notes or comments"
                                              maxlength="500">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Approval Notice -->
                                <div class="col-12">
                                    <div id="approval-notice" class="alert alert-info d-none">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Notice:</strong> This expense will require approval before being recorded.
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Save Expense
                                        </button>
                                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i>Back to List
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="fas fa-undo me-1"></i>Reset Form
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category quick selection
    const categoryCards = document.querySelectorAll('.category-card');
    const categorySelect = document.getElementById('category_id');
    const approvalNotice = document.getElementById('approval-notice');
    const amountInput = document.getElementById('amount');
    
    // Handle category card clicks
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const categoryName = this.dataset.categoryName;
            const requiresApproval = this.dataset.requiresApproval === 'true';
            
            // Update select dropdown
            categorySelect.value = categoryId;
            
            // Update visual selection
            categoryCards.forEach(c => {
                c.querySelector('.category-option').style.borderColor = '#e3e6f0';
                c.querySelector('.category-option').style.backgroundColor = 'transparent';
            });
            
            this.querySelector('.category-option').style.borderColor = '#007bff';
            this.querySelector('.category-option').style.backgroundColor = '#f8f9fa';
            
            // Check if approval is needed
            checkApprovalRequirement();
        });
    });
    
    // Handle category dropdown change
    categorySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // Update visual selection
            categoryCards.forEach(card => {
                card.querySelector('.category-option').style.borderColor = '#e3e6f0';
                card.querySelector('.category-option').style.backgroundColor = 'transparent';
                
                if (card.dataset.categoryId === selectedOption.value) {
                    card.querySelector('.category-option').style.borderColor = '#007bff';
                    card.querySelector('.category-option').style.backgroundColor = '#f8f9fa';
                }
            });
        }
        
        checkApprovalRequirement();
    });
    
    // Check approval requirement
    function checkApprovalRequirement() {
        const selectedCategoryId = categorySelect.value;
        const amount = parseFloat(amountInput.value) || 0;
        let requiresApproval = false;
        
        if (selectedCategoryId) {
            const selectedCard = document.querySelector(`[data-category-id="${selectedCategoryId}"]`);
            if (selectedCard && selectedCard.dataset.requiresApproval === 'true' && amount > 5000) {
                requiresApproval = true;
            }
        }
        
        if (requiresApproval) {
            approvalNotice.classList.remove('d-none');
        } else {
            approvalNotice.classList.add('d-none');
        }
    }
    
    // Monitor amount changes for approval check
    amountInput.addEventListener('input', checkApprovalRequirement);
    
    // Character counter for description
    const descriptionTextarea = document.getElementById('description');
    const descriptionCount = document.getElementById('description-count');
    
    descriptionTextarea.addEventListener('input', function() {
        descriptionCount.textContent = this.value.length;
    });
    
    // Initialize character count
    descriptionCount.textContent = descriptionTextarea.value.length;
    
    // Mobile-First Upload Handler
    const cameraBtn = document.getElementById('cameraBtn');
    const galleryBtn = document.getElementById('galleryBtn');
    const cameraInput = document.getElementById('cameraInput');
    const galleryInput = document.getElementById('galleryInput');
    const previewArea = document.getElementById('previewArea');
    const imagePreview = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');
    const removeImage = document.getElementById('removeImage');
    
    // Handle camera button click
    cameraBtn.addEventListener('click', function() {
        cameraInput.click();
    });
    
    // Handle gallery button click
    galleryBtn.addEventListener('click', function() {
        galleryInput.click();
    });
    
    // Handle file selection from either camera or gallery
    function handleFileSelect(file, source) {
        if (!file) {
            console.error('No file provided to handleFileSelect');
            return;
        }
        
        console.log('File selected:', {
            name: file.name,
            type: file.type,
            size: file.size,
            source: source
        });
        
        // Validate file type - expanded for mobile compatibility
        const allowedTypes = [
            'image/jpeg', 
            'image/jpg', 
            'image/png', 
            'image/webp',
            'image/heic',     // iPhone photos
            'image/heif',     // iPhone photos
            'image/avif',     // Modern format
            'image/bmp',      // Basic bitmap
            'image/gif'       // GIF images
        ];
        
        // Also check file extension as fallback
        const fileName = file.name.toLowerCase();
        const allowedExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.heic', '.heif', '.avif', '.bmp', '.gif'];
        const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
        
        if (!allowedTypes.includes(file.type) && !hasValidExtension) {
            alert('Please select a valid image file. Selected type: ' + file.type + ', file: ' + file.name);
            return;
        }
        
        // Validate file size (5MB)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size must be less than 5MB. Selected size: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB');
            return;
        }
        
        // Copy file to the form input that will be submitted
        if (source === 'camera') {
            try {
                // Transfer file from camera input to gallery input for form submission
                const dt = new DataTransfer();
                dt.items.add(file);
                galleryInput.files = dt.files;
                console.log('File transferred from camera to gallery input successfully');
            } catch (error) {
                console.error('Failed to transfer file from camera to gallery:', error);
                alert('Image failed to upload: ' + error.message);
                return;
            }
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB) - ' + source;
            previewArea.classList.remove('d-none');
            console.log('Preview loaded successfully');
        };
        reader.onerror = function(error) {
            console.error('FileReader error:', error);
            alert('Image failed to upload: Could not read file');
        };
        reader.readAsDataURL(file);
    }
    
    // Camera input change
    cameraInput.addEventListener('change', function(e) {
        console.log('Camera input changed, files count:', e.target.files.length);
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0], 'camera');
        } else {
            console.warn('No files selected from camera');
        }
    });
    
    // Gallery input change
    galleryInput.addEventListener('change', function(e) {
        console.log('Gallery input changed, files count:', e.target.files.length);
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0], 'gallery');
        } else {
            console.warn('No files selected from gallery');
        }
    });
    
    // Remove image
    removeImage.addEventListener('click', function() {
        cameraInput.value = '';
        galleryInput.value = '';
        previewArea.classList.add('d-none');
        imagePreview.src = '';
        fileName.textContent = '';
    });
    
    // Debug button (temporary)
    document.getElementById('debugBtn').addEventListener('click', function() {
        if (galleryInput.files.length === 0) {
            alert('Please select a file first');
            return;
        }
        
        const formData = new FormData();
        formData.append('receipt_image', galleryInput.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        fetch('/expenses/debug-upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Debug upload response:', data);
            alert('Debug info logged to console. Check browser developer tools.');
        })
        .catch(error => {
            console.error('Debug upload error:', error);
            alert('Debug upload failed: ' + error.message);
        });
    });
});
</script>

<style>
/* Mobile-optimized file input styling */
.form-control[type="file"] {
    padding: 0.5rem;
}

@media (max-width: 768px) {
    .upload-help small {
        font-size: 0.8rem;
    }
}
</style>

@endsection