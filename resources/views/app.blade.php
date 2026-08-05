<!DOCTYPE html>
{{--
    lang/dir are rendered server-side so an RTL locale paints correctly on the
    very first frame; the language switcher updates them client-side after.
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />

    <title inertia>{{ config('app.name', 'Athr') }}</title>

    @vite(['resources/js/main.js'])
    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>
