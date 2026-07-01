(function () {
    function fallbackCopy(text, onSuccess) {
        var input = document.createElement('textarea');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'absolute';
        input.style.left = '-9999px';
        document.body.appendChild(input);
        input.select();

        try {
            if (document.execCommand('copy')) {
                onSuccess();
            }
        } catch (error) {
            // Ignore clipboard errors.
        }

        document.body.removeChild(input);
    }

    function resolveUrl(raw) {
        var url = (raw || '').trim();

        if (url === '') {
            return window.location.href;
        }

        if (/^https?:\/\//i.test(url)) {
            return url;
        }

        return window.location.origin + (url.startsWith('/') ? '' : '/') + url;
    }

    function copyText(text, button) {
        var copiedLabel = button.getAttribute('data-copied-label') || 'Copied!';
        var defaultLabel = button.getAttribute('data-default-label') || button.textContent;

        function showCopied() {
            button.textContent = copiedLabel;
            button.classList.add('is-copied');
            window.setTimeout(function () {
                button.textContent = defaultLabel;
                button.classList.remove('is-copied');
            }, 2000);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(showCopied).catch(function () {
                fallbackCopy(text, showCopied);
            });

            return;
        }

        fallbackCopy(text, showCopied);
    }

    function scrollToHashTarget() {
        var hash = window.location.hash;

        if (!hash || !/^#meetup-\d+$/.test(hash)) {
            return;
        }

        var target = document.querySelector(hash);

        if (target) {
            window.setTimeout(function () {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }

    function initCopyButtons() {
        document.querySelectorAll('[data-psa-copy-link]').forEach(function (button) {
            if (button.dataset.psaCopyBound === '1') {
                return;
            }

            button.dataset.psaCopyBound = '1';
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                copyText(resolveUrl(button.getAttribute('data-psa-copy-url') || ''), button);
            });
        });
    }

    function init() {
        initCopyButtons();
        scrollToHashTarget();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
