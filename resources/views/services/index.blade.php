@extends('layouts.app')

@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <!-- Header -->
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Services</h3>
                            <div class="nk-block-des text-soft">
                                <p>You have total {{ $services->count() }} Services.</p>
                            </div>
                        </div>
                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                                    <em class="icon ni ni-menu-alt-r"></em>
                                </a>
                                <div class="toggle-expand-content" data-content="pageMenu">
                                    <ul class="nk-block-tools g-3">
                                        <li>
                                            <div>
                                                <input type="text" class="form-control" id="serviceSearch" placeholder="Search Services">
                                            </div>
                                        </li>
                                        <li class="nk-block-tools-opt d-none d-sm-block">
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                                <em class="icon ni ni-plus"></em><span>Add Service</span>
                                            </a>
                                        </li>
                                        <li class="nk-block-tools-opt d-block d-sm-none">
                                            <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                                <em class="icon ni ni-plus"></em>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- .toggle-wrap -->
                        </div>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Services List Container -->
                <div id="servicesContainer">
                    @include('services._list', ['services' => $services])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Service -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      @include('services.create_modal')
    </div>
  </div>
</div>

<!-- Modal for Editing Service -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Edit form will be loaded via Ajax -->
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



<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script src="{{ asset('assets1/jquery-ui/jquery-ui.js') }}"></script>
<script>
    // Save new Service via Ajax
    function saveService() {
        var formData = new FormData($('#addServiceForm')[0]);
        $.ajax({
            url: "{{ route('service.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                showPopup(response.message);
                $('#addServiceModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                console.log("Save Service Error:", xhr.responseText);
                showPopup("Error saving service.");
            }
        });
    }
    
    // Load Edit Service form via Ajax
      function editService(id) {
        $.ajax({
            url: "{{ url('services') }}/" + id + "/edit",
            type: "GET",
            success: function(response) {
                $('#editServiceModal .modal-content').html(response);
                $('#editServiceModal').modal('show');
            },
            error: function(xhr) {
                console.log("Edit Service Error:", xhr.responseText);
                showPopup("Error loading edit form.");
            }
        });
    }

    
    // Update Service via Ajax
    function updateService() {
        var formData = new FormData($('#editServiceForm')[0]);
        var serviceId = $('#editServiceForm input[name="id"]').val();
        $.ajax({
            url: "{{ url('services') }}/" + serviceId,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                showPopup(response.message);
                $('#editServiceModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                console.log("Update Service Error:", xhr.responseText);
                showPopup("Error updating service.");
            }
        });
    }
    
    // Delete Service via Ajax
    function deleteService(id) {
        if (confirm('Are you sure you want to delete this service?')) {
            $.ajax({
                url: "{{ url('services') }}/" + id,
                type: "DELETE",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    showPopup(response.message);
                    location.reload();
                },
                error: function(xhr) {
                    console.log("Delete Service Error:", xhr.responseText);
                    showPopup("Error deleting service.");
                }
            });
        }
    }
    
    // Live search for services
    $('#serviceSearch').on('keyup', function(){
        var query = $(this).val();
        $.ajax({
            url: "{{ route('service.search') }}",
            type: "GET",
            data: { query: query },
            success: function(response) {
                $('#servicesContainer').html(response.html);
            },
            error: function(xhr) {
                console.log("Service Search Error:", xhr.responseText);
            }
        });
    });
    
    // Utility: Show popup modal with message
    function showPopup(message) {
        $('#popupMessageModal .modal-body').html("<p>" + message + "</p>");
        $('#popupMessageModal').modal('show');
    }
    
    // Bind events
    $(document).on('click', '#saveServiceBtn', function(e) {
        saveService();
    });
    $(document).on('click', '.editServiceBtn', function(e) {
        var id = $(this).data('id');
        editService(id);
    });
    $(document).on('click', '.deleteServiceBtn', function(e) {
        var id = $(this).data('id');
        deleteService(id);
    });
    $(document).on('click', '#updateServiceBtn', function(e) {
        updateService();
    });
</script>
@endsection
