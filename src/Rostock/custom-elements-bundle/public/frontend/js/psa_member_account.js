(function () {
    function disableEditorInputs(editor) {
        editor.querySelectorAll('input, select, textarea').forEach(function (input) {
            input.disabled = true;
            input.dataset.psaAccountOriginalName = input.name || '';
            input.removeAttribute('name');
        });
    }

    function enableEditorInputs(editor) {
        editor.querySelectorAll('input, select, textarea').forEach(function (input) {
            input.disabled = false;

            if (!input.name && input.dataset.psaAccountOriginalName) {
                input.name = input.dataset.psaAccountOriginalName;
            }
        });
    }

    function closeEditor(row) {
        row.classList.remove('is-editing');
        row.querySelector('[data-psa-account-editor]').hidden = true;
        disableEditorInputs(row.querySelector('[data-psa-account-editor]'));
    }

    function openEditor(row) {
        document.querySelectorAll('[data-psa-account-field].is-editing').forEach(closeEditor);

        row.classList.add('is-editing');
        const editor = row.querySelector('[data-psa-account-editor]');
        editor.hidden = false;
        enableEditorInputs(editor);

        const focusable = editor.querySelector('input:not([type="hidden"]), select, textarea');

        if (focusable) {
            focusable.focus();
        }
    }

    function injectHiddenFields(form, activeRow) {
        form.querySelectorAll('[data-psa-account-hidden]').forEach(function (node) {
            node.remove();
        });

        form.querySelectorAll('[data-psa-account-field]').forEach(function (row) {
            if (row === activeRow || row.dataset.skipHidden === '1') {
                return;
            }

            const name = row.dataset.field;
            const value = row.dataset.submitValue || '';

            if (!name) {
                return;
            }

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = value;
            hidden.dataset.psaAccountHidden = '';
            form.appendChild(hidden);
        });
    }

    function initMemberAccount() {
        const form = document.querySelector('[data-psa-member-account]');

        if (!form) {
            return;
        }

        form.querySelectorAll('[data-psa-account-editor]').forEach(disableEditorInputs);

        form.querySelectorAll('[data-psa-account-edit]').forEach(function (button) {
            button.addEventListener('click', function () {
                openEditor(button.closest('[data-psa-account-field]'));
            });
        });

        form.querySelectorAll('[data-psa-account-cancel]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeEditor(button.closest('[data-psa-account-field]'));
            });
        });

        form.querySelectorAll('.psa-account-field__save').forEach(function (button) {
            button.addEventListener('click', function () {
                const row = button.closest('[data-psa-account-field]');

                if (!row) {
                    return;
                }

                injectHiddenFields(form, row);
            });
        });
    }

    function initMemberPosts() {
        const section = document.querySelector('[data-psa-account-posts]');

        if (!section || section.tagName !== 'DETAILS') {
            return;
        }

        const label = section.querySelector('[data-psa-account-posts-label]');
        const countNode = section.querySelector('.psa-member-account__posts-count');
        const showLabel = section.dataset.labelShow || '';
        const hideLabel = section.dataset.labelHide || '';

        if (!label || !showLabel || !hideLabel) {
            return;
        }

        function updateLabel() {
            label.textContent = '';

            label.appendChild(document.createTextNode(section.open ? hideLabel : showLabel));

            if (countNode) {
                label.appendChild(document.createTextNode(' '));
                label.appendChild(countNode);
            }
        }

        section.addEventListener('toggle', updateLabel);
    }

    function init() {
        initMemberPosts();

        try {
            initMemberAccount();
        } catch (error) {
            console.error('PSA member account init failed:', error);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
