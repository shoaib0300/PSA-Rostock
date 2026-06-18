document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-psa-hero]').forEach((hero) => {
        let wasExpanded = false;

        const update = () => {
            const heroRect = hero.getBoundingClientRect();
            const expanded = window.scrollY > 0;
            const inHero = heroRect.bottom > 0;
            const passed = heroRect.bottom <= 0;

            if (expanded !== wasExpanded) {
                hero.style.setProperty('--psa-hero-frame-duration', expanded ? '1s' : '0.55s');
                wasExpanded = expanded;
            }

            hero.classList.toggle('psa-hero--expanded', expanded);
            hero.classList.toggle('psa-hero--fixed-video', expanded && inHero && !passed);
            hero.classList.toggle('psa-hero--passed', passed);
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    });

    document.querySelectorAll('.JS-psa-hero-slider').forEach((slider) => {
        const slides = slider.querySelectorAll('.psa-hero__media-slide');

        if (slides.length < 2) {
            return;
        }

        let index = 0;
        const interval = Number(slider.dataset.interval || 8000);

        window.setInterval(() => {
            slides[index].classList.remove('is-active');
            index = (index + 1) % slides.length;
            slides[index].classList.add('is-active');
        }, interval);
    });

    document.querySelectorAll('[data-psa-hero-scroller]').forEach((scroller) => {
        const items = scroller.querySelectorAll('[data-psa-hero-scroll-item]');
        const progressBar = scroller.querySelector('[data-psa-hero-progress]');
        const indexCurrent = scroller.querySelector('[data-psa-hero-index-current]');

        if (items.length < 2) {
            return;
        }

        const update = () => {
            const rect = scroller.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const start = viewportHeight * 0.15;
            const end = rect.height - viewportHeight * 0.35;
            const scrolled = Math.min(Math.max(start - rect.top, 0), Math.max(end, 1));
            const ratio = scrolled / Math.max(end, 1);
            const activeIndex = Math.min(items.length - 1, Math.floor(ratio * items.length));

            items.forEach((item, itemIndex) => {
                item.classList.toggle('is-active', itemIndex === activeIndex);
            });

            if (progressBar) {
                progressBar.style.transform = `scaleX(${ratio})`;
            }

            if (indexCurrent) {
                indexCurrent.textContent = String(activeIndex + 1).padStart(2, '0');
            }
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    });
});
