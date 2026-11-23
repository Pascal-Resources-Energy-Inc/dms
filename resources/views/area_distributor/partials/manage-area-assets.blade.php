<style>
    .manage-area-modal { --ma-border: #dce5ed; --ma-muted: #60758c; color: #19334e; }
    .manage-area-modal [hidden] { display: none !important; }
    .manage-area-modal .manage-area-dialog { width: calc(100% - 32px); max-width: 1040px; height: calc(100% - 32px); margin: 16px auto; }
    .manage-area-modal .manage-area-shell { max-height: calc(100vh - 32px); max-height: calc(100dvh - 32px); border: 0; border-radius: 14px; overflow: hidden; }
    .manage-area-form { display: flex; flex-direction: column; min-height: 0; overflow: hidden; }
    .manage-area-header, .manage-area-footer { flex: 0 0 auto; padding: 20px 24px; background: #fff; border-color: var(--ma-border); }
    .manage-area-header .modal-title { font-size: 20px; font-weight: 700; line-height: 1.3; }
    .manage-area-header p, .manage-area-toolbar p { color: var(--ma-muted); margin: 4px 0 0; font-size: 13px; }
    .manage-area-header > div { min-width: 0; overflow-wrap: anywhere; }
    .manage-area-header .btn-close { flex-shrink: 0; padding: 12px; margin: 0 0 0 12px; }
    .manage-area-body { min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 20px 24px; background: #f6f8fb; scrollbar-gutter: stable; }
    .manage-area-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 14px; }
    .manage-area-toolbar strong { font-size: 15px; }
    .manage-area-count { display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; margin-left: 5px; padding: 0 6px; border-radius: 20px; background: #e5eef8; color: #175b9a; font-size: 12px; }
    .manage-area-main { display: flex; flex-wrap: wrap; gap: 6px 24px; color: var(--ma-muted); font-size: 12px; margin-bottom: 18px; overflow-wrap: anywhere; }
    .manage-area-main strong { font-weight: 500; color: #30475e; }
    .manage-area-rows { display: grid; gap: 16px; }
    .manage-area-row { min-width: 0; border: 1px solid var(--ma-border); background: #fff; border-radius: 10px; }
    .manage-area-row-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 18px; border-bottom: 1px solid #edf1f5; }
    .manage-area-row-header > div { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; min-width: 0; }
    .manage-area-row-title { font-size: 14px; overflow-wrap: anywhere; }
    .manage-area-project { color: var(--ma-muted); font-size: 11px; padding: 3px 8px; background: #f0f4f8; border-radius: 4px; }
    .manage-area-modal .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; font-size: 13px; font-weight: 600; border-radius: 7px; white-space: nowrap; }
    .manage-area-modal .manage-area-remove { width: 40px; flex: 0 0 40px; padding: 0; border-color: #f1d9db; color: #b33340; background: #fff; }
    .manage-area-remove:hover { background: #fff1f2 !important; }
    .manage-area-fields { min-width: 0; margin: 0; padding: 18px; border: 0; }
    .manage-area-assignment { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; }
    .manage-area-business { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 18px; }
    .manage-area-assignment > div, .manage-area-business > div, .manage-area-file > div { min-width: 0; }
    .manage-area-row label { display: block; margin-bottom: 6px; color: #30475e; font-size: 13px; font-weight: 600; }
    .manage-area-row small { display: block; margin-top: 6px; color: var(--ma-muted); font-size: 12px; line-height: 1.5; }
    .manage-area-row label small { display: inline; font-weight: 400; }
    .manage-area-row .form-control, .manage-area-row .form-select { width: 100%; min-width: 0; min-height: 42px; font-size: 13px; border: 1px solid #bdcddd; border-radius: 6px; margin: 0; background-color: #fff; }
    .manage-area-row input[readonly], .manage-area-row select:disabled { background-color: #f3f6f9; color: #5a6f84; }
    .manage-area-row .manage-area-check { display: flex; align-items: flex-start; gap: 8px; margin: 9px 0 0; font-size: 12px; font-weight: 400; line-height: 1.5; cursor: pointer; }
    .manage-area-check input { width: 16px; height: 16px; flex: 0 0 16px; margin-top: 1px; accent-color: #1766a6; }
    .manage-area-file { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding-top: 18px; margin-top: 18px; border-top: 1px solid #edf1f5; }
    .manage-area-file input { padding: 7px; }
    .manage-area-current-file { align-self: center; overflow-wrap: anywhere; font-size: 13px; }
    .manage-area-current-file a { display: block; margin-top: 3px; text-decoration: underline; }
    .manage-area-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
    .manage-area-footer > * { margin: 0; }
    .manage-area-footer > div { display: flex; gap: 8px; }
    .manage-area-save-hint { color: var(--ma-muted); font-size: 12px; }
    .manage-area-disengagement { padding: 18px; display: flex; align-items: flex-start; gap: 18px; background: #fffbf3; border-radius: 0 0 10px 10px; }
    .manage-area-disengagement > div { flex: 1; min-width: 0; }
    .manage-area-disengagement .form-control { max-width: 260px; }
    .manage-area-disengagement .btn { margin-top: 25px; }
    .manage-area-row.is-removing { border-color: #e9c990; }
    .manage-area-errors { padding: 12px 16px; margin-bottom: 16px; border-radius: 6px; border: 1px solid #efc3c7; background: #fff2f3; color: #9c2838; font-size: 13px; }
    .manage-area-errors ul { margin: 6px 0 0; padding-left: 18px; }
    .manage-area-empty { padding: 32px 16px; text-align: center; border: 1px dashed var(--ma-border); border-radius: 10px; background: #fff; }
    .manage-area-empty p { color: var(--ma-muted); margin: 8px 0 0; font-size: 13px; }
    .manage-area-modal .select2-container { width: 100% !important; min-width: 0; }
    .manage-area-modal .select2-selection--single { min-height: 42px; border: 1px solid #bdcddd; border-radius: 6px; }
    .manage-area-modal .select2-selection--single .select2-selection__rendered { padding-left: 12px; padding-right: 44px; font-size: 13px; line-height: 40px; }
    .manage-area-modal .select2-selection--single .select2-selection__arrow { height: 40px; right: 8px; }
    .manage-area-modal .select2-dropdown { border-color: #bdcddd; font-size: 13px; }
    .manage-area-modal :focus-visible { outline: 2px solid #1974b9; outline-offset: 2px; }
    @media (max-width: 767px) {
        .manage-area-assignment { grid-template-columns: 1fr 1fr; }
        .manage-area-assignment > div:first-child { grid-column: 1 / -1; }
        .manage-area-business, .manage-area-file { grid-template-columns: 1fr; }
        .manage-area-header, .manage-area-footer, .manage-area-body { padding: 16px; }
        .manage-area-save-hint { display: none; }
        .manage-area-footer { justify-content: flex-end; }
    }
    @media (max-width: 480px) {
        .manage-area-modal .manage-area-dialog { width: calc(100% - 16px); height: calc(100% - 16px); margin: 8px auto; }
        .manage-area-modal .manage-area-shell { max-height: calc(100vh - 16px); max-height: calc(100dvh - 16px); border-radius: 10px; }
        .manage-area-header .modal-title { font-size: 17px; }
        .manage-area-assignment { grid-template-columns: 1fr; }
        .manage-area-row-header, .manage-area-fields, .manage-area-disengagement { padding: 12px; }
        .manage-area-toolbar { align-items: flex-start; flex-wrap: wrap; gap: 10px; }
        .manage-area-toolbar .btn { width: 100%; }
        .manage-area-footer > div { width: 100%; }
        .manage-area-footer .btn { flex: 1; }
        .manage-area-disengagement { flex-direction: column; gap: 12px; }
        .manage-area-disengagement > div, .manage-area-disengagement .form-control { width: 100%; max-width: none; }
        .manage-area-disengagement .btn { margin: 0; }
    }
    @media (max-height: 480px) { .manage-area-header, .manage-area-footer { padding-top: 10px; padding-bottom: 10px; } }
</style>
<script>
(function () {
    function init() {
        if (!window.jQuery) return setTimeout(init, 50);
        if (window.manageAreaManagerReady) return;
        window.manageAreaManagerReady = true;
        var $ = window.jQuery, nextIndex = Date.now();

        function refresh(form) {
            var rows = form.querySelectorAll('.manage-area-rows > .manage-area-row');
            form.querySelector('.manage-area-count').textContent = Array.from(rows).filter(function (row) { return !row.classList.contains('is-removing'); }).length;
            form.querySelector('.manage-area-empty').hidden = rows.length > 0;
            var selects = $(form).find('.manage-area-fields:not(:disabled) .manage-area-select');
            var names = selects.map(function () { return String(this.value).trim().toLowerCase(); }).get();
            selects.each(function () {
                var own = this.value.trim().toLowerCase();
                $(this).find('option').each(function () {
                    var name = this.value.trim().toLowerCase();
                    this.disabled = name !== '' && name !== own && names.indexOf(name) !== -1;
                });
                $(this).trigger('change.select2');
            });
        }
        function selectAreas(scope) {
            if (!$.fn.select2) return;
            $(scope).find('.manage-area-select').each(function () {
                if ($(this).data('select2')) return;
                $(this).select2({ width: '100%', dropdownParent: $(this).closest('.manage-area-modal'), placeholder: 'Select area', allowClear: true });
            });
        }
        function copyBusiness(row, kind) {
            var form = row.closest('form');
            var checkbox = row.querySelector('.manage-area-same-' + kind);
            var input = row.querySelector('.manage-area-business-' + kind);
            if (checkbox.checked) {
                input.dataset.customValue = input.value;
                input.value = kind === 'name' ? form.dataset.mainName : form.dataset.mainType;
            } else input.value = input.dataset.customValue || '';
            if (kind === 'name') input.readOnly = checkbox.checked;
            else input.disabled = checkbox.checked;
        }
        $(document).on('click', '.manage-area-add', function () {
            var form = this.closest('form'), fragment = form.querySelector('.manage-area-template').content.cloneNode(true);
            var index = ++nextIndex;
            fragment.querySelectorAll('*').forEach(function (element) {
                ['id', 'for', 'name', 'aria-describedby'].forEach(function (attr) {
                    if (element.hasAttribute(attr)) element.setAttribute(attr, element.getAttribute(attr).replace(/__INDEX__/g, index));
                });
            });
            var row = fragment.querySelector('.manage-area-row');
            form.querySelector('.manage-area-rows').appendChild(fragment);
            selectAreas(row); refresh(form);
            row.scrollIntoView({ block: 'nearest' });
            if ($.fn.select2) $(row).find('.manage-area-select').select2('open');
            else row.querySelector('.manage-area-select').focus();
        });
        $(document).on('change', '.manage-area-same-name, .manage-area-same-type', function () {
            copyBusiness(this.closest('.manage-area-row'), this.classList.contains('manage-area-same-name') ? 'name' : 'type');
        });
        $(document).on('change', '.manage-area-select', function () {
            var row = this.closest('.manage-area-row');
            row.querySelector('.manage-area-row-title').textContent = this.value || 'New area';
            row.querySelector('.manage-area-remove').setAttribute('aria-label', 'Delete ' + (this.value || 'new area'));
            refresh(this.closest('form'));
        });
        $(document).on('click', '.manage-area-remove', function () {
            var row = this.closest('.manage-area-row'), form = row.closest('form');
            if (!row.dataset.areaId) {
                if ($.fn.select2 && $(row).find('.manage-area-select').data('select2')) $(row).find('.manage-area-select').select2('destroy');
                row.remove(); refresh(form); form.querySelector('.manage-area-add').focus(); return;
            }
            row.classList.add('is-removing');
            row.querySelector('.manage-area-fields').disabled = true;
            row.querySelector('.manage-area-fields').hidden = true;
            row.querySelector('.manage-area-disengagement').hidden = false;
            row.querySelector('.manage-area-removed-id').disabled = false;
            var date = row.querySelector('.manage-area-disengagement-date');
            date.disabled = false; date.required = true;
            this.hidden = true; refresh(form); date.focus();
        });
        $(document).on('click', '.manage-area-undo', function () {
            var row = this.closest('.manage-area-row');
            row.classList.remove('is-removing');
            row.querySelector('.manage-area-fields').disabled = false;
            row.querySelector('.manage-area-fields').hidden = false;
            row.querySelector('.manage-area-disengagement').hidden = true;
            row.querySelector('.manage-area-removed-id').disabled = true;
            var date = row.querySelector('.manage-area-disengagement-date');
            date.disabled = true; date.required = false;
            var button = row.querySelector('.manage-area-remove'); button.hidden = false;
            refresh(row.closest('form')); button.focus();
        });
        $(document).on('change', '.manage-area-offer', function () {
            this.setCustomValidity(this.files[0] && this.files[0].size > 10 * 1024 * 1024 ? 'Choose an offer sheet of 10 MB or less.' : '');
            this.reportValidity();
        });
        $(document).on('shown.bs.modal', '.manage-area-modal', function () { selectAreas(this); refresh(this.querySelector('form')); });
        $(document).on('submit', '.manage-area-form', function (event) {
            event.preventDefault();
            var form = this, button = form.querySelector('.manage-area-save'), errors = form.querySelector('.manage-area-errors');
            if (button.disabled || !form.reportValidity()) return;
            errors.hidden = true;
            button.disabled = true; button.textContent = 'Saving…';
            function showErrors(response) {
                var messages = response.errors ? Object.keys(response.errors).reduce(function (all, key) { return all.concat(response.errors[key]); }, []) : [response.message || 'Unable to save areas. Please try again.'];
                errors.textContent = 'Please review the following:';
                var list = document.createElement('ul');
                messages.forEach(function (message) { var item = document.createElement('li'); item.textContent = message; list.appendChild(item); });
                errors.appendChild(list); errors.hidden = false; errors.focus();
            }
            $.ajax({ url: form.action, type: 'POST', data: new FormData(form), processData: false, contentType: false, headers: { Accept: 'application/json' },
                success: function (response) {
                    if (!response.status) { showErrors(response); return; }
                    var modal = form.closest('.manage-area-modal');
                    function done() {
                        if (window.Swal) Swal.fire({ icon: 'success', title: 'Awarded areas updated', text: response.message, confirmButtonText: 'Done', confirmButtonColor: '#249a62' }).then(function () { window.location.reload(); });
                        else window.location.reload();
                    }
                    $(modal).one('hidden.bs.modal', done);
                    (bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal)).hide();
                },
                error: function (xhr) { showErrors(xhr.responseJSON || {}); },
                complete: function () { button.disabled = false; button.textContent = 'Save Areas'; }
            });
        });
    }
    if (document.readyState === 'complete') init(); else window.addEventListener('load', init);
})();
</script>
