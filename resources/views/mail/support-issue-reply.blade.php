<h2>Support reply</h2>
<p>Hello {{ $issue->name }},</p>
<p><strong>Regarding:</strong> {{ $issue->subject }}</p>
<p>{!! nl2br(e($issue->reply)) !!}</p>
