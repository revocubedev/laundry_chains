<!DOCTYPE html>
<html>

<head>
    <title>New Staff Added</title>
</head>

<body>
    <h1>Welcome to our team!</h1>
    <p>Your login credentials are:</p>
    <ul>
        <li><strong>Email:</strong> {{ $email }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
        <li><strong>Staff Code:</strong> {{ $staff_code }}</li>
    </ul>
    <p>Please click the button below to login:</p>
    <a href="{{ $url }}" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: #fff; text-decoration: none;">Click here to login</a>

    <p>Best regards,</p>
    <p>{{ $companyName }} Team</p>
</body>

</html>

@component('mail::message')

Dear {{ $fullName }}<br>
These are your login info: <br>

@component('mail::panel')
Email: {{ $email }}
Password: {{ $password }}<br>
Staff Code: {{ $staff_code }}

@endcomponent

Thanks,<br>
{{ $companyName }} Team
@endcomponent