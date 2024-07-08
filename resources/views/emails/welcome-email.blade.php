<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Welcome to Laundry Chains</title>
</head>

<body>
    <h1>Welcome to Laundry Chains</h1>

    <p>Dear {{ $companyName }},</p>

    <p>Thank you for registering with our platform. We are excited to have you on board!</p>

    <p>Here are a few things you can do to get started:</p>

    <ul>
        <li>Explore our platform and familiarize yourself with its features.</li>
        <li>Set up your company profile and customize your account settings.</li>
        <li>Invite your team members to join and collaborate.</li>
        <li>Reach out to our support team if you have any questions or need assistance.</li>
    </ul>

    <p>We hope you have a great experience using Laundry Chains. If you have any feedback or suggestions, please let us know.</p>

    <p>Thank you again for choosing our platform!</p>
    <p>Click the button below to get started</p>
    <a href="{{ $url }}">
        <button style="background-color: #4CAF50; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Get Started</button>
    </a>

    <p>Best regards,</p>
    <p>Laundry Chains Team</p>
</body>

</html>