<h1>A new order has been placed!</h1>

<p>
    <b>Order number</b><br>
    #{{$order->id}}
</p>

<p>
    <b>Created at</b><br>
    {{$order->created_at}}
</p>

<p>
    <b>Payment option</b><br>
    {{$order->payment_gateway}} 
</p>

<p>
    <b>Customer details</b><br>
    {{$order->first_name.' '.$order->last_name}}<br>
    @if($order->address)
        {{$order->address}}
    @endif
    @if($order->zipcode)
        <br>{{$order->zipcode}}
    @endif
    @if($order->city)
        {{$order->city}}
    @endif

    @if($order->email)
        <br><a href="mailto:{{$order->email}}">{{$order->email}}</a>
    @endif
    @if($order->phone)
        <br>{{$order->phone}}
    @endif
</p>

<p>
    <b>Order specification</b><br>
    <table style="width: 100%; text-align: left; margin-top: 15px;" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{$item->title}}</td>
                    <td>{{$item->quantity}}</td>
                    <td style="text-align: right;">{{$item->total_formatted}}<td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; padding-top: 10px;">
                    <b>Total amount: {{$order->total_formatted}}</b>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right; color: grey;">
                    Tax: {{$order->tax_formatted}}
                </td>
            </tr>
        </tfoot>
    </table>

</p>