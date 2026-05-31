<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-lg border border-transparent bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-[#0c3f6a] focus:outline-none focus:ring-2 focus:ring-[#0F4C81] focus:ring-offset-2 disabled:opacity-50 dark:bg-[#3B82F6] dark:hover:bg-blue-600 dark:focus:ring-[#3B82F6] dark:focus:ring-offset-[#0F172A]']) }}>
    {{ $slot }}
</button>
