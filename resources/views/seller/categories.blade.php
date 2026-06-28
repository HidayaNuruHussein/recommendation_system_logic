{{--
SOURCE CODE OWNER: HAGAI HAROLD NGOBEY
hngobey@gmail.com | +255 765 384 905
Provided to assist students. Students may study, modify, and reuse it
for any non-commercial educational purpose. See LICENSE.md.
--}}
@extends('layouts.dashboard')

@section('title', 'Categories Management - KidsStore Seller')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/seller-categories.css') }}">
<style>
    .remove-tag:hover {
        color: #ff6b6b !important;
    }
    .tags-input-wrapper .form-control {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .tags-input-wrapper .badge {
        font-size: 0.85rem;
        font-weight: 500;
        border-radius: 20px;
    }
    .category-summary-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 4px;
    }
    .category-summary-meta .badge {
        font-size: 0.65rem;
    }
    .badge-parent {
        background: #6c757d;
        color: #fff;
    }
    .badge-group {
        background: #0d6efd;
        color: #fff;
    }
    .badge-tags {
        background: #e9ecef;
        color: #212529;
        border: 1px solid #dee2e6;
    }
    .badge-children {
        background: #17a2b8;
        color: #fff;
    }
    .dropdown-option-disabled {
        color: #6c757d;
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    .dropdown-option-disabled:hover {
        background-color: #f8f9fa !important;
    }
    .dropdown-option-self {
        background-color: #fff3cd !important;
        color: #856404;
    }
    .dropdown-option-child {
        background-color: #f8d7da !important;
        color: #721c24;
    }
</style>
@endsection

@section('content')
<div class="container-fluid mt-2 seller-categories-page"
     data-next-page-url="{{ $categories->nextPageUrl() }}"
     data-store-url="{{ route('seller.categories.store') }}"
     data-show-url-template="{{ route('seller.categories.show', ['id' => '__ID__']) }}"
     data-update-url-template="{{ route('seller.categories.update', ['id' => '__ID__']) }}"
     data-destroy-url-template="{{ route('seller.categories.destroy', ['id' => '__ID__']) }}"
     data-csrf="{{ csrf_token() }}">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 categories-page-title">
                        <i class="bi bi-tags me-3"></i>Categories Management
                    </h1>
                    <p class="categories-page-subtitle mb-0">Create, update, and organize product categories quickly.</p>
                </div>
                <button class="btn btn-outline-primary themed-outline-btn" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Category
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <!-- Categories Summary Scroll -->
            <div class="category-summary-scroll mb-4" id="categorySummaryList">
                @if ($categories->count())
                    @include('seller.partials.category_summary_items', ['categories' => $categories])
                @else
                <div class="w-100">
                    <div class="category-empty-state category-empty-state-lg mx-auto text-center">
                        <div class="category-empty-icon-wrap">
                            <i class="bi bi-tags category-empty-icon"></i>
                        </div>
                        <h6 class="category-empty-title mb-1">No categories found</h6>
                        <p class="category-empty-text mb-3">Create your first category to start organizing products.</p>
                        <button class="btn btn-outline-primary themed-outline-btn" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                            <i class="bi bi-plus-circle me-2"></i>Create your first category
                        </button>
                    </div>
                </div>
                @endif
            </div>

            <!-- Categories Table -->
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 category-table-head">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-list-check me-2"></i>All Categories
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="category-name-col">Category Name</th>
                                    <th class="category-description-col">Description</th>
                                    <th class="fit-content-col">Parent</th>
                                    <th class="fit-content-col">Group</th>
                                    <th class="fit-content-col">Tags</th>
                                    <th class="fit-content-col">Products</th>
                                    <th class="fit-content-col">Created</th>
                                    <th class="fit-content-col text-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                @if ($categories->count())
                                    @include('seller.partials.category_rows', ['categories' => $categories])
                                @else
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="category-empty-state mx-auto text-center">
                                            <div class="category-empty-icon-wrap">
                                                <i class="bi bi-tags category-empty-icon"></i>
                                            </div>
                                            <h6 class="category-empty-title mb-1">No categories found</h6>
                                            <p class="category-empty-text mb-0">Try adding a category to populate this table.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div id="lazyLoader" class="text-center py-3 d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading more
                        categories...
                    </div>
                    <div id="scrollSentinel" class="lazy-sentinel" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW CATEGORY MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="viewCategoryModalLabel">
                    <i class="bi bi-eye me-2"></i>Category Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Category Name</label>
                        <input type="text" class="form-control" id="view_name" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="view_description" rows="3" readonly></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Parent Category</label>
                        <input type="text" class="form-control" id="view_parent" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Category Group</label>
                        <input type="text" class="form-control" id="view_group" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Products Count</label>
                        <input type="text" class="form-control" id="view_products_count" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Tags</label>
                        <div id="view_tags" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Created</label>
                        <input type="text" class="form-control" id="view_created_at" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Slug</label>
                        <input type="text" class="form-control" id="view_slug" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE CATEGORY MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="createCategoryForm" method="POST" action="{{ route('seller.categories.store') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="createCategoryModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Add New Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-12">
                            <label for="create_name" class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name" maxlength="191" required>
                            <div class="invalid-feedback">Please provide a category name.</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="create_description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="create_description" name="description" rows="2" placeholder="Brief description of this category"></textarea>
                        </div>

                        <hr class="my-2">

                        <div class="col-12">
                            <h6 class="fw-bold text-info">
                                <i class="bi bi-robot me-1"></i>AI Recommendation Settings
                                <small class="text-muted fw-normal">(Optional - Helps improve recommendations)</small>
                            </h6>
                        </div>

                        <!-- Parent Category - All categories shown -->
                        <div class="col-md-6">
                            <label for="create_parent_id" class="form-label fw-bold">Parent Category</label>
                            <select class="form-select" id="create_parent_id" name="parent_id">
                                <option value="">None (Top Level)</option>
                                @php
                                    // ✅ Get all categories, group by parent for better display
                                    $topLevelCategories = $allCategories->whereNull('parent_id');
                                    $subCategories = $allCategories->whereNotNull('parent_id');
                                @endphp
                                
                                {{-- Top Level Categories --}}
                                @foreach($topLevelCategories as $cat)
                                    <option value="{{ $cat->id }}" 
                                        {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                        <span class="text-muted">(Top Level)</span>
                                    </option>
                                @endforeach
                                
                                {{-- Sub Categories --}}
                                @if($subCategories->count() > 0)
                                    <option value="" disabled style="background:#e9ecef;color:#6c757d;">────────── Sub-Categories ──────────</option>
                                    @foreach($subCategories as $cat)
                                        <option value="{{ $cat->id }}" 
                                            {{ old('parent_id') == $cat->id ? 'selected' : '' }}
                                            style="padding-left: 20px;">
                                            ↳ {{ $cat->name }}
                                            @if($cat->parent)
                                                <span class="text-muted">({{ $cat->parent->name }})</span>
                                            @endif
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Select a parent category if this is a sub-category</small>
                        </div>

                        <!-- Category Group -->
                        <div class="col-md-6">
                            <label for="create_category_group" class="form-label fw-bold">Category Group</label>
                            <select class="form-select" id="create_category_group" name="category_group">
                                <option value="Other">Other (Default)</option>
                                <option value="Computers">Computers</option>
                                <option value="Accessories">Accessories</option>
                                <option value="Phones">Phones</option>
                                <option value="Phone Accessories">Phone Accessories</option>
                                <option value="Audio">Audio</option>
                                <option value="Displays">Displays</option>
                                <option value="Storage">Storage</option>
                                <option value="Wearables">Wearables</option>
                                <option value="Gaming">Gaming</option>
                                <option value="Networking">Networking</option>
                                <option value="Printers">Printers</option>
                            </select>
                            <small class="text-muted">Group similar categories for better AI recommendations</small>
                        </div>

                        <!-- Tags -->
                        <div class="col-12">
                            <label for="create_tags" class="form-label fw-bold">Tags</label>
                            <div class="tags-input-wrapper">
                                <input type="text" class="form-control" id="create_tags_input" 
                                    placeholder="Type a tag and press Enter..." autocomplete="off">
                                <div id="create_tags_container" class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="badge bg-primary d-flex align-items-center gap-1 p-2">
                                        general
                                        <span class="remove-tag" onclick="removeTag(this)" style="cursor:pointer;font-size:12px;">&times;</span>
                                        <input type="hidden" name="tags[]" value="general">
                                    </span>
                                </div>
                            </div>
                            <small class="text-muted">Press Enter to add tags. Examples: "gaming", "wireless", "performance"</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-outline-primary themed-outline-btn db-primary-btn" id="saveCategoryBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        <i class="bi bi-check2 me-2"></i>Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT CATEGORY MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="editCategoryModalLabel">
                        <i class="bi bi-pencil me-2"></i>Edit Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-12">
                            <label for="edit_name" class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" maxlength="191" required>
                            <div class="invalid-feedback">Please provide a category name.</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="edit_description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="2" placeholder="Brief description of this category"></textarea>
                        </div>

                        <hr class="my-2">

                        <div class="col-12">
                            <h6 class="fw-bold text-info">
                                <i class="bi bi-robot me-1"></i>AI Recommendation Settings
                                <small class="text-muted fw-normal">(Optional - Helps improve recommendations)</small>
                            </h6>
                        </div>

                        <!-- Parent Category - Show all, disable self and children -->
                        <div class="col-md-6">
                            <label for="edit_parent_id" class="form-label fw-bold">Parent Category</label>
                            <select class="form-select" id="edit_parent_id" name="parent_id">
                                <option value="">None (Top Level)</option>
                                @php
                                    $editCategoryId = isset($editCategory) ? $editCategory->id : null;
                                    $editParentId = isset($editCategory) ? $editCategory->parent_id : null;
                                    $topLevelCategories = $allCategories->whereNull('parent_id')->where('id', '!=', $editCategoryId);
                                    $subCategories = $allCategories->whereNotNull('parent_id')->where('id', '!=', $editCategoryId);
                                    // Get children IDs for disabling
                                    $childIds = [];
                                    if ($editCategoryId) {
                                        $childIds = $allCategories->where('parent_id', $editCategoryId)->pluck('id')->toArray();
                                    }
                                @endphp
                                
                                {{-- Top Level Categories --}}
                                @foreach($topLevelCategories as $cat)
                                    @php
                                        $isSelf = $cat->id == $editCategoryId;
                                        $isChild = in_array($cat->id, $childIds);
                                        $disabled = $isSelf || $isChild;
                                    @endphp
                                    <option value="{{ $cat->id }}" 
                                        {{ old('parent_id', $editParentId) == $cat->id ? 'selected' : '' }}
                                        {{ $disabled ? 'disabled' : '' }}
                                        class="{{ $isSelf ? 'dropdown-option-self' : ($isChild ? 'dropdown-option-child' : '') }}">
                                        {{ $cat->name }}
                                        @if($isSelf)
                                            <span class="badge bg-warning text-dark">(Current)</span>
                                        @elseif($isChild)
                                            <span class="badge bg-danger">(Sub-category)</span>
                                        @else
                                            <span class="text-muted">(Top Level)</span>
                                        @endif
                                    </option>
                                @endforeach
                                
                                {{-- Sub Categories --}}
                                @if($subCategories->count() > 0)
                                    <option value="" disabled style="background:#e9ecef;color:#6c757d;">────────── Sub-Categories ──────────</option>
                                    @foreach($subCategories as $cat)
                                        @php
                                            $isSelf = $cat->id == $editCategoryId;
                                            $isChild = in_array($cat->id, $childIds);
                                            $disabled = $isSelf || $isChild;
                                        @endphp
                                        <option value="{{ $cat->id }}" 
                                            {{ old('parent_id', $editParentId) == $cat->id ? 'selected' : '' }}
                                            {{ $disabled ? 'disabled' : '' }}
                                            class="{{ $isSelf ? 'dropdown-option-self' : ($isChild ? 'dropdown-option-child' : '') }}"
                                            style="padding-left: 20px;">
                                            ↳ {{ $cat->name }}
                                            @if($cat->parent)
                                                <span class="text-muted">({{ $cat->parent->name }})</span>
                                            @endif
                                            @if($isSelf)
                                                <span class="badge bg-warning text-dark">(Current)</span>
                                            @elseif($isChild)
                                                <span class="badge bg-danger">(Sub-category)</span>
                                            @endif
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Select a parent category if this is a sub-category. 
                                <span class="text-warning">Disabled options (Current/Sub-category) cannot be selected to avoid circular references.</span>
                            </small>
                        </div>

                        <!-- Category Group -->
                        <div class="col-md-6">
                            <label for="edit_category_group" class="form-label fw-bold">Category Group</label>
                            <select class="form-select" id="edit_category_group" name="category_group">
                                <option value="Other">Other (Default)</option>
                                <option value="Computers">Computers</option>
                                <option value="Accessories">Accessories</option>
                                <option value="Phones">Phones</option>
                                <option value="Phone Accessories">Phone Accessories</option>
                                <option value="Audio">Audio</option>
                                <option value="Displays">Displays</option>
                                <option value="Storage">Storage</option>
                                <option value="Wearables">Wearables</option>
                                <option value="Gaming">Gaming</option>
                                <option value="Networking">Networking</option>
                                <option value="Printers">Printers</option>
                            </select>
                            <small class="text-muted">Group similar categories for better AI recommendations</small>
                        </div>

                        <!-- Tags -->
                        <div class="col-12">
                            <label for="edit_tags" class="form-label fw-bold">Tags</label>
                            <div class="tags-input-wrapper">
                                <input type="text" class="form-control" id="edit_tags_input" 
                                    placeholder="Type a tag and press Enter..." autocomplete="off">
                                <div id="edit_tags_container" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>
                            <small class="text-muted">Press Enter to add tags. Examples: "gaming", "wireless", "performance"</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-outline-primary themed-outline-btn db-primary-btn" id="updateCategoryBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        <i class="bi bi-check2 me-2"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/seller-categories.js') }}"></script>

<script>
// ============================================
// TAGS INPUT HANDLING
// ============================================

// Create Tags
const createTagsInput = document.getElementById('create_tags_input');
const createTagsContainer = document.getElementById('create_tags_container');

if (createTagsInput) {
    createTagsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const tag = this.value.trim();
            if (tag && !isTagExist(tag, createTagsContainer)) {
                addTag(tag, createTagsContainer);
                this.value = '';
            }
        }
    });
}

