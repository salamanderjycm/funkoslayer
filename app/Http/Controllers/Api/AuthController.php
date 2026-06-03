<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Bloquear si no ha verificado su cuenta (opcional pero recomendado)
        if (isset($user->is_verified) && !$user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, verifica tu correo con el código que te enviamos.'
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Register user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        // 1. Generamos el código mágico de 6 dígitos
        $code = strval(rand(100000, 999999));

        // 2. Creamos al usuario con estado "no verificado"
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'verification_code' => $code,
            'is_verified' => false
        ]);

        // 3. Disparamos el correo
        try {
            Mail::to($user->email)->send(new VerificationCodeMail($code));
        } catch (\Exception $e) {
            Log::error('Error enviando correo de verificación: ' . $e->getMessage());
            // Si el correo falla, igual dejamos que siga el flujo para no romper la app en desarrollo
        }

        // 4. Respondemos a Vue PERO SIN TOKEN (aún no están logueados)
        return response()->json([
            'success' => true,
            'message' => 'Registro exitoso. Revisa tu correo.',
            'email' => $user->email // Vue necesita esto para el Paso 2
        ], 201);
    }

    /**
     * Verify Code (NUEVO MÉTODO)
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        // Buscamos al usuario que coincida con el correo y el código exacto
        $user = User::where('email', $request->email)
                    ->where('verification_code', $request->code)
                    ->first();

        if (!$user) {
            return response()->json([
                'success' => false, 
                'message' => 'Código incorrecto.'
            ], 422);
        }

        // Si es correcto, lo actualizamos, borramos el código y lo verificamos
        $user->update([
            'is_verified' => true,
            'verification_code' => null
        ]);

        // Ahora sí le damos su token de acceso (lo logueamos automáticamente)
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Cuenta verificada exitosamente',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role,
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }
}