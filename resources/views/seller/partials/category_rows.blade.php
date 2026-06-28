{{--
SOURCE CODE OWNER: HAGAI HAROLD NGOBEY
hngobey@gmail.com | +255 765 384 905
Provided to assist students. Students may study, modify, and reuse it
for any non-commercial educational purpose. See LICENSE.md.
--}}
@foreach ($categories as $category)
    <tr data-category-id="{{ $category->public_id }}">
        <td class="category-name-col">
            <strong>{{ $category->name }}</strong>
            @if($category->children->count() > 0)
                <span class="badge bg-info ms-1" title="Has {{ $category->children->count() }} sub-categories">
                    <i class="bi bi-diagram-3 me-1"></i>{{ $category->children->count() }}
                </span>
            @endif
        </td>
        <td class="category-description-col">{{ $category->description ? Str::limit($category->description, 60) : 'No description' }}</td>
        
        <!-- ✅ Parent Column -->
        <td class="fit-content-col">
            @if($category->parent)
                <span class="badge bg-secondary">{{ $category->parent->name }}</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        
        <!-- ✅ Group Column -->
        <td class="fit-content-col">
            @if($category->category_group && $category->category_group != 'Other')
                <span class="badge bg-primary">{{ $category->category_group }}</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        
        <!-- ✅ Tags Column -->
        <td class="fit-content-col">
            @if($category->tags && count($category->tags) > 0)
                <div class="d-flex flex-wrap gap-1">
                    @foreach(array_slice($category->tags, 0, 3) as $tag)
                        <span class="badge bg-light text-dark border">{{ $tag }}</span>
                    @endforeach
                    @if(count($category->tags) > 3)
                        <span class="badge bg-light text-muted">+{{ count($category->tags) - 3 }}</span>
                    @endif
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        
        <td class="text-center fit-content-col">
            <span class="badge category-count-badge">{{ $category->products_count }}</span>
        </td>
        <td class="fit-content-col">{{ $category->created_at->format('d M Y') }}</td>
        <td class="text-center fit-content-col text-nowrap">
            <button class="btn btn-sm btn-outline-primary themed-outline-btn me-1"
                onclick="showCategory(@js($category->public_id))" title="View Category">
                <i class="bi bi-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary themed-outline-btn me-1"
                onclick="editCategory(@js($category->public_id))" title="Edit Category">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" 
                onclick="deleteCategory(@js($category->public_id), @js($category->name))" 
                title="Delete Category">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@endforeach