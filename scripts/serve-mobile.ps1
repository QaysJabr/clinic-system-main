# Start Laravel API for physical devices on the local network.
# Usage: .\scripts\serve-mobile.ps1

$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)

$ip = (
  Get-NetIPAddress -AddressFamily IPv4 |
  Where-Object {
    $_.IPAddress -notlike '127.*' -and
    $_.PrefixOrigin -ne 'WellKnown' -and
    $_.IPAddress -notlike '169.254.*'
  } |
  Select-Object -First 1 -ExpandProperty IPAddress
)

if (-not $ip) {
  Write-Host 'Could not detect LAN IP. Connect Wi-Fi and retry.' -ForegroundColor Red
  exit 1
}

Write-Host ''
Write-Host '=== Clinic Mobile API ===' -ForegroundColor Cyan
Write-Host "LAN IP:       $ip"
Write-Host "API URL:      http://${ip}:8000/api/v1"
Write-Host 'Mobile .env:  EXPO_PUBLIC_API_URL=http://' -NoNewline
Write-Host "${ip}:8000/api/v1" -ForegroundColor Yellow
Write-Host ''
Write-Host 'Phone must use the same Wi-Fi network (not mobile data).' -ForegroundColor Gray
Write-Host 'Press Ctrl+C to stop.' -ForegroundColor Gray
Write-Host ''

php artisan serve --host=0.0.0.0 --port=8000
