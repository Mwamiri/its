# Builds public/assets/tailwind.css from assets/tailwind.css with the Tailwind standalone CLI (no Node.js needed).
#   .\build-css.ps1            one-off minified build
#   .\build-css.ps1 -Watch     rebuild automatically while editing views
param([switch]$Watch)
$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$cli  = Join-Path $root 'tools\tailwindcss.exe'

if (-not (Test-Path $cli)) {
    # Pin to v3 — itsupportz uses tw-* dash prefix (v3 syntax); v4 uses colon prefix (tw:flex) which would require renaming all view classes
    Write-Host 'Downloading Tailwind CSS v3 standalone CLI...' -ForegroundColor Cyan
    New-Item -ItemType Directory -Force -Path (Split-Path $cli) | Out-Null
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest 'https://github.com/tailwindlabs/tailwindcss/releases/download/v3.4.17/tailwindcss-windows-x64.exe' -OutFile $cli
}

$twArgs = @('-c', "$root\tailwind.config.js", '-i', "$root\assets\tailwind.css", '-o', "$root\public\assets\tailwind.css")
if ($Watch) { $twArgs += '--watch' } else { $twArgs += '--minify' }
Push-Location $root
try { & $cli @twArgs } finally { Pop-Location }
if ($LASTEXITCODE -ne 0) { throw 'Tailwind build failed' }
