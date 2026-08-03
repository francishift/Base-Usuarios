@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    {{-- Logo de Arengalia Labs en todos los emails --}}
    <img
        src="{{ asset('logo.png') }}"
        alt="{{ config('app.name') }}"
        style="max-height: 60px; width: auto; display: block;"
    >
</a>
</td>
</tr>
