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

                                <!-- Receipt Upload with Filepond -->
                                <div class="col-md-6">
                                    <label for="receipt_image" class="form-label">
                                        <i class="fas fa-camera me-1"></i>Receipt Photo
                                    </label>
                                    <input type="file" 
                                           name="receipt_image" 
                                           id="receipt_image" 
                                           class="filepond" 
                                           accept="image/*">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        📱 Take a photo or drag & drop • Max 2MB • JPEG, PNG, WebP
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
});

// Initialize Filepond for seamless image uploads
FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
    FilePondPluginImageEdit,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

// Configure Filepond
const receiptInput = document.querySelector('#receipt_image');
if (receiptInput) {
    const pond = FilePond.create(receiptInput, {
        labelIdle: `
            <div class="filepond-drop-area">
                <i class="fas fa-camera fa-3x text-primary mb-2"></i>
                <h5>📱 Take Photo or Drop Image</h5>
                <p class="text-muted">Tap to take a photo with your camera<br>or drag & drop an image file</p>
            </div>
        `,
        acceptedFileTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
        maxFileSize: '2MB',
        maxFiles: 1,
        allowMultiple: false,
        allowImagePreview: true,
        allowImageCrop: true,
        allowImageResize: true,
        allowImageTransform: true,
        allowImageEdit: true,
        imageCropAspectRatio: null,
        imageResizeTargetWidth: 1200,
        imageResizeTargetHeight: 1200,
        imageResizeMode: 'cover',
        imageResizeUpscale: false,
        credits: false,
        
        // Custom styling
        stylePanelLayout: 'compact circle',
        styleLoadIndicatorPosition: 'center bottom',
        styleProgressIndicatorPosition: 'right bottom',
        styleButtonRemoveItemPosition: 'left bottom',
        styleButtonProcessItemPosition: 'right bottom',
        
        // Simplified: No server processing, just local preview
        instantUpload: false,
        
        // Event handlers
        onaddfile: (error, file) => {
            if (!error) {
                console.log('File added:', file.filename);
            }
        },
        
        onprocessfile: (error, file) => {
            if (!error) {
                console.log('File processed:', file.filename);
            }
        },
        
        onerror: (error) => {
            console.error('FilePond error:', error);
            // Show user-friendly error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mt-2';
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                Upload failed: ${error.main || 'Please try again'}
            `;
            receiptInput.parentNode.appendChild(errorDiv);
            
            // Remove error after 5 seconds
            setTimeout(() => errorDiv.remove(), 5000);
        }
    });
}
</script>

<!-- Filepond CSS -->
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.css" rel="stylesheet">

<!-- Filepond JavaScript -->
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>

<style>
/* Custom Filepond Styling */
.filepond--root {
    margin-bottom: 1rem;
}

.filepond--drop-label {
    color: #6c757d;
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
}

.filepond--drop-label:hover {
    background-color: #e9ecef;
    border-color: #007bff;
    color: #007bff;
}

.filepond-drop-area {
    padding: 1rem;
}

.filepond--panel-root {
    border-radius: 12px;
    background-color: transparent;
}

.filepond--image-preview-wrapper {
    border-radius: 8px;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .filepond--drop-label {
        padding: 1.5rem 1rem;
    }
    
    .filepond-drop-area h5 {
        font-size: 1.1rem;
    }
    
    .filepond-drop-area p {
        font-size: 0.9rem;
    }
}
</style>

@endsection