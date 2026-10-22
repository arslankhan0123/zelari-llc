New sourcing request — {{ $sourcing->reference }}
=================================================

{{ $sourcing->name }} wants {{ $sourcing->items->count() }} {{ Str::plural('product', $sourcing->items->count()) }} sourced{{ $sourcing->source_country ? ' from ' . $sourcing->source_country : '' }}.

Contact
-------
Name: {{ $sourcing->name }}
Email: {{ $sourcing->email }}
@if($sourcing->phone)Phone: {{ $sourcing->phone }}
@endif
@if($sourcing->company)Company: {{ $sourcing->company }}
@endif
@if($sourcing->deliver_to)Deliver to: {{ $sourcing->deliver_to }}
@endif
@if($sourcing->budget)Budget: {{ $sourcing->budget }}
@endif
@if($sourcing->needed_by)Needed by: {{ $sourcing->needed_by->format('d M Y') }}
@endif

Products
--------
@foreach($sourcing->items as $item)
{{ $loop->iteration }}. {{ $item->name }} — {{ number_format($item->quantity) }}{{ $item->unit ? ' ' . $item->unit : '' }}
@if($item->specs) Specs: {{ $item->specs }}
@endif
@if($item->target_price) Target price: {{ $item->target_price }}
@endif
@if($item->reference_url) Reference: {{ $item->reference_url }}
@endif
@endforeach
@if($sourcing->notes)

Notes
-----
{{ $sourcing->notes }}
@endif

--
Reply to this email and it goes to {{ $sourcing->email }}.
Open in admin: {{ $adminUrl }}