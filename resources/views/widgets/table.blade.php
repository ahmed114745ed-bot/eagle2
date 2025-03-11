<style>
    table.custom-table {
        width: 100%;
        background-color: #c98b39; /* Brownish background */
        color: #fff; /* Dark text for body */
        border-collapse: collapse;
    }

    table.custom-table th, table.custom-table td {
        border: 1px solid #694413;
        padding: 10px;
    }

    /* Fix: Improved header visibility */
    table.custom-table thead {
        background-color: #482808; /* Dark brown */
        color: #fff; /* White text for better contrast */
        font-weight: bold;
    }

    /* Hover effect */
    table.custom-table tbody tr:hover {
        background-color: #e2a76f; /* Light brown */
    }
</style>

<table class="custom-table" {!! $attributes !!}>
    <thead>
        <tr>
            @foreach($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                @foreach($row as $item)
                    <td>{!! $item !!}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

