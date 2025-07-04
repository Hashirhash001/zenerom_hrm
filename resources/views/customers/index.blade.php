@extends('layouts.app')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <!-- Header Section -->
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Customers</h3>
                            <div class="nk-block-des text-soft">
                                <p>You have total {{ $customers->count() }} Customers.</p>
                            </div>
                        </div>
                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                                    <em class="icon ni ni-menu-alt-r"></em>
                                </a>
                                @php
                                    // Convert session privileges to a Collection for easier access control checks.
                                    $userPrivileges = collect(session('user_privileges'));
                                @endphp
                                <div class="toggle-expand-content" data-content="pageMenu">
                                    <ul class="nk-block-tools g-3">
                                        <li>
                                            <div>
                                                <input type="text" class="form-control" id="customerSearch" placeholder="Search Customers">
                                            </div>
                                        </li>
                                        <li class="nk-block-tools-opt d-none d-sm-block">
                                            @if($userPrivileges->has(11) && $userPrivileges->get(11)->can_create)
                                            <!-- Add Customer button triggers the add modal -->
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                                <em class="icon ni ni-plus"></em><span>Add Customer</span>
                                            </a>
                                             @endif
                                        </li>
                                        <li class="nk-block-tools-opt d-block d-sm-none">
                                            <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                                <em class="icon ni ni-plus"></em>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- .toggle-wrap -->
                        </div>
                    </div>
                </div>
                <!-- End Header Section -->

                <!-- Customer List Container -->
                <div id="customersContainer">
                    @include('customers._list', ['customers' => $customers])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      @include('customers.create_modal')
    </div>
  </div>
</div>

<!-- Modal for Editing Customer -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Edit form loaded via Ajax -->
    </div>
  </div>
</div>

<!-- Popup Modal for messages -->
<div class="modal fade" id="popupMessageModal" tabindex="-1" aria-labelledby="popupMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupMessageModalLabel">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Message content will be injected here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Editing Customer Contact -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Content will be loaded via Ajax -->
    </div>
  </div>
</div>

<!-- Modal for Viewing Customer Details -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <!-- Content loaded via Ajax -->
    </div>
  </div>
</div>






<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script src="{{ asset('assets1/jquery-ui/jquery-ui.js') }}"></script>
<script>
    // Save new Customer via Ajax
    function saveCustomer() {
        var formData = new FormData($('#addCustomerForm')[0]);
        $.ajax({
            url: "{{ route('customer.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                showPopup(response.message);
                $('#addCustomerModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                console.log("Save Customer Error:", xhr.responseText);
                showPopup("Error saving customer.");
            }
        });
    }
    
    // Load Edit Customer form via Ajax
    function editCustomer(id) {
        $.ajax({
            url: "{{ url('customers') }}/" + id + "/edit",
            type: "GET",
            success: function(response) {
                $('#editCustomerModal .modal-content').html(response);
                $('#editCustomerModal').modal('show');
            },
            error: function(xhr) {
                console.log("Edit Customer Error:", xhr.responseText);
                showPopup("Error loading edit form.");
            }
        });
    }
    
    // Update Customer via Ajax
    function updateCustomer() {
        var formData = new FormData($('#editCustomerForm')[0]);
        var customerId = $('#editCustomerForm input[name="id"]').val();
        $.ajax({
            url: "{{ url('customers') }}/" + customerId,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                showPopup(response.message);
                $('#editCustomerModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                console.log("Update Customer Error:", xhr.responseText);
                showPopup("Error updating customer.");
            }
        });
    }
    
    // Delete Customer via Ajax
    function deleteCustomer(id) {
        if (confirm('Are you sure you want to delete this customer?')) {
            $.ajax({
                url: "{{ url('customers') }}/" + id,
                type: "DELETE",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    showPopup(response.message);
                    location.reload();
                },
                error: function(xhr) {
                    console.log("Delete Customer Error:", xhr.responseText);
                    showPopup("Error deleting customer.");
                }
            });
        }
    }
    
    // Live search for customers
    $('#customerSearch').on('keyup', function(){
        var query = $(this).val();
        $.ajax({
            url: "{{ route('customer.search') }}",
            type: "GET",
            data: { query: query },
            success: function(response) {
                $('#customersContainer').html(response.html);
            },
            error: function(xhr) {
                console.log("Customer Search Error:", xhr.responseText);
            }
        });
    });
    
    // Popup message function
    function showPopup(message) {
        $('#popupMessageModal .modal-body').html("<p>" + message + "</p>");
        $('#popupMessageModal').modal('show');
    }



// Load Edit Customer Contact form via Ajax
$(document).on('click', '.editContactBtn', function() {
    var contactId = $(this).data('id');
    $.ajax({
        url: "/customers/contacts/" + contactId + "/edit",
        type: "GET",
        success: function(response) {
            $('#editContactModal .modal-content').html(response);
            $('#editContactModal').modal('show');
        },
        error: function(xhr) {
            console.log("Edit Contact Error:", xhr.responseText);
            showPopup("Error loading contact edit form.");
        }
    });
});

