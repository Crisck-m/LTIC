<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-semibold" />
            <x-text-input id="email" class="input-field block mt-2 w-full px-4 py-3 rounded-lg" type="email"
                name="email" :value="old('email')" required autofocus autocomplete="username"
                placeholder="usuario@puce.edu.ec" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-semibold" />
            <x-text-input id="password" class="input-field block mt-2 w-full px-4 py-3 rounded-lg" type="password"
                name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-4 h-4" name="remember">
            <label for="remember_me" class="ml-2 text-sm text-gray-600 cursor-pointer">
                {{ __('Recordarme') }}
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="puce-button w-full py-3 px-4 rounded-lg text-white text-base">
                {{ __('Iniciar sesión') }}
            </button>
        </div>
    </form>
</x-guest-layout>