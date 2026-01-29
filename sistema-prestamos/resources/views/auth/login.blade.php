<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Usuario -->
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                Usuario
            </label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                autocomplete="username" class="input-field w-full px-4 py-3 rounded-lg" placeholder="Usuario">
            @error('username')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Contraseña
            </label>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="input-field w-full px-4 py-3 pr-12 rounded-lg" placeholder="••••••••">
                <button type="button" onclick="togglePassword()"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <!-- Ojo Cerrado (password oculto) -->
                    <svg id="eye-closed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                        </path>
                    </svg>

                    <!-- Ojo Abierto (password visible) -->
                    <svg id="eye-open" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                Recordarme
            </label>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit"
                class="puce-button w-full text-white py-3 px-4 rounded-lg hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Iniciar sesión
            </button>
        </div>
    </form>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeClosed = document.getElementById('eye-closed');
            const eyeOpen = document.getElementById('eye-open');

            if (passwordInput.type === 'password') {
                // Mostrar contraseña
                passwordInput.type = 'text';
                eyeClosed.classList.add('hidden');
                eyeOpen.classList.remove('hidden');
            } else {
                // Ocultar contraseña
                passwordInput.type = 'password';
                eyeClosed.classList.remove('hidden');
                eyeOpen.classList.add('hidden');
            }
        }
    </script>
</x-guest-layout>