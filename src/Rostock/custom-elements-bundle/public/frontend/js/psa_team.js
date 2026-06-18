(function () {
    function initTeamCards() {
        const root = document.querySelector('[data-psa-team]');

        if (!root) {
            return;
        }

        const cards = root.querySelectorAll('[data-psa-team-card]');

        if (!cards.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const angle = 14;

        const lerp = (start, end, amount) => (1 - amount) * start + amount * end;

        const remap = (value, oldMax, newMax) => {
            const newValue = ((value + oldMax) * (newMax * 2)) / (oldMax * 2) - newMax;

            return Math.min(Math.max(newValue, -newMax), newMax);
        };

        cards.forEach((card) => {
            card.addEventListener('mousemove', (event) => {
                const rect = card.getBoundingClientRect();
                const centerX = (rect.left + rect.right) / 2;
                const centerY = (rect.top + rect.bottom) / 2;
                const posX = event.clientX - centerX;
                const posY = event.clientY - centerY;

                card.dataset.rotateX = String(remap(posX, rect.width / 2, angle));
                card.dataset.rotateY = String(-remap(posY, rect.height / 2, angle));
            });

            card.addEventListener('mouseleave', () => {
                card.dataset.rotateX = '0';
                card.dataset.rotateY = '0';
            });
        });

        const update = () => {
            cards.forEach((card) => {
                let currentX = parseFloat(card.style.getPropertyValue('--rotateY'));
                let currentY = parseFloat(card.style.getPropertyValue('--rotateX'));

                if (Number.isNaN(currentX)) {
                    currentX = 0;
                }

                if (Number.isNaN(currentY)) {
                    currentY = 0;
                }

                const targetX = parseFloat(card.dataset.rotateX || '0');
                const targetY = parseFloat(card.dataset.rotateY || '0');
                const x = lerp(currentX, targetX, 0.08);
                const y = lerp(currentY, targetY, 0.08);

                card.style.setProperty('--rotateY', x + 'deg');
                card.style.setProperty('--rotateX', y + 'deg');
            });

            window.requestAnimationFrame(update);
        };

        window.requestAnimationFrame(update);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTeamCards);
    } else {
        initTeamCards();
    }
})();
