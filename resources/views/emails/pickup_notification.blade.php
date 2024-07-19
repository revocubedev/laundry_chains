@component('mail::message')

@component('mail::button', ['url' => ""])
<img style="max-width: 300px;" src="{{ $logo }}">
@endcomponent

Dear {{$user['full_name']}},<br><br>
Your order ({{$order['serial_number']}}) has been completed and is ready for pickup.<br><br>
Kindly go to the {{$order['location']['locationName']}} cleanace store to pick your order.

Thanks,<br>
{{ $companyName }}
@endcomponent