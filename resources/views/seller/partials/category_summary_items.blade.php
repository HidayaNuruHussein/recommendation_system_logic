{{--
SOURCE CODE OWNER: HAGAI HAROLD NGOBEY
hngobey@gmail.com | +255 765 384 905
Provided to assist students. Students may study, modify, and reuse it
for any non-commercial educational purpose. See LICENSE.md.
--}}
@foreach ($categories as $category)
    <div class="category-summary-item {{ $loop->first ? 'active' : '' }}" 
         data-category-id="{{ $category->public_id }}"
         onclick="loadCategoryProducts('{{ $category->public_id }}')">
        <div class="category-summary-card h-100">
            <!-- Watermark Icon -->
            <i class="bi bi-{{ $category->products_count > 0 ? 'box-seam' : 'folder' }} category-summary-watermark"></i>
            
            <!-- Category Name -->
            <div class="category-summary-name">
                {{ $category->name }}
                @if($category->children->count() > 0)
                    <span class="badge bg-info ms-1" title="Has {{ $category->children->count() }} sub-categories">
                        <i class="bi bi-diagram-3"></i>
                    </span>
                @endif
            </div>
            
            <!-- Products Count -->
            <div class="category-summary-value">{{ number_format($category->products_count) }} Products</div>
            
            <!-- ✅ AI Recommendation Metadata -->
            <div class="category-summary-meta mt-1">
                @if($category->category_group && $category->category_group != 'Other')
                    <span class="badge bg-primary category-summary-group">{{ $category->category_group }}</span>
                @endif
                @if($category->parent)
                    <span class="badge bg-secondary">Sub of {{ $category->parent->name }}</span>
                @endif
                @if($category->tags && count($category->tags) > 0)
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-tags me-1"></i>{{ count($category->tags) }}
                    </span>
                @endif
            </div>
        </div>
    </div>
@endforeach