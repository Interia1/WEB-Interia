@php($input = fn ($key, $default) => old('_editor') === $fieldPrefix ? old($key, $default) : $default)
<input type="hidden" name="_editor" value="{{ $fieldPrefix }}">
<div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label" for="{{ $fieldPrefix }}-name">Názov</label><input class="form-control" id="{{ $fieldPrefix }}-name" name="name" value="{{ $input('name', $parameter->name) }}" maxlength="100" required></div>
    <div class="col-md-3"><label class="form-label" for="{{ $fieldPrefix }}-type">Typ</label><select class="form-select" id="{{ $fieldPrefix }}-type" name="type">
        @foreach (['select' => 'Výber zo zoznamu', 'number' => 'Číslo', 'boolean' => 'Áno / nie'] as $value => $label)
            <option value="{{ $value }}" @selected($input('type', $parameter->type) === $value)>{{ $label }}</option>
        @endforeach
    </select></div>
    <div class="col-md-2"><label class="form-label" for="{{ $fieldPrefix }}-unit">Jednotka</label><input class="form-control" id="{{ $fieldPrefix }}-unit" name="unit" value="{{ $input('unit', $parameter->unit) }}" maxlength="20"></div>
    <div class="col-md-2"><label class="form-label" for="{{ $fieldPrefix }}-position">Poradie</label><input class="form-control" id="{{ $fieldPrefix }}-position" name="position" type="number" min="0" max="65535" value="{{ $input('position', $parameter->position ?? 0) }}" required></div>
    <div class="col-md-2"><button class="btn btn-primary" type="submit" title="Uložiť parameter"><i class="bi bi-check-lg" aria-hidden="true"></i> Uložiť</button></div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}-options">Možnosti (jedna na riadok)</label><textarea class="form-control" id="{{ $fieldPrefix }}-options" name="options_text" rows="2">{{ $input('options_text', implode("\n", $parameter->options ?? [])) }}</textarea></div>
    <div class="col-md-6 d-flex gap-3 flex-wrap py-2">
        <label><input class="form-check-input" name="required" type="checkbox" value="1" @checked(old('_editor') === $fieldPrefix ? old('required', false) : $parameter->required)> Povinný údaj</label>
        <label><input class="form-check-input" name="filterable" type="checkbox" value="1" @checked(old('_editor') === $fieldPrefix ? old('filterable', false) : $parameter->filterable)> Zobraziť filter</label>
    </div>
</div>