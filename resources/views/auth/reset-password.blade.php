@extends('layouts.app')

@section('title', 'Obnova hesla | WEB-Interia')
@section('description', 'Nastavenie nového hesla do zákazníckej zóny WEB-Interia.')

@section('content')
<section class="py-5 border-bottom bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 mb-3">Nastavenie nového hesla</h1>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                        @endif

                        <form method="post" action="{{ route('password.update') }}" class="d-grid gap-3">
                            @csrf

                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div>
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" class="form-control" autocomplete="email" required>
                            </div>

                            <div>
                                <label for="password" class="form-label">Nové heslo</label>
                                <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" required>
                            </div>

                            <div>
                                <label for="password_confirmation" class="form-label">Potvrdenie hesla</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Uložiť nové heslo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
