@extends('layouts.app')

@section('title', 'Kontakt | WEB-Interia')
@section('description', 'Kontaktujte WEB-Interia cez jednoduchý formulár.')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="display-6 mb-4">Kontakt</h1>
        <div class="row g-4">
            <div class="col-lg-8">
                <form method="post" action="{{ route('contact.submit') }}" class="card card-body shadow-sm" aria-label="Kontaktný formulár">
                    @if (session('status'))
                        <div class="alert alert-success" role="status">{{ session('status') }}</div>
                    @endif
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Meno</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Správa</label>
                        <textarea id="message" name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Odoslať</button>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="card card-body h-100">
                    <h2 class="h5">Kontaktné údaje</h2>
                    <p class="small mb-1">WEB-Interia</p>
                    <p class="small mb-1"><a href="mailto:info@web-interia.sk">info@web-interia.sk</a></p>
                    <p class="small mb-0">+421 900 000 000</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
