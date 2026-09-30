<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Perpustakaan')</title>
    <style>
        body { font-family: sans-serif; margin: 40px; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        .success, .alert-success { background: #d1fae5; color: #065f46; padding: 10px 14px; border-radius: 4px; margin-top: 16px; }
        .btn { display: inline-block; padding: 6px 14px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        form.inline { display: inline; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-dikembalikan { background-color: #d1fae5; color: #065f46; }
        .badge-dipinjam { background-color: #fef3c7; color: #92400e; }
        .badge-terlambat { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @yield('content')
</body>
</html>
