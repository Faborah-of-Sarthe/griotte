@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="http://{{ env('SANCTUM_STATEFUL_DOMAINS')}}{{env('LOGO')}}" class="logo" alt="{{ $slot ?? 'Logo'}}">
</td>
</tr>
