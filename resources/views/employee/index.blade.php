<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registro de Ponto') }}
        </h2>
    </x-slot>

    @if(session('success'))
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-8">
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">
                                {{ now()->format('d/m/Y') }}
                            </h3>
                            <div class="text-6xl font-mono text-blue-600 mb-4" id="clock">
                                {{ now()->format('H:i:s') }}
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <form method="POST" action="{{ route('clock-in.store') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
                                Registrar Ponto
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('pt-BR');
            document.getElementById('clock').textContent = timeString;
        }
        
        setInterval(updateClock, 1000);
    </script>
</x-app-layout> 