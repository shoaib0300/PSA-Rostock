(function () {
    function initPastEventsToggle() {
        const toggle = document.getElementById('psa-events-past-toggle');
        const panel = document.getElementById('psa-events-past');

        if (!toggle || !panel) {
            return;
        }

        const label = toggle.querySelector('.psa-hero__btn-label-text');
        const showLabel = toggle.getAttribute('data-label-show') || 'See all past events';
        const hideLabel = toggle.getAttribute('data-label-hide') || 'Hide past events';

        function setOpen(open) {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

            if (label) {
                label.textContent = open ? hideLabel : showLabel;
            }

            if (open) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        toggle.addEventListener('click', function () {
            setOpen(panel.hidden);
        });

        if (new URLSearchParams(window.location.search).get('past') === '1') {
            setOpen(true);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPastEventsToggle);
    } else {
        initPastEventsToggle();
    }
})();
