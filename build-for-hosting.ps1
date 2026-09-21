<#
  Builds an upload-ready copy of the site for Network Solutions (or any web host).

  Usage (PowerShell, from this folder):
    .\build-for-hosting.ps1
    .\build-for-hosting.ps1 -Domain https://www.yourdomain.org
    .\build-for-hosting.ps1 -Domain https://www.yourdomain.org -ForceHttps

  Output:
    dist\                      folder to upload with FTP / File Manager
    ccf-website-upload.zip     the same files zipped (files at the zip root)
#>
param(
  [string]$Domain = 'https://www.camcancerfoundation.org',
  [switch]$ForceHttps
)

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$dist = Join-Path $root 'dist'
$Domain = $Domain.TrimEnd('/')

if (Test-Path $dist) { Remove-Item $dist -Recurse -Force }
New-Item -ItemType Directory -Path $dist | Out-Null

Get-ChildItem $root -File -Force | Where-Object {
  $_.Extension -eq '.html' -or $_.Name -in @('style.css', 'script.js', '.htaccess', 'robots.txt')
} | Copy-Item -Destination $dist

Copy-Item (Join-Path $root 'assets') -Destination $dist -Recurse
Copy-Item (Join-Path $root 'downloads') -Destination $dist -Recurse

$pages = Get-ChildItem $dist -File -Filter *.html | Where-Object { $_.Name -ne '404.html' } | Sort-Object Name
$urls = foreach ($p in $pages) {
  $loc = if ($p.Name -eq 'index.html') { "$Domain/" } else { "$Domain/$($p.Name)" }
  "  <url><loc>$loc</loc></url>"
}
$sitemap = @('<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">') + $urls + '</urlset>'
[System.IO.File]::WriteAllLines((Join-Path $dist 'sitemap.xml'), $sitemap, (New-Object System.Text.UTF8Encoding($false)))
Add-Content -Path (Join-Path $dist 'robots.txt') -Value "Sitemap: $Domain/sitemap.xml"

if ($ForceHttps) {
  $redirect = @'

# Force HTTPS (only enabled because the site was built with -ForceHttps)
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{HTTPS} !=on
  RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
'@
  Add-Content -Path (Join-Path $dist '.htaccess') -Value $redirect
}

Add-Type -AssemblyName System.IO.Compression, System.IO.Compression.FileSystem
$zipPath = Join-Path $root 'ccf-website-upload.zip'
if (Test-Path $zipPath) { Remove-Item $zipPath }
$zip = [System.IO.Compression.ZipFile]::Open($zipPath, 'Create')
try {
  Get-ChildItem $dist -Recurse -File -Force | ForEach-Object {
    $rel = $_.FullName.Substring($dist.Length + 1).Replace('\', '/')
    [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $rel, 'Optimal')
  }
} finally { $zip.Dispose() }

$count = (Get-ChildItem $dist -Recurse -File -Force).Count
$mb = [math]::Round((Get-ChildItem $dist -Recurse -File -Force | Measure-Object Length -Sum).Sum / 1MB, 1)
Write-Host "Built $count files ($mb MB) for $Domain$(if ($ForceHttps) { ' with HTTPS redirect' })"
Write-Host "  Folder: $dist"
Write-Host "  Zip:    $zipPath"
