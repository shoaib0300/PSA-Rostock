document.addEventListener('DOMContentLoaded', () => {
    const headers = document.querySelectorAll('.psa-header');

    headers.forEach((header) => {
        const toggle = header.querySelector('[data-psa-menu-toggle]');
        const popup = header.querySelector('.psa-header__popup');
        const bar = header.querySelector('.psa-header__bar');

        const setOpen = (isOpen) => {
            header.dataset.navigationStatus = isOpen ? 'active' : 'not-active';
            document.body.classList.toggle('psa-header-open', isOpen);

            if (toggle) {
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }

            if (popup) {
                popup.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            }
        };

        const closeMenu = () => setOpen(false);
        const openMenu = () => setOpen(true);

        toggle?.addEventListener('click', () => {
            if (header.dataset.navigationStatus === 'active') {
                closeMenu();
            } else {
                openMenu();
            }
        });

        popup?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && header.dataset.navigationStatus === 'active') {
                closeMenu();
            }
        });

        const path = window.location.pathname.replace(/\/$/, '') || '/';
        header.querySelectorAll('.psa-header__link, .psa-header__popup-link').forEach((link) => {
            const href = link.getAttribute('href');

            if (!href || href.startsWith('http') || href.startsWith('#')) {
                return;
            }

            const linkPath = href.replace(/\/$/, '') || '/';

            if (linkPath === path) {
                link.classList.add('is-active');
                link.setAttribute('aria-current', 'page');
            }
        });

        if (bar) {
            bar.style.pointerEvents = 'auto';
        }
    });
});
