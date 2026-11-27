@extends('layouts.header')

@section('css')
<style>
    .survey-page { display: grid; gap: 20px; }
    .survey-page-head { display: flex; align-items: end; justify-content: space-between; gap: 18px; }
    .survey-kicker { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 7px; color: #0877ca; font-size: 11px; font-weight: 850; letter-spacing: .08em; text-transform: uppercase; }
    .survey-page-title { margin: 0; color: #101828; font-size: 26px; font-weight: 900; }
    .survey-page-copy { max-width: 650px; margin: 7px 0 0; color: #667085; font-size: 15px; line-height: 1.6; }
    .survey-complete-alert { display: none; align-items: center; gap: 12px; padding: 16px 18px; color: #176b47; border: 1px solid #bcebd6; border-radius: 12px; background: #effbf5; font-size: 12px; font-weight: 750; }
    .survey-complete-alert.is-visible { display: flex; }

    .survey-page-form { width: 100%; }
    .survey-modal .modal-content { height: min(840px, calc(100dvh - 170px)); min-height: 680px; overflow: hidden; border: 0; border-radius: 18px; box-shadow: 0 18px 50px rgba(15, 23, 42, .14); }
    .survey-modal .modal-header { min-height: 78px; align-items: center; gap: 16px; padding: 17px 22px; border-bottom: 1px solid #e4e7ec; background: #fff; }
    .survey-modal-heading { min-width: 0; display: flex; align-items: center; gap: 12px; }
    .survey-modal-icon { width: 44px; height: 44px; display: grid; flex: 0 0 auto; place-items: center; color: #0877ca; border-radius: 13px; background: #edf9ff; font-size: 19px; }
    .survey-modal-title { margin: 0; color: #101828; font-size: 18px; font-weight: 900; }
    .survey-modal-subtitle { margin-top: 3px; color: #667085; font-size: 12px; }
    .survey-progress-copy { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 6px; color: #667085; font-size: 10px; font-weight: 800; }
    .survey-progress { height: 7px; overflow: hidden; border-radius: 999px; background: #e9eef3; }
    .survey-progress-bar { width: 14.286%; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #0877ca, #19a1d8); transition: width .25s ease; }
    .survey-modal .modal-body { min-height: 0; display: grid; grid-template-columns: 240px minmax(0, 1fr); padding: 0; overflow: hidden; }
    .survey-step-nav { padding: 20px 14px; overflow-y: auto; border-right: 1px solid #e4e7ec; background: #f8fafc; }
    .survey-step-label { margin: 0 9px 12px; color: #98a2b3; font-size: 9px; font-weight: 900; letter-spacing: .09em; text-transform: uppercase; }
    .survey-step-button { width: 100%; display: grid; grid-template-columns: 30px minmax(0, 1fr); align-items: center; gap: 10px; margin-bottom: 5px; padding: 10px; color: #667085; border: 0; border-radius: 10px; background: transparent; text-align: left; transition: color .18s ease, background .18s ease; }
    .survey-step-button:hover { color: #344054; background: #eef4f8; }
    .survey-step-button.is-active { color: #075da7; background: #e8f5fc; }
    .survey-step-button.is-complete { color: #176b47; }
    .survey-step-number { width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid #d0d5dd; border-radius: 9px; background: #fff; font-size: 10px; font-weight: 900; }
    .survey-step-button.is-active .survey-step-number { color: #fff; border-color: #0877ca; background: #0877ca; }
    .survey-step-button.is-complete .survey-step-number { color: #176b47; border-color: #bcebd6; background: #effbf5; }
    .survey-step-name { display: block; font-size: 11px; font-weight: 850; }
    .survey-step-hint { display: block; margin-top: 2px; color: #98a2b3; font-size: 9px; line-height: 1.3; }
    .survey-form-stage { min-width: 0; overflow-y: auto; background: #f5f7fa; scroll-behavior: smooth; }
    .survey-pane { display: none; width: 100%; max-width: none; margin: 0; padding: 24px 72px 30px; }
    .survey-pane.is-active { display: block; }
    .survey-pane-head { margin-bottom: 14px; }
    .survey-pane-kicker { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px; color: #0877ca; font-size: 9px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
    .survey-pane-title { margin: 0; color: #101828; font-size: 22px; font-weight: 900; }
    .survey-pane-copy { max-width: 720px; margin: 6px 0 0; color: #667085; font-size: 13px; line-height: 1.6; }
    .survey-card { margin: 0 -18px 12px; padding: 18px; border: 1px solid #e4e7ec; border-radius: 14px; background: #fff; box-shadow: 0 5px 16px rgba(15, 23, 42, .035); }
    .survey-card:last-child { margin-bottom: 0; }
    .survey-card-title { display: flex; align-items: start; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
    .survey-question { margin: 0; color: #25364a; font-size: 13px; font-weight: 900; line-height: 1.45; }
    .survey-question-help { margin: 4px 0 0; color: #7b8b98; font-size: 12px; line-height: 1.5; }
    .survey-required { color: #d92d20; }
    .survey-counter { flex: 0 0 auto; padding: 5px 8px; color: #667085; border-radius: 999px; background: #f2f4f7; font-size: 9px; font-weight: 850; }
    .survey-counter.is-full { color: #075985; background: #e0f2fe; }
    .survey-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .survey-grid.is-three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .survey-field label, .survey-field-label { display: block; margin-bottom: 6px; color: #344054; font-size: 12px; font-weight: 850; }
    .survey-field .form-control, .survey-field .form-select { min-height: 43px; padding: 9px 11px; border-color: #d0d5dd; border-radius: 9px; font-size: 13px; font-weight: 650; }
    .survey-modal .form-control::placeholder { color: #98a2b3; font-weight: 400; opacity: 1; }
    .survey-field textarea.form-control { min-height: 100px; resize: vertical; }
    .survey-field .form-control:focus, .survey-field .form-select:focus { border-color: #0877ca; box-shadow: 0 0 0 4px rgba(8, 119, 202, .1); }
    .survey-ranked-inputs { display: grid; gap: 10px; }
    .survey-ranked-row { display: grid; grid-template-columns: 24px minmax(0, 1fr); align-items: center; gap: 10px; }
    .survey-ranked-row label { margin: 0; color: #344054; font-size: 11px; font-weight: 900; text-align: right; }
    .survey-choice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .survey-choice-grid.is-five { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .survey-choice { position: relative; min-width: 0; margin: 0; cursor: pointer; }
    .survey-choice input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
    .survey-choice-card { min-height: 54px; display: flex; align-items: center; gap: 9px; padding: 10px 11px; color: #475467; border: 1.5px solid #dce3e9; border-radius: 10px; background: #fff; font-size: 12px; font-weight: 750; line-height: 1.35; transition: border-color .16s ease, background .16s ease, transform .16s ease; }
    .survey-choice:hover .survey-choice-card { transform: translateY(-1px); border-color: #a9d6ed; }
    .survey-choice-mark { width: 20px; height: 20px; display: grid; flex: 0 0 auto; place-items: center; color: transparent; border: 1.5px solid #b8c2cc; border-radius: 6px; background: #fff; font-size: 10px; }
    .survey-choice.is-radio .survey-choice-mark { border-radius: 50%; }
    .survey-choice input:focus-visible + .survey-choice-card { outline: 3px solid rgba(8, 119, 202, .15); outline-offset: 2px; }
    .survey-choice input:checked + .survey-choice-card { color: #075da7; border-color: #0877ca; background: #f0f9ff; box-shadow: 0 5px 14px rgba(8, 119, 202, .08); }
    .survey-choice input:checked + .survey-choice-card .survey-choice-mark { color: #fff; border-color: #0877ca; background: #0877ca; }
    .survey-choice input:disabled + .survey-choice-card { cursor: not-allowed; opacity: .48; }
    .survey-number-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .survey-number-field { display: grid; grid-template-columns: minmax(0, 1fr) 88px; align-items: center; gap: 10px; padding: 9px 10px; border: 1px solid #e4e7ec; border-radius: 10px; background: #fbfcfd; }
    .survey-number-field label { margin: 0; color: #475467; font-size: 10px; font-weight: 750; }
    .survey-number-field .form-control { min-height: 36px; padding: 7px 8px; text-align: center; }
    .survey-table-wrap { overflow-x: auto; border: 1px solid #e4e7ec; border-radius: 11px; }
    .survey-table { min-width: 700px; margin: 0; }
    .survey-table th { padding: 10px 11px; color: #667085; border-color: #edf0f5; background: #f8fafc; font-size: 12px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .survey-table td { padding: 9px 11px; color: #344054; border-color: #edf0f5; font-size: 12px; vertical-align: middle; }
    .survey-table .form-control { min-height: 36px; padding: 7px 8px; font-size: 10px; }
    .survey-channel-percent { color: #0877ca; font-weight: 900; }
    .survey-performance-source { display: inline-flex; align-items: center; gap: 6px; padding: 6px 9px; border-radius: 999px; font-size: 9px; font-weight: 900; white-space: nowrap; }
    .survey-performance-source.is-demo { color: #9a6700; border: 1px solid #f4d58d; background: #fff8e6; }
    .survey-performance-source.is-live { color: #176b47; border: 1px solid #bcebd6; background: #effbf5; }
    .survey-performance-summary { margin-top: 14px; overflow: hidden; border: 1px solid #e4e7ec; border-radius: 10px; background: #fff; }
    .survey-performance-summary table { width: 100%; margin: 0; border-collapse: collapse; }
    .survey-performance-summary th, .survey-performance-summary td { padding: 10px 13px; border-bottom: 1px solid #edf0f5; font-size: 10px; vertical-align: middle; }
    .survey-performance-summary tr:last-child th, .survey-performance-summary tr:last-child td { border-bottom: 0; }
    .survey-performance-summary th { width: 50%; color: #667085; background: #f8fafc; font-size: 11px; font-weight: 900; letter-spacing: .04em; text-align: left; text-transform: uppercase; }
    .survey-performance-summary td { color: #25364a; font-size: 12px; font-weight: 900; text-align: right; }
    .performance-summary-sales.is-best { color: #15803d; }
    .performance-summary-sales.is-lowest { color: #c94b4b; }
    .survey-target-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 7px; border-radius: 999px; font-size: 11px; font-weight: 900; white-space: nowrap; }
    .survey-target-badge.is-above { color: #075985; background: #e0f2fe; }
    .survey-target-badge.is-met { color: #176b47; background: #eafaf2; }
    .survey-target-badge.is-below { color: #b54708; background: #fff4e5; }
    .survey-growth-value { font-weight: 850; }
    .survey-growth-value.is-positive { color: #176b47; }
    .survey-growth-value.is-negative { color: #b42318; }
    .survey-matrix td:not(:first-child), .survey-matrix th:not(:first-child) { width: 66px; text-align: center; }
    .survey-matrix-radio { width: 18px; height: 18px; accent-color: #0877ca; cursor: pointer; }
    .survey-conditional { display: none; margin-top: 12px; }
    .survey-conditional.is-visible { display: block; }
    .survey-review-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .survey-review-card { padding: 15px; border: 1px solid #e4e7ec; border-radius: 11px; background: #fff; }
    .survey-review-card span { display: block; margin-bottom: 5px; color: #98a2b3; font-size: 9px; font-weight: 900; letter-spacing: .06em; text-transform: uppercase; }
    .survey-review-card strong { display: block; color: #25364a; font-size: 12px; line-height: 1.45; }
    .survey-review-note { padding: 13px 15px; color: #475467; border: 1px solid #cce8f7; border-radius: 10px; background: #f0f9ff; font-size: 10px; line-height: 1.55; }
    .survey-modal .modal-footer { min-height: 72px; gap: 9px; padding: 14px 22px; border-top: 1px solid #e4e7ec; background: #fff; }
    .survey-modal .modal-footer .btn { min-height: 40px; padding: 9px 15px; border-radius: 9px; font-size: 11px; font-weight: 850; }
    .survey-footer-status { margin-right: auto; color: #7b8b98; font-size: 9px; font-weight: 700; }
    .survey-footer-status i { color: #12b76a; }

    @media (max-width: 991.98px) {
        .survey-modal .modal-content { height: calc(100dvh - 120px); min-height: 660px; border-radius: 16px; }
        .survey-modal .modal-body { grid-template-columns: 1fr; }
        .survey-step-nav { display: flex; gap: 7px; padding: 10px 14px; overflow-x: auto; border-right: 0; border-bottom: 1px solid #e4e7ec; }
        .survey-step-label, .survey-step-hint { display: none; }
        .survey-step-button { width: auto; flex: 0 0 auto; grid-template-columns: 28px auto; margin: 0; padding: 7px 9px; }
        .survey-step-number { width: 28px; height: 28px; }
        .survey-pane { padding: 20px 16px 28px; }
    }

    @media (max-width: 767.98px) {
        .survey-page-head { align-items: stretch; flex-direction: column; }
        .survey-modal .modal-header { min-height: 68px; padding: 12px 14px; }
        .survey-modal-heading { width: 100%; align-items: center; gap: 10px; }
        .survey-modal-icon { width: 38px; height: 38px; }
        .survey-modal-title { font-size: clamp(14px, 3.2vw, 14px); line-height: 1.2; letter-spacing: -.01em; white-space: nowrap; }
        .survey-modal-subtitle { display: none; }
        .survey-grid, .survey-grid.is-three, .survey-choice-grid, .survey-choice-grid.is-five, .survey-number-grid, .survey-review-grid { grid-template-columns: 1fr; }
        .survey-card { margin-right: -16px; margin-left: -16px; padding: 16px; }
        .survey-choice-grid.is-five { grid-template-columns: repeat(5, minmax(48px, 1fr)); }
        .survey-choice-grid.is-five .survey-choice-card { justify-content: center; padding: 8px 4px; }
        .survey-choice-grid.is-five .survey-choice-mark { display: none; }
        .survey-modal .modal-footer { flex-wrap: wrap; padding: 10px 14px; }
        .survey-footer-status { width: 100%; margin: 0; text-align: center; }
        .survey-modal .modal-footer .btn { flex: 1; }
    }

    /* Section-by-section survey layout. */
    .survey-modal .modal-body { grid-template-columns: minmax(0, 1fr); }
    .survey-modal .modal-footer { box-shadow: 0 -8px 24px rgba(15, 23, 42, .08); }
    .survey-footer-actions { width: 100%; display: flex; justify-content: flex-end; gap: 9px; }
    .survey-footer-progress { width: min(360px, 100%); min-width: 0; margin-top: 8px; margin-left: auto; }
    .survey-footer-progress .survey-progress-copy { justify-content: flex-start; }

    @media (max-width: 767.98px) {
        .survey-footer-actions .btn { flex: 1 1 0; }
        .survey-footer-progress { width: 100%; margin-right: 0; margin-left: 0; }
        .survey-progress-copy { align-items: flex-start; }
        .survey-progress-copy span:last-child { text-align: right; }
    }
</style>
@endsection

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isSubmitted = @json((bool) $isSubmitted);
    const form = document.getElementById('partnerSurveyForm');
    const panes = Array.from(form.querySelectorAll('[data-survey-pane]'));
    const formStage = document.getElementById('surveyFormStage');
    const previousButton = document.getElementById('surveyPrevious');
    const nextButton = document.getElementById('surveyNext');
    const finishButton = document.getElementById('surveyFinish');
    const saveButton = document.getElementById('surveySaveDraft');
    const submissionIntent = document.getElementById('surveySubmissionIntent');
    const draftStatus = document.getElementById('surveyDraftStatus');
    const progressBar = document.getElementById('surveyProgressBar');
    const progressCurrent = document.getElementById('surveyProgressCurrent');
    let currentPaneIndex = 0;

    function notify(title, message, icon) {
        if (window.Swal) {
            window.Swal.fire({
                title: title,
                text: message,
                icon: icon || 'info',
                confirmButtonColor: '#0877ca'
            });
            return;
        }
        window.alert(message);
    }

    function validateLimitGroups(pane) {
        const groups = Array.from(pane.querySelectorAll('.survey-limit-group[data-min]'));
        for (let index = 0; index < groups.length; index += 1) {
            const group = groups[index];
            const minimum = Number(group.dataset.min || 0);
            const checked = group.querySelectorAll('input[type="checkbox"]:checked').length;
            if (checked < minimum) {
                notify('Complete this question', 'Please select ' + minimum + ' choices before continuing.', 'warning');
                group.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        }
        return true;
    }

    function validatePane(pane) {
        if (!validateLimitGroups(pane)) {
            return false;
        }

        const invalid = Array.from(pane.querySelectorAll('[required]')).find(function (field) {
            return !field.checkValidity();
        });

        if (invalid) {
            invalid.reportValidity();
            invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        return true;
    }

    function showPane(index, shouldFocus) {
        currentPaneIndex = Math.max(0, Math.min(index, panes.length - 1));

        panes.forEach(function (pane, paneIndex) {
            pane.classList.toggle('is-active', paneIndex === currentPaneIndex);
        });

        const currentPane = panes[currentPaneIndex];
        const title = currentPane.querySelector('.survey-pane-title');
        const sectionNumber = currentPaneIndex + 1;
        const isFirst = currentPaneIndex === 0;
        const isLast = currentPaneIndex === panes.length - 1;

        previousButton.disabled = isFirst;
        nextButton.classList.toggle('d-none', isLast);
        finishButton.classList.toggle('d-none', !isLast);

        progressCurrent.textContent = 'Section ' + sectionNumber + ' of ' + panes.length;
        progressBar.style.width = ((sectionNumber / panes.length) * 100) + '%';
        progressBar.setAttribute('aria-valuenow', String(sectionNumber));
        progressBar.setAttribute('aria-valuemax', String(panes.length));

        if (formStage) {
            formStage.scrollTop = 0;
        }

        if (shouldFocus && title) {
            title.setAttribute('tabindex', '-1');
            title.focus({ preventScroll: true });
        }
    }

    function updateLimitGroup(group) {
        const maximum = Number(group.dataset.max || 0);
        const checked = Array.from(group.querySelectorAll('input[type="checkbox"]:checked'));
        const unchecked = Array.from(group.querySelectorAll('input[type="checkbox"]:not(:checked)'));
        const counter = group.querySelector('[data-limit-counter]');

        unchecked.forEach(function (input) {
            input.disabled = maximum > 0 && checked.length >= maximum;
        });

        if (counter) {
            counter.textContent = checked.length + ' of ' + maximum + ' selected';
            counter.classList.toggle('is-full', checked.length === maximum);
        }
    }

    function updateChannelMix() {
        const salesInputs = Array.from(form.querySelectorAll('[data-channel-sales]'));
        const total = salesInputs.reduce(function (sum, input) {
            return sum + (Number(input.value) || 0);
        }, 0);

        salesInputs.forEach(function (input) {
            const percentage = total > 0 ? ((Number(input.value) || 0) / total) * 100 : 0;
            const output = form.querySelector('[data-channel-percent="' + input.dataset.channelSales + '"]');
            if (output) output.textContent = percentage.toFixed(total > 0 ? 1 : 0) + '%';
        });

        // document.getElementById('channelTotalBadge').textContent = new Intl.NumberFormat('en-PH', {
            // style: 'currency', currency: 'PHP', maximumFractionDigits: 0
        // }).format(total) + ' total';
    }

    function updateTerritoryReason() {
        const assessments = Array.from(form.querySelectorAll('.territory-assessment'));
        const showReason = assessments.some(function (input) {
            return input.checked && ['probably_no', 'definitely_no'].indexOf(input.value) !== -1;
        });
        const wrap = document.getElementById('territoryReasonWrap');
        const textarea = document.getElementById('territoryReason');
        wrap.classList.toggle('is-visible', showReason);
        textarea.required = showReason;
    }

    function updateOtherFields() {
        form.querySelectorAll('[data-other-selection]').forEach(function (wrap) {
            const selectionNames = wrap.dataset.otherSelection.split('|');
            const mode = wrap.dataset.otherMode || 'option';
            const isActive = selectionNames.some(function (selectionName) {
                return Array.from(form.querySelectorAll('[name="' + selectionName + '"]')).some(function (control) {
                    if (mode === 'positive') {
                        return Number(control.value) > 0;
                    }

                    if (control.tagName === 'SELECT') {
                        return control.value === 'other';
                    }

                    return control.checked && control.value === 'other';
                });
            });

            wrap.classList.toggle('is-visible', isActive);
            wrap.querySelectorAll('input, select, textarea').forEach(function (field) {
                field.disabled = !isActive;
                field.required = isActive;
            });
        });
    }

    function submitSurvey(intent) {
        submissionIntent.value = intent;
        form.submit();
    }

    function validateAllSteps() {
        for (let paneIndex = 0; paneIndex < panes.length; paneIndex += 1) {
            const pane = panes[paneIndex];
            showPane(paneIndex, false);
            if (!validatePane(pane)) {
                return false;
            }
        }

        return true;
    }

    form.querySelectorAll('.survey-limit-group').forEach(function (group) {
        group.addEventListener('change', function () { updateLimitGroup(group); });
        updateLimitGroup(group);
    });

    form.querySelectorAll('[data-channel-sales]').forEach(function (input) {
        input.addEventListener('input', updateChannelMix);
    });

    form.querySelectorAll('.territory-assessment').forEach(function (select) {
        select.addEventListener('change', updateTerritoryReason);
    });

    if (saveButton) {
        saveButton.addEventListener('click', function () {
            draftStatus.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Saving draft...';
            submitSurvey('draft');
        });
    }

    previousButton.addEventListener('click', function () {
        showPane(currentPaneIndex - 1, true);
    });

    nextButton.addEventListener('click', function () {
        if (!validatePane(panes[currentPaneIndex])) return;
        showPane(currentPaneIndex + 1, true);
    });

    finishButton.addEventListener('click', function () {
        if (!validateAllSteps()) return;
        submitSurvey('submit');
    });

    updateChannelMix();
    updateTerritoryReason();
    updateOtherFields();
    showPane(0, false);
    if (isSubmitted) {
        form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(function (field) {
            field.disabled = true;
        });
    }
    form.addEventListener('input', updateOtherFields);
    form.addEventListener('change', updateOtherFields);
});
</script>
@endsection

@section('content')
@php
    $input = function ($key, $default = null) use ($formData) {
        return old($key, data_get($formData, $key, $default));
    };
    $selectedValues = function ($key) use ($input) {
        return array_map('strval', (array) $input($key, []));
    };
    $distributorName = $input('distributor_name', optional($distributor)->name ?: optional(auth()->user())->name);
    $businessName = $input('business_name', optional($distributor)->business_name);
    $province = $input('province', optional($distributor)->location_province);
    $territoryNames = $territories->pluck('area_name')->filter()->values();
    $performanceMonths = collect($monthlyPerformance ?? [])->map(function ($row) {
        return [
            'month' => data_get($row, 'month', data_get($row, 'period_month', 'Month')),
            'sales_php' => (float) data_get($row, 'sales_php', 0),
            'volume_kg' => (float) data_get($row, 'volume_kg', 0),
            'target_php' => (float) data_get($row, 'target_php', 200000),
        ];
    })->values();

    $performanceTotalSales = $performanceMonths->sum('sales_php');
    $performanceAverageSales = $performanceMonths->count() ? $performanceTotalSales / $performanceMonths->count() : 0;
    $performanceAverageKg = $performanceMonths->count() ? $performanceMonths->avg('volume_kg') : 0;
    $performanceMonthsAtTarget = $performanceMonths->filter(function ($row) {
        return $row['sales_php'] >= $row['target_php'];
    })->count();
    $performanceBestMonth = $performanceMonths->sortByDesc('sales_php')->first();
    $performanceLowestMonth = $performanceMonths->sortBy('sales_php')->first();
    $performanceFirstSales = (float) data_get($performanceMonths->first(), 'sales_php', 0);
    $performanceLastSales = (float) data_get($performanceMonths->last(), 'sales_php', 0);
    $performanceTrend = $performanceLastSales > ($performanceFirstSales * 1.05)
        ? 'Growing'
        : ($performanceLastSales < ($performanceFirstSales * .95) ? 'Declining' : 'Stable');
@endphp

<div class="survey-page">
    <div class="survey-page-head">
        <div>
            <div class="survey-kicker"><i class="bi bi-clipboard2-check"></i> Partner insights</div>
            <h4 class="survey-page-title">Area Distributor Survey</h4>
        </div>
    </div>

    @if(session('success'))
        <div class="survey-complete-alert is-visible" role="status">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><strong>{{ session('success') }}</strong></div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-0" role="alert">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-0" role="alert">
            <strong>Please review the highlighted answers.</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="survey-modal survey-page-form" aria-labelledby="partnerSurveyTitle">
        <form id="partnerSurveyForm" class="modal-content" method="POST" action="{{ route('surveys.store') }}" novalidate>
            @csrf
            <input type="hidden" name="submission_intent" id="surveySubmissionIntent" value="draft">
            <div class="modal-header">
                <div class="survey-modal-heading">
                    <span class="survey-modal-icon"><i class="bi bi-clipboard-data"></i></span>
                    <div>
                        <h5 class="survey-modal-title" id="partnerSurveyTitle">Partner Growth and Experience Survey</h5>
                        <div class="survey-modal-subtitle">Please answer candidly. There are no right or wrong answers. PREI will conduct this survey every six months (semiannually) to create a consistent view of how Area Distributor performance, capability, competition, customer behavior, and partner experience are changing. </div>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <div class="survey-form-stage" id="surveyFormStage">
                    <section class="survey-pane is-active" data-survey-pane="0">
                        <div class="survey-pane-head">
                            <div class="survey-pane-kicker"><i class="bi bi-building"></i> Section 1</div>
                            <h2 class="survey-pane-title">Distributor profile</h2>
                            <p class="survey-pane-copy">Confirm the business details we have on record and describe the channels that currently contribute to Gaz Lite sales.</p>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div><h3 class="survey-question">Business information</h3><p class="survey-question-help">Information is prefilled based on your data record.</p></div>
                            </div>
                            <div class="survey-grid">
                                <div class="survey-field">
                                    <label for="surveyDistributorName">Area Distributor name <span class="survey-required">*</span></label>
                                    <input id="surveyDistributorName" class="form-control" name="distributor_name" value="{{ $distributorName }}" placeholder="Enter distributor name" required>
                                </div>
                                <div class="survey-field">
                                    <label for="surveyBusinessName">Business name <span class="survey-required">*</span></label>
                                    <input id="surveyBusinessName" class="form-control" name="business_name" value="{{ $businessName }}" placeholder="Enter registered or trading name" required>
                                </div>
                                <div class="survey-field">
                                    <label for="surveyProvince">Province <span class="survey-required">*</span></label>
                                    <input id="surveyProvince" class="form-control" name="province" value="{{ $province }}" placeholder="Enter province" required>
                                </div>
                                <div class="survey-field">
                                    <label for="surveyTerritories">Awarded Area / Territory</label>
                                    <!-- <input id="surveyTerritories" class="form-control survey-readonly" name="territories_display" value="{{ $input('territories_display', $territoryNames->implode(', ')) }}" placeholder="No awarded territory found" readonly> -->
                                    <input id="surveyTerritories" class="form-control survey-readonly" name="territories_display" placeholder="Enter awarded area" required>
                                </div>
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div><h3 class="survey-question">How long have you been a PREI Area Distributor? <span class="survey-required">*</span></h3></div>
                            </div>
                            <div class="survey-choice-grid">
                                @foreach(['under_6_months' => 'Less than 6 months', '6_to_12_months' => '6–12 months', '1_to_2_years' => '1–2 years', '2_to_3_years' => '2–3 years', 'over_3_years' => 'More than 3 years'] as $value => $label)
                                    <label class="survey-choice is-radio">
                                        <input type="radio" name="distributor_tenure" value="{{ $value }}" {{ (string) $input('distributor_tenure') === (string) $value ? 'checked' : '' }} required>
                                        <span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">Which channels currently contribute to your Gaz Lite business?</h3>
                                    <p class="survey-question-help">Please provide your best estimate for a typical month. Percentage contribution will be calculated automatically.</p>
                                </div>
                                <!-- <span class="survey-counter" id="channelTotalBadge">₱0 total</span> -->
                            </div>
                            <div class="survey-table-wrap">
                                <table class="table survey-table align-middle">
                                    <thead><tr><th>Sales channel</th><th>Estimated monthly sales</th><th>%</th><th>Estimated # Active customers</th></tr></thead>
                                    <tbody>
                                        @foreach($channels as $channelCode => $channelName)
                                            <tr>
                                                <td>
                                                    <strong>{{ $channelName }}</strong>
                                                    @if($channelCode === 'other')
                                                        <div
                                                            class="survey-conditional"
                                                            data-other-selection="channel_sales[other]|channel_customers[other]"
                                                            data-other-mode="positive">
                                                            <input
                                                                class="form-control"
                                                                name="channel_other_label"
                                                                value="{{ $input('channel_other_label') }}"
                                                                placeholder="Specify other channel">
                                                        </div>
                                                    @endif
                                                </td>
                                                <td><input class="form-control" type="number" name="channel_sales[{{ $channelCode }}]" value="{{ $input('channel_sales.' . $channelCode) }}" min="0" step="0.01" placeholder="0.00" data-channel-sales="{{ $channelCode }}"></td>
                                                <td><span class="survey-channel-percent" data-channel-percent="{{ $channelCode }}">0%</span></td>
                                                <td><input class="form-control" type="number" name="channel_customers[{{ $channelCode }}]" value="{{ $input('channel_customers.' . $channelCode) }}" min="0" step="1" placeholder="0"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="survey-pane" data-survey-pane="1">
                        <div class="survey-pane-head">
                            <div class="survey-pane-kicker"><i class="bi bi-graph-up-arrow"></i> Section 2</div>
                            <h2 class="survey-pane-title">Performance and growth</h2>
                            <p class="survey-pane-copy">Tell us how the business is moving, what is limiting growth, and how achievable the PHP 200,000 monthly target is.</p>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">B. Actual AD Performance - Last 6 Months</h3>
                                    <p class="survey-question-help">
                                        Sale order records from {{ $periodStart->format('M Y') }} through {{ $periodEnd->format('M Y') }}.
                                    </p>
                                </div>
                            </div>

                            <div class="survey-table-wrap">
                                <table class="table survey-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Sales</th>
                                            <th>Volume</th>
                                            <th>PHP 200K target</th>
                                            <th>Month-on-month</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $previousSales = null; @endphp
                                        @foreach($performanceMonths as $performanceMonth)
                                            @php
                                                $hasPreviousMonth = $previousSales !== null;
                                                $previousMonthWasZero = $hasPreviousMonth && (float) $previousSales === 0.0;
                                                $monthGrowth = $hasPreviousMonth && !$previousMonthWasZero
                                                    ? (($performanceMonth['sales_php'] - $previousSales) / $previousSales) * 100
                                                    : null;
                                                $targetStatus = $performanceMonth['sales_php'] > $performanceMonth['target_php']
                                                    ? 'above'
                                                    : ($performanceMonth['sales_php'] == $performanceMonth['target_php'] ? 'met' : 'below');
                                                $targetLabel = [
                                                    'above' => 'Above target',
                                                    'met' => 'Target met',
                                                    'below' => 'Below target',
                                                ][$targetStatus];
                                                $targetIcon = $targetStatus === 'below' ? 'bi-dash-circle' : 'bi-check-circle';
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $performanceMonth['month'] }}</strong></td>
                                                <td>₱{{ number_format($performanceMonth['sales_php'], 2) }}</td>
                                                <td>{{ number_format($performanceMonth['volume_kg'], 3) }} KG</td>
                                                <td>
                                                    <span class="survey-target-badge is-{{ $targetStatus }}">
                                                        <i class="bi {{ $targetIcon }}"></i>
                                                        {{ $targetLabel }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if(!$hasPreviousMonth)
                                                        <span class="text-muted">Starting month</span>
                                                    @elseif($previousMonthWasZero && $performanceMonth['sales_php'] > 0)
                                                        <span class="survey-growth-value is-positive">
                                                            <i class="bi bi-arrow-up-right"></i>
                                                            Growth from ₱0
                                                        </span>
                                                    @elseif($previousMonthWasZero || $monthGrowth == 0)
                                                        <span class="text-muted">-</span>
                                                    @else
                                                        <span class="survey-growth-value {{ $monthGrowth >= 0 ? 'is-positive' : 'is-negative' }}">
                                                            <i class="bi {{ $monthGrowth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                                            {{ $monthGrowth > 0 ? 'Growth' : 'Decrease' }}
                                                            {{ number_format(abs($monthGrowth), 1) }}%
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @php $previousSales = $performanceMonth['sales_php']; @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="survey-performance-summary">
                                <table aria-label="Six-month performance summary">
                                    <tbody>
                                        <tr>
                                            <th scope="row">Total sales</th>
                                            <td>₱{{ number_format($performanceTotalSales, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Average monthly sales</th>
                                            <td>₱{{ number_format($performanceAverageSales, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Average monthly KG</th>
                                            <td>{{ number_format($performanceAverageKg, 3) }} KG</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Months at PHP 200K+</th>
                                            <td>{{ $performanceMonthsAtTarget }} / {{ $performanceMonths->count() }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Best month</th>
                                            <td>
                                                {{ data_get($performanceBestMonth, 'month', '—') }} -
                                                <span class="performance-summary-sales is-best">₱{{ number_format((float) data_get($performanceBestMonth, 'sales_php', 0), 2) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Lowest month</th>
                                            <td>
                                                {{ data_get($performanceLowestMonth, 'month', '—') }} -
                                                <span class="performance-summary-sales is-lowest">₱{{ number_format((float) data_get($performanceLowestMonth, 'sales_php', 0), 2) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Six-month trend</th>
                                            <td>{{ $performanceTrend }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">what are the main reasons your sales increased, decreased, or remained stable during the last six months?</h3>
                                </div>
                            </div>
                            <div class="survey-field"><textarea class="form-control" name="performance_reasons" placeholder="Tell us about your performance under that 6 months." style="resize: none;" required>{{ $input('performance_reasons') }}</textarea></div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">
                                        How would you describe the performance of your Gaz Lite business over the past 6 months? <span class="survey-required">*</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="survey-choice-grid">
                                @foreach(['declining_significantly' => 'Declining significantly', 'declining_slightly' => 'Declining slightly', 'stable' => 'About the same', 'growing_slightly' => 'Growing slightly', 'growing_strongly' => 'Growing strongly'] as $value => $label)
                                    <label class="survey-choice is-radio">
                                        <input type="radio" name="business_performance" value="{{ $value }}" {{ (string) $input('business_performance') === (string) $value ? 'checked' : '' }} required>
                                        <span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <fieldset class="survey-card survey-limit-group" data-min="3" data-max="3">
                            <div class="survey-card-title">
                                <div><legend class="survey-question">What are the THREE factors currently limiting your Gaz Lite growth? <span class="survey-required">*</span></legend><p class="survey-question-help">Choose the issues with the greatest impact right now.</p></div>
                                <span class="survey-counter" data-limit-counter>0 of 3 selected</span>
                            </div>
                            <div class="survey-choice-grid">
                                @foreach([
                                    'our_srp' => 'Our SRP', 'competitor_pricing' => 'Competitor pricing', 'competitor_swapping' => 'Competitor cylinder/canister swapping',
                                    'too_many_brands' => 'Too many competing brands', 'consumer_awareness' => 'Low consumer awareness', 'not_enough_retailers' => 'Not enough retailers',
                                    'retailer_interest' => 'Low retailer interest', 'consumer_demand' => 'Low consumer demand', 'repeat_frequency' => 'Low repeat/refill frequency',
                                    'availability' => 'Product availability', 'delivery' => 'Delivery/service limitations', 'working_capital' => 'Limited working capital',
                                    'manpower' => 'AD manpower', 'territory_size' => 'Territory is too small', 'prei_support' => 'PREI sales/marketing support',
                                    'margin' => 'Margin/profitability', 'other' => 'Other'
                                ] as $value => $label)
                                    <label class="survey-choice">
                                        <input type="checkbox" name="growth_constraints[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues('growth_constraints'), true) ? 'checked' : '' }}>
                                        <span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="survey-field survey-conditional" data-other-selection="growth_constraints[]"><label for="growthConstraintOther">Other limiting factor</label><input id="growthConstraintOther" class="form-control" name="growth_constraint_other" value="{{ $input('growth_constraint_other') }}"></div>
                        </fieldset>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">
                                        Do you believe each of your current territory/ies individually can sustainably generate at least PHP 200,000 in Gaz Lite sales per month?
                                        <span class="survey-required">*</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="survey-choice-grid">
                                @foreach(['definitely_yes' => 'Definitely yes', 'probably_yes' => 'Probably yes', 'unsure' => 'Unsure', 'probably_no' => 'Probably no', 'definitely_no' => 'Definitely no'] as $value => $label)
                                    <label class="survey-choice is-radio">
                                        <input
                                            class="territory-assessment"
                                            type="radio"
                                            name="territory_assessment"
                                            value="{{ $value }}"
                                            {{ (string) $input('territory_assessment') === (string) $value ? 'checked' : '' }}
                                            required>
                                        <span class="survey-choice-card">
                                            <span class="survey-choice-mark"><i class="bi bi-check"></i></span>
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <div id="territoryReasonWrap" class="survey-field survey-conditional">
                                <label for="territoryReason">If no, why not?</label>
                                <textarea id="territoryReason" class="form-control" name="territory_target_reason" placeholder="Describe the reason">{{ $input('territory_target_reason') }}</textarea>
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">What would help you reach or consistently exceed PHP 200,000 per month? Select up to THREE. <span class="survey-required">*</span></h3></div></div>
                            <fieldset class="survey-limit-group" data-min="1" data-max="3">
                                <div class="survey-choice-grid">
                                    @foreach(['active_retailers' => 'More active retailers', 'sales_per_retailer' => 'Higher sales per retailer', 'household_customers' => 'More household customers', 'direct_selling' => 'More house-to-house/direct selling', 'consumer_pricing' => 'Better consumer pricing', 'ad_margin' => 'Better AD margin', 'marketing_support' => 'More marketing support', 'promotions' => 'Promotions', 'availability' => 'Better product availability', 'delivery' => 'Faster/more reliable delivery', 'working_capital' => 'Additional working capital', 'training' => 'Sales/business training', 'territory' => 'Additional territory', 'salesperson_support' => 'More PREI salesperson support', 'other' => 'Other'] as $value => $label)
                                        <label class="survey-choice"><input type="checkbox" name="growth_target_supports[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues('growth_target_supports'), true) ? 'checked' : '' }}><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>
                                    @endforeach
                                </div>
                                <div class="survey-field survey-conditional" data-other-selection="growth_target_supports[]"><label for="growthTargetSupportOther">Others</label><input id="growthTargetSupportOther" class="form-control" name="growth_target_support_other" value="{{ $input('growth_target_support_other') }}"></div>
                            </fieldset>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">If PREI could help remove your biggest constraint, how confident are you that the business could grow significantly? <span class="survey-required">*</span></h3>
                                    <p class="survey-question-help">1 - Not Confident 5 - Very Confident</p>
                                </div>
                            </div>

                            <div class="survey-choice-grid is-five">
                                @foreach([1 => 'Very low', 2 => 'Low', 3 => 'Moderate', 4 => 'High', 5 => 'Very high'] as $score => $label)
                                    <label class="survey-choice is-radio" title="{{ $label }}">
                                        <input type="radio" name="growth_confidence" value="{{ $score }}" {{ (string) $input('growth_confidence') === (string) $score ? 'checked' : '' }} required>
                                        <span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $score }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <section class="survey-pane" data-survey-pane="2">
                        <div class="survey-pane-head">
                            <div class="survey-pane-kicker"><i class="bi bi-truck"></i> Section 3</div>
                            <h2 class="survey-pane-title">Operating and delivery capability</h2>
                            <p class="survey-pane-copy">Help us understand current delivery coverage, available resources, and the operation’s readiness for higher sales.</p>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">Do you currently deliver Gaz Lite directly to customers? <span class="survey-required">*</span></h3></div></div>
                            <div class="survey-choice-grid is-three">
                                @foreach(['regularly' => 'Yes, regularly', 'occasionally' => 'Yes, occasionally', 'customer_pickup' => 'No, customers pick up'] as $value => $label)
                                    <label class="survey-choice is-radio"><input type="radio" name="direct_delivery" value="{{ $value }}" {{ (string) $input('direct_delivery') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </div>

                        <fieldset class="survey-card">
                            <div class="survey-card-title"><div><legend class="survey-question">How are Gaz Lite orders currently delivered to your customers? Select all that apply. <span class="survey-required">*</span></legend></div></div>
                            <div class="survey-choice-grid">
                                @foreach(['own_motorcycles' => 'Own motorcycle/s', 'own_tricycles' => 'Own tricycle/s', 'own_car_van' => 'Own car/van', 'own_truck' => 'Own truck', 'own_personnel' => 'Own delivery personnel/riders', 'third_party' => 'Third-party delivery service', 'prei_help' => 'Ask PREI personnel to help', 'customer_pickup' => 'Customers pick up', 'other' => 'Other'] as $value => $label)
                                    <label class="survey-choice"><input type="checkbox" name="delivery_methods[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues('delivery_methods'), true) ? 'checked' : '' }}><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                            <div class="survey-field survey-conditional" data-other-selection="delivery_methods[]"><label for="deliveryMethodOther">Other delivery method</label><input id="deliveryMethodOther" class="form-control" name="delivery_method_other" value="{{ $input('delivery_method_other') }}"></div>
                        </fieldset>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">How often do you rely on PREI personnel to help with deliveries? <span class="survey-required">*</span></h3></div></div>
                            <div class="survey-choice-grid is-five">
                                @foreach(['never' => 'Never', 'rarely' => 'Rarely', 'sometimes' => 'Sometimes', 'often' => 'Often', 'most_of_time' => 'Most of the time'] as $value => $label)
                                    <label class="survey-choice is-radio"><input type="radio" name="prei_delivery_reliance" value="{{ $value }}" {{ (string) $input('prei_delivery_reliance') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">
                                        What is the farthest distance from your business location that you currently deliver Gaz Lite?
                                        <span class="survey-required">*</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="survey-field">
                                <div class="input-group">
                                    <input id="currentDistance" class="form-control" type="number" name="current_delivery_km" value="{{ $input('current_delivery_km') }}" min="0" step="0.1" placeholder="Enter distance e.g 5km" aria-label="Current farthest delivery distance" required>
                                    <span class="input-group-text">km</span>
                                </div>
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">
                                        If there is sufficient customer demand, what is the farthest distance you could realistically serve using your current delivery resources?
                                        <span class="survey-required">*</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="survey-field">
                                <div class="input-group">
                                    <input id="possibleDistance" class="form-control" type="number" name="possible_delivery_km" value="{{ $input('possible_delivery_km') }}" min="0" step="0.1" placeholder="Enter distance e.g 2km" aria-label="Realistic maximum delivery distance" required>
                                    <span class="input-group-text">km</span>
                                </div>
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">
                                        Approximately how many times do you deliver Gaz Lite in a typical week?
                                        <span class="survey-required">*</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="survey-field">
                                <select id="weeklyDeliveries" class="form-select" name="weekly_deliveries" required>
                                    <option value="">Select range</option>
                                    @foreach(['none' => 'None', '1_to_10' => '1–10', '11_to_25' => '11–25', '26_to_50' => '26–50', '51_to_100' => '51–100', 'over_100' => 'More than 100'] as $value => $label)
                                        <option value="{{ $value }}" {{ (string) $input('weekly_deliveries') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">How quickly can you normally fulfill an order within your regular service area? <span class="survey-required">*</span></h3></div></div>
                            <div class="survey-choice-grid">
                                @foreach(['within_30_minutes' => 'Within 30 minutes', '31_to_60_minutes' => '31-60 minutes', '1_to_2_hours' => '1-2 hours', 'same_day' => 'Within the same day', 'next_day_or_longer' => 'Next day or longer', 'depends_on_location' => 'Depends on location'] as $value => $label)
                                    <label class="survey-choice is-radio"><input type="radio" name="fulfillment_speed" value="{{ $value }}" {{ (string) $input('fulfillment_speed') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">What delivery resources do you currently have available?</h3><p class="survey-question-help">Enter zero when a resource is not available.</p></div></div>
                            <div class="survey-number-grid">
                                @foreach(['motorcycles' => 'Motorcycles', 'tricycles' => 'Tricycles', 'cars_vans' => 'Cars / vans', 'trucks' => 'Trucks', 'delivery_riders' => 'Delivery personnel / riders', 'other' => 'Other'] as $value => $label)
                                    <div class="survey-number-field"><label for="resource_{{ $value }}">{{ $label }}</label><input id="resource_{{ $value }}" class="form-control" type="number" name="resources[{{ $value }}]" min="0" step="1" value="{{ $input('resources.' . $value, 0) }}" required></div>
                                @endforeach
                            </div>
                            <div class="survey-field survey-conditional" data-other-selection="resources[other]" data-other-mode="positive"><label for="resourceOtherLabel">Specify other resource</label><input id="resourceOtherLabel" class="form-control" name="resource_other_label" value="{{ $input('resource_other_label') }}"></div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">How many people currently work on your Gaz Lite business?</h3><p class="survey-question-help">Enter zero when a role is not present.</p></div></div>
                            <div class="survey-number-grid">
                                @foreach(['owner_manager' => 'Owner / Manager', 'salespeople' => 'Salespeople', 'delivery_personnel' => 'Delivery personnel / Riders', 'other_staff' => 'Other staff'] as $value => $label)
                                    <div class="survey-number-field"><label for="staffing_{{ $value }}">{{ $label }}</label><input id="staffing_{{ $value }}" class="form-control" type="number" name="staffing[{{ $value }}]" min="0" step="1" value="{{ $input('staffing.' . $value, 0) }}" required></div>
                                @endforeach
                            </div>
                        </div>

                        @foreach([
                            ['customer_acquisition_roles', 'Who actively looks for new Gaz Lite customers, retailers, or accounts?', ['full_time_salesperson' => 'Dedicated full-time salesperson/s', 'part_time_salesperson' => 'Part-time salesperson/s', 'owner_manager' => 'Owner/manager', 'delivery_personnel' => 'Delivery personnel also find customers', 'retailer_referrals' => 'Retailers/dealers refer customers', 'no_one' => 'No one actively looks for new customers', 'other' => 'Other'], 'customer_acquisition_other'],
                            ['order_methods', 'How do customers normally place orders with you?', ['walk_in' => 'Walk-in', 'phone_call' => 'Phone call', 'sms' => 'SMS/Text', 'messenger_pm' => 'Facebook/Messenger PM', 'messenger_gc' => 'Facebook/Messenger GC', 'viber' => 'Viber', 'whatsapp' => 'WhatsApp', 'retailers' => 'Through retailers/dealers', 'salesperson' => 'Salesperson takes the order', 'other' => 'Other'], 'order_method_other']
                        ] as $multi)
                            <fieldset class="survey-card">
                                <div class="survey-card-title">
                                    <div>
                                        <legend class="survey-question">{{ $multi[1] }} Select all that apply. <span class="survey-required">*</span></legend>
                                    </div>
                                </div>
                                <div class="survey-choice-grid">
                                @foreach($multi[2] as $value => $label)
                                <label class="survey-choice">
                                <input type="checkbox" name="{{ $multi[0] }}[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues($multi[0]), true) ? 'checked' : '' }}>
                                <span class="survey-choice-card">
                                    <span class="survey-choice-mark">
                                        <i class="bi bi-check"></i></span>{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            <div class="survey-field survey-conditional" data-other-selection="{{ $multi[0] }}[]"><label>Other</label><input class="form-control" name="{{ $multi[3] }}" value="{{ $input($multi[3]) }}"></div></fieldset>
                        @endforeach

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">If your Gaz Lite sales increased by approximately 50%, could your current operation handle the additional business? <span class="survey-required">*</span></h3></div></div><div class="survey-choice-grid">
                            @foreach(['current_resources' => 'Yes, with current resources', 'minor_resources' => 'Yes, with minor additional resources', 'significant_investment' => 'Yes, but significant investment would be required', 'no' => 'No', 'unsure' => 'Unsure'] as $value => $label)<label class="survey-choice is-radio"><input type="radio" name="capacity_for_50_percent_growth" value="{{ $value }}" {{ (string) $input('capacity_for_50_percent_growth') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach
                        </div></div>

                        <fieldset class="survey-card survey-limit-group" data-min="3" data-max="3">
                            <div class="survey-card-title"><div><legend class="survey-question">What would be your three biggest constraints if sales increased significantly? <span class="survey-required">*</span></legend></div><span class="survey-counter" data-limit-counter>0 of 3 selected</span></div>
                            <div class="survey-choice-grid">
                                @foreach(['working_capital' => 'Working capital', 'inventory' => 'Inventory capacity', 'storage' => 'Storage capacity', 'vehicles' => 'Delivery vehicles', 'delivery_people' => 'Delivery personnel', 'sales_manpower' => 'Sales manpower', 'order_processing' => 'Order processing/administration', 'geography' => 'Distance/geographic coverage', 'availability' => 'Product availability', 'collections' => 'Collections/credit', 'new_customers' => 'Finding new customers', 'new_retailers' => 'Finding new retailers', 'none' => 'No major constraint', 'other' => 'Other'] as $value => $label)
                                    <label class="survey-choice"><input type="checkbox" name="operational_constraints[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues('operational_constraints'), true) ? 'checked' : '' }}><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                            <div class="survey-field survey-conditional" data-other-selection="operational_constraints[]"><label for="operationalConstraintOther">Other constraint</label><input id="operationalConstraintOther" class="form-control" name="operational_constraint_other" value="{{ $input('operational_constraint_other') }}"></div>
                        </fieldset>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">Overall, how confident are you that your current business operation can support significantly higher Gaz Lite sales? <span class="survey-required">*</span></h3><p class="survey-question-help">1 = Very low capability, 5 = Very high capability</p></div></div><div class="survey-choice-grid is-five">
                            @for($score = 1; $score <= 5; $score++)<label class="survey-choice is-radio"><input type="radio" name="operational_confidence" value="{{ $score }}" {{ (string) $input('operational_confidence') === (string) $score ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $score }}</span></label>@endfor
                        </div></div>
                    </section>

                    <section class="survey-pane" data-survey-pane="3">
                        <div class="survey-pane-head">
                            <div class="survey-pane-kicker"><i class="bi bi-stars"></i> Section 4</div>
                            <h2 class="survey-pane-title">Why Gaz Lite Wins</h2>
                            <p class="survey-pane-copy">Choose why consumers stay with Gaz Lite, then rate PREI's people, products, promises and business support.</p>
                        </div>

                        <fieldset class="survey-card survey-limit-group" data-min="3" data-max="3">
                            <div class="survey-card-title">
                                <div>
                                    <legend class="survey-question">What are the THREE reasons consumers choose or stay with Gaz Lite? <span class="survey-required">*</span></legend>
                                    <p class="survey-question-help">Select exactly three reasons.</p>
                                </div>
                                <span class="survey-counter" data-limit-counter>0 of 3 selected</span>
                            </div>
                            <div class="survey-choice-grid">
                                @foreach(['quality' => 'Product quality', 'safety' => 'Safety/confidence', 'brand_trust' => 'Brand trust', 'refillable' => 'Refillable format', 'availability' => 'Availability', 'price_value' => 'Price/value', 'convenience' => 'Convenience', 'retailer_recommendation' => 'Retailer recommendation', 'ad_service' => 'AD service', 'prei_reputation' => 'PREI reputation', 'product_size' => 'Product size', 'delivery_service' => 'Delivery/service', 'other' => 'Other'] as $value => $label)
                                    <label class="survey-choice">
                                        <input type="checkbox" name="consumer_reasons[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues('consumer_reasons'), true) ? 'checked' : '' }}>
                                        <span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="survey-field survey-conditional" data-other-selection="consumer_reasons[]"><label for="consumerReasonOther">Other reason</label><input id="consumerReasonOther" class="form-control" name="consumer_reason_other" value="{{ $input('consumer_reason_other') }}"></div>
                        </fieldset>

                        @foreach([
                            'people' => ['People', ['sales_support' => 'Sales representative support', 'responsiveness' => 'Responsiveness to concerns', 'knowledge' => 'Knowledge and competence', 'growth_help' => 'Help in growing your business', 'communication' => 'Communication']],
                            'products' => ['Products', ['quality' => 'Product quality', 'availability' => 'Product availability', 'value' => 'Consumer value proposition', 'competitiveness' => 'Competitiveness versus alternatives', 'acceptance' => 'Consumer acceptance']],
                            'promises' => ['Promises', ['delivery_reliability' => 'Delivery reliability', 'order_accuracy' => 'Accuracy of orders and deliveries', 'commitments' => 'Fulfillment of commitments', 'incentive_fulfillment' => 'Incentive/program fulfillment', 'problem_handling' => 'Handling of problems or claims', 'pricing_communication' => 'Pricing communication']],
                        ] as $matrixCode => $matrix)
                            <div class="survey-card">
                                <div class="survey-card-title"><div><h3 class="survey-question">{{ $matrix[0] }}</h3><p class="survey-question-help">1 = Very poor · 2 = Poor · 3 = Fair · 4 = Good · 5 = Excellent</p></div></div>
                                <div class="survey-table-wrap">
                                    <table class="table survey-table survey-matrix align-middle">
                                        <thead><tr><th>Area</th>@for($score = 1; $score <= 5; $score++)<th>{{ $score }}</th>@endfor</tr></thead>
                                        <tbody>
                                            @foreach($matrix[1] as $itemCode => $itemLabel)
                                                <tr>
                                                    <td><strong>{{ $itemLabel }}</strong></td>
                                                    @for($score = 1; $score <= 5; $score++)
                                                        <td><input class="survey-matrix-radio" type="radio" name="ratings[{{ $matrixCode }}][{{ $itemCode }}]" value="{{ $score }}" {{ (string) $input('ratings.' . $matrixCode . '.' . $itemCode) === (string) $score ? 'checked' : '' }} aria-label="{{ $itemLabel }}: {{ $score }}" required></td>
                                                    @endfor
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">How effective are the following PREI activities in helping you grow your business?</h3><p class="survey-question-help">1 = Not effective, 5 = Very effective, N/A = Have not experienced</p></div></div>
                            <div class="survey-table-wrap"><table class="table survey-table survey-matrix"><thead><tr><th>PREI support</th>@for($score = 1; $score <= 5; $score++)<th>{{ $score }}</th>@endfor<th>N/A</th></tr></thead><tbody>
                                @foreach(['sales_support' => 'Sales representative support', 'local_marketing' => 'Local marketing', 'promotions' => 'Promotions', 'retailer_activation' => 'Retailer activation', 'marketing_materials' => 'Marketing materials', 'training_support' => 'Training/business support', 'incentive_programs' => 'Incentive programs', 'digital_support' => 'Social media/digital support'] as $itemCode => $itemLabel)
                                    <tr><td><strong>{{ $itemLabel }}</strong></td>@for($score = 1; $score <= 5; $score++)<td><input class="survey-matrix-radio" type="radio" name="support_effectiveness[{{ $itemCode }}]" value="{{ $score }}" {{ (string) $input('support_effectiveness.' . $itemCode) === (string) $score ? 'checked' : '' }} required></td>@endfor<td><input class="survey-matrix-radio" type="radio" name="support_effectiveness[{{ $itemCode }}]" value="na" {{ (string) $input('support_effectiveness.' . $itemCode) === 'na' ? 'checked' : '' }} required></td></tr>
                                @endforeach
                            </tbody></table></div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">Which THREE types of support should PREI focus more on during 2027? Rank them. <span class="survey-required">*</span></h3></div></div>
                            <div class="survey-grid is-three">
                                @for($rank = 0; $rank < 3; $rank++)
                                    <div class="survey-field">
                                        <label for="supportPriority{{ $rank }}">Rank {{ $rank + 1 }}</label>
                                        <select id="supportPriority{{ $rank }}" class="form-select" name="support_priorities[{{ $rank }}]" required>
                                            <option value="">Select support</option>
                                            @foreach(['advertising' => 'Consumer advertising', 'community_marketing' => 'Local/community marketing', 'retailer_acquisition' => 'Retailer acquisition', 'household_acquisition' => 'Household/customer acquisition', 'promotions' => 'Price promotions', 'ad_incentives' => 'AD incentives', 'retailer_incentives' => 'Retailer incentives', 'training' => 'Training/business development', 'sales_manpower' => 'Sales manpower', 'digital' => 'Digital/social media', 'logistics' => 'Delivery/logistics support', 'other' => 'Other'] as $value => $label)
                                                <option value="{{ $value }}" {{ (string) $input('support_priorities.' . $rank) === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="survey-conditional" data-other-selection="support_priorities[{{ $rank }}]">
                                            <label for="supportPriorityOther{{ $rank }}">Specify other support</label>
                                            <input id="supportPriorityOther{{ $rank }}" class="form-control" name="support_priority_other[{{ $rank }}]" value="{{ $input('support_priority_other.' . $rank) }}">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">What is the most common consumer complaint about Gaz Lite?</h3></div></div>
                            <div class="survey-field"><textarea class="form-control" name="common_consumer_complaint" placeholder="Share the complaint you hear most often, or write None if there is no recurring complaint." required>{{ $input('common_consumer_complaint') }}</textarea></div>
                        </div>

                    </section>

                    <section class="survey-pane" data-survey-pane="4">
                        <div class="survey-pane-head">
                            <div class="survey-pane-kicker"><i class="bi bi-lightbulb"></i> Section 5</div>
                            <h2 class="survey-pane-title">Household opportunity, expansion and incentives</h2>
                            <p class="survey-pane-copy">Assess unreached households, adjacent territories, expansion readiness and the incentives that would motivate growth.</p>
                        </div>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">Do you believe there are households or communities in your territory that are not currently being effectively reached by Gaz Lite? <span class="survey-required">*</span></h3></div></div><div class="survey-choice-grid">
                            @foreach(['definitely_yes' => 'Definitely yes', 'probably_yes' => 'Probably yes', 'unsure' => 'Unsure', 'probably_no' => 'Probably no', 'definitely_no' => 'Definitely no'] as $value => $label)<label class="survey-choice is-radio"><input type="radio" name="household_reach" value="{{ $value }}" {{ (string) $input('household_reach') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach
                        </div></div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">How significant is the opportunity to grow through direct household or community selling? <span class="survey-required">*</span></h3></div></div>
                            <div class="survey-choice-grid is-five">
                                @foreach([1 => 'Very low', 2 => 'Low', 3 => 'Moderate', 4 => 'High', 5 => 'Very high'] as $score => $label)
                                    <label class="survey-choice is-radio" title="{{ $label }}"><input type="radio" name="household_opportunity" value="{{ $score }}" {{ (string) $input('household_opportunity') === (string) $score ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $score }}</span></label>
                                @endforeach
                            </div>
                        </div>

                        <fieldset class="survey-card"><div class="survey-card-title"><div><legend class="survey-question">Which areas offer the greatest opportunity to gain additional household customers? Select all that apply. <span class="survey-required">*</span></legend></div></div><div class="survey-choice-grid">
                            @foreach(['residential' => 'Residential communities', 'subdivisions' => 'Subdivisions', 'barangays' => 'Barangays / sitios', 'public_housing' => 'Public housing communities', 'rural' => 'Rural communities', 'workplaces' => 'Workplaces / employee communities', 'schools' => 'Schools / institutions', 'other' => 'Other'] as $value => $label)<label class="survey-choice"><input type="checkbox" name="household_opportunity_areas[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues('household_opportunity_areas'), true) ? 'checked' : '' }}><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach
                        </div><div class="survey-field survey-conditional" data-other-selection="household_opportunity_areas[]"><label>Other area</label><input class="form-control" name="household_opportunity_other" value="{{ $input('household_opportunity_other') }}"></div></fieldset>

                        <fieldset class="survey-card survey-limit-group" data-min="3" data-max="3"><div class="survey-card-title"><div><legend class="survey-question">What are the THREE barriers to reaching more households directly? <span class="survey-required">*</span></legend></div><span class="survey-counter" data-limit-counter>0 of 3 selected</span></div><div class="survey-choice-grid">
                            @foreach(['finding_customers' => 'Finding potential customers', 'awareness' => 'Consumer awareness', 'price' => 'Consumer price', 'competition' => 'Competition', 'delivery' => 'Delivery/logistics', 'manpower' => 'Manpower', 'trust' => 'Customer trust', 'repeat_frequency' => 'Repeat/refill frequency', 'collection' => 'Payment/collection', 'marketing' => 'Marketing support', 'connections' => 'Lack of local/community connections', 'other' => 'Other'] as $value => $label)<label class="survey-choice"><input type="checkbox" name="household_barriers[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues('household_barriers'), true) ? 'checked' : '' }}><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach
                        </div><div class="survey-field survey-conditional" data-other-selection="household_barriers[]"><label>Other barrier</label><input class="form-control" name="household_barrier_other" value="{{ $input('household_barrier_other') }}"></div></fieldset>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">Would you be interested in more direct household or community-based selling if PREI provided appropriate support? <span class="survey-required">*</span></h3></div></div><div class="survey-choice-grid">
                            @foreach(['definitely_yes' => 'Definitely yes', 'probably_yes' => 'Probably yes', 'maybe' => 'Maybe', 'probably_no' => 'Probably no', 'definitely_no' => 'Definitely no'] as $value => $label)<label class="survey-choice is-radio"><input type="radio" name="household_selling_interest" value="{{ $value }}" {{ (string) $input('household_selling_interest') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach
                        </div></div>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">Are there areas adjacent to your existing territory that you believe you could serve profitably? <span class="survey-required">*</span></h3></div></div><div class="survey-choice-grid is-three">
                            @foreach(['yes' => 'Yes', 'no' => 'No', 'unsure' => 'Unsure'] as $value => $label)<label class="survey-choice is-radio"><input type="radio" name="adjacent_areas_available" value="{{ $value }}" {{ (string) $input('adjacent_areas_available') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach
                        </div><div class="survey-field mt-3"><label for="adjacentAreaNames">If yes, which areas?</label><textarea id="adjacentAreaNames" class="form-control" name="adjacent_area_names">{{ $input('adjacent_area_names') }}</textarea></div></div>

                        <div class="survey-card">
                            <div class="survey-card-title"><div><h3 class="survey-question">Would you be interested in expanding your PREI business into additional territories if performance and capability requirements were met? <span class="survey-required">*</span></h3></div></div>
                            <div class="survey-choice-grid">
                                @foreach(['definitely_yes' => 'Definitely yes', 'probably_yes' => 'Probably yes', 'maybe' => 'Maybe', 'probably_no' => 'Probably no', 'definitely_no' => 'Definitely no'] as $value => $label)
                                    <label class="survey-choice is-radio"><input type="radio" name="expansion_interest" value="{{ $value }}" {{ (string) $input('expansion_interest') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </div>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">What is the biggest constraint preventing you from expanding? <span class="survey-required">*</span></h3></div></div><div class="survey-choice-grid">
                            @foreach(['capital' => 'Capital', 'manpower' => 'Manpower', 'delivery_logistics' => 'Delivery/logistics', 'demand_uncertainty' => 'Demand uncertainty', 'competition' => 'Competition', 'current_territory_potential' => 'Current territory still has significant growth potential', 'prei_support' => 'PREI support', 'profitability' => 'Profitability', 'no_interest' => 'No interest in expansion', 'other' => 'Other'] as $value => $label)<label class="survey-choice is-radio"><input type="radio" name="expansion_constraint" value="{{ $value }}" {{ (string) $input('expansion_constraint') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach
                        </div><div class="survey-field survey-conditional" data-other-selection="expansion_constraint"><label>Other constraint</label><input class="form-control" name="expansion_constraint_other" value="{{ $input('expansion_constraint_other') }}"></div></div>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">If you were to expand, when? <span class="survey-required">*</span></h3></div></div><div class="survey-field"><input class="form-control" name="expansion_timing" value="{{ $input('expansion_timing') }}" placeholder="Describe..." required></div></div>

                        @foreach([
                            ['motivating_incentives', 'Which THREE incentives would motivate you most to grow your Gaz Lite business?', ['cash' => 'Cash incentive', 'rebates' => 'Rebates/discounts', 'free_product' => 'Free product', 'marketing' => 'Marketing support', 'equipment' => 'Business equipment/support', 'territory' => 'Territory expansion opportunity', 'travel' => 'Travel incentive', 'recognition' => 'Recognition/awards', 'training' => 'Training/business development', 'other' => 'Other'], 'motivating_incentive_other'],
                            ['reward_achievements', 'Which THREE achievements should PREI reward most?', ['highest_sales' => 'Highest sales', 'kg_growth' => 'Highest KG growth', 'most_improved' => 'Most improved AD', 'sustain_200k' => 'Reaching/sustaining PHP 200K+ monthly sales', 'new_customers' => 'New household/customer acquisition', 'retailer_growth' => 'Retailer growth', 'territory_development' => 'Territory development', 'consistent' => 'Consistent year-round performance', 'other' => 'Other'], 'reward_achievement_other']
                        ] as $group)
                            <fieldset class="survey-card survey-limit-group" data-min="3" data-max="3"><div class="survey-card-title"><div><legend class="survey-question">{{ $group[1] }} <span class="survey-required">*</span></legend></div><span class="survey-counter" data-limit-counter>0 of 3 selected</span></div><div class="survey-choice-grid">@foreach($group[2] as $value => $label)<label class="survey-choice"><input type="checkbox" name="{{ $group[0] }}[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues($group[0]), true) ? 'checked' : '' }}><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach</div><div class="survey-field survey-conditional" data-other-selection="{{ $group[0] }}[]"><label>Other</label><input class="form-control" name="{{ $group[3] }}" value="{{ $input($group[3]) }}"></div></fieldset>
                        @endforeach
                    </section>

                    <section class="survey-pane" data-survey-pane="5">
                        <div class="survey-pane-head"><div class="survey-pane-kicker"><i class="bi bi-award"></i> Section 6</div><h2 class="survey-pane-title">Thanksgiving program and future relationship</h2><p class="survey-pane-copy">Tell PREI what makes its recognition program valuable and how you see the long-term partnership.</p></div>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">How motivating is PREI's annual Thanksgiving Program in encouraging you to grow your Gaz Lite business? <span class="survey-required">*</span></h3><p class="survey-question-help">1 = Not motivating, 5 = Very motivating</p></div></div><div class="survey-choice-grid is-five">@for($score = 1; $score <= 5; $score++)<label class="survey-choice is-radio"><input type="radio" name="thanksgiving_motivation" value="{{ $score }}" {{ (string) $input('thanksgiving_motivation') === (string) $score ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $score }}</span></label>@endfor</div></div>

                        @foreach([
                            ['thanksgiving_values', 'Which parts of the Thanksgiving Program are most valuable to you? Select up to THREE.', 1, ['recognition' => 'Recognition and awards', 'business_support' => 'Business support/equipment', 'networking' => 'Networking with other PREI partners', 'celebration' => 'Celebration/appreciation', 'other' => 'Other'], 'thanksgiving_value_other'],
                            ['thanksgiving_recognition', 'What achievements should PREI recognize during Thanksgiving? Select up to THREE.', 1, ['highest_sales' => 'Highest annual sales', 'highest_kg' => 'Highest KG volume', 'growth' => 'Highest sales/KG growth', 'most_improved' => 'Most improved AD', 'sustain_200k' => 'Consistently achieving PHP 200K+', 'new_customers' => 'New customer/household growth', 'retailer_growth' => 'Retailer growth', 'territory' => 'Territory development/expansion', 'consistent' => 'Consistent year-round performance', 'other' => 'Other'], 'thanksgiving_recognition_other'],
                            ['thanksgiving_event_activities', 'Which THREE activities would make the Thanksgiving / Partner Event more valuable?', 3, ['recognition' => 'Recognition and awards', 'business_updates' => 'PREI business updates and future plans', 'training' => 'Sales/business training', 'new_products' => 'New product information', 'best_practices' => 'Successful AD best-practice sharing', 'networking' => 'Networking with other ADs', 'market_insights' => 'Competitive/market insights', 'business_planning' => 'Business planning with PREI', 'consumer_trends' => 'Consumer/market trends', 'entertainment' => 'Entertainment/celebration', 'other' => 'Other'], 'thanksgiving_event_activity_other']
                        ] as $group)
                            <fieldset class="survey-card survey-limit-group" data-min="{{ $group[2] }}" data-max="3"><div class="survey-card-title"><div><legend class="survey-question">{{ $group[1] }} <span class="survey-required">*</span></legend></div><span class="survey-counter" data-limit-counter>0 of 3 selected</span></div><div class="survey-choice-grid">@foreach($group[3] as $value => $label)<label class="survey-choice"><input type="checkbox" name="{{ $group[0] }}[]" value="{{ $value }}" {{ in_array((string) $value, $selectedValues($group[0]), true) ? 'checked' : '' }}><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach</div><div class="survey-field survey-conditional" data-other-selection="{{ $group[0] }}[]"><label>Other</label><input class="form-control" name="{{ $group[4] }}" value="{{ $input($group[4]) }}"></div></fieldset>
                            @if($group[0] === 'thanksgiving_recognition')
                                <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">What ONE change would make the Thanksgiving Program more motivating for you? <span class="survey-required">*</span></h3></div></div><div class="survey-field"><textarea class="form-control" name="thanksgiving_change" required>{{ $input('thanksgiving_change') }}</textarea></div></div>
                                <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">How valuable is the annual PREI Thanksgiving / Partner Event itself? <span class="survey-required">*</span></h3><p class="survey-question-help">1 = Not valuable, 5 = Very valuable</p></div></div><div class="survey-choice-grid is-five">@for($score = 1; $score <= 5; $score++)<label class="survey-choice is-radio"><input type="radio" name="thanksgiving_event_value" value="{{ $score }}" {{ (string) $input('thanksgiving_event_value') === (string) $score ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $score }}</span></label>@endfor</div></div>
                            @endif
                        @endforeach

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">Overall, what should the PREI Thanksgiving Program become in the future? <span class="survey-required">*</span></h3></div></div><div class="survey-choice-grid">@foreach(['reward_celebration' => 'Mainly a reward and celebration program', 'sales_incentive' => 'Mainly a sales incentive program', 'recognition' => 'Mainly a recognition program', 'business_development' => 'Mainly a business development/learning event', 'combined' => 'A combination of incentives, recognition, learning and celebration', 'other' => 'Other'] as $value => $label)<label class="survey-choice is-radio"><input type="radio" name="thanksgiving_future" value="{{ $value }}" {{ (string) $input('thanksgiving_future') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach</div><div class="survey-field survey-conditional" data-other-selection="thanksgiving_future"><label>Other</label><input class="form-control" name="thanksgiving_future_other" value="{{ $input('thanksgiving_future_other') }}"></div></div>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">How likely are you to continue growing your business with PREI over the next 2-3 years? <span class="survey-required">*</span></h3></div></div><div class="survey-field"><label for="futureGrowthLikelihood">0 = Not at all likely, 10 = Extremely likely</label><select id="futureGrowthLikelihood" class="form-select" name="future_growth_likelihood" required><option value="">Select score</option>@for($score = 0; $score <= 10; $score++)<option value="{{ $score }}" {{ (string) $input('future_growth_likelihood') === (string) $score ? 'selected' : '' }}>{{ $score }}</option>@endfor</select></div></div>

                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">Compared with one year ago, your confidence in PREI as a long-term business partner is: <span class="survey-required">*</span></h3></div></div><div class="survey-choice-grid is-five">@foreach(['much_lower' => 'Much lower', 'slightly_lower' => 'Slightly lower', 'same' => 'About the same', 'higher' => 'Higher', 'much_higher' => 'Much higher'] as $value => $label)<label class="survey-choice is-radio"><input type="radio" name="partner_confidence_change" value="{{ $value }}" {{ (string) $input('partner_confidence_change') === (string) $value ? 'checked' : '' }} required><span class="survey-choice-card"><span class="survey-choice-mark"><i class="bi bi-check"></i></span>{{ $label }}</span></label>@endforeach</div></div>
                    </section>

                    <section class="survey-pane" data-survey-pane="6">
                        <div class="survey-pane-head"><div class="survey-pane-kicker"><i class="bi bi-chat-square-text"></i> Section 7</div><h2 class="survey-pane-title">Final management questions</h2><p class="survey-pane-copy">Share the specific actions and concerns PREI management should prioritize.</p></div>
                        <div class="survey-card">
                            <div class="survey-card-title">
                                <div>
                                    <h3 class="survey-question">What are the THREE things PREI could do that would help you grow your business over the next 12 months? <span class="survey-required">*</span></h3>
                                </div>
                            </div>
                            <div class="survey-ranked-inputs">
                                @for($rank = 0; $rank < 3; $rank++)
                                    <div class="survey-ranked-row">
                                        <label for="growthAction{{ $rank }}">{{ $rank + 1 }}.</label>
                                        <input id="growthAction{{ $rank }}" class="form-control" name="growth_actions[]" value="{{ $input('growth_actions.' . $rank) }}" required>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">If you were the leader of PREI for one day, what is the ONE thing you would change first? <span class="survey-required">*</span></h3></div></div><div class="survey-field"><textarea class="form-control" name="leader_change" placeholder="One thing that you would do first..." required>{{ $input('leader_change') }}</textarea></div></div>
                        <div class="survey-card"><div class="survey-card-title"><div><h3 class="survey-question">Is there anything else PREI management needs to know about your market, competitors, customers, or your experience as a PREI partner? <span class="survey-required">*</span></h3></div></div><div class="survey-field"><textarea class="form-control" name="management_note" placeholder="Share your thoughts..." required>{{ $input('management_note') }}</textarea></div></div>
                    </section>

                </div>
            </div>

            <div class="modal-footer">
                <!-- <div class="survey-footer-status" id="surveyDraftStatus"><i class="bi bi-circle-fill me-1"></i> {{ $isSubmitted ? 'Submitted ' . optional($survey->submitted_at)->format('M d, Y h:i A') : ($survey ? 'Saved draft loaded' : 'Not saved yet') }}</div> -->
                <!-- @if(!$isSubmitted)<button class="btn btn-light" id="surveySaveDraft" type="button"><i class="bi bi-cloud-arrow-down me-1"></i> Save draft</button>@else<span id="surveySaveDraft"></span>@endif -->
                <div class="survey-footer-actions">
                    <button class="btn btn-light" id="surveyPrevious" type="button" disabled>Previous</button>
                    <button class="btn btn-primary" id="surveyNext" type="button">Next</button>
                    <button class="btn btn-success d-none" id="surveyFinish" type="button" {{ $isSubmitted ? 'disabled' : '' }}><i class="bi bi-check2-circle me-1"></i> Submit survey</button>
                </div>
                <div class="survey-footer-progress">
                    <div class="survey-progress-copy">
                        <span id="surveyProgressCurrent">Section 1 of 7</span>
                    </div>
                    <div class="survey-progress">
                        <div
                            class="survey-progress-bar"
                            id="surveyProgressBar"
                            role="progressbar"
                            aria-label="Survey progress"
                            aria-valuemin="1"
                            aria-valuemax="7"
                            aria-valuenow="1"
                        ></div>
                    </div>
                </div>
            </div>
        </form>
</section>
</div>
@endsection
