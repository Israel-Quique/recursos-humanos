<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SucursalNormalizer
{
    private const ALIASES = [
        'la-paz' => ['La Paz', 'LaPaz', 'Oficina Central La Paz', 'Regional La Paz', 'Sucursal La Paz', 'El Alto', 'Regional El Alto'],
        'santa-cruz' => ['Santa Cruz', 'SantaCruz', 'Regional Santa Cruz', 'Sucursal Santa Cruz'],
        'cochabamba' => ['Cochabamba', 'Regional Cochabamba', 'Sucursal Cochabamba'],
        'chuquisaca' => ['Chuquisaca', 'Sucre', 'Sucursal Sucre', 'Sucursal Chuquisaca', 'Regional Sucre', 'Regional Chuquisaca'],
        'oruro' => ['Oruro', 'Sucursal Oruro', 'Regional Oruro'],
        'potosi' => ['Potosi', 'Potosí', 'Sucursal Potosi', 'Sucursal Potosí', 'Regional Potosi', 'Regional Potosí'],
        'tarija' => ['Tarija', 'Sucursal Tarija', 'Regional Tarija'],
        'beni' => ['Beni', 'Trinidad', 'Sucursal Trinidad', 'Sucursal Beni', 'Regional Trinidad', 'Regional Beni'],
        'pando' => ['Pando', 'Cobija', 'Sucursal Cobija', 'Sucursal Pando', 'Regional Cobija', 'Regional Pando'],
    ];

    public static function canonicalKey(?string $value): ?string
    {
        $normalized = self::normalizeText($value);

        if ($normalized === '') {
            return null;
        }

        foreach (self::ALIASES as $key => $aliases) {
            foreach ($aliases as $alias) {
                if (self::normalizeText($alias) === $normalized) {
                    return $key;
                }
            }
        }

        return null;
    }

    public static function normalize(?string $value): string
    {
        return self::canonicalLabel($value);
    }

    public static function canonicalLabel(?string $value): string
    {
        $trimmed = trim((string) $value);
        $canonicalKey = self::canonicalKey($trimmed);

        if ($canonicalKey !== null) {
            return self::ALIASES[$canonicalKey][0];
        }

        return $trimmed;
    }

    public static function aliasesFor(?string $value): array
    {
        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return [];
        }

        $canonicalKey = self::canonicalKey($trimmed);

        if ($canonicalKey !== null) {
            return self::ALIASES[$canonicalKey];
        }

        return [$trimmed];
    }

    public static function applyFilter(Builder $query, string $column, ?string $value): Builder
    {
        $aliases = self::aliasesFor($value);

        if ($aliases === []) {
            return $query;
        }

        if (count($aliases) === 1) {
            return $query->where($column, $aliases[0]);
        }

        return $query->whereIn($column, $aliases);
    }

    public static function optionsFromValues(iterable $values, bool $includeTodas = false): array
    {
        $options = collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->map(fn (string $value) => self::canonicalLabel($value))
            ->unique()
            ->values();

        if ($includeTodas) {
            $options = collect(['TODAS'])->concat($options->reject(fn (string $value) => $value === 'TODAS'))->values();
        }

        return $options->all();
    }

    public static function matches(?string $left, ?string $right): bool
    {
        $leftTrimmed = trim((string) $left);
        $rightTrimmed = trim((string) $right);

        if ($leftTrimmed === '' || $rightTrimmed === '') {
            return $leftTrimmed === $rightTrimmed;
        }

        $leftKey = self::canonicalKey($leftTrimmed);
        $rightKey = self::canonicalKey($rightTrimmed);

        if ($leftKey !== null || $rightKey !== null) {
            return $leftKey === $rightKey;
        }

        return self::normalizeText($leftTrimmed) === self::normalizeText($rightTrimmed);
    }

    private static function normalizeText(?string $value): string
    {
        return str((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->trim()
            ->toString();
    }
}
