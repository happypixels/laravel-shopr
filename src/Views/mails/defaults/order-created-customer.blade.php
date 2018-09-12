<h1>Thank you for your order!</h1>

<p>Your order has been received and will be processed shortly.</p>

<h2 style="margin-bottom: 5px;">Order number: #{{ $order->id }}</h2>
<p style="margin-top: 0;">
    Payment status:
    @if($order->payment_status === 'paid')
        <span style="color: green;">Paid</span>
    @else
        <span style="color: yellow;">Awaiting payment</span>
    @endif
</p>

<table style="width: 100%; text-align: left;" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th style="padding: 5px;">Product</th>
            <th style="padding: 5px;">Quantity</th>
            <th style="padding: 5px; text-align: right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
            <tr>
                <td style="border-top: 1px solid grey; border-bottom: 1px solid grey; padding: 5px;">{{$item->title}}</td>
                <td style="border-top: 1px solid grey; border-bottom: 1px solid grey; padding: 5px;">{{$item->quantity}}</td>
                <td style="border-top: 1px solid grey; border-bottom: 1px solid grey; padding: 5px; text-align: right;">{{$item->total_formatted}}<td>
            </tr>
        @endforeach
    </tbody>
    <tfoot style="text-align: right;">
        <tr>
            <td colspan="3" style="padding: 5px; font-weight: bold;">
                Total amount: {{$order->total_formatted}}
            </td>
        </tr>
        <tr>
            <td colspan="3" style="padding-right: 5px; color: grey;">
                Tax: {{$order->tax_formatted}}
            </td>
        </tr>
    </tfoot>
</table>

<h2>Your details</h2>

<p>
    {{$order->first_name}} {{$order->last_name}}<br>
    @if($order->address)
        {{$order->address}}
    @endif
    @if($order->zipcode)
        <br>{{$order->zipcode}}
    @endif
    @if($order->city)
        {{$order->city}}
    @endif
</p>

<p>
@if($order->phone)
    {{$order->phone}}<br>
@endif
@if($order->email)
    {{$order->email}}
@endif
</p>