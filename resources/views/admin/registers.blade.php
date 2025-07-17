<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registros de Ponto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Data Início</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Data Fim</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Filtrar</button>
                        <a href="{{ route('admin.registers') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Limpar</a>
                    </div>
                </form>

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="overflow-x-auto bg-white rounded shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Funcionário</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cargo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Idade</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Gestor</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($clockIns as $clockIn)
                                <tr>
                                    <td class="px-4 py-2">{{ $clockIn['id'] }}</td>
                                    <td class="px-4 py-2">{{ $clockIn['name'] }}</td>
                                    <td class="px-4 py-2">{{ $clockIn['job_title'] }}</td>
                                    <td class="px-4 py-2">{{ $clockIn['age'] }}</td>
                                    <td class="px-4 py-2">{{ $clockIn['manager_name'] }}</td>
                                    <td class="px-4 py-2">{{ $clockIn['registered_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">Nenhum registro encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $clockIns->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 