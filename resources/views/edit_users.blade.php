{{-- <div id="edit-users-{{ $user->id }}" class="modal fade" tabindex="-1" aria-labelledby="bs-example-modal-md" aria-hidden="true" style="display:none;">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center">
        <h4 class="modal-title" id="myModalLabel">
          Edit Users
        </h4>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form action="{{ route('edit-users', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                @if($user->role !== 'Dealer' && $user->role !== 'Admin')
                <div class="col-12 mb-3">
                  <label for="serialNumber" class="form-label">
                      Serial Number
                  </label>
                  <div class="dropdown-container">
                      @php
                          $currentSerial = '';
                          $currentStoveId = null;
                          if($user->role == 'Client' && $user->client && $user->client->serial) {
                              $currentSerial = $user->client->serial->serial_number;
                              $currentStoveId = $user->client->serial_number;
                          } else {
                              $currentSerial = $user->serial ?? '';
                          }
                      @endphp
                      
                      <select id="serial_number_{{ $user->id }}"
                              name="serial_number" 
                              data-user-id="{{ $user->id }}"
                              data-current-stove-id="{{ $currentStoveId }}"
                              class="form-control js-serial-number-select" 
                              style="width: 100%;">
                          
                          @if($currentSerial && $currentStoveId)
                              <option value="{{ $currentStoveId }}" selected>{{ $currentSerial }}</option>
                          @endif
                          
                          <option value="">--  Available Serial Number  --</option>
                          <option value=""></option>
                          
                          @foreach($stoves as $stove)
                              @if($currentStoveId != $stove->id)
                                  <option value="{{ $stove->id }}">{{ $stove->serial_number }}</option>
                              @endif
                          @endforeach
                      </select>
                  </div>
                  <small class="text-muted">Current Serial Number</small>
                </div>
                @endif

                <div class="col-12 mb-3">
                    <label for="fullName" class="form-label">
                        Full Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="fullName" name="full_name" value="{{ $user->name }}" required class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="emailAddress" class="form-label">
                        Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="emailAddress" name="email" value="{{ $user->email }}" required class="form-control" required>
                </div>
                
                <div class="form-group col-md-6 mb-3">
                    <label for="role">Role</label>
                    <input type="text" id="role" name="role" value="{{ $user->role }}" required class="form-control" disabled>
                </div>

                
               @if($user->role !== 'Admin')
                <div class="col-md-6 mb-3">
                    <label for="phoneNumber" class="form-label">
                        Phone Number <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="tel" 
                        id="phoneNumber" 
                        name="phone" 
                        maxlength="11" 
                        inputmode="numeric"
                        pattern="\d{11}" 
                        value="{{ 
                            $user->role == 'Dealer' && $user->dealer ? $user->dealer->number : 
                            ($user->role == 'Client' && $user->client ? $user->client->number : 
                            ($user->number ?? '')) 
                        }}" 
                        required 
                        class="form-control"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);"
                    >
                </div>
                @else
                <input type="hidden" name="phone" value="{{ $user->phone ?? $user->number ?? 'N/A' }}">
                @endif

                <div class="col-12 mb-3">
                    <label for="address" class="form-label">
                        Address <span class="text-danger">*</span>
                    </label>
                    <textarea id="address" name="address" required rows="3" class="form-control" required>{{ 
                        $user->role == 'Dealer' && $user->dealer ? $user->dealer->address : 
                        ($user->role == 'Client' && $user->client ? $user->client->address : 
                        ($user->address ?? '')) 
                    }}</textarea>
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
    </div>
  </div>
</div> --}}

<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5>Edit User</h5>
      </div>

      <div class="modal-body">
        <input type="hidden" id="edit_user_id">

        <input type="text" id="edit_name" class="form-control mb-2" placeholder="Name">
        <input type="email" id="edit_email" class="form-control mb-2" placeholder="Email">
        <textarea id="edit_address" class="form-control" rows="3" placeholder="Address"></textarea>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" id="saveUser">Save</button>
      </div>

    </div>
  </div>
</div>

<style>
.modal-body {
  padding: 1rem 1.5rem 0.5rem 1.5rem;
}

.modal-header {
  padding: 1rem 1.5rem 0.5rem 1.5rem;
}

/* .modal-footer {
  padding: 0.5rem 1.5rem 1rem 1.5rem;
} */

.mb-3 {
  margin-bottom: 0.75rem !important;
}

#address {
  min-height: 80px;
  rows: 3;
}

.dropdown-container {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 1000;
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  background-color: white;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.dropdown-item {
  padding: 0.5rem 1rem;
  color: #212529;
  text-decoration: none;
  display: block;
  cursor: pointer;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
  color: #212529;
}

.card {
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
}

.card-body {
  background-color: #f8f9fa;
}

.custom-select {
    position: relative;
    display: inline-block;
    width: 100%;
}

.custom-select select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 100%;
    padding: 10px 40px 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: white;
    font-size: 16px;
    cursor: pointer;
}

/* custom arrow */
.custom-select::after {
    content: "▼";
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    pointer-events: none;
    font-size: 14px;
    color: #555;
}

</style>
