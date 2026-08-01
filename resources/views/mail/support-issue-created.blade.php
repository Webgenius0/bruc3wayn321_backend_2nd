<h2>New support issue</h2>
<p><strong>From:</strong> {{ $issue->name }} ({{ $issue->email }})</p>
<p><strong>Subject:</strong> {{ $issue->subject }}</p>
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($issue->message)) !!}</p>
