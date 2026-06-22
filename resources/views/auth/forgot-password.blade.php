@extends('layouts.app')

@section('title', 'Zabudnuté heslo | WEB-Interia')
@section('description', 'Obnova hesla do zákazníckej zóny WEB-Interia.')

@section('content')
<section class="py-5 border-bottom auth-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="card auth-card border-0">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 mb-3">Zabudnuté heslo</h1>
                        <p class="text-secondary mb-4">Zadajte e-mail vášho účtu. Pošleme vám odkaz na obnovu hesla.</p>

                        @if (session('status'))
                            <div class="alert alert-success" role="status">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                        @endif

                        <form method="post" action="{{ route('password.email') }}" class="d-grid gap-3">
                            @csrf
                            <div>
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" autocomplete="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Odoslať odkaz na obnovu</button>
                        </form>

                        <hr class="my-4">
                        <p class="small mb-0"><a href="{{ route('login') }}">Späť na prihlásenie</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
