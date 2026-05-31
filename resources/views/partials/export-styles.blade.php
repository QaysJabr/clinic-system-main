{{-- Styles for Headless Chrome PDF export (RTL + layout). --}}
@include('partials.export-tailwind')
<style>
    .grid { display: grid; }
    .flex { display: flex; }
    .flex-wrap { flex-wrap: wrap; }
    .items-start { align-items: flex-start; }
    .justify-between { justify-content: space-between; }
    .gap-2 { gap: 0.5rem; }
    .gap-3 { gap: 0.75rem; }
    .gap-4 { gap: 1rem; }
    .gap-6 { gap: 1.5rem; }
    .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .shrink-0 { flex-shrink: 0; }
    .min-w-0 { min-width: 0; }
    .flex-1 { flex: 1 1 0%; }
    .overflow-x-auto { overflow-x: auto; }
    .w-full { width: 100%; }
    .min-w-\[640px\] { min-width: 640px; }
    .rounded-xl { border-radius: 0.75rem; }
    .rounded-lg { border-radius: 0.5rem; }
    .border { border-width: 1px; border-style: solid; border-color: #e5e7eb; }
    .border-b { border-bottom-width: 1px; border-bottom-style: solid; border-color: #e5e7eb; }
    .border-dashed { border-style: dashed; }
    .border-gray-200 { border-color: #e5e7eb; }
    .border-gray-300 { border-color: #d1d5db; }
    .bg-white { background-color: #fff; }
    .bg-gray-50\/80 { background-color: rgba(249, 250, 251, 0.8); }
    .p-4 { padding: 1rem; }
    .p-5 { padding: 1.25rem; }
    .px-4 { padding-left: 1rem; padding-right: 1rem; }
    .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
    .pb-6 { padding-bottom: 1.5rem; }
    .mb-3 { margin-bottom: 0.75rem; }
    .mb-6 { margin-bottom: 1.5rem; }
    .mb-8 { margin-bottom: 2rem; }
    .mt-1 { margin-top: 0.25rem; }
    .mt-2 { margin-top: 0.5rem; }
    .mt-10 { margin-top: 2.5rem; }
    .m-0 { margin: 0; }
    .text-xs { font-size: 0.75rem; line-height: 1rem; }
    .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
    .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
    .text-2xl { font-size: 1.5rem; line-height: 2rem; }
    .font-bold { font-weight: 700; }
    .font-semibold { font-weight: 600; }
    .uppercase { text-transform: uppercase; }
    .tracking-wide { letter-spacing: 0.025em; }
    .tabular-nums { font-variant-numeric: tabular-nums; }
    .text-gray-400 { color: #9ca3af; }
    .text-gray-500 { color: #6b7280; }
    .text-gray-600 { color: #4b5563; }
    .text-gray-700 { color: #374151; }
    .text-gray-800 { color: #1f2937; }
    .text-\[\#0F4C81\] { color: #0F4C81; }
    .text-emerald-700 { color: #047857; }
    .text-rose-700 { color: #be123c; }
    .text-cyan-900 { color: #164e63; }
    .text-indigo-950 { color: #1e1b4b; }
    .text-amber-900 { color: #78350f; }
    .text-violet-950 { color: #2e1065; }
    .text-teal-900 { color: #134e4a; }
    .text-red-700 { color: #b91c1c; }
    .shadow-sm { box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
    .whitespace-pre-wrap { white-space: pre-wrap; }
    .whitespace-nowrap { white-space: nowrap; }
    .object-contain { object-fit: contain; }
    .h-16 { height: 4rem; }
    .max-w-\[180px\] { max-width: 180px; }
    table { border-collapse: collapse; }
    .divide-y > * + * { border-top: 1px solid #e5e7eb; }
    @media (min-width: 640px) {
        .sm\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sm\:px-5 { padding-left: 1.25rem; padding-right: 1.25rem; }
    }
    @media (min-width: 1024px) {
        .lg\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
</style>