// Edit Tags
const editTagsInput = document.getElementById('edit_tags_input');
const editTagsContainer = document.getElementById('edit_tags_container');

if (editTagsInput) {
    editTagsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const tag = this.value.trim();
            if (tag && !isTagExist(tag, editTagsContainer)) {
                addTag(tag, editTagsContainer);
                this.value = '';
            }
        }
    });
}

function addTag(tag, container) {
    const badge = document.createElement('span');
    badge.className = 'badge bg-primary d-flex align-items-center gap-1 p-2';
    badge.innerHTML = `
        ${tag}
        <span class="remove-tag" onclick="removeTag(this)" style="cursor:pointer;font-size:12px;">&times;</span>
        <input type="hidden" name="tags[]" value="${tag}">
    `;
    container.appendChild(badge);
}

function removeTag(element) {
    const badge = element.closest('.badge');
    // Don't remove if it's the only tag (at least keep 'general')
    const container = badge.parentElement;
    if (container.querySelectorAll('.badge').length <= 1) {
        return;
    }
    badge.remove();
}

function isTagExist(tag, container) {
    const hiddenInputs = container.querySelectorAll('input[type="hidden"]');
    for (let input of hiddenInputs) {
        if (input.value.toLowerCase() === tag.toLowerCase()) {
            return true;
        }
    }
    return false;
}

