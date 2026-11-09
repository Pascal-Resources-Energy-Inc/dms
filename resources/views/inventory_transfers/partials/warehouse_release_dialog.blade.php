<dialog id="pullOutReleaseDialog" class="dr-dialog" aria-labelledby="drReleaseTitle" aria-describedby="drReleaseHelp">
    <form method="POST" id="pullOutReleaseForm">
        @csrf
        <header class="dr-dialog-head">
            <div><h2 id="drReleaseTitle">Release products</h2><span id="drReleaseReference"></span></div>
            <button type="button" class="btn btn-sm btn-light dr-close js-close-dr" aria-label="Close release form">&times;</button>
        </header>
        <div class="dr-dialog-body">
            <div class="dr-receipt">
                <label for="drReleaseChoice">Delivery receipt number <small>required</small></label>
                <select id="drReleaseChoice" class="form-select" aria-describedby="drNumberHelp" required autofocus disabled>
                    <option value="">Loading delivery receipts…</option>
                </select>
                <input id="drReleaseOrder" type="hidden" name="release_ad_purchase_order_id">
                <div id="drManualEntry" hidden>
                    <label for="drReleaseNumber" class="visually-hidden">New delivery receipt number</label>
                    <input id="drReleaseNumber" class="form-control" name="replacement_dr_number" maxlength="255" placeholder="Enter new DR number" aria-describedby="drNumberHelp">
                </div>
                <p id="drNumberHelp" role="status">Loading this distributor's For Delivery DPOs…</p>
                <button type="button" class="btn btn-light btn-sm" id="drRetryReceipts" hidden>Retry loading DR numbers</button>
            </div>
            <section class="dr-selection" aria-labelledby="drProductsTitle">
                <div class="dr-products-toolbar">
                    <h3 id="drProductsTitle">Products <span class="queue-total" id="drProductCount"></span></h3>
                    <div class="queue-fill-actions"><button type="button" class="btn btn-outline-primary btn-sm" id="drFillAll">Fill all</button>
                        <button type="button" class="btn btn-light btn-sm" id="drClear" disabled>Clear</button></div>
                </div>
                <div class="dr-columns" aria-hidden="true"><span>Product</span><span>Approved</span><span>Release qty</span><span>Remaining</span></div>
                <div id="drReleaseProducts"></div>
                <p class="dr-balance-help" id="drReleaseHelp">Remaining quantities stay on this request for a later release.</p>
            </section>
        </div>
        <footer class="dr-dialog-foot">
            <div class="dr-release-summary"><strong id="drReleaseTotal" aria-live="polite">0 products · 0 units</strong></div>
            <div class="dr-footer-actions"><button type="button" class="btn btn-light js-close-dr">Cancel</button>
                <button type="submit" id="drReleaseSubmit" class="btn btn-primary" disabled>Release products</button></div>
        </footer>
    </form>
</dialog>
<template id="drReleaseLine">
    <div class="dr-product" role="group">
        <div class="dr-product-name"><strong></strong><small class="dr-product-sku"></small></div>
        <input type="hidden" class="dr-line-id">
        <div class="dr-approved"><span class="dr-field-label">Approved</span><span class="dr-approved-value"><b class="dr-approved-count"></b><small>pcs</small></span></div>
        <div class="dr-quantity-field"><label><span class="dr-field-label">Release qty</span>
            <span class="queue-quantity-input"><input type="number" class="form-control dr-line-qty" min="0" step="1" inputmode="numeric" placeholder="0"><span aria-hidden="true">pcs</span></span></label>
            <small class="dr-quantity-limit visually-hidden"></small>
        </div>
        <div class="dr-remaining"><span class="dr-field-label">Remaining<span class="visually-hidden"> after release</span></span><span class="queue-balance-value"><b></b><small>pcs</small></span></div>
        <small class="dr-quantity-error" hidden></small>
    </div>
</template>
