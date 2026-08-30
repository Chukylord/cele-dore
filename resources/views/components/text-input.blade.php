@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'border-[#e7e1ec]
                    focus:border-[#8f57a6]
                    focus:ring-[#8f57a6]
                    rounded-xl shadow-sm
                    transition'
    ]) }}
>