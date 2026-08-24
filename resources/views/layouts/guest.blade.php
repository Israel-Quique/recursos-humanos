<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Ingreso - Recursos Humanos' }}</title>
    <link href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    @livewireStyles
  </head>
  <body class="guest-body text-slate-800">
    <main class="guest-shell">
      {{ $slot }}
    </main>
    @livewireScripts
    <script src="{{ asset('vendor/d3/d3.min.js') }}?v={{ @filemtime(public_path('vendor/d3/d3.min.js')) }}"></script>
    <script src="{{ asset('vendor/topojson/topojson-client.min.js') }}?v={{ @filemtime(public_path('vendor/topojson/topojson-client.min.js')) }}"></script>
    <script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
  </body>
</html>
