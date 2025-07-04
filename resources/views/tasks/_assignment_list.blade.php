@if($assignments->count())
  @foreach($assignments as $assignment)
    <div class="card mb-3 assignment-card" 
         data-date="{{ \Carbon\Carbon::parse($assignment->date)->toDateString() }}" 
         data-staff="{{ optional($assignment->staff)->id }}" 
         data-status="{{ $assignment->status }}">
      <div class="card-header">
        <strong>Staff:</strong> {{ optional($assignment->staff)->first_name }} {{ optional($assignment->staff)->middle_name }} {{ optional($assignment->staff)->last_name }}
        | <strong>Date:</strong> {{ \Carbon\Carbon::parse($assignment->date)->format('d M Y') }}
        | <strong>Status:</strong> {{ $assignment->status == 0 ? 'Pending' : 'Completed' }}
      </div>
      <div class="card-body">
        <h5>Documents</h5>
        @if($assignment->documents->count())
          <ul>
            @foreach($assignment->documents as $document)
              <li>
                <a href="{{ asset($document->file_path) }}" target="_blank">
                  {{ $document->document_name }}
                </a> - {{ $document->description }}
              </li>
            @endforeach
          </ul>
        @else
          <p>No documents uploaded.</p>
        @endif

        <h5>Comments</h5>
        @if($assignment->comments->count())
          <ul>
            @foreach($assignment->comments as $comment)
              <li>
                <strong>{{ optional($comment->user)->first_name }} {{ optional($comment->user)->middle_name }} {{ optional($comment->user)->last_name }}:</strong>
                {{ $comment->comment }}
                <small> ({{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }})</small>
              </li>
            @endforeach
          </ul>
        @else
          <p>No comments.</p>
        @endif
      </div>
    </div>
  @endforeach
@else
  <p>No assignments found for the selected filters.</p>
@endif
