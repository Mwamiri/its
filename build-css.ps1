# Builds public/assets/tailwind.css from assets/tailwind.css using Tailwind CSS v4.
#   .\build-css.ps1            one-off minified build
#   .\build-css.ps1 -Watch     rebuild automatically while editing views
#
# Requires Node.js + npm. Run `npm install` once to install Tailwind CSS v4.
param([switch]$Watch)
$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$cli  = Join-Path $root 'node_modules\.bin\tailwindcss.cmd'

if (-not (Test-Path $cli)) {
    Write-Host 'Installing Tailwind CSS v4 + CLI (npm install)...' -ForegroundColor Cyan
    Push-Location $root
    npm install
    Pop-Location
}
if (-not (Test-Path $cli)) { throw 'Tailwind CSS CLI not found after npm install. Run: npm install' }

$twArgs = @('-i', "$root\assets\tailwind.css", '-o', "$root\public\assets\tailwind.css")
if ($Watch) { $twArgs += '--watch' } else { $twArgs += '--minify' }
Push-Location $root
try { & $cli @twArgs } finally { Pop-Location }
if ($LASTEXITCODE -ne 0) { throw 'Tailwind build failed' }
