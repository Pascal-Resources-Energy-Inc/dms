@extends('layouts.header')
@section('css')
<style>
  .dealer-view {
    padding: 18px 0 34px;
    color: #132f45;
  }

  .dealer-view-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
  }

  .dealer-view-kicker {
    margin: 0 0 6px;
    color: #2f9bd7;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
  }

  .dealer-view-title {
    margin: 0;
    color: #0f172a;
    font-size: 28px;
    font-weight: 900;
    line-height: 1.15;
  }

  .dealer-view-subtitle {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 14px;
  }

  .dealer-back-link {
    min-height: 38px;
    border: 1px solid #dbe7ef;
    border-radius: 8px;
    padding: 8px 13px;
    color: #1678b4;
    background: #fff;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
  }

  .dealer-back-link:hover {
    color: #0f5f90;
    border-color: #9ed4ee;
    text-decoration: none;
  }

  .dealer-profile-card,
  .dealer-panel,
  .dealer-doc-card,
  .dealer-stat {
    border: 1px solid #e2edf4;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 18px 45px rgba(19, 47, 69, .07);
  }

  .dealer-profile-card {
    overflow: hidden;
  }

  .dealer-profile-cover {
    min-height: 92px;
    background:
      linear-gradient(135deg, rgba(47, 155, 215, .95), rgba(22, 120, 180, .9)),
      url("{{ asset('images/gazlite.png') }}") center / cover no-repeat;
  }

  .dealer-profile-body {
    padding: 0 20px 20px;
  }

  .profile-avatar {
    width: 118px;
    height: 118px;
    margin-top: -52px;
    object-fit: cover;
    border-radius: 8px;
    border: 5px solid #fff;
    background: #f8fafc;
    box-shadow: 0 14px 34px rgba(15, 23, 42, .12);
  }

  .dealer-profile-actions {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin: 12px 0 18px;
  }

  .dealer-icon-btn {
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .profile-name {
    margin: 12px 0 4px;
    color: #0f172a;
    font-size: 20px;
    font-weight: 900;
    line-height: 1.2;
  }

  .profile-store {
    color: #64748b;
    font-size: 13px;
    font-weight: 800;
  }

  .dealer-status-pill {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    margin-top: 12px;
    border-radius: 999px;
    padding: 4px 11px;
    font-size: 12px;
    font-weight: 900;
  }

  .dealer-status-pill.is-active {
    color: #166534;
    background: #dcfce7;
  }

  .dealer-status-pill.is-inactive {
    color: #991b1b;
    background: #fee2e2;
  }

  .profile-info {
    display: grid;
    gap: 10px;
    margin-top: 18px;
  }

  .profile-info-item {
    display: grid;
    grid-template-columns: 34px 1fr;
    gap: 10px;
    align-items: flex-start;
    padding: 11px;
    border: 1px solid #edf2f7;
    border-radius: 8px;
    background: #f8fafc;
  }

  .profile-info-item i {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #e8f6fc;
    color: #1678b4;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .profile-info-label {
    display: block;
    color: #64748b;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .05em;
    text-transform: uppercase;
  }

  .profile-info-value {
    display: block;
    margin-top: 2px;
    color: #111827;
    font-size: 13px;
    font-weight: 800;
    word-break: break-word;
  }

  .dealer-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
  }

  .dealer-stat {
    padding: 15px;
  }

  .dealer-stat span {
    display: block;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
  }

  .dealer-stat strong {
    display: block;
    margin-top: 5px;
    color: #0f172a;
    font-size: 22px;
    font-weight: 900;
  }

  .dealer-panel {
    margin-bottom: 14px;
  }

  .dealer-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 18px;
    border-bottom: 1px solid #e8eef4;
  }

  .dealer-panel-title {
    margin: 0;
    color: #0f172a;
    font-size: 16px;
    font-weight: 900;
  }

  .dealer-panel-subtitle {
    margin: 3px 0 0;
    color: #64748b;
    font-size: 12px;
  }

  .dealer-panel-body {
    padding: 18px;
  }

  .dealer-doc-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }

  .dealer-doc-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 16px;
  }

  .dealer-doc-main {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    min-width: 0;
  }

  .dealer-doc-icon {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    background: #e8f6fc;
    color: #1678b4;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    font-size: 18px;
  }

  .dealer-doc-title {
    margin: 0;
    color: #0f172a;
    font-size: 14px;
    font-weight: 900;
  }

  .dealer-doc-copy {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 12px;
    line-height: 1.45;
  }

  .dealer-table {
    margin-bottom: 0;
    font-size: 12px;
  }

  .dealer-table thead th {
    border-top: 0;
    border-bottom: 1px solid #dbe7ef;
    color: #475569;
    background: #f8fafc;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .dealer-table tbody td {
    vertical-align: middle;
    border-color: #edf2f7;
  }

  .dealer-table tbody tr:hover {
    background: #f8fbff;
  }

  .dealer-empty {
    padding: 34px 16px;
    color: #64748b;
    text-align: center;
    font-size: 13px;
  }

  @media (max-width: 991px) {
    .dealer-view-header {
      display: block;
    }

    .dealer-back-link {
      display: inline-flex;
      margin-top: 12px;
    }

    .dealer-stat-grid,
    .dealer-doc-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
@endsection

@section('content')
@php
  $isRemoteDealer = $isRemoteDealer ?? false;
  $firstName = strtoupper($dealer->user->first_name ?? '');
  $lastName = strtoupper($dealer->user->last_name ?? '');
  $profileName = trim($firstName . ' ' . $lastName);
  $profileName = $profileName ?: strtoupper($dealer->name ?? '-');
  $storeName = strtoupper($dealer->store_name ?? '-');
  $dealerReference = strtoupper($dealer->dealer_reference ?? '-');
  $sourceLabel = $dealer->source_label ?? 'Local Database';
  $avatar = $dealer->avatar ?? null;
  $avatarUrl = $avatar
      ? (preg_match('/^https?:\/\//i', $avatar) ? $avatar : asset($avatar))
      : asset('design/assets/images/profile/user-1.png');
  $status = $dealer->status ?: 'Active';
  $isActive = strcasecmp($status, 'Active') === 0;
  $transactionStats = $transactionStats ?? [
      'count' => $transactions->count(),
      'qty' => $transactions->sum('qty'),
      'amount' => $transactions->sum(function ($transaction) {
          return (float) $transaction->qty * (float) $transaction->price;
      }),
      'points' => $transactions->sum('points_client'),
  ];
  $totalTransactions = $transactionStats['count'];
  $totalQty = $transactionStats['qty'];
  $totalAmount = $transactionStats['amount'];
  $totalPoints = $transactionStats['points'];
@endphp

<section class="dealer-view">
  <div class="dealer-view-header">
    <div>
      <p class="dealer-view-kicker">{{ $isRemoteDealer ? $sourceLabel : (auth()->user()->role == 'Admin' ? 'Dealer Profile' : 'Dealer Profile') }}</p>
      <h1 class="dealer-view-title">Dealer Information</h1>
      <p class="dealer-view-subtitle">{{ $profileName }} / {{ $storeName }} / {{ $dealerReference }}</p>
    </div>
    @if(auth()->user()->role == 'Admin')
      <a href="{{ url('/dealers') }}" class="dealer-back-link">
        <i class="bi bi-arrow-left"></i> Back to Dealers
      </a>
    @endif
  </div>

  <div class="row">
    <div class="col-xl-4 col-lg-5 mb-3">
      <div class="dealer-profile-card">
        <div class="dealer-profile-cover"></div>
        <div class="dealer-profile-body">
          <div class="text-center">
            <img src="{{ $avatarUrl }}" class="profile-avatar mx-auto" alt="Dealer avatar">
            <div class="profile-name">{{ $profileName }}</div>
            <div class="profile-store">{{ $storeName }}</div>
            <span class="dealer-status-pill {{ $isActive ? 'is-active' : 'is-inactive' }}">{{ strtoupper($status) }}</span>
          </div>

          @unless($isRemoteDealer)
            <div class="dealer-profile-actions">
              <button type="button" class="btn btn-primary dealer-icon-btn" data-toggle="modal" data-target="#uploadAvatarModal" data-bs-toggle="modal" data-bs-target="#uploadAvatarModal" title="Upload Avatar">
                <i class="fas fa-camera"></i>
                <span class="sr-only">Upload Avatar</span>
              </button>
              @if(($canEditDealer ?? false) || auth()->user()->role == 'Admin')
                <button type="button" class="btn btn-warning dealer-icon-btn" data-toggle="modal" data-target="#editDealerModal" data-bs-toggle="modal" data-bs-target="#editDealerModal" title="Edit Dealer">
                  <i class="fas fa-edit"></i>
                  <span class="sr-only">Edit Dealer</span>
                </button>
              @endif
            </div>
          @endunless

          <div class="profile-info">
            @if($isRemoteDealer)
              <div class="profile-info-item">
                <i class="bi bi-database"></i>
                <div>
                  <span class="profile-info-label">Data Source</span>
                  <span class="profile-info-value">{{ strtoupper($sourceLabel) }}</span>
                </div>
              </div>
            @endif
            <div class="profile-info-item">
              <i class="bi bi-telephone"></i>
              <div>
                <span class="profile-info-label">Contact Number</span>
                <span class="profile-info-value">{{ strtoupper($dealer->number ?? '-') }}</span>
              </div>
            </div>
            <div class="profile-info-item">
              <i class="bi bi-geo-alt"></i>
              <div>
                <span class="profile-info-label">Address</span>
                <span class="profile-info-value">{{ strtoupper($dealer->address ?? '-') }}</span>
              </div>
            </div>
            <div class="profile-info-item">
              <i class="bi bi-shop"></i>
              <div>
                <span class="profile-info-label">Store Type</span>
                <span class="profile-info-value">{{ strtoupper($dealer->store_type ?? '-') }}</span>
              </div>
            </div>
            <div class="profile-info-item">
              <i class="bi bi-facebook"></i>
              <div>
                <span class="profile-info-label">Facebook</span>
                <span class="profile-info-value">{{ strtoupper($dealer->facebook ?? '-') }}</span>
              </div>
            </div>
            <div class="profile-info-item">
              <i class="bi bi-envelope"></i>
              <div>
                <span class="profile-info-label">Email Address</span>
                <span class="profile-info-value">{{ strtoupper($dealer->email_address ?? '-') }}</span>
              </div>
            </div>
            <div class="profile-info-item">
              <i class="bi bi-map"></i>
              <div>
                <span class="profile-info-label">Sales Territory</span>
                <span class="profile-info-value">{{ strtoupper($dealer->area ?? '-') }}</span>
              </div>
            </div>
          </div>
          <div id="qrcode" class="mt-4 text-center"></div>
        </div>
      </div>
    </div>

    <div class="col-xl-8 col-lg-7">
      <div class="dealer-stat-grid">
        <div class="dealer-stat">
          <span>Total Transactions</span>
          <strong>{{ number_format($totalTransactions) }}</strong>
        </div>
        <div class="dealer-stat">
          <span>Total Quantity</span>
          <strong>{{ number_format($totalQty) }}</strong>
        </div>
        <div class="dealer-stat">
          <span>Total Amount</span>
          <strong>{{ number_format($totalAmount, 2) }}</strong>
        </div>
      </div>

      <div class="dealer-panel">
        <div class="dealer-panel-header">
          <div>
            <h2 class="dealer-panel-title">Dealer Requirements</h2>
            <p class="dealer-panel-subtitle">{{ $isRemoteDealer ? 'Read-only records from the selected CRM database.' : 'Valid ID and contract records for this dealer.' }}</p>
          </div>
        </div>
        <div class="dealer-panel-body">
          <div class="dealer-doc-grid">
            <div class="dealer-doc-card">
              <div class="dealer-doc-main">
                <span class="dealer-doc-icon"><i class="bi bi-person-vcard"></i></span>
                <div>
                  <h3 class="dealer-doc-title">{{ $dealer->valid_id ? 'Valid ID Information' : 'Upload Valid ID' }}</h3>
                  @if($dealer->valid_id)
                    <p class="dealer-doc-copy">ID Type: {{ $dealer->valid_id }}<br>ID Number: {{ $dealer->valid_id_number ?: '-' }}</p>
                  @else
                    <p class="dealer-doc-copy">Submit a valid government-issued ID.</p>
                  @endif
                </div>
              </div>
              @if($isRemoteDealer)
                <span class="btn btn-outline-secondary btn-sm disabled">{{ $dealer->valid_id ? 'Available' : 'Missing' }}</span>
              @elseif($dealer->valid_id)
                <button type="button" data-toggle="modal" data-target="#viewValidId" data-bs-toggle="modal" data-bs-target="#viewValidId" class="btn btn-primary btn-sm">
                  View
                </button>
              @else
                <button class="btn btn-danger btn-sm" type="button" data-toggle="modal" data-target="#uploadIdModal" data-bs-toggle="modal" data-bs-target="#uploadIdModal">
                  Upload
                </button>
              @endif
            </div>

            {{-- <div class="dealer-doc-card">
              <div class="dealer-doc-main">
                <span class="dealer-doc-icon"><i class="bi bi-file-earmark-text"></i></span>
                <div>
                  <h3 class="dealer-doc-title">{{ $dealer->signature ? 'Signed Contract' : 'Contract Signing' }}</h3>
                  <p class="dealer-doc-copy">{{ $dealer->signature ? 'Contract has been signed and saved.' : 'Review and sign the contract.' }}</p>
                </div>
              </div>
              @if($isRemoteDealer)
                <span class="btn btn-outline-secondary btn-sm disabled">{{ $dealer->signature ? 'Available' : 'Missing' }}</span>
              @elseif($dealer->signature)
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#contractView" data-bs-toggle="modal" data-bs-target="#contractView">
                  View
                </button>
              @else
                <button class="btn btn-danger btn-sm" type="button" data-toggle="modal" data-target="#contractModal" data-bs-toggle="modal" data-bs-target="#contractModal">
                  Sign
                </button>
              @endif
            </div> --}}
          </div>
        </div>
      </div>

      <div class="dealer-panel">
        <div class="dealer-panel-header">
          <div>
            <h2 class="dealer-panel-title">Transactions</h2>
            <p class="dealer-panel-subtitle">{{ number_format($totalPoints) }} total points earned from this dealer.</p>
          </div>
        </div>
        <div class="dealer-panel-body p-0">
          <div class="table-responsive">
            <table class="table dealer-table">
              <thead>
                <tr>
                  <th>Transaction No.</th>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Points Earned</th>
                  <th>Amount</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($transactions as $transaction)
                  <tr>
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->item ?: '-' }}</td>
                    <td>{{ number_format($transaction->qty) }}</td>
                    <td><span class="text-success font-weight-bold">{{ number_format($transaction->points_client) }}</span></td>
                    <td>{{ number_format($transaction->qty * $transaction->price, 2) }}</td>
                    <td>{{ date('M d, Y', strtotime($transaction->created_at)) }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="dealer-empty">No transactions found for this dealer.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if(method_exists($transactions, 'links') && $transactions->hasPages())
            <div class="px-3 py-3 border-top">
              {{ $transactions->links() }}
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

@unless($isRemoteDealer)
  @include('change_avatar_dealer')
  @include('upload_valid_id_dealer')
  @include('viewValidIdDealer')
  @include('sign_contract_dealer')
  @include('view_contract_signed_dealer')
  @include('edit_dealer')
@endunless
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@unless($isRemoteDealer)
<script>
  const canvas = document.getElementById('signatureCanvas');
  const ctx = canvas.getContext('2d');
  let drawing = false;

  canvas.addEventListener('mousedown', () => drawing = true);
  canvas.addEventListener('mouseup', () => {
    drawing = false;
    ctx.beginPath();
    saveSignatureAsFile(); // Save after drawing
  });
  canvas.addEventListener('mouseout', () => drawing = false);
  canvas.addEventListener('mousemove', draw);

  function draw(e) {
    if (!drawing) return;
    const rect = canvas.getBoundingClientRect();
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';
    ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
  }

  function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('contract_signature').value = '';
  }

  function saveSignatureAsFile() {
    canvas.toBlob(function (blob) {
      const file = new File([blob], "signature.png", { type: "image/png" });

      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);

      const input = document.getElementById('contract_signature');
      input.files = dataTransfer.files;
    }, 'image/png');
  }
