document.addEventListener('DOMContentLoaded', function () {
    var searchForm = document.getElementById('shop-search-form');
    var searchInput = searchForm ? searchForm.querySelector('input[name="search"]') : null;
    var categoriesGrid = document.getElementById('shop-categories-grid');
    var featuredRowsContainer = document.getElementById('featured-rows-container');
    var productsContainer = document.getElementById('productsContainer');
    var paginationContainer = document.getElementById('shop-pagination');
    var sortSelect = document.getElementById('shop-sort-select');
    var searchSpinner = document.getElementById('search-loading-spinner');
    var infiniteLoader = document.getElementById('shop-infinite-loader');
    var infiniteEnd = document.getElementById('shop-infinite-end');
    var scrollSentinel = document.getElementById('shop-scroll-sentinel');

    if (!searchForm || !searchInput || !categoriesGrid || !productsContainer || !paginationContainer || !featuredRowsContainer) return;

    var debounceId;
    var controller;
    var currentParams = new URLSearchParams(window.location.search);
    var resizeTimer;
    var nextPageUrl = null;
    var isLoadingMore = false;
    var observer = null;

    function applyFeaturedRowsWindowing() {
        var featuredGrids = document.querySelectorAll('.featured-products-grid');
        if (!featuredGrids.length) return;

        featuredGrids.forEach(function (grid) {
            var cards = Array.prototype.slice.call(grid.querySelectorAll('.featured-product-card'));
            if (!cards.length) return;
            cards.forEach(function (card, index) {
                card.style.display = '';
            });
        });
    }

    function bindMobileScrollIndicators() {
        var rows = document.querySelectorAll('.featured-row-panel');
        rows.forEach(function (row) {
            var grid = row.querySelector('.featured-products-grid');
            var thumb = row.querySelector('.mobile-scroll-thumb');
            var indicator = row.querySelector('.mobile-scroll-indicator');
            if (!grid || !thumb || !indicator) return;

            var update = function () {
                var max = Math.max(0, grid.scrollWidth - grid.clientWidth);
                if (max <= 0) {
                    thumb.style.transform = 'translateX(0)';
                    return;
                }
                var ratio = grid.scrollLeft / max;
                var usable = Math.max(0, indicator.clientWidth - thumb.clientWidth);
                thumb.style.transform = 'translateX(' + Math.round(usable * ratio) + 'px)';
            };

            grid.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
            update();
        });
    }


    function requestUrlFromParams() {
        var url = new URL(searchForm.action, window.location.origin);
        url.search = currentParams.toString();
        return url;
    }

    function getNextPageUrlFromDoc(doc) {
        var nextLink = doc.querySelector('#shop-pagination .pagination .page-item.active + .page-item a.page-link');
        if (!nextLink) {
            nextLink = doc.querySelector('#shop-pagination .pagination a[rel="next"]');
        }
        return nextLink ? nextLink.href : null;
    }

    function setInfiniteState() {
        if (infiniteEnd) infiniteEnd.style.display = nextPageUrl ? 'none' : '';
    }

    function fetchAndReplace(url) {
        if (controller) controller.abort();
        controller = new AbortController();

        // Show spinner, hide products and featured rows while loading
        if (searchSpinner) searchSpinner.style.display = '';
        productsContainer.style.display = 'none';
        if (featuredRowsContainer) featuredRowsContainer.style.display = 'none';

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Request failed');
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');

                var nextCategories = doc.getElementById('shop-categories-grid');
                var nextFeaturedRows = doc.getElementById('featured-rows-container');
                var nextProducts = doc.getElementById('productsContainer');
                var nextPagination = doc.getElementById('shop-pagination');
                var nextSearch = doc.querySelector('#shop-search-form input[name="search"]');
                var nextSortSelect = doc.getElementById('shop-sort-select');

                if (nextCategories) categoriesGrid.innerHTML = nextCategories.innerHTML;
                if (nextProducts) productsContainer.innerHTML = nextProducts.innerHTML;
                if (nextPagination) paginationContainer.innerHTML = nextPagination.innerHTML;

                // Show/hide featured rows based on whether response has them
                if (nextFeaturedRows) {
                    featuredRowsContainer.style.display = '';
                } else {
                    featuredRowsContainer.style.display = 'none';
                }
                if (nextSearch) searchInput.value = nextSearch.value;
                if (sortSelect && nextSortSelect) sortSelect.value = nextSortSelect.value;
                nextPageUrl = getNextPageUrlFromDoc(doc);
                setInfiniteState();
                applyFeaturedRowsWindowing();
                bindMobileScrollIndicators();

                // Keep URL clean while using AJAX filters/search.
                window.history.replaceState({}, '', window.location.pathname);
            })
            .catch(function (error) {
                if (error.name === 'AbortError') return;
                console.error(error);
            })
            .finally(function () {
                // Hide spinner, show products
                if (searchSpinner) searchSpinner.style.display = 'none';
                productsContainer.style.display = '';
            });
    }

    function appendNextProducts() {
        if (!nextPageUrl || isLoadingMore) return;

        isLoadingMore = true;
        if (infiniteLoader) infiniteLoader.style.display = '';

        fetch(nextPageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) throw new Error('Request failed');
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var nextProducts = doc.getElementById('productsContainer');
                var nextPagination = doc.getElementById('shop-pagination');

                if (nextProducts) {
                    var articles = nextProducts.querySelectorAll('.product-card');
                    articles.forEach(function (article) {
                        productsContainer.appendChild(article);
                    });
                }

                if (nextPagination) {
                    paginationContainer.innerHTML = nextPagination.innerHTML;
                }

                nextPageUrl = getNextPageUrlFromDoc(doc);
                setInfiniteState();
            })
            .catch(function (error) {
                console.error(error);
            })
            .finally(function () {
                isLoadingMore = false;
                if (infiniteLoader) infiniteLoader.style.display = 'none';
            });
    }

    function resetInfiniteObserver() {
        if (!scrollSentinel) return;
        if (observer) observer.disconnect();

        observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    appendNextProducts();
                }
            });
        }, { rootMargin: '600px 0px' });

        observer.observe(scrollSentinel);
    }

    function buildSearchUrl() {
        var searchValue = searchInput.value.trim();

        if (searchValue) {
            currentParams.set('search', searchValue);
        } else {
            currentParams.delete('search');
        }

        currentParams.delete('page');
        return requestUrlFromParams();
    }

    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        fetchAndReplace(buildSearchUrl());
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceId);
        debounceId = setTimeout(function () {
            fetchAndReplace(buildSearchUrl());
        }, 250);
    });

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            var sortValue = this.value;

            if (!sortValue) {
                currentParams.delete('sort_by');
                currentParams.delete('sort_order');
            } else {
                var parts = sortValue.split('-');
                if (parts.length === 2) {
                    currentParams.set('sort_by', parts[0]);
                    currentParams.set('sort_order', parts[1]);
                }
            }

            currentParams.delete('page');
            fetchAndReplace(requestUrlFromParams());
        });
    }

    document.addEventListener('click', function (event) {
        var categoryLink = event.target.closest('#shop-categories-grid a.category-pill');
        if (categoryLink) {
            event.preventDefault();
            // Use the link's URL directly — it already has all params (search, sort, etc.)
            currentParams = new URLSearchParams(new URL(categoryLink.href, window.location.origin).search);
            fetchAndReplace(requestUrlFromParams());
            return;
        }

        var pageLink = event.target.closest('#shop-pagination .pagination a');
        if (pageLink) {
            event.preventDefault();
            var pagingUrl = new URL(pageLink.href, window.location.origin);
            currentParams = new URLSearchParams(pagingUrl.search);
            fetchAndReplace(requestUrlFromParams());
        }
    });

    searchInput.addEventListener('focus', function () {
        var wrap = this.closest('.search-bar');
        if (wrap) wrap.classList.add('search-focused');
    });

    searchInput.addEventListener('blur', function () {
        var wrap = this.closest('.search-bar');
        if (wrap) wrap.classList.remove('search-focused');
    });

    applyFeaturedRowsWindowing();
    bindMobileScrollIndicators();
    nextPageUrl = getNextPageUrlFromDoc(document);
    setInfiniteState();
    resetInfiniteObserver();
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(applyFeaturedRowsWindowing, 120);
    });
});

