<div class="modal-header">
    <h5 class="modal-title">Customer Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
     <div class="card card-bordered">
        <div class="card-inner">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="icon-circle icon-circle-lg">
                        <em class="icon ni ni-user"></em>
                    </div>
                    <div class="ms-3">
                        <h6 class="lead-text">{{ $customer->name }}</h6>
                        <span class="sub-text">{{ $customer->contact_info }}</span>
                        <span class="sub-text">{{ $customer->created_at->format('d M Y') }}</span>
                    </div>
                </div>    
            </div>
        </div>
      </div>

   
    <hr>
    <!-- Inline Form for Adding New Contact -->
    <h5>Manage Contacts</h5>
    <div class="row align-items-center mb-3">
        <div class="col">
            <input type="text" class="form-control" id="new_contact_name" placeholder="Name">
        </div>
        <div class="col">
            <input type="email" class="form-control" id="new_contact_email" placeholder="Email">
        </div>
        <div class="col">
            <input type="text" class="form-control" id="new_contact_phone" placeholder="Phone">
        </div>
        <!-- If you need a fourth input (e.g. Position), uncomment below -->
        <!--
        <div class="col">
            <input type="text" class="form-control" id="new_contact_position" placeholder="Position">
        </div>
        -->
        <div class="col-auto">
            <button type="button" class="btn btn-primary" id="saveNewContact">
                <em class="icon ni ni-check"></em>
            </button>
        </div>
    </div>
    <!-- Hidden field to store current customer id -->
    <input type="hidden" id="current_customer_id" value="{{ $customer->id }}">
    
    <!-- Existing Contacts Table -->
    <div id="contactsSection">
        @if($customer->contacts->isNotEmpty())
            <table class="table table-bordered" id="contactsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->contacts as $contact)
                        <tr id="contactRow-{{ $contact->id }}">
                            <td>{{ $contact->contact_name }}</td>
                            <td>{{ $contact->contact_email }}</td>
                            <td>{{ $contact->contact_phone }}</td>
                            <td>
                                <!-- <button type="button" class="btn btn-sm btn-secondary editContactBtn" data-id="{{ $contact->id }}">Edit</button> -->
                                <button type="button" class="btn btn-sm btn-danger deleteContactBtn" data-id="{{ $contact->id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p id="noContactsMsg">No contacts found.</p>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