</script>
<script>
  const video = document.getElementById('video');
  const preview = document.getElementById('preview');
  const imageInput = document.getElementById('image_data');
  const cameraSection = document.getElementById('cameraSection');

  function handleFileUpload(event) {
    stopCamera();
    const file = event.target.files[0];
    if (file) {
      if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file.');
        return;
      }
      
      const maxSize = 5 * 1024 * 1024;
      if (file.size > maxSize) {
        alert('File size is too large. Please select an image smaller than 5MB.');
        return;
      }
      
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          
          const maxWidth = 800;
          const maxHeight = 800;
          let { width, height } = img;
          
          if (width > height) {
            if (width > maxWidth) {
              height = (height * maxWidth) / width;
              width = maxWidth;
            }
          } else {
            if (height > maxHeight) {
              width = (width * maxHeight) / height;
              height = maxHeight;
            }
          }
          
          canvas.width = width;
          canvas.height = height;
          
          ctx.drawImage(img, 0, 0, width, height);
          const compressedDataUrl = canvas.toDataURL('image/png', 0.8);
          
          preview.src = compressedDataUrl;
          imageInput.value = compressedDataUrl;
        };
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  function enableCamera() {
    cameraSection.style.display = 'block';
    navigator.mediaDevices.getUserMedia({ 
      video: { 
        width: { ideal: 1280 }, 
        height: { ideal: 720 } 
      } 
    })
      .then(stream => {
        video.srcObject = stream;
      })
      .catch(err => {
        console.error("Camera access error:", err);
        alert("Camera access denied: " + err.message);
      });
  }

  function captureImage() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    
    const imageData = canvas.toDataURL('image/png', 0.8);
    preview.src = imageData;
    imageInput.value = imageData;
    stopCamera();
  }

  function stopCamera() {
    const stream = video.srcObject;
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
    }
    video.srcObject = null;
    if (cameraSection) {
      cameraSection.style.display = 'none';
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
  const uploadModal = document.getElementById('uploadAvatarModal');
  if (uploadModal) {
    uploadModal.addEventListener('hidden.bs.modal', function() {
      stopCamera();
    });
  }
});
</script>
@endunless
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
<script>
    // Valid data for QR code generation
    const customerData = {
        customerId: 'ST12345',
    };

    // Create a JSON string of the customer data
    const customerDataString = JSON.stringify(customerData);

    // Generate QR code for the customer data
    QRCode.toCanvas(document.getElementById('qrcode'), customerDataString, function(error) {
        if (error) {
            console.error(error);
        } else {
            console.log('QR code generated!');
        }
    });

  @unless($isRemoteDealer)
  $('#editDealerModal').on('shown.bs.modal', function () {
    initSelect2(this);
    initSelect3(this);
  });

  function initSelect3(parent = document) {
    if (!$.fn.select2) return;

    $(parent).find('.select2-area').each(function () {
        const $this = $(this);

        // ✅ ALWAYS destroy first (important)
        if ($this.hasClass('select2-hidden-accessible')) {
            $this.select2('destroy');
        }

        const $modal = $this.closest('.modal');

        $this.select2({
            width: '100%',
            dropdownParent: $modal,
            placeholder: $this.data('placeholder') || 'Select Area',
            allowClear: true,

            templateResult: formatArea,
            templateSelection: formatArea,

            escapeMarkup: function (markup) {
                return markup;
            }
        });

        // ✅ FIX: re-set selected value properly
        let selectedVal = $this.find('option:selected').val();
        if (selectedVal) {
            $this.val(selectedVal).trigger('change.select2');
        }
    });
  }
  $('#editDealerModal').on('shown.bs.modal', function () {
    initMap();

    // 🔥 If dealer already has address → sync map
    if ($('#complete_address').val()) {
        geocodeAddressToMap();
    }
  });
  @endunless
</script>

@endsection
