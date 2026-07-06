<x-mail::message>
# Welcome, {{ $name }}!

An account has been created for you on {{ config('app.name') }}.

@if($password)
Here are your login details:

**Email:** {{ $email }}
**Temporary password:** {{ $password }}

Please log in and change your password as soon as possible.
@else
You can log in using the email address **{{ $email }}** and the password you set.
@endif

<x-mail::button :url="config('app.url').'/login'">
Log In
</x-mail::button>

If you weren't expecting this email, please contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>