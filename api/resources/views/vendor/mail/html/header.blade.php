@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ getFrontUrl() }}/{{env('LOGO')}}" class="logo" alt="{{ $slot ?? 'Logo'}}">
</td>
</tr>
