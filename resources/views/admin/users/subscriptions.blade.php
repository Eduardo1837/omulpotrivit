<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin - Istoric Abonamente:') }} {{ $user->name }}
            </h2>
            <div class="text-xs text-gray-500 font-medium">
                Total înregistrări: <span class="font-bold text-gray-800">{{ $promotions->count() }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-admin-nav :breadcrumbs="['Utilizatori' => route('admin.users.index'), 'Abonamente: ' . $user->name => null]" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 overflow-x-auto">
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Istoric Planuri & Promovări</h3>
                        <p class="text-xs text-gray-500">Utilizator: <strong>{{ $user->name }}</strong> ({{ $user->email }})</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                        &larr; Înapoi la Utilizatori
                    </a>
                </div>

                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tl-lg">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Plan</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Preț</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Anunț Asociat</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Data Început</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Data Expirare</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tr-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 divide-y divide-gray-100">
                        @forelse($promotions as $promotion)
                            @php
                                $plan = $promotion->plan;
                                $payment = $promotion->payment->first();
                                $isPaid = $payment ? ((float) $payment->amount > 0) : ($plan && (float) $plan->price > 0);
                                $listing = $promotion->listing;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-xs text-gray-500">{{ $promotion->id }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-900">{{ $plan?->name ?? 'Plan șters' }}</span>
                                        @if($isPaid)
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Pro</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">Gratuit</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm font-semibold text-gray-800">
                                    @if($payment)
                                        <div>{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency ?? 'RON' }}</div>
                                        <div class="text-[11px] font-medium text-emerald-700">{{ $payment->status }} ({{ $payment->method ?? 'card' }})</div>
                                    @elseif($plan)
                                        {{ number_format((float) $plan->price, 2) }} {{ $plan->currency ?? 'RON' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    @if($listing)
                                        <span class="font-medium text-gray-900">{{ $listing->title }}</span>
                                        @if($listing->status === 'deleted')
                                            <span class="ml-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-100 text-rose-700">Șters de utilizator</span>
                                        @elseif($listing->status === 'inactiv')
                                            <span class="ml-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-600">Inactiv</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-600">
                                    {{ $promotion->startsAt ? \Carbon\Carbon::parse($promotion->startsAt)->format('d.m.Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-600">
                                    {{ $promotion->endsAt ? \Carbon\Carbon::parse($promotion->endsAt)->format('d.m.Y H:i') : 'Nelimitat / Gratuit' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $promotion->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $promotion->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 px-4 text-center text-sm text-gray-500">
                                    Acest utilizator nu are niciun abonament sau promovare înregistrată.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
