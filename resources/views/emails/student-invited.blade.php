<x-mail::message>
# {{ __('Hello :name', ['name' => $recipientName]) }}

{{ __('You have been invited to join **:workspace**.', ['workspace' => $workspaceName]) }}

<x-mail::button :url="$loginUrl">
{{ __('Sign in') }}
</x-mail::button>

<x-mail::panel>
**{{ __('Email') }}:** {{ $inviteeEmail }}<br>
**{{ __('Temporary password') }}:** {{ $temporaryPassword }}
</x-mail::panel>

{{ __('Please sign in and change your password as soon as possible.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>
