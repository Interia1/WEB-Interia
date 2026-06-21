@extends('layouts.app')

@section('title', 'Služby | WEB-Interia')
@section('description', 'Prehľad hlavných služieb WEB-Interia: návrh riešenia, výroba na mieru, montáž a servis.')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="display-6 mb-3">Služby</h1>
        <p class="lead text-secondary">Poskytujeme kompletný proces od konzultácie po realizáciu, aby ste mali jedno kontaktné miesto pre celý projekt.</p>
        <div class="row g-4 mt-1">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">Konzultácia a návrh</h2>
                        <p class="mb-0 text-secondary">Vyhodnotíme požiadavky, navrhneme riešenie a odporučíme vhodné materiály.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">Výroba na mieru</h2>
                        <p class="mb-0 text-secondary">Realizujeme kusovú aj sériovú výrobu podľa špecifikácie klienta.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">Montáž a odovzdanie</h2>
                        <p class="mb-0 text-secondary">Zabezpečíme logistiku, montáž a odovzdanie hotového riešenia.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">Podpora a servis</h2>
                        <p class="mb-0 text-secondary">Po realizácii poskytujeme technickú podporu a servisné zásahy.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection