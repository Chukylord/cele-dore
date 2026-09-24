<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center px-5 py-2.5
                bg-[#13c79a] border border-transparent rounded-xl
                font-bold text-xs text-white uppercase tracking-wider
                shadow-sm hover:bg-[#078263]
                focus:outline-none focus:ring-2 focus:ring-[#13c79a]
                focus:ring-offset-2
                active:bg-[#05634d]
                transition ease-in-out duration-150'
]) }}>
    {{ $slot }}
</button>