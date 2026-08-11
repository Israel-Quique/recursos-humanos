$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if (-not (Test-Path ".env")) {
    throw "No existe .env. Ejecuta primero scripts\\setup-project.ps1"
}

if (-not (Test-Path "vendor")) {
    throw "No existe vendor. Ejecuta primero scripts\\setup-project.ps1"
}

Write-Host "Iniciando scheduler de Laravel para tareas automaticas..."
php artisan schedule:work
