<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Auth' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ loaded: true, darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false') }"
      x-init="$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
      :class="{'dark': darkMode === true}"
      class="bg-gray-50 dark:bg-gray-900">
    {{ $slot }}
</body>
</html>
