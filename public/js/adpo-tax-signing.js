document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const section = document.getElementById('taxSigning');
    if (!section) return;
    const form = section.closest('form');
    const canvas = document.getElementById('taxSignatureCanvas');
    const context = canvas.getContext('2d');
    const data = document.getElementById('taxSignatureData');
    const consent = document.getElementById('taxConsent');
    const status = document.getElementById('taxSignatureStatus');
    const hint = document.getElementById('taxSignatureHint');
    const error = document.getElementById('taxSignatureError');
    let strokes = [], active = null, pointer = null;
    function redraw() {
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.strokeStyle = '#153255'; context.lineWidth = 2.2;
        context.lineCap = 'round'; context.lineJoin = 'round';
        strokes.forEach(function (stroke) {
            context.beginPath();
            stroke.forEach(function (p, i) { context[i ? 'lineTo' : 'moveTo'](p[0] * canvas.width, p[1] * canvas.height); });
            context.stroke();
        });
        hint.hidden = strokes.length > 0;
        data.value = strokes.length ? JSON.stringify(strokes) : '';
        status.textContent = strokes.length ? 'Signature captured · Confirm the declaration below' : 'Signature required before submission';
        status.classList.toggle('is-ready', strokes.length > 0);
    }
    function point(event) {
        const rect = canvas.getBoundingClientRect();
        return [Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width)), Math.max(0, Math.min(1, (event.clientY - rect.top) / rect.height))].map(function (v) { return Math.round(v * 10000) / 10000; });
    }
    canvas.addEventListener('pointerdown', function (event) {
        if (pointer !== null || (event.pointerType === 'mouse' && event.button !== 0)) return;
        event.preventDefault(); pointer = event.pointerId; canvas.setPointerCapture(pointer);
        active = [point(event)]; strokes.push(active); consent.checked = false; error.textContent = ''; redraw();
    });
    canvas.addEventListener('pointermove', function (event) {
        if (!active || event.pointerId !== pointer) return;
        event.preventDefault();
        if (strokes.reduce(function (sum, stroke) { return sum + stroke.length; }, 0) >= 4500) return;
        active.push(point(event)); redraw();
    });
    function finish(event) {
        if (event.pointerId !== pointer) return;
        if (active && active.length < 2) strokes.pop();
        active = null; pointer = null; redraw();
    }
    canvas.addEventListener('pointerup', finish); canvas.addEventListener('pointercancel', finish);
    function clear() { strokes = []; active = null; pointer = null; consent.checked = false; redraw(); }
    document.getElementById('taxClearSignature').addEventListener('click', clear);
    function invalidate(event) {
        if (event.target === consent || event.target === data || event.target.type === 'search' || event.target.id === 'productSearch') return;
        if (strokes.length) { clear(); status.textContent = 'Order or taxpayer details changed. Please sign again.'; }
    }
    form.addEventListener('input', invalidate);
    form.addEventListener('change', invalidate);
    form.addEventListener('submit', function (event) {
        let points = 0, length = 0;
        strokes.forEach(function (stroke) {
            points += stroke.length;
            stroke.forEach(function (p, i) { if (i) length += Math.hypot(p[0] - stroke[i - 1][0], p[1] - stroke[i - 1][1]); });
        });
        if (points < 8 || length < 0.15 || !consent.checked || form.dataset.submitting === '1') {
            event.preventDefault(); event.stopImmediatePropagation();
            if (form.dataset.submitting === '1') return;
            error.textContent = 'Please draw your signature and confirm the declaration before submitting.';
            section.scrollIntoView({behavior: 'smooth', block: 'center'}); canvas.focus({preventScroll: true});
        }
    }, true);
    redraw();
});
