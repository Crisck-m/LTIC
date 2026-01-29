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
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="input-field w-full px-4 py-3 rounded-lg" placeholder="••••••••">
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
</x-guest-layout>