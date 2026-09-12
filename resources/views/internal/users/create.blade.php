@extends('layouts.app')

@section('title', 'Nový používateľ | WEB-Interia')

@section('content')
<section class="internal-shell py-5"><div class="container">
    <h1 class="h2 mb-4">Nový používateľ</h1>
    @include('internal._navigation')
    <form class="internal-panel" method="post" action="{{ route('internal.users.store') }}">
        @csrf
        @include('internal.users._form')
    </form>
</div></section>
@endsection