<x-guest-layout>
    <div class="text-center py-4">
        <!-- Lock Icon -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 text-amber-600 mb-4">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>

        <h2 class="text-xl font-bold text-gray-900 mb-2">
            Autentificare Necesară
        </h2>

        <p class="text-sm text-gray-600 mb-6 leading-relaxed">
            Pentru a accesa această secțiune și restul funcționalităților platformei, este necesar să fii autentificat în contul tău.
        </p>

        <!-- Actions -->
        <div class="space-y-3">
            <a href="{{ route('login') }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm text-sm transition">
                Intră în cont (Log in)
            </a>

            <a href="{{ route('register') }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-lg border border-gray-300 shadow-sm text-sm transition">
                Creează cont nou (Înregistrare)
            </a>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100">
            <a href="{{ route('home') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-gray-800 transition">
                &larr; Înapoi la Pagina Principală
            </a>
        </div>
    </div>
</x-guest-layout>
