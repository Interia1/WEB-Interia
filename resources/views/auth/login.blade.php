@extends('layouts.app')

@section('title', 'Prihlásenie | WEB-Interia')
@section('description', 'Prihlásenie do zákazníckej zóny WEB-Interia.')

@section('content')
<section class="py-2 border-bottom auth-shell auth-shell-compact">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="card auth-card border-0">
                    <div class="card-body p-2 p-lg-3">
                        <h1 class="h4 mb-1">Prihlásenie do zákazníckej zóny</h1>
                        <p class="small text-secondary mb-2">Po prihlásení získate prístup k objednávkam, dokumentom a komunikácii podľa architektúry projektu.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="post" action="{{ route('login.store') }}" class="d-grid gap-1 auth-form-compact">
                            @csrf

                            <div>
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" autocomplete="email" required>
                            </div>

                            <div>
                                <label for="password" class="form-label">Heslo</label>
                                <input id="password" name="password" type="password" class="form-control" autocomplete="current-password" required>
                            </div>

                            <div class="form-check">
                                <input id="remember" name="remember" type="checkbox" value="1" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember" class="form-check-label">Zapamätať si ma</label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">Prihlásiť sa</button>
                        </form>

                        <p class="small mt-2 mb-0"><a href="{{ route('password.request') }}">Zabudli ste heslo?</a></p>

                        <hr class="my-2">
                        <p class="small mb-2">Nemáte účet? <a href="{{ route('register') }}">Zaregistrujte sa</a>.</p>
                        <p class="small text-secondary mb-0">Pokračovaním súhlasíte s <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener">obchodnými podmienkami</a> a ste oboznámený so <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener">spracovaním osobných údajov</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
