@component('mail::message')

@component('mail::button', ['url' => ""])
<img style="max-width: 300px;" src="{{ $logo }}">
@endcomponent

Dear {{$user['full_name']}},<br><br>
Your order has been successfully created.<br><br>

@component('mail::panel')
Order Number: {{$order['serial_number']}}<br>
Number of Pieces: {{$order['itemsCount']}}<br>
Estimated Pickup Date: {{$order['dateTimeOut']}}<br>
Balance Due: {{money_format("NGN %i",$order['bill'] - $order['paidAmount'])}}
@endcomponent

ITEM SUMMARY<br>
{{$order['summary']}}<br><br>

Thanks,<br>
{{ $companyName }}
@endcomponent