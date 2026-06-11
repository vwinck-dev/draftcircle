{{ trans('ui.brand.name') }}

{{ trans('ui.mail.greeting', ['name' => $recipientName]) }}

{{ $heading }}
{{ $body }}

@if (! empty($detailValue))
{{ $detailLabel }}: {{ $detailValue }}

@endif
{{ $actionText }}: {{ $actionUrl }}

{{ trans('ui.mail.footer') }}
{{ trans('ui.brand.motto') }}
