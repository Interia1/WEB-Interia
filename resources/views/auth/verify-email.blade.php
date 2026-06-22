@extends('layouts.app')

@section('title', 'Overenie e-mailu | WEB-Interia')
@section('description', 'Overenie e-mailovej adresy pre prístup do zákazníckej zóny WEB-Interia.')

@section('content')
<section class="py-5 border-bottom bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 mb-3">Potvrďte svoj e-mail</h1>
                        <p class="text-secondary">Pred vstupom do zákazníckej zóny je potrebné overiť e-mailovú adresu kliknutím na odkaz, ktorý sme vám poslali.</p>

                        @if (session('status'))
                            <div class="alert alert-success" role="status">{{ session('status') }}</div>
                        @endif

                        <div class="d-flex flex-wrap gap-2">
                            <form method="post" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">Znova odoslať overovací e-mail</button>
                            </form>

                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">Odhlásiť sa</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
