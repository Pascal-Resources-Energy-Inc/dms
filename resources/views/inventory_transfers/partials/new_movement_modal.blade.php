<div class="modal fade inventory-movement-modal" id="newMovementModal" tabindex="-1" aria-labelledby="newMovementModalTitle" aria-describedby="newMovementModalDescription" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="newMovementModalTitle">New Movement</h5>
                    <p id="newMovementModalDescription">Record products and quantities under one reference.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('inventory-transfers.store') }}" id="newMovementForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <strong>Please review the movement details.</strong>
                            <ul class="mb-0 mt-2">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                        </div>
                    @endif

                    @php
                        $transferDisabled = !$canTransfer;
                        $oldMovementType = old('movement_type', 'in');
                        $oldMovementType = $transferDisabled && $oldMovementType === 'transfer' ? 'in' : $oldMovementType;
                        $defaultFromArea = old('from_area') ?: ($areas->count() === 1 ? $areas->first() : null);
                        $defaultToArea = old('to_area') ?: ($areas->count() === 1 ? $areas->first() : null);
                    @endphp

                    <fieldset class="movement-type-fieldset">
                        <legend>Movement type</legend>
                        <div class="movement-type-grid" id="movementTypeGrid">
                            @foreach(['in' => ['Stock in', 'arrow-down'], 'out' => ['Stock out', 'arrow-up'], 'transfer' => ['Transfer', 'arrows-exchange']] as $value => $option)
                                <label class="movement-type-option {{ $oldMovementType === $value ? 'active' : '' }} {{ $value === 'transfer' && $transferDisabled ? 'is-disabled' : '' }}">
                                    <input type="radio" name="movement_type" value="{{ $value }}" {{ $oldMovementType === $value ? 'checked' : '' }} {{ $value === 'transfer' && $transferDisabled ? 'disabled' : '' }}>
                                    <i class="ti ti-{{ $option[1] }}" aria-hidden="true"></i>
                                    <span>{{ $option[0] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if($transferDisabled)<p class="movement-type-help">Transfers require at least two assigned areas.</p>@endif
                    </fieldset>

                    <div class="js-single-product" hidden>
                        <label for="productSelect">Product</label>
                        <select name="product_id" id="productSelect" class="form-control" data-selected-value="{{ old('product_id') }}">
                            <option value="">Select product</option>
                            @foreach($stockProducts as $product)<option value="{{ $product->id }}">{{ $product->sku ? $product->sku . ' - ' : '' }}{{ $product->product_name }}</option>@endforeach
                        </select>
                    </div>

                    <div class="movement-fields">
                        <div class="js-from-area">
                            <label for="fromAreaSelect">From area</label>
                            <select name="from_area" id="fromAreaSelect" class="form-control select2">
                                <option value="">Select source area</option>
                                @foreach($areas as $area)<option value="{{ $area }}" {{ $defaultFromArea === $area ? 'selected' : '' }}>{{ $area }}</option>@endforeach
                            </select>
                            <span class="available-pill" id="availableQty" hidden>Available: 0</span>
                            <div class="stock-warning" id="stockWarning"></div>
                        </div>
                        <div class="js-to-area">
                            <label for="toAreaSelect">To area</label>
                            <select name="to_area" id="toAreaSelect" class="form-control select2">
                                <option value="">Select receiving area</option>
                                @foreach($areas as $area)<option value="{{ $area }}" {{ $defaultToArea === $area ? 'selected' : '' }}>{{ $area }}</option>@endforeach
                            </select>
                        </div>
                        <div class="js-movement-reason">
                            <label id="movementReasonLabel" for="outTypeSelect">Reason</label>
                            <select name="out_type" id="outTypeSelect" class="form-control select2" data-selected-value="{{ old('out_type') }}"></select>
                            <div class="form-text" id="movementReasonHelp"></div>
                        </div>
                        <div class="js-transfer-date">
                            <label for="transferDateInput">Movement date</label>
                            <input type="date" name="transfer_date" id="transferDateInput" class="form-control" value="{{ old('transfer_date', date('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="movement-context-note js-out-adjustment-note" style="display:none;">This adjustment deducts stock from the selected source area.</div>
                    <div class="movement-context-note js-return-refund-note" style="display:none;">Products share one Return and Refund request, submitted to Sir Dennis for approval and RIS documents.</div>

                    <section class="movement-products-section js-movement-items" aria-labelledby="movementProductsHeading">
                        <div class="movement-products-heading">
                            <h6 id="movementProductsHeading">Products</h6>
                            
                        </div>
                        <div id="movementRows"></div>
                        <button type="button" id="addMovementRow" class="btn btn-sm btn-outline-primary mt-2"><i class="ti ti-plus" aria-hidden="true"></i> Add product</button>
                    </section>

                    <div class="js-pull-out-ris movement-documents" style="display:none;">
                        <h6>Pull-out documents</h6>
                        <p>One RIS covers all products. Partial releases stay on this request.</p>
                        <div class="movement-document-fields">
                            <div>
                                <label for="pullOutRisNumber">RIS number <span class="text-danger">*</span></label>
                                <input type="text" name="ris_number" id="pullOutRisNumber" class="form-control" value="{{ old('ris_number') }}" placeholder="Enter RIS number" disabled>
                            </div>
                            <div>
                                <label for="pullOutRisDate">RIS date <span class="text-danger">*</span></label>
                                <input type="date" name="ris_date" id="pullOutRisDate" class="form-control" value="{{ old('ris_date', date('Y-m-d')) }}" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="js-pull-out-attachments movement-attachments" style="display:none;">
                        <label for="pullOutAttachments">Signed RIS and supporting files <span class="text-danger">*</span></label>
                        <input type="file" name="pull_out_attachments[]" id="pullOutAttachments" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple disabled>
                        <p class="form-text">Up to 5 JPG, PNG or PDF files, 5 MB each.</p>
                    </div>

                    <div class="movement-notes">
                        <label for="movementRemarks">Remarks <span class="movement-optional">(optional)</span></label>
                        <textarea name="remarks" id="movementRemarks" class="form-control" rows="2" placeholder="Add a note for this movement">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="movement-save-summary" id="movementPreview" aria-live="polite">Select products and quantities.</div>
                    <div class="movement-save-actions">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveMovementButton">Save Movement</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
