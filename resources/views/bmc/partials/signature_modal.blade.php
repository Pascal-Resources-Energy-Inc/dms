@php
    $signatureRetry = (string) old('signature_accountability_id') === (string) $accountability['id'];
    $signatureItems = collect($accountability['items'] ?? []);
    $serialCount = $signatureItems->sum(function ($item) { return count($item['serials'] ?? []); });
@endphp
<div class="modal fade bmc-signature-modal" id="bmcSignature{{ $accountability['id'] }}" tabindex="-1" aria-labelledby="bmcSignatureTitle{{ $accountability['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content border-0" method="POST" action="{{ route('bmc.signature', $accountability['id']) }}" data-bmc-signature-form>
            @csrf
            <input type="hidden" name="signature_accountability_id" value="{{ $accountability['id'] }}">
            @if(!empty($signatureFromRequest))<input type="hidden" name="signature_from_request" value="1">@endif
            <input type="hidden" name="signature" data-bmc-signature-value>
            <div class="modal-header rounded-top" style="background: #3993d1; color: #fff;">
                <div><h5 class="modal-title text-white" id="bmcSignatureTitle{{ $accountability['id'] }}">Sign Accountability</h5><small>{{ $accountability['accountability_ref'] ?? '-' }}</small></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body">
                @if($signatureRetry && $errors->any())
                    <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="bg-light rounded p-3 mb-3">
                    <div class="mb-2"><strong class="text-muted">Borrower:</strong> {{ trim(($signatureBorrowing['borrower_first_name'] ?? '') . ' ' . ($signatureBorrowing['borrower_last_name'] ?? '')) ?: '-' }}</div>
                    <div><strong class="text-muted">Released Items:</strong>
                        @foreach($signatureItems as $item)
                            <span class="badge bg-info text-white">{{ $item['item_name'] ?? '-' }} &times; {{ $item['released_quantity'] ?? 0 }}</span>
                        @endforeach
                    </div>
                </div>
                <p class="text-muted small">Note any damage or missing parts you noticed at handover.</p>
                @foreach($signatureItems as $item)
                    @foreach($item['serials'] ?? [] as $serial)
                        <details class="border rounded px-3 py-2 mb-2" @if($serialCount === 1 || !empty($serial['borrower_remarks']) || $signatureRetry) open @endif>
                            <summary>Item: {{ $item['item_name'] ?? '-' }} - {{ $serial['serial_number'] ?? '-' }}</summary>
                            <label class="visually-hidden" for="bmcSignatureRemark{{ $accountability['id'] }}_{{ $serial['id'] }}">Remark for {{ $item['item_name'] ?? 'item' }}, serial {{ $serial['serial_number'] ?? '-' }}</label>
                            <textarea id="bmcSignatureRemark{{ $accountability['id'] }}_{{ $serial['id'] }}" name="borrower_remarks[{{ $serial['id'] }}]" class="form-control mt-2" rows="2" maxlength="2000" placeholder="Add a remark.">{{ $signatureRetry ? old('borrower_remarks.'.$serial['id'], '') : ($serial['borrower_remarks'] ?? '') }}</textarea>
                        </details>
                    @endforeach
                @endforeach
                <div class="text-center mt-3">
                    <canvas data-bmc-signature-canvas width="450" height="200" aria-label="Draw your signature" style="width: 100%; max-width: 450px; height: 200px; border: 2px dashed #cbd5e1; border-radius: 8px; background: #fff; touch-action: none;"></canvas>
                    <p class="text-muted small mt-2 mb-0">Draw your signature using a mouse or finger.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-secondary" data-bmc-signature-clear>Clear</button>
                <button type="submit" class="btn btn-primary">Save Signature</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('bmcSignature{{ $accountability['id'] }}');
        var form = modal.querySelector('[data-bmc-signature-form]');
        var canvas = modal.querySelector('[data-bmc-signature-canvas]');
        var context = canvas.getContext('2d');
        var drawing = false, signed = false;
        context.lineWidth = 2;
        context.lineCap = 'round';
        function point(event) {
            var rect = canvas.getBoundingClientRect();
            return { x: (event.clientX - rect.left) * canvas.width / rect.width, y: (event.clientY - rect.top) * canvas.height / rect.height };
        }
        canvas.addEventListener('pointerdown', function (event) {
            event.preventDefault();
            drawing = true;
            canvas.setPointerCapture(event.pointerId);
            var p = point(event);
            context.beginPath();
            context.moveTo(p.x, p.y);
        });
        canvas.addEventListener('pointermove', function (event) {
            if (!drawing) return;
            var p = point(event);
            context.lineTo(p.x, p.y);
            context.stroke();
            signed = true;
        });
        canvas.addEventListener('pointerup', function () { drawing = false; });
        canvas.addEventListener('pointercancel', function () { drawing = false; });
        modal.querySelector('[data-bmc-signature-clear]').addEventListener('click', function () {
            context.clearRect(0, 0, canvas.width, canvas.height);
            signed = false;
        });
        form.addEventListener('submit', function (event) {
            if (!signed) {
                event.preventDefault();
                Swal.fire('Signature required', 'Please provide your signature before saving.', 'warning');
                return;
            }
            form.querySelector('[data-bmc-signature-value]').value = canvas.toDataURL('image/png');
            form.querySelector('button[type="submit"]').disabled = true;
        });
        @if($signatureRetry && $errors->any())
            bootstrap.Modal.getOrCreateInstance(modal).show();
        @endif
    });
</script>
