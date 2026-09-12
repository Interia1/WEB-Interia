@extends('layouts.app')

@section('title', 'Upraviť používateľa | WEB-Interia')

@section('content')
<section class="internal-shell py-5"><div class="container">
    <h1 class="h2 mb-4">Upraviť používateľa</h1>
    @include('internal._navigation')
    <form class="internal-panel" method="post" action="{{ route('internal.users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('internal.users._form')
    </form>
</div></section>
@endsection