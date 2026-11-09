<link rel="stylesheet" href="{{ asset('css/return-refund-ris.css') }}?v={{ filemtime(public_path('css/return-refund-ris.css')) }}">
<dialog id="returnRefundRisDialog" class="rrr-ris-dialog" aria-labelledby="rrrRisTitle" aria-describedby="rrrRisDescription">
    <form id="returnRefundRisForm" method="POST" enctype="multipart/form-data">
        @csrf
        <header class="rrr-ris-header">
            <div><h2 id="rrrRisTitle">Submit RIS documents</h2><p id="rrrRisReference"></p></div>
            <button type="button" class="rrr-ris-close" data-close-ris aria-label="Close RIS form">&times;</button>
        </header>
        <div class="rrr-ris-body">
            <p id="rrrRisDescription">Enter one RIS for the approved products below. The warehouse can then receive your return.</p>
            <section class="rrr-ris-products" aria-labelledby="rrrRisProductsTitle">
                <h3 id="rrrRisProductsTitle">Approved products</h3>
                <ul id="rrrRisProductList"></ul>
            </section>
            <div class="rrr-ris-fields">
                <div><label for="rrrRisNumber">RIS number <span aria-hidden="true">*</span></label><input id="rrrRisNumber" class="form-control" name="ris_number" maxlength="255" placeholder="Enter RIS number" required></div>
                <div><label for="rrrRisDate">RIS date <span aria-hidden="true">*</span></label><input id="rrrRisDate" class="form-control" type="date" name="ris_date" required></div>
            </div>
            <div class="rrr-ris-upload">
                <label for="rrrRisAttachments">Attachments <span aria-hidden="true">*</span></label>
                <input id="rrrRisAttachments" class="form-control" type="file" name="return_attachments[]" accept=".jpg,.jpeg,.png,.pdf" aria-describedby="rrrRisFileHelp" multiple required>
                <small id="rrrRisFileHelp">JPG, PNG, or PDF. Up to 5 files, 5 MB each.</small>
            </div>
            <div id="rrrRisErrors" class="rrr-ris-errors d-none" role="alert" tabindex="-1"></div>
        </div>
        <footer class="rrr-ris-footer">
            <button type="button" class="btn btn-outline-secondary" data-close-ris>Cancel</button>
            <button type="submit" class="btn btn-success" id="rrrRisSubmit">Submit to warehouse</button>
        </footer>
    </form>
</dialog>
