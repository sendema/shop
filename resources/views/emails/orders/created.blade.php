@component('mail::message')
    # Order #{{ $order->id }} Created

    Dear {{ $order->name }},

    Thank you for your order. Here are the details:

    @component('mail::table')
        | Product | Quantity | Price |
        |:--------|:---------|:------|
        @foreach($order->items as $item)
            | {{ $item->name_product }} | {{ $item->qty }} | ${{ number_format($item->price * $item->qty, 2) }} |
        @endforeach
    @endcomponent

    **Total: ${{ number_format($order->sum, 2) }}**

    Thanks,
    {{ config('app.name') }}
@endcomponent

