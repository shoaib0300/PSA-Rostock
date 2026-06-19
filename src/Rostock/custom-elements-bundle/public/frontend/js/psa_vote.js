(function () {
    function showError(form, message) {
        var box = form.querySelector('[data-psa-vote-error]');
        if (!box) {
            window.alert(message);
            return;
        }

        box.textContent = message;
        box.hidden = false;
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearError(form) {
        var box = form.querySelector('[data-psa-vote-error]');
        if (box) {
            box.textContent = '';
            box.hidden = true;
        }
    }

    function syncSelection(card, selected) {
        card.classList.toggle('is-selected', selected);
        var input = card.querySelector('.psa-vote-candidate__input');
        if (input) {
            input.checked = selected;
        }
    }

    function validateForm(form) {
        var missing = [];

        form.querySelectorAll('[data-psa-vote-position]').forEach(function (position) {
            var checked = position.querySelector('.psa-vote-candidate__input:checked');
            if (!checked) {
                missing.push(position.getAttribute('data-position-title') || 'Position');
            }
        });

        if (missing.length === 0) {
            return true;
        }

        var template = form.getAttribute('data-error-incomplete')
            || 'Please select a candidate for: %s';
        showError(form, template.replace('%s', missing.join(', ')));
        return false;
    }

    function campaignIdFromHash() {
        var match = window.location.hash.match(/^#campaign-(\d+)$/);
        return match ? match[1] : null;
    }

    function activateCampaign(root, campaignId, updateHash) {
        var id = String(campaignId);
        var panel = root.querySelector('[data-psa-vote-campaign="' + id + '"]');

        if (!panel) {
            return;
        }

        root.querySelectorAll('[data-psa-vote-tab]').forEach(function (tab) {
            var isActive = tab.getAttribute('data-psa-vote-tab') === id;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        root.querySelectorAll('[data-psa-vote-campaign]').forEach(function (item) {
            var isVisible = item.getAttribute('data-psa-vote-campaign') === id;
            item.classList.toggle('is-visible', isVisible);
            item.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
        });

        if (updateHash !== false) {
            var nextHash = '#campaign-' + id;
            if (window.location.hash !== nextHash) {
                history.replaceState(null, '', nextHash);
            }
        }

        if (window.matchMedia('(max-width: 899px)').matches) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        var main = root.querySelector('.psa-vote__main');
        if (main) {
            main.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function formatDuration(totalSeconds) {
        var seconds = Math.max(0, totalSeconds);
        var days = Math.floor(seconds / 86400);
        var hours = Math.floor((seconds % 86400) / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var parts = [];

        if (days > 0) {
            parts.push(days + 'd');
        }

        if (hours > 0 || days > 0) {
            parts.push(hours + 'h');
        }

        parts.push(minutes + 'm');

        return parts.join(' ');
    }

    function updateCountdowns(root) {
        var now = Math.floor(Date.now() / 1000);
        var leftTemplate = root.getAttribute('data-countdown-left') || '%s left';
        var startsTemplate = root.getAttribute('data-countdown-starts-in') || 'Opens in %s';
        var endedLabel = root.getAttribute('data-countdown-ended') || 'Voting closed';

        root.querySelectorAll('[data-psa-vote-countdown]').forEach(function (node) {
            var target = parseInt(node.getAttribute('data-countdown-target') || '0', 10);
            var mode = node.getAttribute('data-countdown-mode') || 'end';
            var output = node.querySelector('[data-psa-vote-countdown-remaining]');

            if (!output || !target) {
                return;
            }

            var remaining = target - now;

            if (remaining <= 0) {
                output.textContent = mode === 'start' ? '' : ' · ' + endedLabel;
                return;
            }

            var duration = formatDuration(remaining);
            var template = mode === 'start' ? startsTemplate : leftTemplate;
            output.textContent = ' · ' + template.replace('%s', duration);
        });
    }

    function initCountdowns(root) {
        updateCountdowns(root);

        if (root.dataset.psaVoteCountdownBound === '1') {
            return;
        }

        root.dataset.psaVoteCountdownBound = '1';

        window.setInterval(function () {
            updateCountdowns(root);
        }, 30000);
    }

    function initCampaignTabs(root) {
        if (root.dataset.psaVoteTabsBound === '1') {
            return;
        }

        root.dataset.psaVoteTabsBound = '1';

        root.addEventListener('click', function (event) {
            var tab = event.target.closest('[data-psa-vote-tab]');
            if (!tab || !root.contains(tab)) {
                return;
            }

            event.preventDefault();
            activateCampaign(root, tab.getAttribute('data-psa-vote-tab'));
        });

        var hashId = campaignIdFromHash();
        if (hashId && root.querySelector('[data-psa-vote-campaign="' + hashId + '"]')) {
            activateCampaign(root, hashId, false);
        }
    }

    function initVoteForms(root) {
        root.querySelectorAll('[data-psa-vote-form]').forEach(function (form) {
            if (form.dataset.psaVoteBound === '1') {
                return;
            }

            form.dataset.psaVoteBound = '1';

            form.querySelectorAll('[data-psa-vote-position]').forEach(function (position) {
                position.querySelectorAll('.psa-vote-candidate').forEach(function (card) {
                    var input = card.querySelector('.psa-vote-candidate__input');
                    if (!input) {
                        return;
                    }

                    if (input.checked) {
                        card.classList.add('is-selected');
                    }

                    card.addEventListener('click', function (event) {
                        if (event.target.closest('a, button')) {
                            return;
                        }

                        position.querySelectorAll('.psa-vote-candidate').forEach(function (other) {
                            syncSelection(other, other === card);
                        });

                        clearError(form);
                    });

                    input.addEventListener('change', function () {
                        position.querySelectorAll('.psa-vote-candidate').forEach(function (other) {
                            syncSelection(other, other === card);
                        });
                        clearError(form);
                    });
                });
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                clearError(form);

                if (!validateForm(form)) {
                    return;
                }

                var submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            return { ok: response.ok, payload: payload };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok || !result.payload.ok) {
                            throw new Error((result.payload && result.payload.error) || 'Vote failed.');
                        }

                        window.location.reload();
                    })
                    .catch(function (error) {
                        showError(form, error.message || 'Vote failed.');
                    })
                    .finally(function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                        }
                    });
            });
        });
    }

    function initVoteModule(root) {
        initCampaignTabs(root);
        initCountdowns(root);
        initVoteForms(root);
    }

    function boot() {
        document.querySelectorAll('[data-psa-vote]').forEach(initVoteModule);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    window.addEventListener('hashchange', function () {
        var hashId = campaignIdFromHash();
        if (!hashId) {
            return;
        }

        document.querySelectorAll('[data-psa-vote]').forEach(function (root) {
            if (root.querySelector('[data-psa-vote-campaign="' + hashId + '"]')) {
                activateCampaign(root, hashId, false);
            }
        });
    });
})();
