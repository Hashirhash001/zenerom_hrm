<div class="accordion-item" id="descriptionRow-{{ $description->id }}">
    <a class="accordion-head collapsed" data-bs-toggle="collapse" data-bs-target="#accordionDesc-{{ $description->id }}" aria-expanded="false">
        <h6 class="title">{{ $description->title }}</h6>
        <span class="accordion-icon"></span>
    </a>
    <div id="accordionDesc-{{ $description->id }}" class="accordion-body collapse" data-bs-parent="#accordionService-{{ $description->project_service_id }}">
        <div class="accordion-inner">
            {!! $description->details !!}
            <p>
                <small>
                    Entered on: 
                    {{ $description->entered_date ? \Carbon\Carbon::parse($description->entered_date)->format('d M Y') : 'N/A' }}
                </small>
            </p>

            <!-- Attached Files Section -->
            <div class="attached-files">
                <ul class="file-list list-group" id="documentList-{{ $description->id }}">
        @if($description->files->count())
            @foreach($description->files as $file)
                @include('project_descriptions._document_item', ['document' => $file])
            @endforeach
        @else
            <p id="noFilesMsg-{{ $description->id }}">No files attached.</p>
        @endif
    </ul>
            </div>
            <!-- End Attached Files Section -->

            <!-- Action Buttons -->
            <div class="mt-2">
                <!-- <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                    <em class="icon ni ni-plus"></em> Upload Document
                </button> -->
                <button type="button" class="btn btn-primary btn-sm addDocumentBtn" data-description-id="{{ $description->id }}">
                <em class="icon ni ni-plus"></em> Add Document
            </button>
                <button type="button" class="btn btn-sm btn-warning editDescriptionBtn" data-id="{{ $description->id }}">
                    <em class="icon ni ni-edit"></em> Edit
                </button>
                <button type="button" class="btn btn-sm btn-danger deleteDescriptionBtn" data-id="{{ $description->id }}">
                    <em class="icon ni ni-trash"></em> Delete
                </button>
            </div>
        </div>
    </div>
</div>
