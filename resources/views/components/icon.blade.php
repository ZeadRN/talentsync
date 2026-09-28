@props(['name'=>'grid'])
@php($paths=[
'grid'=>'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
'briefcase'=>'M8 6V4h8v2 M3 7h18v14H3z M3 12c6 4 12 4 18 0 M10 13h4',
'search'=>'M21 21l-5-5 M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0',
'bookmark'=>'M6 3h12v18l-6-4-6 4z',
'user'=>'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0 M4 21v-2a8 8 0 0 1 16 0v2',
'users'=>'M15 21v-2a6 6 0 0 0-12 0v2 M12 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0 M16 4a3 3 0 0 1 0 6 M21 21v-3a6 6 0 0 0-4-5',
'bell'=>'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9 M10 21h4',
'arrow'=>'M5 12h14 M13 6l6 6-6 6',
'up'=>'M6 18L18 6 M6 6h12v12',
'check'=>'M5 12l4 4L19 6',
'clock'=>'M12 7v5l3 2 M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0',
'location'=>'M19 9c0 6-7 12-7 12S5 15 5 9a7 7 0 1 1 14 0 M14 9a2 2 0 1 1-4 0 2 2 0 0 1 4 0',
'chart'=>'M4 3v18h17 M8 16v-5 M13 16V7 M18 16v-8',
'shield'=>'M12 2l9 4v6c0 5-9 10-9 10S3 17 3 12V6z M8 12l3 3 5-6',
'logout'=>'M9 3H3v18h6 M8 12h13 M16 7l5 5-5 5',
'plus'=>'M12 5v14 M5 12h14',
'menu'=>'M4 6h16 M4 12h16 M4 18h16',
'spark'=>'M12 3l2.4 6.6L21 12l-6.6 2.4L12 21l-2.4-6.6L3 12l6.6-2.4z',
'calendar'=>'M4 5h16v16H4z M8 2v6 M16 2v6 M4 11h16',
'file'=>'M5 3h10l4 4v14H5z M14 3v5h5 M8 12h8 M8 16h5'
])
<svg {{ $attributes->merge(['class'=>'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name]??$paths['grid'] }}"/></svg>
