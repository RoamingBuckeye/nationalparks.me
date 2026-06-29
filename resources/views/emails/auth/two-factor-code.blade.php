@component('mail::message')
# Your login code

Use the code below to finish signing in. It expires in 10 minutes.

@component('mail::panel')
# {{ $code }}
@endcomponent

If you didn't try to sign in, you can ignore this email — your account is still secure.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
