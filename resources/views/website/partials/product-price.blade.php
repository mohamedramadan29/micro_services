{{-- A product's price: the discounted price with the old one struck through, or «مجاني». --}}
<span class="rf-pr-price">
    @if ((float) $product->price <= 0)
        <strong>مجاني</strong>
    @elseif ((float) $product->discount > 0)
        <del>{{ \App\Support\Money::show($product->price) }}</del>
        <strong>{{ \App\Support\Money::show($product->discount) }}</strong>
    @else
        <strong>{{ \App\Support\Money::show($product->price) }}</strong>
    @endif
</span>
