@extends('layouts.app')

@section('title', 'Materiály | WEB-Interia')
@section('description', 'Prehľad používaných materiálov a odporúčania podľa použitia, odolnosti a ceny.')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="display-6 mb-3">Materiály</h1>
        <p class="lead text-secondary">Správny materiál je základ kvality. Vyberáme ho podľa účelu, prostredia a rozpočtu projektu.</p>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th scope="col">Materiál</th>
                    <th scope="col">Vhodné použitie</th>
                    <th scope="col">Výhody</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Hliník</td>
                    <td>Exteriér, ľahké konštrukcie</td>
                    <td>Nízka hmotnosť, odolnosť voči korózii</td>
                </tr>
                <tr>
                    <td>Oceľ</td>
                    <td>Nosné a namáhané prvky</td>
                    <td>Vysoká pevnosť, dlhá životnosť</td>
                </tr>
                <tr>
                    <td>Nerez</td>
                    <td>Vlhké prostredie, dizajnové prvky</td>
                    <td>Estetika, odolnosť, jednoduchá údržba</td>
                </tr>
                <tr>
                    <td>Kompozity</td>
                    <td>Špecifické technické aplikácie</td>
                    <td>Variabilita vlastností podľa potreby</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection