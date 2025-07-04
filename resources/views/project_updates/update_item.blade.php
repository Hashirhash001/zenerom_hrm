<div class="accordion-item" id="updateRow-{{ $update->id }}">
    <a class="accordion-head collapsed" data-bs-toggle="collapse" data-bs-target="#accordionUpdate-{{ $update->id }}" aria-expanded="false">
        <h6 class="title">
            {{ $update->title }}
            @if(isset($update->employee) && $update->employee->first_name )
                <span class="text-muted">( {{ $update->employee->first_name }} {{ $update->employee->middle_name }} {{ $update->employee->last_name }})</span>
            @elseif(isset($update->first_name))
                <span class="text-muted">({{ $update->first_name }} {{ $update->middle_name }} {{ $update->last_name }})</span>
            @endif
        </h6>
        <span class="accordion-icon"></span>
    </a>
    <div id="accordionUpdate-{{ $update->id }}" class="accordion-body collapse" data-bs-parent="#accordionUpdates">
        <div class="accordion-inner">
            <p>{!! $update->note !!}</p>
            <p>
                <small>
                    Entered on: {{ $update->date ? \Carbon\Carbon::parse($update->date)->format('d M Y') : 'N/A' }}
                </small>
            </p>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-warning editUpdateBtn" data-id="{{ $update->id }}">
                    <em class="icon ni ni-edit"></em> Edit
                </button>
                <button type="button" class="btn btn-sm btn-danger deleteUpdateBtn" data-id="{{ $update->id }}">
                    <em class="icon ni ni-trash"></em> Delete
                </button>
            </div>
        </div>
    </div>
</div>