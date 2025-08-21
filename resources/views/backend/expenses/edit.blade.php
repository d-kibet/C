@extends('admin_master')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit Expense</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Expense Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Expense Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
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
                                                    {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
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
                                               value="{{ old('amount', $expense->amount) }}" 
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
                                              required>{{ old('description', $expense->description) }}</textarea>
                                    <div class="form-text">
                                        <span id="description-count">{{ strlen($expense->description) }}</span>/500 characters
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
                                           value="{{ old('vendor_name', $expense->vendor_name) }}" 
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
                                           value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" 
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
                                        <option value="Cash" {{ old('payment_method', $expense->payment_method) == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="M-Pesa" {{ old('payment_method', $expense->payment_method) == 'M-Pesa' ? 'selected' : '' }}>M-Pesa</option>
                                        <option value="Bank Transfer" {{ old('payment_method', $expense->payment_method) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="Cheque" {{ old('payment_method', $expense->payment_method) == 'Cheque' ? 'selected' : '' }}>Cheque</option>
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
                                           value="{{ old('transaction_reference', $expense->transaction_reference) }}">
                                    @error('transaction_reference')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Current Receipt Display -->
                                @if($expense->receipt_image)
                                <div class="col-12">
                                    <label class="form-label">
                                        <i class="fas fa-image me-1"></i>Current Receipt
                                    </label>
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $expense->receipt_image) }}" 
                                             alt="Current Receipt" 
                                             class="img-thumbnail" 
                                             style="max-height: 150px;">
                                    </div>
                                </div>
                                @endif

                                <!-- Receipt Upload with Filepond -->
                                <div class="col-md-6">
                                    <label for="receipt_image" class="form-label">
                                        <i class="fas fa-camera me-1"></i>{{ $expense->receipt_image ? 'Replace Receipt Photo' : 'Receipt Photo' }}
                                    </label>
                                    <input type="file" 
                                           name="receipt_image" 
                                           id="receipt_image" 
                                           class="filepond" 
                                           accept="image/*">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        📱 {{ $expense->receipt_image ? 'Take new photo to replace current receipt' : 'Take a photo or drag & drop' }} • Max 2MB • JPEG, PNG, WebP
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
                                              maxlength="500">{{ old('notes', $expense->notes) }}</textarea>
                                    @error('notes')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status Information -->
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Current Status:</strong> 
                                        <span class="badge bg-{{ $expense->approval_status == 'Approved' ? 'success' : ($expense->approval_status == 'Pending' ? 'warning' : 'danger') }}">
                                            {{ $expense->approval_status }}
                                        </span>
                                        @if($expense->approval_status == 'Pending')
                                            - This expense is waiting for approval
                                        @elseif($expense->approval_status == 'Rejected')
                                            - This expense was rejected
                                        @endif
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Update Expense
                                        </button>
                                        <a href="{{ route('expenses.show', $expense) }}" class="btn btn-info">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i>Back to List
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="fas fa-undo me-1"></i>Reset Changes
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
    // Character counter for description
    const descriptionTextarea = document.getElementById('description');
    const descriptionCount = document.getElementById('description-count');
    
    descriptionTextarea.addEventListener('input', function() {
        descriptionCount.textContent = this.value.length;
    });

    // Initialize Filepond for receipt upload
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginImageCrop,
        FilePondPluginImageResize,
        FilePondPluginImageTransform,
        FilePondPluginImageEdit,
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize
    );

    const receiptInput = document.querySelector('#receipt_image');
    if (receiptInput) {
        const pond = FilePond.create(receiptInput, {
            labelIdle: `
                <div class="filepond-drop-area">
                    <i class="fas fa-camera fa-3x text-primary mb-2"></i>
                    <h5>📱 Take New Photo or Drop Image</h5>
                    <p class="text-muted">Replace current receipt with a new photo<br>or drag & drop an image file</p>
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
            instantUpload: false,
            
            stylePanelLayout: 'compact circle',
            styleLoadIndicatorPosition: 'center bottom',
            styleProgressIndicatorPosition: 'right bottom',
            styleButtonRemoveItemPosition: 'left bottom',
            styleButtonProcessItemPosition: 'right bottom',
            
            onaddfile: (error, file) => {
                if (!error) {
                    console.log('New receipt file added:', file.filename);
                }
            },
            
            onerror: (error) => {
                console.error('FilePond error:', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-2';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Upload failed: ${error.main || 'Please try again'}
                `;
                receiptInput.parentNode.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 5000);
            }
        });
    }
});
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