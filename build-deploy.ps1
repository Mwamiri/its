# ============================================================
# ITSupportz release builder (Mwamiri Computers Limited)
#
# Usage (PowerShell, in the project folder):
#   powershell -ExecutionPolicy Bypass -File .\build-deploy.ps1                       patch bump: 3.5.0 -> 3.5.1
#   powershell -ExecutionPolicy Bypass -File .\build-deploy.ps1 -Version 3.6.0 -Notes "New feature"
#   Add -SkipTests to package without running PHPUnit.
#
# Writes two files to dist\ :
#   itsupportz_install_<version>.zip   full package for a NEW site (no Composer needed on the server)
#   itsupportz_update_<version>.zip    update package — upload in System > Updates
# And a clean system folder next to this one (override with -Folder, skip with -NoFolder):
#   ..\ITSUPPORTZ_SYS\                 only what a live site needs; its .env and writable\ are left alone
# ============================================================
param(
    [string]$Version  = '',
    [string]$Notes    = '',
    [switch]$SkipTests,
    [string]$Folder   = '',
    [switch]$NoFolder
)
$ErrorActionPreference = 'Stop'
$root  = $PSScriptRoot
$dist  = Join-Path $root 'dist'
$stamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$stage = Join-Path $env:TEMP "itsupportz_stage_$stamp"
$utf8  = New-Object System.Text.UTF8Encoding($false)

$previousVersion = (Get-Content "$root\VERSION" -Raw).Trim()
if ($Version) {
    if ($Version -notmatch '^\d+\.\d+\.\d+$') { throw 'Version must be semver: 3.5.0' }
} else {
    # Auto-bump the patch number: 3.5.0 -> 3.5.1
    if ($previousVersion -match '^(\d+\.\d+\.)(\d+)$') {
        $Version = $Matches[1] + ([int]$Matches[2] + 1)
    } else {
        throw "Cannot work out the next version after $previousVersion - give it with -Version."
    }
}
[System.IO.File]::WriteAllText("$root\VERSION", "$Version`n", $utf8)
Write-Host "Building ITSupportz $Version" -ForegroundColor Cyan

function Find-Php {
    # Prefer WAMP PHP (has mysqli for tests); fall back to PATH; last resort: any PHP in PATH
    $wamp = Get-ChildItem 'C:\wamp*\bin\php\php8*\php.exe' -ErrorAction SilentlyContinue | Sort-Object FullName -Descending | Select-Object -First 1
    if ($wamp) { return $wamp.FullName }
    $cmd = Get-Command php -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }
    throw 'PHP not found. Install WAMP or add PHP 8.2+ to PATH.'
}
$php = Find-Php

# 0. Automated tests
if ($SkipTests) {
    Write-Host '[0/5] Tests skipped (-SkipTests)' -ForegroundColor Yellow
} elseif (Test-Path "$root\vendor\bin\phpunit") {
    Write-Host '[0/5] Running automated tests...' -ForegroundColor Cyan
    Push-Location $root
    & $php vendor\bin\phpunit --no-progress
    $testExit = $LASTEXITCODE
    Pop-Location
    if ($testExit -ne 0) {
        [System.IO.File]::WriteAllText("$root\VERSION", "$previousVersion`n", $utf8)
        throw "Tests failed - nothing was packaged (VERSION left at $previousVersion). Fix them or use -SkipTests."
    }
} else {
    Write-Host '[0/5] PHPUnit not installed (composer install) — tests skipped' -ForegroundColor Yellow
}

# 1. Build CSS
Write-Host '[1/5] Building CSS...' -ForegroundColor Cyan
& "$root\build-css.ps1"

# 2. Copy to staging
Write-Host '[2/5] Copying project to staging...' -ForegroundColor Cyan
New-Item -ItemType Directory -Force -Path $stage | Out-Null
$excludeDirs  = @('.git', 'dist', 'tests', 'tools', 'node_modules', 'changelogs', '.vscode', '.idea', 'docs')
$excludeFiles = @('.env', '*.zip', 'build-deploy.ps1', 'build-css.ps1', 'phpunit.dist.xml', 'phpunit.xml',
                  '.phpunit.result.cache', 'phpstan.neon', 'tailwind.config.js', 'package.json',
                  'package-lock.json', 'psalm-autoload.php', 'DEPLOY.md', 'CHANGELOG.md',
                  'RELEASES.md', 'SECURITY.md', 'itsupport_schema.sql')
robocopy $root $stage /E /NFL /NDL /NJH /NJS /NP /XD $excludeDirs /XF $excludeFiles | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy failed with code $LASTEXITCODE" }