/* ============================================
   AI RECOMMENDATIONS - AJAX LOADER
   ============================================ */
(function () {
    'use strict';

    var AIEndpoints = {
        forProduct: '/api/recommendations/product',
    };

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showLoader(section) {
        if (!section) return;
        var loader = document.createElement('div');
        loader.className = 'ai-section-loader';
        loader.innerHTML = '<div class="spinner"></div>';
        section.appendChild(loader);
    }

    function hideLoader(section) {
        if (!section) return;
        var loader = section.querySelector('.ai-section-loader');
        if (loader) loader.remove();
    }

    function renderProductCard(item, showAiBadge) {
        var p = item.product || item;
        var confidence = item.confidence || 0;
        var confidencePercent = confidence > 0 ? Math.round(confidence * 100) : 0;
        var imageUrl = (p.thumbnail || p.image) ? '/storage/' + (p.thumbnail || p.image) : '/img/logo.png';
        var showUrl = p.public_id && p.slug
            ? '/shop/' + p.public_id + '/' + p.slug
            : (p.url || '#');

        var aiBadgeHtml = '';
        if (showAiBadge) {
            aiBadgeHtml = '<span class="product-badge badge-ai">' +
                '<i class="bi bi-stars"></i> AI Pick' +
                (confidencePercent > 0 ? ' <span class="badge-confidence">' + confidencePercent + '%</span>' : '') +
                '</span>';
        }

        return '<article class="product-card ai-product-card ' + (showAiBadge ? 'has-ai-badge' : '') + '">' +
            '<div class="product-image">' +
                '<a href="' + showUrl + '">' +
                    '<img src="' + imageUrl + '" alt="' + (p.name || '') + '" loading="lazy">' +
                '</a>' +
                '<div class="product-badges">' + aiBadgeHtml + '</div>' +
            '</div>' +
            '<div class="product-info">' +
                '<h3 class="product-title"><a href="' + showUrl + '">' + (p.name || '') + '</a></h3>' +
                '<div class="product-prices">' +
                    '<span class="product-price">Tsh ' + new Intl.NumberFormat('en-US').format(p.new_price || 0) + '</span>' +
                '</div>' +
            '</div>' +
        '</article>';
    }

    /**
     * Lazy load AI recommendations for the current product page.
     */
    function loadProductRecommendations() {
        var section = document.getElementById('ai-related-section');
        if (!section) return;

        // Only AJAX if section is empty (i.e. server didn't render any)
        if (section.querySelectorAll('.ai-product-card').length > 0) return;

        var productId = section.getAttribute('data-product-id');
        if (!productId) return;

        showLoader(section);
        fetch(AIEndpoints.forProduct + '/' + productId, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            hideLoader(section);
            if (!data || !data.recommendations) return;
            var grid = section.querySelector('.ai-products-grid');
            if (!grid) return;
            grid.innerHTML = '';
            (data.recommendations || []).forEach(function (rec) {
                grid.insertAdjacentHTML('beforeend', renderProductCard(rec, true));
            });
        })
        .catch(function () {
            hideLoader(section);
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            loadProductRecommendations();
        });
    } else {
        loadProductRecommendations();
    }
})();
