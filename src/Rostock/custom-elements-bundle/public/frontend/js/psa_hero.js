document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-psa-hero]').forEach((hero) => {
        const videos = hero.querySelectorAll('video');
        let wasExpanded = false;

        const update = () => {
            const scrollY = window.scrollY;
            const heroRect = hero.getBoundingClientRect();
            const expanded = scrollY > 2;
            const passed = heroRect.bottom <= 0;

            if (expanded !== wasExpanded) {
                hero.style.setProperty('--psa-hero-frame-duration', expanded ? '1s' : '0.55s');
                wasExpanded = expanded;
            }

            hero.classList.toggle('psa-hero--expanded', expanded);
            hero.classList.toggle('psa-hero--passed', passed);
            document.body.classList.toggle('psa-hero-sequence-done', passed);

            videos.forEach((video) => {
                if (passed) {
                    video.pause();
                    return;
                }

                if (expanded && video.paused) {
                    video.play().catch(() => {});
                }
            });
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
});
