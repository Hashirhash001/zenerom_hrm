<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout Blocked</title>
    <!-- Optionally include Bootstrap CSS (using CDN here) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Bootstrap Modal -->
    <div class="modal" tabindex="-1" id="logoutModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Incomplete Task Update</h5>
          </div>
          <div class="modal-body">
            <p>You haven't updated the task status for all assigned tasks. Please update them before logging out.</p>
          </div>
          <div class="modal-footer">
            <button type="button" id="closeModal" class="btn btn-primary">OK</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script>
        // When the page loads, show the modal.
        document.addEventListener("DOMContentLoaded", function(){
            var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();

            // When the user clicks the OK button, redirect to my tasks page.
            document.getElementById('closeModal').addEventListener('click', function(){
                window.location.href = "{{ route('my_tasks.index') }}";
            });
        });
    </script>
</body>
</html>
