<x-mail::message>
# Welcome, {{ $name }}!

An account has been created for you on {{ config('app.name') }}.

You'll receive a separate email shortly with a link to set your password. Once that's done, you can log in using the email address **{{ $email }}**.

<x-mail::button :url="config('app.url').'/login'">
Log In
</x-mail::button>

If you weren't expecting this email, please contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>