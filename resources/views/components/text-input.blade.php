@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'border-[#bdeedf]
                    focus:border-[#13c79a]
                    focus:ring-[#13c79a]
                    rounded-xl shadow-sm
                    transition'
    ]) }}
>