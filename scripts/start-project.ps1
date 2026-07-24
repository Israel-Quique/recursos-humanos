$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if (-not (Test-Path ".env")) {
    throw "No existe .env. Ejecuta primero scripts\\setup-project.ps1"
}

if (-not (Test-Path "vendor")) {
    throw "No existe vendor. Ejecuta primero scripts\\setup-project.ps1"
}

Write-Host "Levantando servidor Laravel en http://127.0.0.1:8000"
php artisan serve
