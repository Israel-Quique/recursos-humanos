<?php

namespace App\Services;

use App\Mail\CodigoBoletaMailable;
use App\Models\Empleado;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class CodigoBoletaService
{
    public function enviar(Empleado $empleado): void
    {
        $key = 'boleta-mail:'.hash('sha256', (string) request()->ip());
        if (RateLimiter::tooManyAttempts($key, 60)) {
            $this->error('Has solicitado demasiados códigos. Intenta nuevamente en unos minutos.');
        }
        RateLimiter::hit($key, 60);

        DB::transaction(function () use ($empleado) {
            $empleado = Empleado::query()->lockForUpdate()->findOrFail($empleado->id);
            $email = strtolower(trim((string) $empleado->email));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('No tienes un correo válido registrado. Solicita a RRHH que registre o corrija tu correo Zimbra.');
            }
            $anterior = DB::table('boleta_codigos')->where('empleado_id', $empleado->id)->first();
            if ($anterior && Carbon::parse($anterior->sent_at)->addMinute()->isFuture()) {
                $this->error('Espera un minuto antes de solicitar otro código.');
            }
            if (! app()->environment('testing') && in_array(config('mail.default'), ['log', 'array'], true)) {
                $this->error('El envío de correo no está activo. Contacta a RRHH para habilitar el servidor de correo.');
            }

            do {
                $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            } while ($anterior && Hash::check($codigo, $anterior->codigo_hash));
            DB::table('boleta_codigos')->updateOrInsert(['empleado_id' => $empleado->id], [
                'session_hash' => hash('sha256', session()->getId()),
                'email' => $email,
                'codigo_hash' => Hash::make($codigo),
                'intentos' => 0,
                'expires_at' => now()->addMinutes(10),
                'sent_at' => now(),
                'used_at' => null,
            ]);
            try {
                $mensaje = Mail::to($email)->send(new CodigoBoletaMailable($empleado->nombre_completo, $codigo));
                Log::info('Código de boleta aceptado por el servidor SMTP.', [
                    'empleado_id' => $empleado->id,
                    'message_id' => $mensaje?->getMessageId(),
                ]);
            } catch (\Throwable $exception) {
                // No registrar el mensaje SMTP completo: puede contener credenciales o el código.
                $certificadoInvalido = str_contains(strtolower($exception->getMessage()), 'certificate verify failed');
                Log::warning('No se pudo enviar el código de boleta.', [
                    'empleado_id' => $empleado->id,
                    'error' => $exception::class,
                    'motivo' => $certificadoInvalido ? 'certificado_tls' : 'envio_smtp',
                    'php_sapi' => PHP_SAPI,
                    'ca_file' => ini_get('openssl.cafile'),
                ]);
                if ($certificadoInvalido) {
                    $this->error('No se pudo conectar de forma segura con el correo institucional. RRHH debe revisar los certificados y reiniciar el servidor PHP.');
                }
                $this->error('No pudimos enviar el código. Intenta nuevamente o contacta a RRHH.');
            }
        });
    }

    public function consumir(Empleado $empleado, string $codigo, Closure $generar): mixed
    {
        $result = DB::transaction(function () use ($empleado, $codigo, $generar) {
            $empleado = Empleado::query()->lockForUpdate()->findOrFail($empleado->id);
            $registro = DB::table('boleta_codigos')->where('empleado_id', $empleado->id)->first();
            if (! $registro || $registro->used_at || Carbon::parse($registro->expires_at)->lessThanOrEqualTo(now())
                || $registro->intentos >= 5 || ! hash_equals($registro->session_hash, hash('sha256', session()->getId()))
                || $registro->email !== strtolower(trim((string) $empleado->email))) {
                return ['error' => 'Solicita un nuevo código para este empleado desde este navegador. El anterior venció, se utilizó o ya no es válido.'];
            }
            if (! preg_match('/^\d{6}$/D', $codigo) || ! Hash::check($codigo, $registro->codigo_hash)) {
                DB::table('boleta_codigos')->where('id', $registro->id)->increment('intentos');
                return ['error' => 'El código no es correcto. Después de 5 intentos deberás solicitar uno nuevo.'];
            }
            DB::table('boleta_codigos')->where('id', $registro->id)->update(['used_at' => now()]);

            return ['response' => $generar()];
        });
        if (isset($result['error'])) {
            $this->error($result['error']);
        }

        return $result['response'];
    }

    private function error(string $message): never
    {
        throw ValidationException::withMessages(['codigoBoleta' => $message]);
    }
}
