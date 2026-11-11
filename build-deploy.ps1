# ============================================================
# ITSupportz release builder (Mwamiri Computers Limited)
#
# Usage (PowerShell, in the project folder):
#   .\build-deploy.ps1                          auto patch bump:  5.3.2 -> 5.3.3
#   .\build-deploy.ps1 -Bump minor              minor bump:       5.3.2 -> 5.4.0
#   .\build-deploy.ps1 -Bump major              major bump:       5.3.2 -> 6.0.0
#   .\build-deploy.ps1 -Version 5.5.0           explicit version (overrides -Bump)
#   Add -Notes "..." to include release notes in the update manifest.
#   Add -SkipTests to package without running PHPUnit.
#
# Writes two files to dist\ :
#   itsupportz_install_<version>.zip   full package for a NEW site (no Composer needed on the server)
#   itsupportz_update_<version>.zip    update package — upload in System > Updates
# And a clean system folder next to this one (override with -Folder, skip with -NoFolder):
#   ..\ITSUPPORTZ_SYS\                 only what a live site needs; its .env and writable\ are left alone
# ============================================================
param(
    [string]$Version       = '',
    [string]$Bump          = '',   # major | minor | patch  (default: patch when Version is blank)
    [string]$Notes         = '',
    [switch]$SkipTests,
    [string]$Folder        = '',
    [switch]$NoFolder,
    [switch]$IncludeVendor,  # add -IncludeVendor when composer.lock changed
    [switch]$CompatV35       # add -CompatV35 to produce a package that installs on v3.5 sites
)
$ErrorActionPreference = 'Stop'
$root  = $PSScriptRoot
$dist  = Join-Path $root 'dist'
$stamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$stage = Join-Path $env:TEMP "itsupportz_stage_$stamp"
$utf8  = New-Object System.Text.UTF8Encoding($false)

$previousVersion = (Get-Content "$root\VERSION" -Raw).Trim()
if ($Version) {
    if ($Version -notmatch '^\d+\.\d+\.\d+$') { throw 'Version must be semver: 5.4.0' }
} else {
    if ($previousVersion -notmatch '^(\d+)\.(\d+)\.(\d+)$') {
        throw "Cannot parse current version '$previousVersion'. Use -Version x.y.z explicitly."
    }
    $maj = [int]$Matches[1]; $min = [int]$Matches[2]; $pat = [int]$Matches[3]
    $Version = switch ($Bump.ToLower()) {
        'major' { ($maj + 1).ToString() + '.0.0' }
        'minor' { "$maj." + ($min + 1) + '.0' }
        default { "$maj.$min." + ($pat + 1) }   # 'patch' or blank
    }
    Write-Host "  Bump: $previousVersion  ->  $Version  ($( if ($Bump) { $Bump } else { 'patch' } ))" -ForegroundColor DarkCyan
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
    & $php vendor\bin\phpunit --no-progress --no-coverage
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

# 2. Copy only deployment files to staging
Write-Host '[2/5] Copying project to staging...' -ForegroundColor Cyan
New-Item -ItemType Directory -Force -Path $stage | Out-Null
$excludeFiles = @('.env', '.env.*', '*.zip', '*.bak', '*.log')
foreach ($dir in 'app', 'public', 'system', 'vendor') {
    $source = Join-Path $root $dir
    if (-not (Test-Path $source -PathType Container)) { throw "Required release directory missing: $source" }
    $destination = Join-Path $stage $dir
    robocopy $source $destination /E /NFL /NDL /NJH /NJS /NP /XD "$root\public\uploads" /XF $excludeFiles | Out-Null
    if ($LASTEXITCODE -ge 8) { throw "robocopy failed for $dir with code $LASTEXITCODE" }
}
foreach ($file in '.htaccess', 'VERSION', 'composer.json', 'composer.lock', 'spark', 'preload.php', 'env', 'LICENSE', 'itsupport_schema.sql', 'INSTALL.txt') {
    $source = Join-Path $root $file
    if (-not (Test-Path $source -PathType Leaf)) { throw "Required release file missing: $source" }
    Copy-Item -LiteralPath $source -Destination $stage
}
New-Item -ItemType Directory -Force -Path "$stage\public\uploads" | Out-Null
Copy-Item -LiteralPath "$root\public\uploads\.htaccess" -Destination "$stage\public\uploads\.htaccess"
foreach ($sub in 'clients', 'signatures', 'photos', 'branding', 'extensions') {
    New-Item -ItemType Directory -Force -Path "$stage\public\uploads\$sub" | Out-Null
}

# 3. Create empty writable directories without copying local data
Write-Host '[3/5] Preparing writable/...' -ForegroundColor Cyan
New-Item -ItemType Directory -Force -Path "$stage\writable" | Out-Null
Copy-Item -LiteralPath "$root\writable\.htaccess" -Destination "$stage\writable\.htaccess"
Copy-Item -LiteralPath "$root\writable\index.html" -Destination "$stage\writable\index.html"
foreach ($sub in 'cache', 'logs', 'session', 'uploads', 'backups', 'debugbar', 'updates', 'extensions', 'releases') {
    $dir = Join-Path $stage "writable\$sub"
    New-Item -ItemType Directory -Force -Path $dir | Out-Null
}
foreach ($sub in 'uploads', 'backups', 'updates', 'extensions', 'releases') {
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

# Strip public/uploads/ from stage before building update — updates must never touch user files
Remove-Item "$stage\public\uploads" -Recurse -Force -ErrorAction SilentlyContinue

# Update package: only what an update touches (vendor excluded by default — ~36 MB saved)
$updateManifest = @{
    version        = $Version
    min_php        = '8.2.0'
    built          = (Get-Date -Format 's')
    notes          = $Notes
    remove         = @()
    vendor_bundled = [bool]$IncludeVendor
} | ConvertTo-Json
[System.IO.File]::WriteAllText("$stage\update.json", $updateManifest, $utf8)

if ($CompatV35) {
    # v3.5 allowedPath() only accepts: app/ public/ system/ composer.json spark VERSION
    # composer.lock, preload.php, .htaccess are rejected by the old code.
    # Admin must record an integrity baseline on their v3.5 site before uploading this package.
    $coreItems = @('update.json', 'app', 'public', 'system', 'VERSION', 'composer.json', 'spark')
    $update = $update -replace '\.zip$', '-compat35.zip'
    Write-Host '  [v3.5-compatible package — admin must record baseline first]' -ForegroundColor Yellow
} else {
    $coreItems = @('update.json', 'app', 'public', 'system', 'VERSION', 'composer.json', 'composer.lock', 'spark', 'preload.php', '.htaccess')
}
if ($IncludeVendor) { $coreItems += 'vendor'; Write-Host '  [vendor included — package will be large]' -ForegroundColor Yellow }
$updateItems = $coreItems | Where-Object { Test-Path (Join-Path $stage $_) }
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
  4. Follow INSTALL.txt to import the schema and run protected browser setup.

UPDATE an existing site (v4.x):
  System > Updates > upload itsupportz_update_*.zip > Install update.
  Host manifest.json + the update zip at a stable URL and set it in System > Updates.

UPGRADE from v3.5:
  1. On the v3.5 site: System > Updates > Integrity > Record baseline.
  2. Build:  .\build-deploy.ps1 -CompatV35
  3. Upload itsupportz_update_*-compat35.zip and install.
  Note: composer.lock, preload.php and .htaccess are excluded (not supported by v3.5).
'@ -ForegroundColor Gray

exit 0