// ============================================
// LOAD TAGS FOR EDIT MODAL
// ============================================

function loadTagsForEdit(tags) {
    const container = document.getElementById('edit_tags_container');
    container.innerHTML = '';
    if (tags && tags.length > 0) {
        tags.forEach(tag => {
            addTag(tag, container);
        });
    } else {
        addTag('general', container);
    }
}

// ============================================
// LOAD CATEGORY DATA FOR VIEW MODAL
// ============================================

function loadCategoryForView(data) {
    document.getElementById('view_name').value = data.name || '';
    document.getElementById('view_description').value = data.description || '';
    document.getElementById('view_parent').value = data.parent ? data.parent.name : 'None (Top Level)';
    document.getElementById('view_group').value = data.category_group || 'Other';
    document.getElementById('view_products_count').value = data.products_count || 0;
    document.getElementById('view_created_at').value = data.created_at ? new Date(data.created_at).toLocaleString() : '';
    document.getElementById('view_slug').value = data.slug || '';

    // Tags
    const tagsContainer = document.getElementById('view_tags');
    tagsContainer.innerHTML = '';
    if (data.tags && data.tags.length > 0) {
        data.tags.forEach(tag => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary p-2';
            badge.textContent = tag;
            tagsContainer.appendChild(badge);
        });
    }
}

