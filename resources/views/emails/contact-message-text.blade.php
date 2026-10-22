New contact message — {{ $store['name'] }}
==========================================

{{ $contact->subject_line }}

{{ $contact->name }} wrote to you on {{ $contact->created_at->format('d M Y \a\t H:i') }}.

From:  {{ $contact->name }}
Email: {{ $contact->email }}

Message
-------
{{ $contact->message }}

--
Reply straight to this email and it goes to {{ $contact->email }}.
Open in admin: {{ $adminUrl }}

Sent automatically by {{ $store['name'] }} when someone submits the contact form.
