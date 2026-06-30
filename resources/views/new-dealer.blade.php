@extends('layouts.header')
@section('css')
<style>
    #signatureCanvas {
      border: 1px solid #ccc;
      touch-action: none;
    }
  </style>

@endsection
@section('content')
<section class="welcome">
  <div class='row'>
    <div class="col-12">
      <!--  start Step wizard with validation -->
      <div class="card">
        <div class="card-body wizard-content">
          <h4 class="card-title">New Dealer</h4>
          <p class="card-subtitle mb-3"> Please fill out all required fields marked with an asterisk (<span class='text-danger'>*</span>).  </p>
          <form action="#" class="validation-wizard wizard-circle mt-5">
            <!-- Step 1 -->
            <h6>Information</h6>
            <section>
              <div class="row">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label" for="wfirstName2"> Full Name  <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control required" id="wfirstName2" name="firstName" />
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label" for="wemailAddress2"> Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control required" id="wemailAddress2" name="emailAddress" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label" for="wphoneNumber2">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control required" id="wphoneNumber2" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label" for="facebook">Facebook <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control required" id="facebook" />
                  </div>
                </div>
              </div>
              <div class="row">
                  <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label" for="stoveId">Store Name  <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" name='store_name'  />
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label" for="stoveId">Store Type  <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" name='store_type'  />
                      </div>
                    </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label" for="wlocation2"> Address <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control required"></textarea>
                  </div>
                </div>
                
              </div>
            </section>
            <!-- Step 2 -->
            <h6>Validation</h6>
            <section>
              <div class="row">
                  
                <div class="col-md-6">
                  <div class="mb-3">
                    
                    <label class="form-label" for="jobTitle3">Valid ID  <span class="text-danger">*</span></label>
                    <select class="form-select required" id="wintType1" data-placeholder="Type to search valid Id"
                    name="valid_id">
                    <option value="">Select</option>
                    <option value="Passports">Passports</option>
                    <option value="Driver License">Driver License</option>
                    <option value="SSS">SSS</option>
                    <option value="UMID">UMID</option>
                  </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="webUrl3">ID #  <span class="text-danger">*</span></label>
                    <input type="text" class="form-control required" id="webUrl3" name="webUrl3" />
                  </div>
                </div>
              </div>
              <div class='row'>
                <div class="col-md-12 text-center">
                    <h6>Capture Valid ID</h6>
                  <div class="mb-3">
                      <video id="video" width="640" height="480" autoplay class="border mb-3 d-none"></video>
                      
                      <canvas id="canvas" width="640" height="480" class="border mb-3 d-none"></canvas>
                      <br>
                      <div class="d-flex justify-content-center gap-3">
                          <span id="startCameraBtn" class="btn btn-success">Access Camera</span>
                          <span id="captureBtn" class="btn btn-primary d-none">Capture Photo</span>
                          <span id="recaptureBtn" class="btn btn-warning d-none">Re-Capture</span>
                        </div>
                  </div>
                </div>
              </div>
            </section>
            <!-- Step 3 -->
            <h6>Contract</h6>
            <section>
              <div class="row">
                <div class="col-md-12">
                  <div class="mb-3">
                      <h5 class="mb-0">KASUNDUAN SA PAKIKIBAHAGI SA PROYEKTO NG CARBON CREDIT GAZ LITE FEASIBILITY STUDY			
                      </h5>
                      <br>
                      Ito ay pagpapatunay na ako, __________________________________, ay pumapayag na makibahagi sa proyekto ng CARBON CREDIT GAZ LITE FEASIBILITY STUDY.
                      <br>
                      <br>
                      <u>Mga panuntunan na dapat sundin ng mga kalahok sa proyekto:</u>
                      <ol>
                          <li><b>Ako ay nagkukusang loob na gagamit ng Gaz Lite LPG cookstove at micro-cylinders sa halip na kahoy at/o uling para sa pagluluto.</b> (I volunteer to use the Gaz Lite LPG cookstove and micro-cylinders instead of firewood and/or charcoal for cooking)</li>
                          <li><b>Ako ay nagkukusang loob na gagamit ng Gaz Lite LPG cookstove at micro-cylinders upang ihanda ang aming pangunahing pagkain (karaniwang tanghalian) kada araw sa loob ng limang (5) araw. Kung magiging angkop, malugod na tinatanggap ang pagpapalawak ng paggamit ng LPG cookstoves at micro-cylinders sa ibang mga pagkain kung ang mga ito ay inihanda gamit ang kahoy at/o uling.</b> (I volunteer to use the Gaz Lite LPG cookstove and micro-cylinders to prepare at least our main meal (usually lunch) every day over a five (5) day period. If I find it suitable, I welcome to extend the use of LPG cookstoves and micro-cylinders to other meals that were cooked with firewood and/or charcoal.)</li>
                          <li><b>(I agree not to use any similar product other than Gaz Lite. Any violation on my part means I will cease participation and voluntary return the Gaz Lite products that were given to me.)</b> (I agree not to use any similar product other than Gaz Lite. Any violation on my part means I will cease participation and voluntary return the Gaz Lite products that were given to me.)</li>
                          <li><b>Pananatilihin ko ang aking karaniwang paraan ng pagluluto sa panahon ng pilot (halimbawa, bilang ng mga myembrong pinagluluto, uri ng mga nilutong ulam).</b> Pananatilihin ko ang aking karaniwang paraan ng pagluluto sa panahon ng pilot (halimbawa, bilang ng mga myembrong pinagluluto, uri ng mga nilutong ulam).</li>
                          
                        </ol>
                    </div>
                  
                </div>
                <div class='col-md-12 text-center'>
                      <span class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#signatureModal">
                      Signature
                    </span>
                    <div class="mt-4" id="signaturePreview" style="display: none;">
                      <h5>Saved Signature Preview:</h5>
                      <img id="savedSignature" src="" alt="Signature will appear here" style="border: 1px solid #ccc; max-width: 100%; height: auto;">
                    </div>
                </div>
              </div>
            </section>
            <!-- Step 4 -->
            <h6>Capture Avatar</h6>
            <section>
              <div class="row">
                  <div class='col-md-12 text-center'>
                          <h5 class="mb-0">Capture Avatar</h5>
                          <div class='text-center'>
                              <video id="cameraPreview" width="320" height="240" class="border mb-3 " autoplay></video>
                            </div>
                        
                            <div class="mt-4 text-center">
                              <canvas id="avatarCanvas" width="320" height="240" style="display: none;" class="border mb-3 "></canvas>
                              <img id="capturedAvatar" src="" alt="Captured Avatar" style="display: none; border: 1px solid #ccc;" />
                            </div>
                            <div class="mt-3 text-center">
                              <span id="startCameraBtnAvatar" class="btn btn-success">Start Camera</span>
                              <span id="capturePhotoBtnAvatar" class="btn btn-primary d-none">Capture Photo</span>
                              <span id="retakePhotoBtnAvatar" class="btn btn-warning d-none">Retake Photo</span>
                            </div>
                  </div>
              </div>
            </section>
          </form>
        </div>
      </div>
      <!--  end Step wizard with validation -->
    </div>
  </div>
  <div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Draw Your Signature</h5>
          <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></span>
        </div>

        <div class="modal-body text-center">
          <canvas id="signatureCanvas" width="600" height="200"></canvas>
        </div>

        <div class="modal-footer">
          <span id="clearBtn" class="btn btn-warning">Clear</span>
          <span id="saveBtn" class="btn btn-success" data-bs-dismiss="modal">Save Signature</span>
        </div>

      </div>
    </div>
  </div>
