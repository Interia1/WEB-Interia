@if ($errors->any())
    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Meno</label>
        <input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">E-mail</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="role">Rola</label>
        <select class="form-select" id="role" name="role" @disabled($user->exists && auth()->user()->is($user)) required>
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
        @if ($user->exists && auth()->user()->is($user))
            <input type="hidden" name="role" value="{{ $user->role->value }}">
            <div class="form-text">Vlastnú rolu nie je možné zmeniť.</div>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password">{{ $user->exists ? 'Nové heslo (voliteľné)' : 'Dočasné heslo' }}</label>
        <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" @required(! $user->exists)>
    </div>
    <div class="col-md-6 ms-md-auto">
        <label class="form-label" for="password_confirmation">Potvrdenie hesla</label>
        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $user->exists)>
    </div>
</div>
<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Uložiť používateľa</button>
    <a class="btn btn-outline-secondary" href="{{ route('internal.users.index') }}">Zrušiť</a>
</div>