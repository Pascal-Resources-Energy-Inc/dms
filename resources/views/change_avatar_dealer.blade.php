<div id="uploadAvatarModal" class="modal fade" tabindex="-1" aria-labelledby="bs-example-modal-md" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center">
        <h4 class="modal-title" id="myModalLabel">Change Avatar</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method='POST' action='{{url('change-avatar-dealer/'.$dealer->id)}}' onsubmit='show()' enctype="multipart/form-data" class="validation-wizard wizard-circle mt-5">
        @csrf
        <input type="hidden" name="partner_record_type" value="{{ $partnerRecordType ?? 'dealer' }}">
        <div class="modal-body text-center">
          <input type="file" id="fileInput" accept="image/*" style="display:none" onchange="handleFileUpload(event)">
          
          <div class="d-flex justify-content-center gap-2 mb-3">
            <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
              <i class="bi bi-upload"></i> Upload Image
            </button>
            <button type="button" class="btn btn-secondary" onclick="enableCamera()">
              <i class="bi bi-camera"></i> Capture via Camera
            </button>
          </div>

          <div id="cameraSection" style="display:none">
            <video id="video" autoplay class="w-100 rounded border"></video>
            <button type="button" class="btn btn-success mt-2" onclick="captureImage()">
              <i class="bi bi-check-circle"></i> Capture
            </button>
          </div>

          <div id="previewSection" class="mt-3">
            <img id="preview" src="{{$dealer->avatar ? asset($dealer->avatar) : asset('design/assets/images/profile/user-1.png')}}" alt="Image Preview" height="300px" width="300px">
          </div>

          <input type="hidden" id="image_data" name="image_data" />
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
