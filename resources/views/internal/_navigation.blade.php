<nav class="internal-nav mb-4" aria-label="Interná navigácia">
    <a href="{{ route('internal.dashboard') }}" @class(['active' => request()->routeIs('internal.dashboard')])>Prehľad</a>
    @can('manage-products')
        <a href="{{ route('internal.products.index') }}" @class(['active' => request()->routeIs('internal.products.*')])>Produkty</a>
    @endcan
    @can('manage-users')
        <a href="{{ route('internal.users.index') }}" @class(['active' => request()->routeIs('internal.users.*')])>Používatelia</a>
    @endcan
    @can('export-consents')
        <a href="{{ route('admin.consents.export') }}">Export súhlasov</a>
    @endcan
</nav>