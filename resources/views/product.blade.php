@extends('layouts.header')

@section('css')
<style>
    .dealer-product-page{
        margin-top: 90px;
    }
    .pricing-container {
        padding: 40px 20px;
        background-color: #ffffffff;
        min-height: 100vh;
        border-radius: 20px;
    }

    .page-header {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 50px;
    flex-wrap: wrap;
    gap: 20px;
    position: relative;
    }

    .page-title {
        color: #87CEEB;
        font-size: 2rem;
        font-weight: 600;
        margin: 0;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, #ff4757, #ff6b7a);
        border-radius: 2px;
    }

    .add-product-btn {
        background: white;
        color: #333;
        border: 1px solid #ddd;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-left: auto;
    }

    .add-product-btn:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        color: #333;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .product-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    border: 1px solid #ddd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
    z-index: 1;
    }

    .product-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    .product-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #87CEEB;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        z-index: 2;
    }

    .product-image-container {
        position: relative;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
    }

    .product-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .product-info {
        padding: 15px;
        text-align: left;
    }

    .product-name {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        line-height: 1.4;
        min-height: 20px;
    }

    .product-price {
        font-size: 12px;
        color: #666;
        margin: 0;
    }

    .product-deposit {
        font-size: 12px;
        color: #888;
        margin: 4px 0 0 0;
    }

    .product-actions {
    position: absolute;
    top: 8px;
    left: 8px;
    display: flex;
    gap: 5px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 100;
    pointer-events: none;
    }

    .product-card:hover .product-actions {
        opacity: 1;
        pointer-events: auto;
    }

    .action-btn {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
    text-decoration: none;
    pointer-events: auto;
    z-index: 101;
    }


    .delete-btn {
        color: #f44336;
        background: rgba(255, 255, 255, 0.95);
    }

    .action-btn:hover {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transform: scale(1.1);
    }

    .product-image-container {
        position: relative;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
        z-index: 1;
        border-bottom: 1px solid #6f5f5f22;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #666;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .empty-state p {
        font-size: 1.1rem;
        opacity: 0.7;
        margin-bottom: 20px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 0;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }

    .close {
        background: none;
        border: none;
        font-size: 24px;
        color: #666;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .close:hover {
        background: #f0f0f0;
        color: #333;
    }

    .modal-body {
        padding: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-label-optional {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-label-optional::after {
        content: " (Optional)";
        color: #999;
        font-weight: 400;
        font-size: 12px;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        background-color: #fff;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #87CEEB;
        box-shadow: 0 0 0 3px rgba(135, 206, 235, 0.1);
    }

    .file-input-wrapper {
        position: relative;
        display: block;
        width: 100%;
    }

    .file-input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 2;
    }
    
    .file-input-button {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        background-color: #fafafa;
        color: #666;
        font-size: 14px;
        transition: all 0.3s ease;
        min-height: 80px;
        flex-direction: column;
        gap: 8px;
        cursor: pointer;
        position: relative;
        z-index: 1;
    }

    .file-input-button:hover {
        border-color: #87CEEB;
        background-color: #f8f9ff;
        color: #333;
    }

    .image-preview {
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
        margin-top: 10px;
        border: 1px solid #ddd;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid #ddd;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        min-width: 80px;
    }

    .btn-primary {
        background: #87CEEB;
        color: white;
    }

    .btn-primary:hover {
        background: #5fa8c7;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(135, 206, 235, 0.3);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid transparent;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .error-list {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 14px;
    }

    .error-list li {
        margin-bottom: 5px;
    }

    .form-control::placeholder {
    color: #dbdbdbd2;
    opacity: 1;
    }

    .form-control:focus::placeholder {
    color: transparent;
    }

    .required-asterisk {
    color: red;
    }

    @media (max-width: 768px) {
        .page-header {
        flex-direction: column;
        align-items: center;
        position: static;
        }

        .page-title {
            position: static;
            transform: none;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .page-title::after {
            position: static;
            left: auto;
            transform: none;
            display: block;
            margin: 10px auto 0 auto;
            width: 50px;
            height: 3px;
        }

        .add-product-btn {
            margin-left: 0;
            align-self: center;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            padding: 0 10px;
        }

        .product-image-container {
            height: 120px;
            padding: 10px;
        }

        .product-info {
            padding: 10px;
        }

        .product-name {
            font-size: 12px;
            min-height: 35px;
        }

        .product-price {
            font-size: 11px;
        }

        .product-deposit {
            font-size: 10px;
        }

        .modal-content {
            width: 95%;
            margin: 10% auto;
        }

        .modal-footer {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="pricing-container @if(auth()->user()->role === 'Client') dealer-product-page @endif">
    <div class="page-header">
        <h1 class="page-title">Price List</h1>
        {{-- @if(Auth::user()->role === 'Admin' && Auth::user()->can_add === 'on') --}}
        @if(Auth::user()->hasAreaDistributorAccess())
            <button class="add-product-btn" onclick="openModal()">
                <span>+</span>
                Add Product
            </button>
        @endif
    </div>
    
    @if($products && $products->count() > 0)
        <div class="products-grid">
            @foreach($products as $product)
                <div class="product-card">
                    @if(Auth::user()->role === 'Admin' && Auth::user()->can_delete === 'on')
                        <div class="product-actions">
                            <form action="{{ route('product.destroy', $product->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete-btn" title="Delete">
                                    <i class="fas fa-trash" style="color: red;"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                    
                    <div class="product-badge">₱{{ number_format($product->price ?? 0, 2) }}</div>
                    
                    <div class="product-image-container">
                        @if($product->product_image && file_exists(public_path('uploads/products/' . $product->product_image)))
                            <img src="{{ asset('uploads/products/' . $product->product_image) }}" alt="{{ $product->product_name }}" class="product-image">
                        @else
                            <div style="width: 80px; height: 100px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px;">
                                No Image
                            </div>
                        @endif
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name">{{ $product->product_name }}</h3>
                        @if($product->deposit && $product->deposit > 0)
                            <p class="product-deposit">Deposit - ₱{{ number_format($product->deposit, 2) }}</p>
                        @endif
                    </div>
                </div>  
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <h3>No Products Available</h3>
            <p>Start by adding your first product to the system.</p>
            {{-- Only show Add First Product button if user is Admin and has can_add permission --}}
            {{-- @if(Auth::user()->role === 'Admin' && Auth::user()->can_add === 'on') --}}
            @if(Auth::user()->hasAreaDistributorAccess())
                <button class="add-product-btn" onclick="openModal()">
                    <span>+</span>
                    Add First Product
                </button>
            @endif
        </div>
    @endif
</div>

{{-- Only show modal if user is Admin and has can_add permission --}}
{{-- @if(Auth::user()->role === 'Admin' && Auth::user()->can_add === 'on') --}}
@if(Auth::user()->hasAreaDistributorAccess())
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Add New Product</h2>
            <button class="close" onclick="closeModal()">&times;</button>
        </div>
        
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label for="product_name" class="form-label">Product Name <span class="required-asterisk">*</span></label>
                    <input type="text" id="product_name" name="product_name" class="form-control" 
                           value="{{ old('product_name') }}" required placeholder="Enter product name">
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">Price (₱) <span class="required-asterisk">*</span></label>
                    <input type="number" id="price" name="price" class="form-control" 
                           value="{{ old('price') }}" step="0.01" min="0" required placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="deposit" class="form-label-optional">Deposit (₱)</label>
                    <input type="number" id="deposit" name="deposit" class="form-control" 
                           value="{{ old('deposit') }}" step="0.01" min="0" placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="product_image" class="form-label">Product Image <span class="required-asterisk">*</span></label>
                    <div class="file-input-wrapper">
                        <input type="file" id="product_image" name="product_image" class="file-input" 
                               accept="image/jpeg,image/png,image/jpg,image/gif" required>
                        <div class="file-input-button" id="file-button">
                            <span style="font-size: 20px;">📷</span>
                            <span>Click to upload image</span>
                        </div>
                    </div>
                    <div id="image-preview" style="display: none;">
                        <img id="preview-img" src="" alt="Preview" class="image-preview">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn bg-info-subtle text-info  waves-effect">Add Product</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
// Only define modal functions if user has permission to add products

function openModal() {
    document.getElementById('addProductModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('addProductModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    
    document.querySelector('#addProductModal form').reset();
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('file-button').innerHTML = `
        <span style="font-size: 20px;">📷</span>
        <span>Click to upload image</span>
    `;
}

window.onclick = function(event) {
    const modal = document.getElementById('addProductModal');
    if (event.target == modal) {
        closeModal();
    }
}

document.getElementById('product_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const fileButton = document.getElementById('file-button');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            fileButton.innerHTML = `
                <span style="font-size: 20px;">✓</span>
                <span>Image selected: ${file.name}</span>
            `;
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        fileButton.innerHTML = `
            <span style="font-size: 20px;">📷</span>
            <span>Click to upload image</span>
        `;
    }
});

document.getElementById('file-button').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('product_image').click();
});

@if ($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        openModal();
    });
@endif


</script>
@endsection
