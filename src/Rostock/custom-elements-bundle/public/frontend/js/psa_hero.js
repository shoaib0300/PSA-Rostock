document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-psa-hero]').forEach((hero) => {
        const stage = hero.querySelector('.psa-hero__stage');
        const scroller = hero.querySelector('[data-psa-hero-scroller]');
        const runway = scroller?.querySelector('.psa-hero__scroller-runway');
        const below = hero.querySelector('.psa-hero__below');
        const videos = hero.querySelectorAll('video');
        const items = scroller ? scroller.querySelectorAll('[data-psa-hero-scroll-item]') : [];
        const progressBar = scroller?.querySelector('[data-psa-hero-progress]');
        const indexCurrent = scroller?.querySelector('[data-psa-hero-index-current]');
        const stepCount = items.length;
        let wasExpanded = false;

        const update = () => {
            const scrollY = window.scrollY;
            const viewportHeight = window.innerHeight;
            const heroRect = hero.getBoundingClientRect();
            const expanded = scrollY > 2;
            const passed = heroRect.bottom <= 0;
            let ratio = 0;
            let inSteps = false;
            let introDone = false;

            if (stage) {
                const stageRect = stage.getBoundingClientRect();
                introDone = stageRect.bottom <= viewportHeight * 0.12;
            }

            if (scroller && runway && stepCount >= 2) {
                const runwayRect = runway.getBoundingClientRect();
                const runwayTop = scrollY + runwayRect.top;
                const runwayHeight = runway.offsetHeight;
                const scrollRange = Math.max(runwayHeight - viewportHeight, 1);
                const scrolled = Math.min(Math.max(scrollY - runwayTop, 0), scrollRange);
                ratio = scrolled / scrollRange;

                const activeIndex = Math.min(stepCount - 1, Math.floor(ratio * stepCount));

                items.forEach((item, itemIndex) => {
                    item.classList.toggle('is-active', itemIndex === activeIndex);
                });

                if (progressBar) {
                    const progress = Math.min(ratio * stepCount / Math.max(stepCount, 1), 1);
                    progressBar.style.transform = `scaleX(${progress})`;
                }

                if (indexCurrent) {
                    indexCurrent.textContent = String(activeIndex + 1).padStart(2, '0');
                }

                inSteps = runwayRect.top <= 1
                    && runwayRect.bottom > viewportHeight * 0.25
                    && ratio < 0.995;
            }

            const stepsComplete = scroller && runway && stepCount >= 2
                ? ratio >= 0.995
                : passed
                    || (below !== null && below.getBoundingClientRect().bottom <= viewportHeight * 0.15)
                    || (stage !== null && stage.getBoundingClientRect().bottom <= 0);

            const sequenceReleased = stepsComplete && !inSteps;

            if (expanded !== wasExpanded) {
                hero.style.setProperty('--psa-hero-frame-duration', expanded ? '1s' : '0.55s');
                wasExpanded = expanded;
            }

            hero.classList.toggle('psa-hero--expanded', expanded);
            hero.classList.toggle('psa-hero--intro-done', introDone);
            hero.classList.toggle('psa-hero--in-steps', inSteps);
            hero.classList.toggle('psa-hero--video-done', stepsComplete);
            hero.classList.toggle('psa-hero--fixed-video', expanded && !stepsComplete && !passed);
            hero.classList.toggle('psa-hero--passed', passed);
            document.body.classList.toggle(
                'psa-hero-sequence-done',
                passed || (sequenceReleased && heroRect.bottom <= viewportHeight * 0.4)
            );

            videos.forEach((video) => {
                if ((stepsComplete && !inSteps) || passed) {
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
