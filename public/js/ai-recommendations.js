/**
 * AI Recommendations - Client side handler.
 * Provides async loading, refresh, and tracking for AI-powered sections.
 */
(function () {
    'use strict';

    const endpoints = {
        product: '/api/recommendations/',
    };

    /**
     * Fetch recommendations for a product.
     */
    async function fetchRecommendations(productId, topN = 8) {
        try {
            const res = await fetch(`${endpoints.product}${productId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) return [];
            const data = await res.json();
            return data.recommendations || [];
        } catch (err) {
            console.warn('AI recommendations fetch failed', err);
            return [];
        }
    }

    /**
     * Render a single AI product card.
     */
    function renderCard(product) {
        const price = Number(product.new_price || 0).toLocaleString();
        const oldPrice = product.old_price > product.new_price
            ? `<span class="product-old-price">Tsh ${Number(product.old_price).toLocaleString()}</span>` : '';
        const showUrl = `/shop/${product.public_id}/${product.slug}`;
        const img = product.thumbnail
            ? `/storage/${product.thumbnail}`
            : '/img/logo.png';
        return `
        <article class="product-card ai-product-card">
            <div class="product-image">
                <a href="${showUrl}" class="text-decoration-none">
                    <img src="${img}" alt="${product.name}" loading="lazy">
                </a>
                <div class="product-badges">
                    <span class="product-badge badge-ai"><i class="bi bi-stars"></i> AI Pick</span>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-title">
                    <a href="${showUrl}" class="text-decoration-none">${product.name}</a>
                </h3>
                <div class="product-prices">
                    <span class="product-price">Tsh ${price}</span>
                    ${oldPrice}
                </div>
            </div>
        </article>`;
    }

    /**
     * Show skeleton loader.
     */
    function showSkeleton(container, count = 4) {
        if (!container) return;
        let html = '<div class="products-grid ai-products-grid">';
        for (let i = 0; i < count; i++) {
            html += '<div class="ai-skeleton-card"></div>';
        }
        html += '</div>';
        container.innerHTML = html;
    }

    /**
     * Replace section content with rendered cards.
     */
    function renderInto(container, products) {
        if (!container) return;
        if (!products || products.length === 0) {
            container.style.display = 'none';
            return;
        }
        const cards = products.map(renderCard).join('');
        container.querySelector('.ai-products-grid').innerHTML = cards;
    }

    /**
     * Track click on AI-recommended product (analytics).
     */
    function trackClick(event) {
        const card = event.target.closest('.ai-product-card');
        if (!card) return;
        const productId = card.dataset.productId;
        if (!productId || !navigator.sendBeacon) return;
        try {
            navigator.sendBeacon('/api/recommendations/track-click', JSON.stringify({
                product_id: productId,
                source: 'ai',
                ts: Date.now(),
            }));
        } catch (e) { /* ignore */ }
    }

    /**
     * Refresh recommendations in a section.
     */
    async function refreshSection(sectionEl, productId) {
        if (!sectionEl || !productId) return;
        showSkeleton(sectionEl, 4);
        const products = await fetchRecommendations(productId, 8);
        renderInto(sectionEl, products);
    }

    /**
     * Initialize on DOM ready.
     */
    function init() {
        // Track clicks on AI cards
        document.addEventListener('click', trackClick);

        // Auto-refresh AI section on shop page every 5 minutes (optional)
        const aiSection = document.getElementById('ai-recommendations-section');
        if (aiSection) {
            const productId = aiSection.dataset.productId;
            // Could add timer here for periodic refresh
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose helpers
    window.AIRecommendations = { fetchRecommendations, refreshSection, renderCard };
})();