// ============================================
// LOAD CATEGORY DATA FOR EDIT MODAL
// ============================================

function loadCategoryForEdit(data) {
    document.getElementById('edit_category_id').value = data.public_id || data.id;
    document.getElementById('edit_name').value = data.name || '';
    document.getElementById('edit_description').value = data.description || '';
    document.getElementById('edit_parent_id').value = data.parent_id || '';
    document.getElementById('edit_category_group').value = data.category_group || 'Other';
    
    // Load tags
    loadTagsForEdit(data.tags || ['general']);
    
    // Update form action
    const form = document.getElementById('editCategoryForm');
    const updateUrl = "{{ route('seller.categories.update', ['id' => '__ID__']) }}".replace('__ID__', data.public_id || data.id);
    form.action = updateUrl;
}

// ============================================
// AUTO-GENERATE SLUG FROM NAME (Create)
// ============================================
document.getElementById('create_name').addEventListener('input', function() {
    // Just for visual feedback - slug is auto-generated on server
});

// ============================================
// FORM SUBMIT HANDLING
// ============================================

// Create Form
document.getElementById('createCategoryForm').addEventListener('submit', function(e) {
    // Ensure at least one tag exists
    const container = document.getElementById('create_tags_container');
    const tags = container.querySelectorAll('input[type="hidden"]');
    if (tags.length === 0) {
        addTag('general', container);
    }
});