// Update customer contact via Ajax
$(document).on('click', '#updateContactBtn', function() {
    var formData = new FormData($('#editContactForm')[0]);
    var contactId = $('#editContactForm input[name="id"]').val();
    $.ajax({
        url: "/customers/contacts/" + contactId,
        type: "POST", // use method spoofing with _method in form
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            var contact = response.contact;
            var row = $('#contactRow-' + contact.id);
            row.find('td:eq(0)').text(contact.contact_name);
            row.find('td:eq(1)').text(contact.contact_email);
            row.find('td:eq(2)').text(contact.contact_phone);
            $('#editContactModal').modal('hide');
            showPopup(response.message);
        },
        error: function(xhr) {
            console.log("Update Contact Error:", xhr.responseText);
            showPopup("Error updating contact.");
        }
    });
});

// Delete customer contact via Ajax
$(document).on('click', '.deleteContactBtn', function() {
    if (confirm('Are you sure you want to delete this contact?')) {
        var contactId = $(this).data('id');
        // Generate the URL using the route helper; since our explicit route requires only the contact id:
        var deleteUrl = "{{ route('customer.contact.destroy', ':id') }}";
        deleteUrl = deleteUrl.replace(':id', contactId);
        
        $.ajax({
            url: deleteUrl,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                alert(response.message);
                $('#contactRow-' + contactId).remove();
            },
            error: function(xhr, status, error) {
                console.log("Delete Contact Error:", xhr.responseText);
                alert("Error deleting contact.");
            }
        });
    }
});



// When a "View Details" button is clicked:
$(document).on('click', '.viewCustomerBtn', function(e) {
    e.preventDefault();
    var customerId = $(this).data('id');
    $.ajax({
        url: "{{ url('customers') }}/" + customerId,
        type: "GET",
        success: function(response) {
            // Load the modal content with the returned view
            $('#viewCustomerModal .modal-content').html(response);
            $('#viewCustomerModal').modal('show');
        },
        error: function(xhr) {
            console.log("View Customer Error:", xhr.responseText);
            alert("Error loading customer details.");
        }
    });
});

// Save new customer contact via Ajax
$(document).on('click', '#saveNewContact', function(){
    var customerId = $('#current_customer_id').val();
    var name  = $('#new_contact_name').val();
    var email = $('#new_contact_email').val();
    var phone = $('#new_contact_phone').val();
    // If you have a fourth input, grab it here as well.
    // var position = $('#new_contact_position').val();

    if (!name) {
        alert("Please enter the contact name.");
        return;
    }
    
    $.ajax({
        url: "/customers/" + customerId + "/contacts",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            contact_name: name,
            contact_email: email,
            contact_phone: phone
            // contact_position: position // if applicable
        },
        success: function(response) {
            var contact = response.contact;
            var newRow = `<tr id="contactRow-${contact.id}">
                <td>${contact.contact_name}</td>
                <td>${contact.contact_email}</td>
                <td>${contact.contact_phone}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-secondary editContactBtn" data-id="${contact.id}">Edit</button>
                    <button type="button" class="btn btn-sm btn-danger deleteContactBtn" data-id="${contact.id}">Delete</button>
                </td>
            </tr>`;
            if ($('#contactsTable tbody').length) {
                $('#contactsTable tbody').append(newRow);
            } else {
                $('#noContactsMsg').remove();
                var tableHtml = `<table class="table table-bordered" id="contactsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${newRow}
                    </tbody>
                </table>`;
                $('#contactsSection').html(tableHtml);
            }
            // Clear the form inputs
            $('#new_contact_name').val('');
            $('#new_contact_email').val('');
            $('#new_contact_phone').val('');
            // If using a fourth input, clear it as well.
            // $('#new_contact_position').val('');
            //showPopup(response.message);
            alert(response.message);
        },
        error: function(xhr) {
            console.log("Error saving contact:", xhr.responseText);
            alert("Error saving contact.");
        }
    });
});
// Utility: Show popup modal with message
function showPopup(message) {
    $('#popupMessageModal .modal-body').html("<p>" + message + "</p>");
    $('#popupMessageModal').modal('show');
}



    
    // Bind events
    $(document).on('click', '#saveCustomerBtn', function(e) {
        saveCustomer();
    });
    $(document).on('click', '.editCustomerBtn', function(e) {
        var id = $(this).data('id');
        editCustomer(id);
    });
    $(document).on('click', '.deleteCustomerBtn', function(e) {
        var id = $(this).data('id');
        deleteCustomer(id);
    });
    $(document).on('click', '#updateCustomerBtn', function(e) {
        updateCustomer();
    });
</script>
@endsection
