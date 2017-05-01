
<option value="0">Select your unit number</option>
@for ($i = 0; $i < count($unitNumbers); $i++)
    <option value="{{ $i+1 }}">{{ $unitNumbers[$i] }}</option>
@endfor
