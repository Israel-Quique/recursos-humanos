<div style="margin:1rem 0;padding:1rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:.75rem;color:#1e293b;">
  <h3 style="font-size:.9rem;font-weight:800;">Autenticación por correo para esta boleta</h3>
  <p style="font-size:.8rem;margin:.5rem 0;">Solicita el código de un solo uso que RRHH enviará a tu correo registrado. Si no tienes correo o necesitas corregirlo, contacta a RRHH.</p>
  <button type="button" wire:click="enviarCodigoBoleta" wire:loading.attr="disabled" style="padding:.5rem .8rem;background:#0f67c0;color:white;border-radius:.5rem;border:0;font-weight:700;cursor:pointer;">Enviar / reenviar código a mi correo</button>
  @if ($estadoCodigoBoleta)
    <p role="status" style="font-size:.8rem;color:#166534;margin-top:.5rem;">{{ $estadoCodigoBoleta }}</p>
  @endif
  <label for="codigo-boleta" style="display:block;font-weight:700;font-size:.8rem;margin:.75rem 0 .25rem;">Código recibido (6 dígitos)</label>
  <input id="codigo-boleta" type="text" wire:model="codigoBoleta" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" style="width:100%;max-width:14rem;padding:.6rem;border:1px solid #94a3b8;border-radius:.5rem;color:#0f172a;background:white;letter-spacing:.2rem;">
  @error('codigoBoleta') <p role="alert" style="color:#b91c1c;font-size:.8rem;margin-top:.5rem;">{{ $message }}</p> @enderror
</div>
