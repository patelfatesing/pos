<h2>Shift Closed Summary</h2>

<p><strong>User:</strong> </p>
<p><strong>Branch:</strong> </p>
<p><strong>Start Time:</strong> </p>
<p><strong>End Time:</strong> </p>

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
