@extends('layouts.app')

@section('title', 'Interná zóna | WEB-Interia')

@section('content')
<section class="internal-shell py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <p class="text-uppercase small text-primary fw-semibold mb-2">Interná zóna</p>
                <h1 class="h2 mb-2">Pracovný prehľad</h1>
                <p class="text-secondary mb-0">Prihlásený: {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</p>
            </div>
            <a class="btn btn-outline-primary" href="{{ route('customer.account.edit') }}">Môj účet</a>
        </div>

        @include('internal._navigation')

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4"><div class="internal-stat"><span>Produkty</span><strong>{{ $productCount }}</strong></div></div>
            <div class="col-12 col-md-4"><div class="internal-stat"><span>Zákazníci</span><strong>{{ $customerCount }}</strong></div></div>
            <div class="col-12 col-md-4"><div class="internal-stat"><span>Interní pracovníci</span><strong>{{ $workerCount }}</strong></div></div>
        </div>

        <div class="internal-panel">
            <h2 class="h4 mb-3">Pracovné moduly</h2>
            <div class="d-flex flex-wrap gap-2">
                @can('manage-products')
                    <a class="btn btn-primary" href="{{ route('internal.products.index') }}">Spravovať produkty</a>
                @endcan
                @can('manage-users')
                    <a class="btn btn-outline-primary" href="{{ route('internal.users.index') }}">Spravovať používateľov</a>
                @endcan
            </div>
        </div>
    </div>
</section>
@endsection