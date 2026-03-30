document.addEventListener("DOMContentLoaded", function () {
    function createSwiper(selector, next, prev, config = {}) {
        const element = document.querySelector(selector);

        if (!element || typeof Swiper === "undefined") return null;

        return new Swiper(selector, {
            speed: 700,
            loop: false,
            rewind: false,
            watchOverflow: false,
            grabCursor: true,
            allowTouchMove: true,
            slidesPerGroup: 1,
            centeredSlides: false,
            observer: true,
            observeParents: true,
            navigation: {
                nextEl: next,
                prevEl: prev,
            },
            ...config,
        });
    }

    const productSliderConfig = {
        slidesPerView: 2,
        spaceBetween: 14,
        loop: false,
        rewind: false,
        breakpoints: {
            0: { slidesPerView: 2, spaceBetween: 12 },
            576: { slidesPerView: 2, spaceBetween: 14 },
            768: { slidesPerView: 3, spaceBetween: 16 },
            992: { slidesPerView: 4, spaceBetween: 16 },
            1200: { slidesPerView: 5, spaceBetween: 18 },
        },
    };

    createSwiper(
        ".flash-sale-swiper",
        ".flash-sale-next",
        ".flash-sale-prev",
        productSliderConfig
    );

    createSwiper(
        ".featured-slider",
        ".featured-next",
        ".featured-prev",
        productSliderConfig
    );

    createSwiper(
        ".latest-slider",
        ".latest-next",
        ".latest-prev",
        productSliderConfig
    );

    const brandCategoryConfig = {
        slidesPerView: 2,
        spaceBetween: 12,
        slidesPerGroup: 1,
        speed: 600,
        loop: true,
        rewind: false,
        watchOverflow: false,
        loopAdditionalSlides: 10,
        breakpoints: {
            0: { slidesPerView: 2, spaceBetween: 12 },
            576: { slidesPerView: 3, spaceBetween: 12 },
            768: { slidesPerView: 4, spaceBetween: 14 },
            992: { slidesPerView: 5, spaceBetween: 14 },
            1200: { slidesPerView: 6, spaceBetween: 16 },
        },
    };

    createSwiper(
        ".brand-slider",
        ".brand-next",
        ".brand-prev",
        brandCategoryConfig
    );

    createSwiper(
        ".category-slider",
        ".category-next",
        ".category-prev",
        brandCategoryConfig
    );

    const flashSaleSection = document.querySelector(".flash-sale-section");

    if (flashSaleSection) {
        const endTime = flashSaleSection.dataset.countdownEnd;
        const daysEl = document.getElementById("flash-days");
        const hoursEl = document.getElementById("flash-hours");
        const minutesEl = document.getElementById("flash-minutes");
        const secondsEl = document.getElementById("flash-seconds");

        if (endTime && daysEl && hoursEl && minutesEl && secondsEl) {
            const target = new Date(endTime.replace(" ", "T")).getTime();
            const pad = (num) => String(num).padStart(2, "0");

            function updateCountdown() {
                const distance = target - Date.now();

                if (distance <= 0) {
                    daysEl.textContent = "00 NGÀY";
                    hoursEl.textContent = "00 GIỜ";
                    minutesEl.textContent = "00 PHÚT";
                    secondsEl.textContent = "00 GIÂY";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
                const minutes = Math.floor((distance / (1000 * 60)) % 60);
                const seconds = Math.floor((distance / 1000) % 60);

                daysEl.textContent = `${pad(days)} NGÀY`;
                hoursEl.textContent = `${pad(hours)} GIỜ`;
                minutesEl.textContent = `${pad(minutes)} PHÚT`;
                secondsEl.textContent = `${pad(seconds)} GIÂY`;
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        }
    }

    function showFlashToast(message) {
        const oldToast = document.querySelector(".flash-inline-toast");
        if (oldToast) oldToast.remove();

        const toast = document.createElement("div");
        toast.className = "flash-inline-toast";
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 2200);
    }

    document.addEventListener("click", function (e) {
        const actionBtn = e.target.closest(".btn-add-to-cart, .btn-buy-now");

        if (actionBtn && actionBtn.dataset.outStock === "1") {
            e.preventDefault();
            e.stopPropagation();
            showFlashToast("Sản phẩm đã hết hàng!");
            return;
        }

        const card = e.target.closest(".js-card");

        if (card && !e.target.closest("button, a, .wishlist-btn, .btn-favorite")) {
            const href = card.dataset.href;
            if (href) {
                window.location.href = href;
            }
        }
    });
});