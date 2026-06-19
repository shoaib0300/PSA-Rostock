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

    document.querySelectorAll('[data-psa-vote]').forEach(initVoteForms);
})();
