(function () {
    var AJAX_ACTIONS = {
        psa_meetup_join: true,
        psa_meetup_poll_vote: true,
        psa_meetup_comment: true,
        psa_meetup_comment_reaction: true,
        psa_delete_meetup_comment: true,
    };

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function nl2br(value) {
        return escapeHtml(value).replace(/\r?\n/g, '<br>');
    }

    function parseJsonDataset(node, key, fallback) {
        if (!node || !node.dataset[key]) {
            return fallback;
        }

        try {
            return JSON.parse(node.dataset[key]);
        } catch (error) {
            return fallback;
        }
    }

    function getCard(node) {
        return node.closest('.psa-meetup-card');
    }

    function renderAvatar(url, author, className, size) {
        if (url) {
            return '<img class="' + escapeHtml(className) + '" src="' + escapeHtml(url) + '" alt="' + escapeHtml(author) + '" width="' + size + '" height="' + size + '" loading="lazy" decoding="async">';
        }

        return '<svg class="' + escapeHtml(className) + '" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/></svg>';
    }

    function buildReactionMap(reactions) {
        var map = {};

        (reactions || []).forEach(function (item) {
            if (item && item.emoji) {
                map[item.emoji] = item;
            }
        });

        return map;
    }

    function renderReactionsHtml(commentId, reactions, config) {
        var map = buildReactionMap(reactions);
        var html = '';

        (config.reactionEmojis || []).forEach(function (emoji) {
            var reaction = map[emoji] || { count: 0, memberReacted: false };
            var count = Number(reaction.count || 0);
            var classes = 'psa-meetup-card__reaction-btn';
            var countHtml = count > 0 ? '<span class="psa-meetup-card__reaction-count">' + count + '</span>' : '';

            if (reaction.memberReacted) {
                classes += ' is-active';
            }

            if (count === 0) {
                classes += ' is-empty';
            }

            html += '<form class="psa-meetup-card__reaction-form" method="post" data-psa-meetup-ajax">' +
                '<input type="hidden" name="FORM_SUBMIT" value="psa_meetup_comment_reaction">' +
                '<input type="hidden" name="REQUEST_TOKEN" value="' + escapeHtml(config.requestToken) + '">' +
                '<input type="hidden" name="comment_id" value="' + Number(commentId) + '">' +
                '<input type="hidden" name="emoji" value="' + escapeHtml(emoji) + '">' +
                '<button type="submit" class="' + classes + '" aria-label="' + escapeHtml(emoji) + '" title="' + escapeHtml(emoji) + '">' +
                '<span class="psa-meetup-card__reaction-emoji" aria-hidden="true">' + emoji + '</span>' +
                countHtml +
                '</button></form>';
        });

        return html;
    }

    function renderCommentHtml(comment, config) {
        var deleteForm = '';

        if (Number(comment.member_id) === Number(config.memberId)) {
            deleteForm = '<form class="psa-meetup-card__comment-delete" method="post" data-psa-meetup-ajax data-confirm="' + escapeHtml(config.lang.commentDeleteConfirm) + '">' +
                '<input type="hidden" name="FORM_SUBMIT" value="psa_delete_meetup_comment">' +
                '<input type="hidden" name="REQUEST_TOKEN" value="' + escapeHtml(config.requestToken) + '">' +
                '<input type="hidden" name="comment_id" value="' + Number(comment.id) + '">' +
                '<button type="submit" class="psa-meetup-card__comment-delete-btn">' + escapeHtml(config.lang.commentDelete) + '</button>' +
                '</form>';
        }

        return '<li class="psa-meetup-card__comment" data-comment-id="' + Number(comment.id) + '">' +
            '<div class="psa-meetup-card__comment-head">' +
            renderAvatar(comment.authorAvatarUrl, comment.author, 'psa-meetup-card__comment-avatar', 28) +
            '<div class="psa-meetup-card__comment-meta">' +
            '<strong class="psa-meetup-card__comment-author">' + escapeHtml(comment.author) + '</strong>' +
            '<time class="psa-meetup-card__comment-date" datetime="' + escapeHtml(new Date(comment.tstamp * 1000).toISOString()) + '">' + escapeHtml(comment.datim) + '</time>' +
            '</div></div>' +
            '<p class="psa-meetup-card__comment-text">' + nl2br(comment.comment) + '</p>' +
            '<div class="psa-meetup-card__reactions" data-psa-reactions aria-label="' + escapeHtml(config.lang.reactionsLabel) + '">' +
            renderReactionsHtml(comment.id, comment.reactions, config) +
            '</div>' +
            deleteForm +
            '</li>';
    }

    function updateRsvp(card, rsvp, lang) {
        if (!card || !rsvp) {
            return;
        }

        var joinStatus = rsvp.joinStatus || '';
        var upBtn = card.querySelector('.psa-meetup-card__rsvp-btn--up');
        var downBtn = card.querySelector('.psa-meetup-card__rsvp-btn--down');
        var joinCount = card.querySelector('[data-psa-join-count]');
        var declineCount = card.querySelector('[data-psa-decline-count]');
        var joiners = card.querySelector('[data-psa-joiners]');
        var joinersList = card.querySelector('[data-psa-joiners-list]');

        if (upBtn) {
            upBtn.classList.toggle('is-active', joinStatus === 'join');
        }

        if (downBtn) {
            downBtn.classList.toggle('is-active', joinStatus === 'decline');
        }

        if (joinCount) {
            joinCount.textContent = Number(rsvp.joinCount || 0) + ' ' + (lang.joinCountShort || 'coming');
        }

        if (declineCount) {
            declineCount.textContent = Number(rsvp.declineCount || 0) + ' ' + (lang.declineCountShort || 'not coming');
        }

        if (joiners && joinersList) {
            var names = Array.isArray(rsvp.joiners) ? rsvp.joiners : [];

            joinersList.textContent = names.join(', ');
            joiners.hidden = names.length === 0;
        }
    }

    function updatePoll(card, pollData, lang) {
        if (!card || !pollData || !pollData.poll) {
            return;
        }

        var poll = pollData.poll;
        var pollVote = pollData.pollVote;
        var totalNode = card.querySelector('[data-psa-poll-total]');

        if (totalNode) {
            var totalLabel = lang.pollVotes || '%d votes';
            totalNode.textContent = totalLabel.replace('%d', String(poll.totalVotes || 0));
        }

        (poll.options || []).forEach(function (option) {
            var item = card.querySelector('[data-poll-option-id="' + option.id + '"]');

            if (!item) {
                return;
            }

            item.classList.toggle('is-voted', Number(pollVote) === Number(option.id));

            var meta = item.querySelector('.psa-meetup-card__poll-meta');

            if (meta) {
                meta.textContent = Number(option.votes || 0) + ' · ' + Number(option.percent || 0) + '%';
            }

            var fill = item.querySelector('.psa-meetup-card__poll-fill');

            if (fill) {
                fill.style.width = Number(option.percent || 0) + '%';
            }
        });
    }

    function updateCommentReactions(commentNode, reactions, config) {
        var container = commentNode.querySelector('[data-psa-reactions]');

        if (!container) {
            return;
        }

        container.innerHTML = renderReactionsHtml(commentNode.dataset.commentId, reactions, config);
    }

    function submitAjaxForm(form, root, config) {
        var submitter = form.querySelector('[type="submit"]');
        var formData = new FormData(form);
        var action = String(formData.get('FORM_SUBMIT') || '');

        if (!AJAX_ACTIONS[action]) {
            return Promise.resolve(false);
        }

        if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
            return Promise.resolve(true);
        }

        if (submitter) {
            submitter.disabled = true;
        }

        form.classList.add('is-loading');

        return fetch(window.location.href, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok || !payload.ok) {
                        throw new Error((payload && payload.error) || 'Request failed.');
                    }

                    return payload;
                });
            })
            .then(function (payload) {
                var card = getCard(form);

                if (action === 'psa_meetup_join' && card && payload.rsvp) {
                    updateRsvp(card, payload.rsvp, config.lang);
                }

                if (action === 'psa_meetup_poll_vote' && card && payload.poll) {
                    updatePoll(card, payload.poll, config.lang);
                }

                if (action === 'psa_meetup_comment' && card && payload.comment) {
                    var list = card.querySelector('[data-psa-comment-list]');

                    if (list) {
                        list.insertAdjacentHTML('beforeend', renderCommentHtml(payload.comment, config));
                    }

                    if (form.querySelector('textarea')) {
                        form.querySelector('textarea').value = '';
                    }
                }

                if (action === 'psa_meetup_comment_reaction') {
                    var commentNode = form.closest('[data-comment-id]');

                    if (commentNode && payload.reactions) {
                        updateCommentReactions(commentNode, payload.reactions, config);
                    }
                }

                if (action === 'psa_delete_meetup_comment') {
                    var commentToRemove = form.closest('[data-comment-id]');

                    if (commentToRemove) {
                        commentToRemove.remove();
                    }
                }
            })
            .catch(function (error) {
                window.alert(error.message || 'Something went wrong. Please try again.');
            })
            .finally(function () {
                form.classList.remove('is-loading');

                if (submitter) {
                    submitter.disabled = false;
                }
            })
            .then(function () {
                return true;
            });
    }

    function initMeetupAjax(root) {
        root.addEventListener('submit', function (event) {
            var form = event.target;

            if (!(form instanceof HTMLFormElement) || !root.contains(form)) {
                return;
            }

            if (!form.matches('[data-psa-meetup-ajax]')) {
                return;
            }

            var formData = new FormData(form);
            var action = String(formData.get('FORM_SUBMIT') || '');

            if (!AJAX_ACTIONS[action]) {
                return;
            }

            event.preventDefault();

            var config = {
                requestToken: root.dataset.psaRequestToken || '',
                memberId: Number(root.dataset.psaMemberId || 0),
                reactionEmojis: parseJsonDataset(root, 'psaReactionEmojis', []),
                lang: parseJsonDataset(root, 'psaLang', {}),
            };

            submitAjaxForm(form, root, config);
        });
    }

    function initMeetupModal() {
        var root = document.querySelector('[data-psa-meetups]');

        if (!root) {
            return;
        }

        initMeetupAjax(root);

        var modal = root.querySelector('[data-psa-meetup-modal]');
        var openBtn = root.querySelector('[data-psa-meetup-open]');
        var closeEls = root.querySelectorAll('[data-psa-meetup-close]');
        var dialog = modal ? modal.querySelector('.psa-meetups-modal__dialog') : null;
        var typeInputs = root.querySelectorAll('[data-psa-meetup-type]');
        var meetupFields = root.querySelector('[data-psa-meetup-fields]');
        var pollToggle = root.querySelector('[data-psa-meetup-poll-toggle]');
        var pollFields = root.querySelector('[data-psa-meetup-poll]');
        var pollOptions = root.querySelector('[data-psa-meetup-poll-options]');
        var pollAddBtn = root.querySelector('[data-psa-meetup-poll-add]');
        var lastFocus = null;

        function setMeetupFieldsVisible() {
            if (!meetupFields) {
                return;
            }

            var selected = root.querySelector('[data-psa-meetup-type]:checked');
            var isMeetup = !selected || selected.value === 'meetup';
            meetupFields.hidden = !isMeetup;
        }

        function setPollFieldsVisible() {
            if (!pollFields || !pollToggle) {
                return;
            }

            pollFields.hidden = !pollToggle.checked;
        }

        function openModal() {
            if (!modal || !dialog) {
                return;
            }

            lastFocus = document.activeElement;
            modal.hidden = false;
            document.body.classList.add('psa-meetups-modal-open');
            dialog.focus();
        }

        function closeModal() {
            if (!modal) {
                return;
            }

            modal.hidden = true;
            document.body.classList.remove('psa-meetups-modal-open');

            if (lastFocus && typeof lastFocus.focus === 'function') {
                lastFocus.focus();
            }
        }

        if (openBtn) {
            openBtn.addEventListener('click', openModal);
        }

        closeEls.forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && !modal.hidden) {
                closeModal();
            }
        });

        typeInputs.forEach(function (input) {
            input.addEventListener('change', setMeetupFieldsVisible);
        });

        if (pollToggle) {
            pollToggle.addEventListener('change', setPollFieldsVisible);
        }

        if (pollAddBtn && pollOptions) {
            pollAddBtn.addEventListener('click', function () {
                var count = pollOptions.querySelectorAll('.psa-meetups__poll-option-row').length + 1;
                var row = document.createElement('div');
                row.className = 'psa-meetups__poll-option-row';
                row.innerHTML = '<input class="psa-meetups__input" type="text" name="poll_options[]" maxlength="255" placeholder="Option ' + count + '">';
                pollOptions.appendChild(row);
                row.querySelector('input')?.focus();
            });
        }

        setMeetupFieldsVisible();
        setPollFieldsVisible();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMeetupModal);
    } else {
        initMeetupModal();
    }
})();
