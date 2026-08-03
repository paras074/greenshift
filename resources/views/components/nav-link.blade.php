@props(['active'])

@php
$classes = ($active ?? false)
            ? 'transition duration-150 ease-in-out'
            : 'transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
