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
            button.addEventListener('click', function (event) {
                const row = button.closest('[data-psa-account-field]');

                if (!row) {
                    return;
                }

                injectHiddenFields(form, row);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMemberAccount);
    } else {
        initMemberAccount();
    }
})();
