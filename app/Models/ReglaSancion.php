<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglaSancion extends Model
{
    use HasFactory;

    protected $table = 'reglas_sanciones';

    protected $fillable = [
        'categoria',
        'causal',
        'rango_min',
        'rango_max',
        'dias_sancion',
        'sancion_texto',
        'unidad',
        'es_destitucion',
        'orden',
        'activo',
        'tipo_vigencia',
        'aplica_desde_gestion',
        'aplica_desde_mes',
        'aplica_hasta_gestion',
        'aplica_hasta_mes',
        'explicacion_vigencia',
        'observaciones',
    ];

    protected $casts = [
        'rango_min' => 'integer',
        'rango_max' => 'integer',
        'dias_sancion' => 'decimal:2',
        'es_destitucion' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
        'aplica_desde_gestion' => 'integer',
        'aplica_desde_mes' => 'integer',
        'aplica_hasta_gestion' => 'integer',
        'aplica_hasta_mes' => 'integer',
    ];

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopePorCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria)->orderBy('orden');
    }

    public function scopeAtrasos(Builder $query): Builder
    {
        return $query->porCategoria('atraso');
    }

    public function scopeInasistencias(Builder $query): Builder
    {
        return $query->porCategoria('inasistencia');
    }

    public function scopeOmisiones(Builder $query): Builder
    {
        return $query->porCategoria('omision');
    }

    public function scopeGravisimas(Builder $query): Builder
    {
        return $query->porCategoria('gravisima');
    }

    /**
     * Evalúa si un valor dado (minutos, días, cantidad) calza dentro del rango de esta regla.
     */
    public function aplicaParaValor(float|int $valor): bool
    {
        if ($this->rango_min !== null && $valor < $this->rango_min) {
            return false;
        }

        if ($this->rango_max !== null && $valor > $this->rango_max) {
            return false;
        }

        return true;
    }

    /**
     * Evalúa si la regla aplica para un periodo (fecha, mes y gestión) específico.
     * Soporta reglas en transición/marcha blanca, reglas que entran recién a partir de un mes/gestión,
     * y reglas aplicables solo a ciertas gestiones.
     */
    public function aplicaEnPeriodo(?\Carbon\CarbonInterface $fecha = null): bool
    {
        if (! $this->activo || $this->tipo_vigencia === 'no_aplica') {
            return false;
        }

        if ($this->tipo_vigencia === 'siempre' && ! $this->aplica_desde_gestion && ! $this->aplica_desde_mes && ! $this->aplica_hasta_gestion && ! $this->aplica_hasta_mes) {
            return true;
        }

        $fechaCarbon = $fecha ? \Illuminate\Support\Carbon::instance($fecha) : now();
        $gestion = (int) $fechaCarbon->year;
        $mes = (int) $fechaCarbon->month;

        // Si solo aplica en una gestión específica
        if ($this->tipo_vigencia === 'solo_gestion') {
            if ($this->aplica_desde_gestion !== null && $gestion !== (int) $this->aplica_desde_gestion) {
                return false;
            }
        }

        // Validación de inicio de vigencia (Gestión y Mes)
        if ($this->aplica_desde_gestion !== null) {
            if ($gestion < (int) $this->aplica_desde_gestion) {
                return false;
            }
            if ($gestion === (int) $this->aplica_desde_gestion && $this->aplica_desde_mes !== null) {
                if ($mes < (int) $this->aplica_desde_mes) {
                    return false;
                }
            }
        } elseif ($this->aplica_desde_mes !== null) {
            if ($mes < (int) $this->aplica_desde_mes) {
                return false;
            }
        }

        // Validación de fin de vigencia (Gestión y Mes)
        if ($this->aplica_hasta_gestion !== null) {
            if ($gestion > (int) $this->aplica_hasta_gestion) {
                return false;
            }
            if ($gestion === (int) $this->aplica_hasta_gestion && $this->aplica_hasta_mes !== null) {
                if ($mes > (int) $this->aplica_hasta_mes) {
                    return false;
                }
            }
        } elseif ($this->aplica_hasta_mes !== null) {
            if ($mes > (int) $this->aplica_hasta_mes) {
                return false;
            }
        }

        return true;
    }

    public static function nombresMeses(): array
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }

    public function getDescripcionVigenciaAttribute(): string
    {
        if (! $this->activo || $this->tipo_vigencia === 'no_aplica') {
            return 'No aplica (Regla inactiva o exenta)';
        }

        $meses = self::nombresMeses();

        if ($this->tipo_vigencia === 'solo_gestion') {
            return 'Aplica solo en la Gestión ' . ($this->aplica_desde_gestion ?? date('Y'));
        }

        if ($this->aplica_desde_gestion || $this->aplica_desde_mes) {
            $partes = [];
            if ($this->aplica_desde_mes && isset($meses[$this->aplica_desde_mes])) {
                $partes[] = $meses[$this->aplica_desde_mes];
            }
            if ($this->aplica_desde_gestion) {
                $partes[] = (string) $this->aplica_desde_gestion;
            }
            return 'Aplica a partir de ' . implode(' de ', $partes);
        }

        return 'Vigente en todas las gestiones y meses';
    }

    /**
     * Devuelve información visual del estado de aplicación para la interfaz.
     */
    public function getEstadoVigenciaBadge(?\Carbon\CarbonInterface $fecha = null): array
    {
        $aplica = $this->aplicaEnPeriodo($fecha);

        if (! $this->activo || $this->tipo_vigencia === 'no_aplica') {
            return [
                'aplica' => false,
                'texto' => 'No aplica',
                'detalle' => 'Inactiva / No se aplica',
                'color' => 'slate',
                'bg' => 'bg-slate-100 text-slate-600 border-slate-300',
                'punto' => 'bg-slate-400',
                'icono' => '✕',
            ];
        }

        if ($aplica) {
            $tieneRestriccion = (bool) ($this->aplica_desde_gestion || $this->aplica_desde_mes || $this->tipo_vigencia === 'solo_gestion');
            return [
                'aplica' => true,
                'texto' => 'Aplica',
                'detalle' => $tieneRestriccion ? $this->descripcion_vigencia : 'Vigente',
                'color' => 'emerald',
                'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                'punto' => 'bg-emerald-500',
                'icono' => '✓',
            ];
        }

        // Caso: Activa pero no aplica en el periodo evaluado (está entrando recién / en transición)
        return [
            'aplica' => false,
            'texto' => 'En espera',
            'detalle' => $this->descripcion_vigencia,
            'color' => 'amber',
            'bg' => 'bg-amber-50 text-amber-900 border-amber-300',
            'punto' => 'bg-amber-500',
            'icono' => '⏳',
        ];
    }
}
