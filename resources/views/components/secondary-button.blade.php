<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center justify-center px-5 py-2.5
                bg-white border border-[#e7e1ec] rounded-xl
                font-bold text-xs text-[#6f3e86] uppercase tracking-wider
                shadow-sm
                hover:bg-[#f8f4fa] hover:border-[#8f57a6]
                focus:outline-none focus:ring-2 focus:ring-[#8f57a6]
                focus:ring-offset-2
                disabled:opacity-25
                transition ease-in-out duration-150'
]) }}>
    {{ $slot }}
</button>