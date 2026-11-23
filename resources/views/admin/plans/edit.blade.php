<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin - Editează Plan de Promovare') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-admin-nav :breadcrumbs="['Planuri Pro' => route('admin.plans.index'), 'Editează: ' . $plan->name => null]" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nume Plan</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $plan->name) }}"
                               required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descriere Plan</label>
                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  placeholder="Descrierea beneficiilor acestui plan..."
                                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $plan->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Preț</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="price"
                                   id="price"
                                   value="{{ old('price', $plan->price) }}"
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Monedă</label>
                            <select name="currency"
                                    id="currency"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="RON" @selected(old('currency', $plan->currency ?? 'RON') === 'RON')>RON</option>
                                <option value="EUR" @selected(old('currency', $plan->currency ?? 'RON') === 'EUR')>EUR</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="durationDays" class="block text-sm font-medium text-gray-700 mb-1">Durată Promovare (zile)</label>
                            <input type="number"
                                   min="0"
                                   name="durationDays"
                                   id="durationDays"
                                   value="{{ old('durationDays', $plan->durationDays) }}"
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="maxItems" class="block text-sm font-medium text-gray-700 mb-1">Limită Anunțuri Promovate</label>
                            <input type="number"
                                   min="1"
                                   name="maxItems"
                                   id="maxItems"
                                   value="{{ old('maxItems', $plan->maxItems) }}"
                                   placeholder="Lasă gol pentru nelimitat"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Anulează
                        </a>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                            Actualizează Planul
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