</section>

@endsection

@section('javascript')

<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
<script src="{{asset('design/assets/libs/jquery-steps/build/jquery.steps.min.js')}}"></script>
<script src="{{asset('design/assets/libs/jquery-validation/dist/jquery.validate.min.js')}}"></script>
<script src="{{asset('design/assets/js/forms/form-wizard.js')}}"></script>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');

    const startCameraBtn = document.getElementById('startCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const recaptureBtn = document.getElementById('recaptureBtn');

    let stream;

    // Start the camera
    startCameraBtn.addEventListener('click', async () => {
      try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;

        video.classList.remove('d-none');
        captureBtn.classList.remove('d-none');
        startCameraBtn.classList.add('d-none');
      } catch (err) {
        alert("Error accessing camera: " + err);
      }
    });

    // Capture photo
    captureBtn.addEventListener('click', () => {
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
      video.classList.add('d-none');
      canvas.classList.remove('d-none');
      captureBtn.classList.add('d-none');
      recaptureBtn.classList.remove('d-none');
    });

    // Re-capture photo
    recaptureBtn.addEventListener('click', () => {
      canvas.classList.add('d-none');
      video.classList.remove('d-none');
      captureBtn.classList.remove('d-none');
      recaptureBtn.classList.add('d-none');
    });
  </script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script>
     const signatureCanvas = document.getElementById('signatureCanvas');
     const ctx = signatureCanvas.getContext('2d');
     let drawing = false;
 
     const startDrawing = (e) => {
       drawing = true;
       ctx.beginPath();
       ctx.moveTo(getX(e), getY(e));
     };
 
     const draw = (e) => {
       if (!drawing) return;
       ctx.lineTo(getX(e), getY(e));
       ctx.stroke();
     };
 
     const stopDrawing = () => {
       drawing = false;
     };
 
     const getX = (e) => e.offsetX || e.touches?.[0].clientX - signatureCanvas.getBoundingClientRect().left;
     const getY = (e) => e.offsetY || e.touches?.[0].clientY - signatureCanvas.getBoundingClientRect().top;
 
     // Mouse events
     signatureCanvas.addEventListener('mousedown', startDrawing);
     signatureCanvas.addEventListener('mousemove', draw);
     signatureCanvas.addEventListener('mouseup', stopDrawing);
     signatureCanvas.addEventListener('mouseout', stopDrawing);
 
     // Touch events for mobile
     signatureCanvas.addEventListener('touchstart', (e) => { e.preventDefault(); startDrawing(e); });
     signatureCanvas.addEventListener('touchmove', (e) => { e.preventDefault(); draw(e); });
     signatureCanvas.addEventListener('touchend', stopDrawing);
 
     // Clear button
     document.getElementById('clearBtn').addEventListener('click', () => {
       ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
     });
 
     // Save button
     document.getElementById('saveBtn').addEventListener('click', () => {
       const dataURL = signatureCanvas.toDataURL('image/png');
       document.getElementById('savedSignature').src = dataURL;
       document.getElementById('signaturePreview').style.display = 'block';
     });
   </script>
