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
            let progressComplete = false;
            let introDone = false;

            if (stage) {
                const stageRect = stage.getBoundingClientRect();
                introDone = stageRect.bottom <= viewportHeight * 0.12;
            }

            if (scroller && runway && stepCount >= 2) {
                const runwayRect = runway.getBoundingClientRect();
                const scrollRange = Math.max(runway.offsetHeight - viewportHeight, 1);
                const inRunway = runwayRect.top <= 0 && runwayRect.bottom > viewportHeight;

                if (inRunway) {
                    ratio = Math.min(Math.max(-runwayRect.top / scrollRange, 0), 1);
                } else if (runwayRect.bottom <= viewportHeight) {
                    ratio = 1;
                }

                const activeIndex = Math.min(stepCount - 1, Math.floor(ratio * stepCount));

                items.forEach((item, itemIndex) => {
                    item.classList.toggle('is-active', itemIndex === activeIndex);
                });

                if (progressBar) {
                    progressBar.style.transform = `scaleX(${Math.min(ratio, 1)})`;
                }

                if (indexCurrent) {
                    indexCurrent.textContent = String(activeIndex + 1).padStart(2, '0');
                }

                progressComplete = ratio >= (stepCount - 1) / stepCount;
                inSteps = inRunway;
            }

            const sequenceReleased = scroller && runway && stepCount >= 2
                ? !inSteps && ratio >= 0.98
                : false;

            if (expanded !== wasExpanded) {
                hero.style.setProperty('--psa-hero-frame-duration', expanded ? '1s' : '0.55s');
                wasExpanded = expanded;
            }

            hero.classList.toggle('psa-hero--expanded', expanded || inSteps);
            hero.classList.toggle('psa-hero--intro-done', introDone);
            hero.classList.toggle('psa-hero--in-steps', inSteps);
            hero.classList.toggle('psa-hero--progress-complete', progressComplete);
            hero.classList.toggle('psa-hero--released', sequenceReleased);
            hero.classList.toggle('psa-hero--fixed-video', inSteps && !passed);
            hero.classList.toggle('psa-hero--passed', passed);
            document.body.classList.toggle('psa-hero-sequence-done', passed);

            videos.forEach((video) => {
                if (passed) {
                    video.pause();
                    return;
                }

                if ((inSteps || expanded) && video.paused) {
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
