<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión empresarial (D06).
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión del usuario anunciante.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))
                ->with('status', '¡Bienvenido de nuevo a tu panel de anunciante!');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Muestra el formulario de registro empresarial (D06).
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $regions = Region::where('is_active', true)->orderBy('name')->get();

        return view('auth.register', compact('regions'));
    }

    /**
     * Registra un nuevo usuario anunciante y su empresa inicial (D06, D08).
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'region_id' => ['required', 'exists:regions,id'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'El nombre del contacto es obligatorio.',
            'business_name.required' => 'El nombre comercial de la empresa es obligatorio.',
            'region_id.required' => 'Debes seleccionar tu región base de operaciones.',
            'region_id.exists' => 'La región seleccionada no es válida.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        // 1. Crear el usuario anunciante (exclusivo empresarial, D06)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 2. Crear la entidad empresarial asociada
        Business::create([
            'owner_user_id' => $user->id,
            'region_id' => $validated['region_id'],
            'name' => $validated['business_name'],
            'description' => 'Perfil comercial registrado en Un Minuto Para Vender.',
            'status' => 'verified',
        ]);

        // 3. Autenticar inmediatamente
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', '¡Registro exitoso! Tu cuenta empresarial ha sido creada.');
    }

    /**
     * Cierra la sesión activa.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('viewer.home')
            ->with('status', 'Has cerrado sesión correctamente.');
    }
}
