<x-mail::message>
# Welcome to the circle, {{ $name }}.

You've joined a space where people practice standing in each other's shoes.

We believe every perspective is a door, not a verdict. We're glad you're here to open a few.

<x-mail::button :url="$url">
Explore Perspectives
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
