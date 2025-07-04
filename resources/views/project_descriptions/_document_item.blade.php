<li class="list-group-item d-flex justify-content-between align-items-center" id="fileRow-{{ $document->id }}">
    <a href="{{ asset('uploads/project_descriptions/' . $document->file_name) }}" download>
        {{ $document->file_name }}
    </a>
    <button type="button" class="btn btn-sm btn-danger deleteFileBtn" data-file-id="{{ $document->id }}">
        <em class="icon ni ni-trash"></em> Delete
    </button>
</li>
