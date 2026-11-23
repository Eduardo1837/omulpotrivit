<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin - Planuri Pro & Promovare') }}
            </h2>
            <div class="flex items-center gap-4">
                <div class="text-xs text-gray-500 font-medium">
                    Total planuri: <span class="font-bold text-gray-800">{{ $plans->count() }}</span>
                </div>
                <a href="{{ route('admin.plans.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs shadow-sm transition">
                    + Adaugă Plan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-admin-nav :breadcrumbs="['Planuri Pro' => null]" />

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 overflow-x-auto">
                <div class="mb-4">
                    <h3 class="text-base font-bold text-gray-900">Planuri de Promovare Disponibile pe Platformă</h3>
                    <p class="text-xs text-gray-500">Configurația planurilor gratuite și Pro oferite utilizatorilor pentru promovarea anunțurilor.</p>
                </div>

                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tl-lg">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Nume Plan</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Tip</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Preț</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Durată Promovare</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Limită Anunțuri Promovate</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Vizibilitate</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tr-lg">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 divide-y divide-gray-100">
                        @forelse($plans as $plan)
                            @php
                                $isPaid = (float) $plan->price > 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-xs text-gray-500">{{ $plan->id }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-900">{{ $plan->name }}</td>
                                <td class="py-3 px-4">
                                    @if($isPaid)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            ★ Pro (Plătit)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                            Gratuit
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-semibold text-gray-900">
                                    {{ number_format((float) $plan->price, 2) }} {{ $plan->currency ?? 'RON' }}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600">
                                    {{ $plan->durationDays > 0 ? $plan->durationDays . ' zile' : 'Fără promovare' }}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600">
                                    @if($plan->maxItems === null)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">
                                            Nelimitat
                                        </span>
                                    @else
                                        {{ $plan->maxItems }} {{ $plan->maxItems === 1 ? 'anunț' : 'anunțuri' }}
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($plan->is_hidden)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                            Ascuns
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            Vizibil
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.plans.edit', $plan->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white py-1 px-3 rounded text-xs font-semibold transition">
                                            Editează
                                        </a>

                                        <form action="{{ route('admin.plans.toggle', $plan->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="py-1 px-3 rounded text-xs font-semibold transition {{ $plan->is_hidden ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-gray-600 hover:bg-gray-700 text-white' }}">
                                                {{ $plan->is_hidden ? 'Afișează' : 'Ascunde' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Sigur dorești să ștergi acest plan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-xs font-semibold transition">
                                                Șterge
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 px-4 text-center text-sm text-gray-500">
                                    Nu există planuri de promovare active sau ascunse.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deletedPlans->isNotEmpty())
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 overflow-x-auto">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Istoric Planuri Șterse</h3>
                            <p class="text-xs text-gray-500">Planurile eliminate din ofertă, păstrate în istoric pentru referință și abonamente anterioare.</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                            {{ $deletedPlans->count() }} {{ $deletedPlans->count() === 1 ? 'plan șters' : 'planuri șterse' }}
                        </span>
                    </div>

                    <table class="min-w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600">
                                <th class="py-2.5 px-4 uppercase font-semibold text-xs rounded-tl-lg">ID</th>
                                <th class="py-2.5 px-4 uppercase font-semibold text-xs">Nume Plan</th>
                                <th class="py-2.5 px-4 uppercase font-semibold text-xs">Tip</th>
                                <th class="py-2.5 px-4 uppercase font-semibold text-xs">Preț</th>
                                <th class="py-2.5 px-4 uppercase font-semibold text-xs">Durată</th>
                                <th class="py-2.5 px-4 uppercase font-semibold text-xs">Limită</th>
                                <th class="py-2.5 px-4 uppercase font-semibold text-xs rounded-tr-lg">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 divide-y divide-gray-100">
                            @foreach($deletedPlans as $deletedPlan)
                                @php
                                    $isPaidDeleted = (float) $deletedPlan->price > 0;
                                @endphp
                                <tr class="bg-rose-50/30 opacity-80">
                                    <td class="py-3 px-4 font-mono text-xs text-gray-500">{{ $deletedPlan->id }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-500 line-through">{{ $deletedPlan->name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isPaidDeleted ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $isPaidDeleted ? '★ Pro (Plătit)' : 'Gratuit' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm font-semibold text-gray-700">
                                        {{ number_format((float) $deletedPlan->price, 2) }} {{ $deletedPlan->currency ?? 'RON' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-500">
                                        {{ $deletedPlan->durationDays > 0 ? $deletedPlan->durationDays . ' zile' : 'Fără promovare' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-500">
                                        {{ $deletedPlan->maxItems === null ? 'Nelimitat' : $deletedPlan->maxItems . ($deletedPlan->maxItems === 1 ? ' anunț' : ' anunțuri') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                            Șters
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
