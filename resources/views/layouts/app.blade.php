<!DOCTYPE html>
<html>
<head>
    <title>Thumbnail Processor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <div style="max-width: 800px; margin: 0 auto;">
            @yield('content')
        </div>
    </div>
</body>
</html>