# 3. Clean writable/ — keep folder structure and protection files only
Write-Host '[3/5] Cleaning writable/...' -ForegroundColor Cyan
foreach ($f in 'installed.lock', 'maintenance.flag', 'integrity.json') {
    Remove-Item "$stage\writable\$f" -Force -ErrorAction SilentlyContinue
}
foreach ($sub in 'cache', 'logs', 'session', 'uploads', 'backups', 'debugbar', 'updates') {
    $dir = Join-Path $stage "writable\$sub"
    New-Item -ItemType Directory -Force -Path $dir | Out-Null
    Get-ChildItem $dir -Force | Where-Object { $_.Name -notin @('index.html', '.htaccess') } | Remove-Item -Recurse -Force
}
foreach ($sub in 'uploads', 'backups', 'updates') {
    [System.IO.File]::WriteAllText("$stage\writable\$sub\.htaccess", "Require all denied`n", $utf8)
}

# 4. Clean-system folder next to development folder
if (-not $NoFolder) {
    if (-not $Folder) { $Folder = Join-Path (Split-Path $root -Parent) ((Split-Path $root -Leaf) + '_SYS') }
    if ((Resolve-Path -LiteralPath $root).Path -eq $Folder) { throw 'The clean folder cannot be the development folder.' }
    Write-Host "[4/5] Writing clean system folder: $Folder" -ForegroundColor Cyan
    New-Item -ItemType Directory -Force -Path $Folder | Out-Null
    robocopy $stage $Folder /MIR /NFL /NDL /NJH /NJS /NP /XD "$stage\writable" "$Folder\writable" /XF .env | Out-Null
    if ($LASTEXITCODE -ge 8) { throw "robocopy failed with code $LASTEXITCODE (clean folder)" }
    if (-not (Test-Path "$Folder\writable")) {
        robocopy "$stage\writable" "$Folder\writable" /E /NFL /NDL /NJH /NJS /NP | Out-Null
    }
    [System.IO.File]::WriteAllText("$Folder\BUILD.txt", "ITSupportz $Version`r`nClean system built $(Get-Date -Format 'yyyy-MM-dd HH:mm') from $root`r`n", $utf8)
} else {
    Write-Host '[4/5] Clean folder skipped (-NoFolder)' -ForegroundColor Yellow
}

# 5. Packages
Write-Host '[5/5] Creating packages...' -ForegroundColor Cyan
New-Item -ItemType Directory -Force -Path $dist | Out-Null
$install = Join-Path $dist "itsupportz_install_$Version.zip"
$update  = Join-Path $dist "itsupportz_update_$Version.zip"
Remove-Item $install, $update -Force -ErrorAction SilentlyContinue

# Install package: everything
tar.exe -a -c -f $install -C $stage .
if ($LASTEXITCODE -ne 0) { throw 'tar failed (install package)' }

# Update package: only what an update touches
$updateManifest = @{
    version   = $Version
    min_php   = '8.2.0'
    built     = (Get-Date -Format 's')
    notes     = $Notes
    remove    = @()
} | ConvertTo-Json
[System.IO.File]::WriteAllText("$stage\update.json", $updateManifest, $utf8)
$updateItems = @('update.json', 'app', 'public', 'system', 'vendor', 'VERSION', 'composer.json', 'composer.lock', 'spark', 'preload.php', '.htaccess') |
    Where-Object { Test-Path (Join-Path $stage $_) }
tar.exe -a -c -f $update -C $stage @updateItems
if ($LASTEXITCODE -ne 0) { throw 'tar failed (update package)' }

# manifest.json for the auto-update feed (upload alongside the update zip)
$sha = (Get-FileHash $update -Algorithm SHA256).Hash.ToLower()
$manifest = @{ version = $Version; url = "https://example.com/itsupportz_update_$Version.zip"; sha256 = $sha; notes = $Notes } | ConvertTo-Json
[System.IO.File]::WriteAllText("$dist\manifest.json", $manifest, $utf8)

Remove-Item $stage -Recurse -Force

if (-not $NoFolder) { Write-Host "  $Folder  (clean system folder)" -ForegroundColor Green }
foreach ($z in $install, $update) {
    Write-Host ("  {0}  ({1} MB)" -f (Split-Path $z -Leaf), [math]::Round((Get-Item $z).Length / 1MB, 2)) -ForegroundColor Green
}
Write-Host "  manifest.json  (set the update URL before hosting)" -ForegroundColor Green
Write-Host @'

Deploy a NEW site:
  1. Extract itsupportz_install_*.zip on the server.
  2. Point the domain's Document Root to the public/ folder.
  3. Create a MySQL database and fill in .env (copy env to .env, edit).
  4. Open the site - the installer runs once, then locks itself.

UPDATE an existing site:
  System > Updates > upload itsupportz_update_*.zip > Install update.
  Host manifest.json + the update zip at a stable URL and set it in System > Updates.
'@ -ForegroundColor Gray

exit 0
