(function () {
    function initTickerClicks() {
        document.querySelectorAll('[data-psa-vote-ticker]').forEach(function (ticker) {
            if (ticker.dataset.psaVoteTickerBound === '1') {
                return;
            }

            ticker.dataset.psaVoteTickerBound = '1';

            ticker.addEventListener('click', function (event) {
                var item = event.target.closest('[data-psa-vote-ticker-item]');
                if (!item || !ticker.contains(item)) {
                    return;
                }

                var campaignId = item.getAttribute('data-psa-vote-ticker-item');
                if (!campaignId) {
                    return;
                }

                var voteRoot = document.querySelector('[data-psa-vote]');
                if (voteRoot) {
                    var tab = voteRoot.querySelector('[data-psa-vote-tab="' + campaignId + '"]');
                    if (tab) {
                        event.preventDefault();
                        tab.click();
                        return;
                    }
                }

                var votePageUrl = ticker.getAttribute('data-vote-page-url') || '/vote';
                window.location.href = votePageUrl + '#campaign-' + campaignId;
            });
        });
    }

    function boot() {
        initTickerClicks();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
