param(
    [switch]$SkipNpm,
    [switch]$SkipMigrate
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

function Require-Command($name) {
    if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
        throw "No se encontro el comando requerido: $name"
    }
}

function Run-Step($label, $scriptBlock) {
    Write-Host $label
    & $scriptBlock
    if ($LASTEXITCODE -ne 0) {
        throw "Fallo el paso: $label"
    }
}

Require-Command php
Require-Command composer

if (-not $SkipNpm) {
    Require-Command npm
}

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host ".env creado desde .env.example"
}

if (-not (Test-Path "database\\database.sqlite")) {
    New-Item -ItemType File -Path "database\\database.sqlite" -Force | Out-Null
    Write-Host "database\\database.sqlite creado"
}

$requiredPaths = @(
    "bootstrap\\cache",
    "storage\\app",
    "storage\\framework\\cache\\data",
    "storage\\framework\\sessions",
    "storage\\framework\\testing",
    "storage\\framework\\views",
    "storage\\logs",
    "public\\css",
    "public\\js"
)

foreach ($path in $requiredPaths) {
    New-Item -ItemType Directory -Path $path -Force | Out-Null
}

if (-not (Test-Path "public\\js\\app.js")) {
    Set-Content -Path "public\\js\\app.js" -Value "// generado por setup-project.ps1"
}

Run-Step "Instalando dependencias PHP..." { composer install --no-interaction }

Run-Step "Generando APP_KEY..." { php artisan key:generate --force }

if (-not $SkipMigrate) {
    Run-Step "Ejecutando migraciones..." { php artisan migrate --force }
}

if (-not $SkipNpm) {
    Run-Step "Instalando dependencias Node..." { npm install }

    Run-Step "Compilando assets..." { npm run build }
}

Write-Host "Setup completado correctamente."
