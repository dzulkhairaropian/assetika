<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory QR Codes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .qr-container {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code {
            border: 1px solid #ddd;
            padding: 10px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Inventory Item QR Codes</h1>
    
    <div class="qr-container">
        <h2>Item: {{ $item->name ?? 'Sample Item' }}</h2>
        <div class="qr-code">
            {!! $qrCode !!}
        </div>
        <p>Scan to view item details</p>
    </div>
    
    <h3>Inventory List</h3>
    <table>
        <thead>
            <tr>
                <th>Item ID</th>
                <th>Name</th>
                <th>QR Code</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Laptop</td>
                <td>
                    <img src="{{ route('qrcode.inventory', ['id' => 1]) }}" alt="QR Code" width="50" height="50">
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>Monitor</td>
                <td>
                    <img src="{{ route('qrcode.inventory', ['id' => 2]) }}" alt="QR Code" width="50" height="50">
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>Keyboard</td>
                <td>
                    <img src="{{ route('qrcode.inventory', ['id' => 3]) }}" alt="QR Code" width="50" height="50">
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>