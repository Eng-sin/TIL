<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{$title ?? 'My App'}}</title>
  <link rel="stylesheet" href="{{asset('css/reset.css')}}">
  @stack('styles')
</head>

<body>
  <x-header />

  <main>
    @yield('content')
  </main>

  @stack('scripts')
</body>