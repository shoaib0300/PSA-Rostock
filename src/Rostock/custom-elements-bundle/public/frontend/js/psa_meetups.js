(function () {
    function initMeetupModal() {
        const root = document.querySelector('[data-psa-meetups]');

        if (!root) {
            return;
        }

        const modal = root.querySelector('[data-psa-meetup-modal]');
        const openBtn = root.querySelector('[data-psa-meetup-open]');
        const closeEls = root.querySelectorAll('[data-psa-meetup-close]');
        const dialog = modal ? modal.querySelector('.psa-meetups-modal__dialog') : null;
        const typeInputs = root.querySelectorAll('[data-psa-meetup-type]');
        const meetupFields = root.querySelector('[data-psa-meetup-fields]');
        const pollToggle = root.querySelector('[data-psa-meetup-poll-toggle]');
        const pollFields = root.querySelector('[data-psa-meetup-poll]');
        const pollOptions = root.querySelector('[data-psa-meetup-poll-options]');
        const pollAddBtn = root.querySelector('[data-psa-meetup-poll-add]');
        let lastFocus = null;

        function setMeetupFieldsVisible() {
            if (!meetupFields) {
                return;
            }

            const selected = root.querySelector('[data-psa-meetup-type]:checked');
            const isMeetup = !selected || selected.value === 'meetup';
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
                const count = pollOptions.querySelectorAll('.psa-meetups__poll-option-row').length + 1;
                const row = document.createElement('div');
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
