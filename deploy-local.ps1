# deploy-local.ps1
# Usage: .\deploy-local.ps1 [-Message "your commit message"]
param(
    [string]$Message = ""
)

Set-Location $PSScriptRoot

# ── Passphrase guard ──────────────────────────────────────────
$pass = Read-Host "Deploy passphrase"
if ($pass -ne "insizaexpo") {
    Write-Host "Wrong passphrase. Aborting." -ForegroundColor Red
    exit 1
}

# ── Commit message ────────────────────────────────────────────
if (-not $Message) {
    $Message = Read-Host "Commit message"
}
if (-not $Message) { $Message = "chore: update" }

# ── Build assets ──────────────────────────────────────────────
Write-Host "`n==> Building assets..." -ForegroundColor Cyan
npm run build
if ($LASTEXITCODE -ne 0) { Write-Host "Build failed." -ForegroundColor Red; exit 1 }

# ── Git: stage, commit, push ──────────────────────────────────
Write-Host "`n==> Committing and pushing..." -ForegroundColor Cyan
git add -A
git add -f public/build
git commit -m $Message
git push origin master 2>&1 | Out-String | Write-Host

# ── Trigger server deploy via webhook ────────────────────────
Write-Host "`n==> Triggering server deploy..." -ForegroundColor Cyan
$token = "b4fb873dd06db860b37d18af508e00274e209e2568427f7a"
try {
    $response = Invoke-WebRequest `
        -Uri "https://insizaexpo.online/deploy" `
        -Method POST `
        -Headers @{ "X-Deploy-Token" = $token } `
        -TimeoutSec 60
    Write-Host $response.Content -ForegroundColor Green
} catch {
    Write-Host "Webhook error: $_" -ForegroundColor Yellow
    Write-Host "Server may still be deploying. Check: https://insizaexpo.online" -ForegroundColor Yellow
}

Write-Host "`n==> Deploy complete!" -ForegroundColor Green
