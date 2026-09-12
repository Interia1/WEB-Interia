@extends('layouts.app')

@section('title', 'Používatelia | WEB-Interia')

@section('content')
<section class="internal-shell py-5"><div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="h2 mb-0">Používatelia</h1>
        <a class="btn btn-primary" href="{{ route('internal.users.create') }}">Pridať používateľa</a>
    </div>
    @include('internal._navigation')
    <div class="internal-panel table-responsive">
        <table class="table align-middle mb-0 internal-table">
            <thead><tr><th>Meno</th><th>E-mail</th><th>Rola</th><th>Overenie</th><th></th></tr></thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role->label() }}</td>
                        <td>{{ $user->hasVerifiedEmail() ? 'Overený' : 'Neoverený' }}</td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('internal.users.edit', $user) }}">Upraviť</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
</div></section>
@endsection