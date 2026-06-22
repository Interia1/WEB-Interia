@extends('layouts.app')

@section('title', 'Registrácia | WEB-Interia')
@section('description', 'Registrácia do zákazníckej zóny WEB-Interia s GDPR a obchodnými podmienkami.')

@section('content')
<section class="py-5 border-bottom auth-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">
                <div class="card auth-card border-0">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 mb-3">Registrácia zákazníka</h1>
                        <p class="text-secondary mb-4">Vytvorte si účet pre objednávky, dokumenty a komunikáciu. Povinné súhlasy sú evidované s časom a IP adresou.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="post" action="{{ route('register.store') }}" class="d-grid gap-3">
                            @csrf

                            <div>
                                <label for="name" class="form-label">Meno a priezvisko</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control" autocomplete="name" required>
                            </div>

                            <div>
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" autocomplete="email" required>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="password" class="form-label">Heslo</label>
                                    <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="password_confirmation" class="form-label">Potvrdenie hesla</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required>
                                </div>
                            </div>

                            <div class="form-check mt-2">
                                <input id="gdpr_consent" name="gdpr_consent" type="checkbox" value="1" class="form-check-input" {{ old('gdpr_consent') ? 'checked' : '' }} required>
                                <label for="gdpr_consent" class="form-check-label">
                                    Súhlasím so spracovaním osobných údajov podľa
                                    <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener">zásad ochrany osobných údajov</a>.
                                </label>
                            </div>

                            <div class="form-check">
                                <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" class="form-check-input" {{ old('terms_accepted') ? 'checked' : '' }} required>
                                <label for="terms_accepted" class="form-check-label">
                                    Súhlasím s
                                    <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener">obchodnými podmienkami</a>.
                                </label>
                            </div>

                            <div class="form-check">
                                <input id="marketing_consent" name="marketing_consent" type="checkbox" value="1" class="form-check-input" {{ old('marketing_consent') ? 'checked' : '' }}>
                                <label for="marketing_consent" class="form-check-label">
                                    Chcem dostávať obchodné a marketingové informácie e-mailom (voliteľné).
                                </label>
                            </div>

                            <div class="auth-info">
                                Povinné súhlasy sú archivované s časovou pečiatkou a IP adresou v súlade s nastavením zákazníckej zóny.
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">Vytvoriť účet</button>
                        </form>

                        <hr class="my-4">
                        <p class="small text-secondary mb-0">Už máte účet? <a href="{{ route('login') }}">Prihláste sa</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
