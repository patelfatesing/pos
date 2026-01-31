<h2>Shift Closed Summary</h2>

<p><strong>Branch:</strong> {{ $shift->branch->name ?? '' }}</p>
<p><strong>Start Time:</strong> {{ $shift->start_time }}</p>
<p><strong>End Time:</strong> {{ $shift->end_time }}</p>

<hr>

<h3>Shift Summary</h3>

<table cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse; width:100%;">
    @foreach ($summary as $label => $value)
        <tr>
            <td>{{ $label }}</td>
            <td style="text-align:right;">{{ $value }}</td>
        </tr>
    @endforeach
</table>

<p>-- End of Report --</p>
