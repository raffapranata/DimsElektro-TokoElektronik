@props(['value', 'format' => 'd F Y, H:i'])

{{ $value === null ? '-' : $value->translatedFormat($format) }}
