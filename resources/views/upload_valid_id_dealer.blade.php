<div id="uploadIdModal" class="modal fade" tabindex="-1" aria-labelledby="bs-example-modal-md" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center">
        <h4 class="modal-title" id="myModalLabel">
          Upload Valid ID
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <form method='POST' action='{{url('valid-id-dealer/'.$dealer->id)}}' onsubmit='show()' enctype="multipart/form-data" class="validation-wizard wizard-circle mt-5">
      @csrf
        <input type="hidden" name="partner_record_type" value="{{ $partnerRecordType ?? 'dealer' }}">
        <div class="modal-body text-center">
          <div class="mb-3">
            <label for="valid_id_type" class="form-label">Select ID Type</label>
            <select class="form-select" id="valid_id_type" name="valid_id_type" required>
            <option selected disabled>Choose valid ID...</option>
            <option>Passport</option>
            <option>Driver's License</option>
            <option>SSS ID</option>
            <option>UMID</option>
            <option>PhilHealth ID</option>
            <option>PRC ID</option>
            <option>Postal ID</option>
            <option>National ID</option>
            <option>Voter's ID</option>
            <option>TIN ID</option>
            <option>Barangay Clearance</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="id_number" class="form-label">ID Number</label>
            <input type="text" class="form-control" id="id_number" name="id_number" placeholder="Enter ID number">
        </div>
        <div class="mb-3">
            <label for="id_file" class="form-label">Upload ID Image</label>
            <input type="file" class="form-control" id="id_file" name="id_file" accept="image/*,application/pdf" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn bg-danger-subtle text-danger  waves-effect"
          data-bs-dismiss="modal">
          Close
        </button>
        <button type="submit" class="btn bg-info-subtle text-info  waves-effect">
          Submit
        </button>
      </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
<!-- /.modal-dialog -->
</div>
