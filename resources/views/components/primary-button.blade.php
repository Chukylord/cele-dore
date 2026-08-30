<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center px-5 py-2.5
                bg-[#8f57a6] border border-transparent rounded-xl
                font-bold text-xs text-white uppercase tracking-wider
                shadow-sm hover:bg-[#6f3e86]
                focus:outline-none focus:ring-2 focus:ring-[#8f57a6]
                focus:ring-offset-2
                active:bg-[#5c3272]
                transition ease-in-out duration-150'
]) }}>
    {{ $slot }}
</button>