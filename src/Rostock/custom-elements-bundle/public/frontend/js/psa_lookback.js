(function () {
    function initLookback(root) {
        const slider = root.querySelector('[data-psa-lookback-slider]');

        if (!slider) {
            return;
        }

        const slides = Array.from(slider.querySelectorAll('[data-psa-lookback-slide]'));
        const monthButtons = Array.from(root.querySelectorAll('[data-psa-lookback-month]'));
        let isDragging = false;
        let dragStartX = 0;
        let scrollStart = 0;
        let dragDistance = 0;
        let suppressClick = false;
        let activeMonth = '';

        function setActiveMonth(monthKey) {
            activeMonth = monthKey;

            monthButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.psaLookbackMonth === monthKey);
            });

            slides.forEach((slide) => {
                slide.classList.toggle('is-active', slide.dataset.month === monthKey);
            });
        }

        function scrollToSlide(slide) {
            if (!slide) {
                return;
            }

            const offset = slide.offsetLeft - slider.offsetLeft;

            slider.scrollTo({
                left: offset,
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            });

            setActiveMonth(slide.dataset.month || '');
        }

        function updateActiveFromScroll() {
            if (!slides.length) {
                return;
            }

            const center = slider.scrollLeft + slider.clientWidth * 0.35;
            let closest = slides[0];
            let closestDistance = Number.POSITIVE_INFINITY;

            slides.forEach((slide) => {
                const slideCenter = slide.offsetLeft + slide.clientWidth * 0.5;
                const distance = Math.abs(slideCenter - center);

                if (distance < closestDistance) {
                    closestDistance = distance;
                    closest = slide;
                }
            });

            if ((closest.dataset.month || '') !== activeMonth) {
                setActiveMonth(closest.dataset.month || '');
            }
        }

        monthButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number(button.dataset.psaLookbackIndex || '0');
                const target = slides.find((slide) => Number(slide.dataset.index) === index);

                scrollToSlide(target || slides[0]);
            });
        });

        slider.addEventListener('scroll', () => {
            if (!isDragging) {
                window.requestAnimationFrame(updateActiveFromScroll);
            }
        }, { passive: true });

        slider.addEventListener('mousedown', (event) => {
            isDragging = true;
            dragStartX = event.pageX;
            scrollStart = slider.scrollLeft;
            dragDistance = 0;
            suppressClick = false;
            slider.classList.add('is-dragging');
        });

        window.addEventListener('mousemove', (event) => {
            if (!isDragging) {
                return;
            }

            const delta = event.pageX - dragStartX;
            dragDistance = Math.max(dragDistance, Math.abs(delta));

            slider.scrollLeft = scrollStart - delta;
        });

        window.addEventListener('mouseup', () => {
            if (!isDragging) {
                return;
            }

            isDragging = false;
            suppressClick = dragDistance > 6;
            slider.classList.remove('is-dragging');
            updateActiveFromScroll();
        });

        slider.addEventListener('click', (event) => {
            if (!suppressClick) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            suppressClick = false;
        }, true);

        slider.addEventListener('wheel', (event) => {
            if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) {
                return;
            }

            event.preventDefault();
            slider.scrollLeft += event.deltaY;
        }, { passive: false });

        if (slides[0]) {
            setActiveMonth(slides[0].dataset.month || '');
        }
    }

    function boot() {
        document.querySelectorAll('[data-psa-lookback]').forEach(initLookback);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