// Edit Form
document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
    const container = document.getElementById('edit_tags_container');
    const tags = container.querySelectorAll('input[type="hidden"]');
    if (tags.length === 0) {
        addTag('general', container);
    }
});

// ============================================
// OVERRIDE VIEW MODAL OPENING
// ============================================

document.getElementById('viewCategoryModal').addEventListener('show.bs.modal', function(event) {
    // The data is loaded by the JavaScript that opens the modal
});

// ============================================
// OVERRIDE EDIT MODAL OPENING
// ============================================

document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function(event) {
    // The data is loaded by the JavaScript that opens the modal
});

// ============================================
// POPULATE PARENT DROPDOWN FOR EDIT MODAL
// ============================================

function populateParentDropdown(categories, currentCategoryId, selectedParentId) {
    const select = document.getElementById('edit_parent_id');
    select.innerHTML = '<option value="">None (Top Level)</option>';
    
    // Get child IDs to disable
    const childIds = [];
    categories.forEach(cat => {
        if (cat.parent_id == currentCategoryId) {
            childIds.push(cat.id);
        }
    });
    
    // Top level categories
    const topLevel = categories.filter(cat => cat.parent_id === null && cat.id !== currentCategoryId);
    const subCategories = categories.filter(cat => cat.parent_id !== null && cat.id !== currentCategoryId);
    
    topLevel.forEach(cat => {
        const isChild = childIds.includes(cat.id);
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name + ' (Top Level)';
        if (selectedParentId == cat.id) option.selected = true;
        if (isChild) {
            option.disabled = true;
            option.textContent += ' ⚠️ (Sub-category)';
            option.className = 'dropdown-option-child';
        }
        select.appendChild(option);
    });
    
    if (subCategories.length > 0) {
        const divider = document.createElement('option');
        divider.value = '';
        divider.disabled = true;
        divider.textContent = '────────── Sub-Categories ──────────';
        divider.style.background = '#e9ecef';
        divider.style.color = '#6c757d';
        select.appendChild(divider);
        
        subCategories.forEach(cat => {
            const isChild = childIds.includes(cat.id);
            const isSelf = cat.id === currentCategoryId;
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = '↳ ' + cat.name;
            if (cat.parent) {
                option.textContent += ' (' + cat.parent.name + ')';
            }
            if (selectedParentId == cat.id) option.selected = true;
            if (isSelf || isChild) {
                option.disabled = true;
                if (isSelf) {
                    option.textContent += ' ⚠️ (Current)';
                    option.className = 'dropdown-option-self';
                } else {
                    option.textContent += ' ⚠️ (Sub-category)';
                    option.className = 'dropdown-option-child';
                }
            }
            option.style.paddingLeft = '20px';
            select.appendChild(option);
        });
    }
}
</script>
@endpush