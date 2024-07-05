@component('mail::message')

Dear {{ $fullName }}<br>
These are your login info: <br>

@component('mail::panel')
Email: {{ $email }}
Password: {{ $password}}<br>
Staff Code: {{ $staff_code }}

@component('mail::button', ['url' => {{ $url }}])
Click Here To Login
@endcomponent

@endcomponent

Thanks,<br>
{{ $companyName }}
@endcomponent