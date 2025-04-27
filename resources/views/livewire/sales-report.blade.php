<div>
    <!-- Date Range Selector -->
    <div class="mb-4">
        <label for="range">Select Date Range:</label>
        <select wire:model="range" id="range" class="form-select w-1/4">
            <option value="week">Last Week</option>
            <option value="month">Last Month</option>
            <option value="year">Last Year</option>
            <option value="custom">Custom</option>
        </select>
    </div>

    <!-- Show date picker inputs when "Custom" is selected -->
    @if ($range === 'custom')
        <div class="mb-4">
            <label for="start_date">Start Date:</label>
            <input wire:model="start_date" type="date" id="start_date" class="form-input w-1/4">
        </div>
        <div class="mb-4">
            <label for="end_date">End Date:</label>
            <input wire:model="end_date" type="date" id="end_date" class="form-input w-1/4">
        </div>
    @endif

    <!-- DataTable -->
    <table id="sales-table" class="display">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Total Quantity</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($this->getFilteredData() as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['total_quantity'] }}</td>
                    <td>₹{{ number_format($item['total_amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#sales-table').DataTable();

        // Watch for changes in range or dates and reload the table
        Livewire.on('reloadData', () => {
            $('#sales-table').DataTable().ajax.reload();
        });
    });
</script>
