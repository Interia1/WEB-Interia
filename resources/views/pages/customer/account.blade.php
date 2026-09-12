@extends('layouts.app')

@section('title', 'Môj účet | WEB-Interia')

@section('content')
<section class="auth-shell py-5 border-bottom">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">
                <div class="card auth-card border-0"><div class="card-body p-4 p-lg-5">
                    <h1 class="h3 mb-2">Môj účet</h1>
                    <p class="text-secondary mb-4">Rola účtu: {{ $user->role->label() }}</p>
                    @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
                    <form method="post" action="{{ route('customer.account.update') }}" class="d-grid gap-3">
                        @csrf
                        @method('PUT')
                        <div><label class="form-label" for="name">Meno</label><input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
                        <div><label class="form-label" for="email">E-mail</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required></div>
                        <button class="btn btn-primary" type="submit">Uložiť účet</button>
                    </form>
                </div></div>
            </div>
        </div>
    </div>
</section>
@endsection