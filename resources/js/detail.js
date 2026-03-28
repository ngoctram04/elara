document.addEventListener('DOMContentLoaded', () => {
    const mainImg = document.getElementById('main-image');
    const qtyInput = document.querySelector('input[name="qty"]');
    const addBtn = document.querySelector('#add-to-cart-form button[type="submit"]');
    const variantIdInput = document.getElementById('variant_id');
    const priceFinal = document.getElementById('price-final');
    const priceOriginal = document.getElementById('price-original');
    const stockText = document.getElementById('stock-text');

    const lightbox = document.getElementById('media-lightbox');
    const lightImg = document.getElementById('lightbox-img');
    const lightVideo = document.getElementById('lightbox-video');
    const lightVideoSrc = document.getElementById('lightbox-video-src');
    const closeBtn = document.getElementById('lightbox-close');
    const zoomMainImageBtn = document.getElementById('zoom-main-image');

    const form = document.getElementById('add-to-cart-form');
    const minusBtn = document.getElementById('qty-minus');
    const plusBtn = document.getElementById('qty-plus');
    const toggleReviewsBtn = document.getElementById('toggle-reviews-btn');
    const reviewAnchor = document.querySelector('.review-anchor');

    const recentSlider = document.getElementById('recent-slider');
    const recentPrev = document.getElementById('recent-prev');
    const recentNext = document.getElementById('recent-next');

    const relatedSlider = document.getElementById('related-slider');
    const relatedPrev = document.getElementById('related-prev');
    const relatedNext = document.getElementById('related-next');

    const wishlistButtons = document.querySelectorAll('.btn-wishlist, .btn-wishlist-top');

    function formatPrice(value) {
        const number = Number(value || 0);
        return new Intl.NumberFormat('vi-VN').format(number) + 'đ';
    }

    function getThumbs() {
        return document.querySelectorAll('.thumb-img');
    }

    function getVariantButtons() {
        return document.querySelectorAll('.variant-btn');
    }

    function openImageLightbox(src) {
        if (!lightbox || !lightImg || !lightVideo) return;

        lightbox.style.display = 'flex';
        lightImg.src = src;
        lightImg.style.display = 'block';

        lightVideo.pause();
        lightVideo.style.display = 'none';

        if (lightVideoSrc) {
            lightVideoSrc.src = '';
        }
    }

    function openVideoLightbox(src) {
        if (!lightbox || !lightImg || !lightVideo || !lightVideoSrc) return;

        lightbox.style.display = 'flex';
        lightVideoSrc.src = src;
        lightVideo.load();

        lightVideo.style.display = 'block';
        lightImg.style.display = 'none';
    }

    function closeLightbox() {
        if (!lightbox || !lightVideo) return;

        lightbox.style.display = 'none';
        lightVideo.pause();

        if (lightVideoSrc) {
            lightVideoSrc.src = '';
        }

        if (lightImg) {
            lightImg.src = '';
        }
    }

    function switchToTab(tabId) {
        const tabButtons = document.querySelectorAll('.detail-tab-btn');
        const tabPanels = document.querySelectorAll('.detail-tab-panel');

        tabButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tabId);
        });

        tabPanels.forEach(panel => {
            panel.classList.toggle('active', panel.id === tabId);
        });
    }

    function setActiveThumbByImage(imageSrc) {
        const thumbs = getThumbs();
        thumbs.forEach(img => img.classList.remove('active'));

        const matchedThumb = Array.from(thumbs).find(img => img.dataset.image === imageSrc);
        if (matchedThumb) {
            matchedThumb.classList.add('active');
        }
    }

    function setMainImage(imageSrc) {
        if (!mainImg || !imageSrc) return;
        mainImg.src = imageSrc;
        setActiveThumbByImage(imageSrc);
    }

    function updateQtyMax(stock) {
        if (!qtyInput) return;

        qtyInput.max = stock > 0 ? stock : 1;

        let currentQty = parseInt(qtyInput.value || 1, 10);
        if (Number.isNaN(currentQty) || currentQty < 1) currentQty = 1;
        if (stock > 0 && currentQty > stock) currentQty = stock;

        qtyInput.value = currentQty;
    }

    function updateAddToCartState(stock) {
        if (!addBtn) return;

        if (stock > 0) {
            addBtn.disabled = false;
            addBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Thêm vào giỏ hàng';
        } else {
            addBtn.disabled = true;
            addBtn.textContent = 'Hết hàng';
        }
    }

    function updateStockText(stock) {
        if (!stockText) return;

        stockText.innerText = stock > 0
            ? 'Còn ' + stock + ' sản phẩm'
            : 'Sản phẩm đã hết hàng';
    }

    function updatePrice(finalPrice, originalPrice) {
        if (priceFinal) {
            priceFinal.innerText = formatPrice(finalPrice);
        }

        if (priceOriginal) {
            if (originalPrice && Number(originalPrice) > Number(finalPrice)) {
                priceOriginal.style.display = 'block';
                priceOriginal.innerText = formatPrice(originalPrice);
            } else {
                priceOriginal.style.display = 'none';
                priceOriginal.innerText = '';
            }
        }
    }

    function updateVariantUI(btn) {
        if (!btn) return;

        getVariantButtons().forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const final = parseFloat(btn.dataset.final || 0);
        const original = btn.dataset.original;
        const stock = parseInt(btn.dataset.stock || 0, 10);
        const image = btn.dataset.image;
        const variantId = btn.dataset.id || '';

        updatePrice(final, original);
        updateStockText(stock);
        updateQtyMax(stock);
        updateAddToCartState(stock);

        if (image) {
            setMainImage(image);
        }

        if (variantIdInput) {
            variantIdInput.value = variantId;
        }
    }

    function initDefaultThumb() {
        if (!mainImg) return;

        const activeThumb = document.querySelector('.thumb-img.active');
        if (activeThumb) return;

        setActiveThumbByImage(mainImg.src);
    }

    function updateWishlistButtons(productId, isFavorited, countText) {
    wishlistButtons.forEach(btn => {
        if (String(btn.dataset.productId) !== String(productId)) return;

        const icon = btn.querySelector('i');
        const countEl = btn.querySelector('#wishlist-count, .wishlist-count');
        const span = btn.querySelector('span');

        if (icon) {
            icon.className = `bi ${isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart text-danger'}`;
        }

        if (countEl) {
            countEl.textContent = countText;
        }

        if (span && !countEl) {
            span.textContent = `${isFavorited ? 'Đã thích' : 'Yêu thích'} (${countText})`;
        }
    });
}

    function formatWishlistCount(value) {
        const raw = Number(value || 0);

        if (raw >= 1000) {
            return `${String(Math.round((raw / 1000) * 10) / 10).replace('.', ',')}k`;
        }

        return new Intl.NumberFormat('vi-VN').format(raw);
    }

    function initHorizontalSlider({
        slider,
        prevBtn,
        nextBtn,
        slideSelector,
        gapFallback = 18,
        autoInterval = 0,
        loop = false
    }) {
        if (!slider) return;

        const getSlide = () => slider.querySelector(slideSelector);

        const getScrollAmount = () => {
            const firstSlide = getSlide();
            if (!firstSlide) return 300;

            const sliderStyle = window.getComputedStyle(slider);
            const gap = parseInt(sliderStyle.gap, 10) || gapFallback;

            return firstSlide.offsetWidth + gap;
        };

        const scrollNext = () => {
            const amount = getScrollAmount();
            const maxScrollLeft = slider.scrollWidth - slider.clientWidth;

            if (loop && slider.scrollLeft + amount >= maxScrollLeft - 10) {
                slider.scrollTo({
                    left: 0,
                    behavior: 'smooth'
                });
                return;
            }

            slider.scrollBy({
                left: amount,
                behavior: 'smooth'
            });
        };

        const scrollPrev = () => {
            const amount = getScrollAmount();

            if (loop && slider.scrollLeft <= 10) {
                slider.scrollTo({
                    left: Math.max(0, slider.scrollWidth - slider.clientWidth),
                    behavior: 'smooth'
                });
                return;
            }

            slider.scrollBy({
                left: -amount,
                behavior: 'smooth'
            });
        };

        if (prevBtn) {
            prevBtn.addEventListener('click', scrollPrev);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', scrollNext);
        }

        let autoSlide = null;

        if (autoInterval > 0) {
            autoSlide = setInterval(scrollNext, autoInterval);

            slider.addEventListener('mouseenter', () => {
                if (autoSlide) clearInterval(autoSlide);
            });

            slider.addEventListener('mouseleave', () => {
                autoSlide = setInterval(scrollNext, autoInterval);
            });
        }
    }

    function initVerticalSlider({
        slider,
        prevBtn,
        nextBtn,
        slideSelector,
        gapFallback = 18,
        autoInterval = 0,
        loop = false
    }) {
        if (!slider) return;

        const isDesktopVertical = () => window.innerWidth > 1200;

        const getSlide = () => slider.querySelector(slideSelector);

        const getScrollAmount = () => {
            const firstSlide = getSlide();
            if (!firstSlide) return 300;

            const sliderStyle = window.getComputedStyle(slider);
            const gap = parseInt(sliderStyle.gap, 10) || gapFallback;

            if (isDesktopVertical()) {
                return firstSlide.offsetHeight + gap;
            }

            return firstSlide.offsetWidth + gap;
        };

        const scrollNext = () => {
            const amount = getScrollAmount();

            if (isDesktopVertical()) {
                const maxScrollTop = slider.scrollHeight - slider.parentElement.clientHeight;

                if (loop && slider.parentElement.scrollTop + amount >= maxScrollTop - 10) {
                    slider.parentElement.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    return;
                }

                slider.parentElement.scrollBy({
                    top: amount,
                    behavior: 'smooth'
                });
                return;
            }

            const maxScrollLeft = slider.parentElement.scrollWidth - slider.parentElement.clientWidth;

            if (loop && slider.parentElement.scrollLeft + amount >= maxScrollLeft - 10) {
                slider.parentElement.scrollTo({
                    left: 0,
                    behavior: 'smooth'
                });
                return;
            }

            slider.parentElement.scrollBy({
                left: amount,
                behavior: 'smooth'
            });
        };

        const scrollPrev = () => {
            const amount = getScrollAmount();

            if (isDesktopVertical()) {
                if (loop && slider.parentElement.scrollTop <= 10) {
                    slider.parentElement.scrollTo({
                        top: Math.max(0, slider.scrollHeight - slider.parentElement.clientHeight),
                        behavior: 'smooth'
                    });
                    return;
                }

                slider.parentElement.scrollBy({
                    top: -amount,
                    behavior: 'smooth'
                });
                return;
            }

            if (loop && slider.parentElement.scrollLeft <= 10) {
                slider.parentElement.scrollTo({
                    left: Math.max(0, slider.parentElement.scrollWidth - slider.parentElement.clientWidth),
                    behavior: 'smooth'
                });
                return;
            }

            slider.parentElement.scrollBy({
                left: -amount,
                behavior: 'smooth'
            });
        };

        if (prevBtn) {
            prevBtn.addEventListener('click', scrollPrev);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', scrollNext);
        }

        let autoSlide = null;

        if (autoInterval > 0) {
            autoSlide = setInterval(scrollNext, autoInterval);

            slider.parentElement.addEventListener('mouseenter', () => {
                if (autoSlide) clearInterval(autoSlide);
            });

            slider.parentElement.addEventListener('mouseleave', () => {
                autoSlide = setInterval(scrollNext, autoInterval);
            });
        }

        window.addEventListener('resize', () => {
            if (slider.parentElement) {
                slider.parentElement.scrollTo({
                    top: 0,
                    left: 0,
                    behavior: 'auto'
                });
            }
        });
    }

    getVariantButtons().forEach(btn => {
        if (!btn.classList.contains('variant-out')) {
            btn.addEventListener('click', () => {
                updateVariantUI(btn);
            });
        }
    });

    const firstAvailable = document.querySelector('.variant-btn:not(.variant-out)');
    const hasVariantButton = document.querySelector('.variant-btn');

    if (firstAvailable) {
        updateVariantUI(firstAvailable);
    } else if (hasVariantButton) {
        updateAddToCartState(0);
        updateStockText(0);
    } else {
        initDefaultThumb();
    }

    getThumbs().forEach(img => {
        img.addEventListener('click', function () {
            const imageSrc = this.dataset.image;
            const variantId = this.dataset.variant;

            if (imageSrc) {
                setMainImage(imageSrc);
            }

            if (variantId) {
                const variantBtn = document.querySelector(`.variant-btn[data-id="${variantId}"]`);
                if (variantBtn && !variantBtn.classList.contains('variant-out')) {
                    updateVariantUI(variantBtn);
                }
            }
        });

        img.addEventListener('dblclick', function () {
            if (this.dataset.image) {
                openImageLightbox(this.dataset.image);
            }
        });
    });

    initDefaultThumb();

    if (zoomMainImageBtn && mainImg) {
        zoomMainImageBtn.addEventListener('click', () => {
            openImageLightbox(mainImg.src);
        });
    }

    if (mainImg) {
        mainImg.addEventListener('click', () => {
            openImageLightbox(mainImg.src);
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);

            fetch(form.dataset.cartUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': form.dataset.csrf
                },
                body: formData
            })
                .then(async res => {
                    const data = await res.json().catch(() => null);
                    if (!res.ok || !data) throw new Error();
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        if (typeof showToast === 'function') {
                            showToast('Đã thêm vào giỏ', 'success');
                        }

                        const badge = document.querySelector('.cart-badge');
                        if (badge && data.cart_count !== undefined) {
                            badge.innerText = data.cart_count > 99 ? '99+' : data.cart_count;
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('Số lượng sản phẩm trong giỏ đã đạt tối đa tồn kho', 'error');
                        }
                    }
                })
                .catch(() => {
                    if (typeof showToast === 'function') {
                        showToast('Có lỗi hệ thống, vui lòng thử lại', 'error');
                    }
                });
        });
    }

    wishlistButtons.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            const productId = this.dataset.productId;
            if (!productId) return;

            try {
                const response = await fetch(typeof wishlistToggleUrl !== 'undefined' ? wishlistToggleUrl : '/wishlist/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                });

                const data = await response.json().catch(() => null);
                if (!response.ok || !data) throw new Error();

                const isFavorited = !!(data.is_favorited ?? data.favorited ?? data.isFavorited);
                const rawCount = data.favorites_count ?? data.count ?? data.total_favorites ?? 0;
                const displayCount = formatWishlistCount(rawCount);

                updateWishlistButtons(productId, isFavorited, displayCount);

                if (typeof showToast === 'function') {
                    showToast(
                        isFavorited ? 'Đã thêm vào yêu thích' : 'Đã bỏ khỏi yêu thích',
                        'success'
                    );
                }
            } catch (error) {
                if (typeof showToast === 'function') {
                    showToast('Có lỗi khi cập nhật yêu thích', 'error');
                }
            }
        });
    });

    if (minusBtn && qtyInput) {
        minusBtn.addEventListener('click', () => {
            let current = parseInt(qtyInput.value, 10) || 1;
            if (current > 1) {
                qtyInput.value = current - 1;
            }
        });
    }

    if (plusBtn && qtyInput) {
        plusBtn.addEventListener('click', () => {
            let current = parseInt(qtyInput.value, 10) || 1;
            let max = parseInt(qtyInput.max, 10) || 9999;

            if (current < max) {
                qtyInput.value = current + 1;
            }
        });
    }

    if (qtyInput) {
        qtyInput.addEventListener('input', () => {
            let value = parseInt(qtyInput.value, 10) || 1;
            let max = parseInt(qtyInput.max, 10) || 9999;

            if (value < 1) value = 1;
            if (value > max) value = max;

            qtyInput.value = value;
        });
    }

    document.querySelectorAll('.detail-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            switchToTab(btn.dataset.tab);
        });
    });

    if (reviewAnchor) {
        reviewAnchor.addEventListener('click', function (e) {
            e.preventDefault();
            switchToTab('tab-reviews');

            const reviewPanel = document.getElementById('tab-reviews');
            if (reviewPanel) {
                reviewPanel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }

    document.querySelectorAll('.review-media img').forEach(img => {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function () {
            openImageLightbox(this.src);
        });
    });

    document.querySelectorAll('.review-media video').forEach(video => {
        video.style.cursor = 'pointer';
        video.addEventListener('click', function () {
            const source = this.querySelector('source');
            if (source && source.src) {
                openVideoLightbox(source.src);
            }
        });
    });

    if (toggleReviewsBtn) {
        let reviewsExpanded = false;

        toggleReviewsBtn.addEventListener('click', () => {
            const hiddenReviews = document.querySelectorAll('.review-hidden');

            reviewsExpanded = !reviewsExpanded;

            hiddenReviews.forEach(item => {
                item.style.display = reviewsExpanded ? 'block' : 'none';
            });

            toggleReviewsBtn.innerText = reviewsExpanded
                ? 'Thu gọn đánh giá'
                : 'Xem thêm đánh giá';
        });
    }

    initHorizontalSlider({
        slider: recentSlider,
        prevBtn: recentPrev,
        nextBtn: recentNext,
        slideSelector: '.recent-slide',
        gapFallback: 18,
        autoInterval: 0,
        loop: false
    });

    initHorizontalSlider({
        slider: relatedSlider,
        prevBtn: relatedPrev,
        nextBtn: relatedNext,
        slideSelector: '.related-slide-full',
        gapFallback: 18,
        autoInterval: 3000,
        loop: true
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeLightbox);
    }

    if (lightbox) {
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });
});