<script>
    const videoAvatar = document.getElementById('cameraPreview');
    const photoCanvas = document.getElementById('avatarCanvas');
    const capturedAvatar = document.getElementById('capturedAvatar');
    const startCameraBtnAvatar = document.getElementById('startCameraBtnAvatar');
    const capturePhotoBtnAvatar = document.getElementById('capturePhotoBtnAvatar');
    const retakePhotoBtnAvatar = document.getElementById('retakePhotoBtnAvatar');
    const ctxAvatar = photoCanvas.getContext('2d');
  
    let cameraStream = null;
  
    async function startCamera() {
      try {
        if (cameraStream) {
          cameraStream.getTracks().forEach(track => track.stop());
        }
        cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
        videoAvatar.srcObject = cameraStream;
        videoAvatar.style.display = 'block';
      } catch (err) {
        alert('Camera access denied or error occurred: ' + err.message);
      }
    }
  
    // Start camera button
    startCameraBtnAvatar.addEventListener('click', async () => {
      await startCamera();
      startCameraBtnAvatar.classList.add('d-none');
      capturePhotoBtnAvatar.classList.remove('d-none');
      retakePhotoBtnAvatar.classList.add('d-none');
      capturedAvatar.style.display = 'none';
    });
  
    // Capture avatar
    capturePhotoBtnAvatar.addEventListener('click', () => {
      ctxAvatar.drawImage(videoAvatar, 0, 0, photoCanvas.width, photoCanvas.height);
      const imageData = photoCanvas.toDataURL('image/png');
      capturedAvatar.src = imageData;
      capturedAvatar.style.display = 'block';
      videoAvatar.style.display = 'none';
      capturePhotoBtnAvatar.classList.add('d-none');
      retakePhotoBtnAvatar.classList.remove('d-none');
  
      // Stop camera stream
      if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
      }
    });
  
    // Retake photo
    retakePhotoBtnAvatar.addEventListener('click', async () => {
      await startCamera();
      videoAvatar.style.display = 'block';
      capturedAvatar.style.display = 'none';
      retakePhotoBtnAvatar.classList.add('d-none');
      capturePhotoBtnAvatar.classList.remove('d-none');
    });
  </script>
@endsection
