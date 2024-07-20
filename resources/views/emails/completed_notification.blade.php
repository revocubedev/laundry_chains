@component('mail::message')

@component('mail::button', ['url' => ""])
<img style="max-width: 300px;" src="{{ $logo }}">
@endcomponent

Dear {{$user['full_name']}},<br><br>
Congratulations!!! Your order ({{$order['serial_number']}}) has been picked up and is now completed!!!<br><br>
We hope our services met your expectations. Please ensure to use Cleanace Laundry and Dry Cleaning service again in the future.

Thanks,<br>
{{ $companyName }}
@endcomponent