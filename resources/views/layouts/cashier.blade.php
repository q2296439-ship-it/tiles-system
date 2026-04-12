@extends('layouts.cashier') {{-- or layouts.app kung ito gamit mo sa POS --}}

@section('content')
<div class="container-fluid">
    <div class="row">

        {{-- LEFT SIDE --}}
        <div class="col-md-8">
            <h4>Transfer In Request</h4>

            <input type="text" id="search" class="form-control mb-3" placeholder="Search product...">

            <div class="row" id="product-list">
                @foreach($products as $product)
                    <div class="col-md-4 mb-3">
                        <div class="card p-3 product-card"
                             data-id="{{ $product->id }}"
                             data-name="{{ $product->name }}">
                             
                            <h6>{{ $product->name }}</h6>
                            <p class="text-success">₱{{ number_format($product->price,2) }}</p>
                            <small>Stock: {{ $product->stock }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- RIGHT SIDE --}}
        <div class="col-md-4">
            <h5>Request Cart</h5>

            {{-- ✅ ONLY FIX NEEDED --}}
            <form action="{{ route('cashier.transfer.in.store') }}" method="POST">
                @csrf

                <table class="table" id="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="mb-2">
                    <label>From Branch</label>
                    <select name="from_branch_id" class="form-control" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>

                <button class="btn btn-primary w-100">Submit Request</button>
            </form>
        </div>

    </div>
</div>
@endsection


@section('scripts')
<script>
let cart = [];

document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', () => {
        let id = card.dataset.id;
        let name = card.dataset.name;

        let existing = cart.find(item => item.id == id);

        if (existing) {
            existing.qty++;
        } else {
            cart.push({id, name, qty: 1});
        }

        renderCart();
    });
});

function renderCart() {
    let tbody = document.querySelector('#cart-table tbody');
    tbody.innerHTML = '';

    cart.forEach((item, index) => {
        tbody.innerHTML += `
            <tr>
                <td>
                    ${item.name}
                    <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                </td>
                <td>
                    <input type="number" name="items[${index}][qty]" value="${item.qty}" min="1" class="form-control">
                </td>
                <td>
                    <button type="button" onclick="removeItem(${index})">❌</button>
                </td>
            </tr>
        `;
    });
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}
</script>
@endsection