# push.ps1 — Add, commit, and push all changes to GitHub
# Usage: .\push.ps1
#        .\push.ps1 "feat: add new feature"

param(
    [string]$Message = ""
)

$ProjectRoot = "C:\InsizaExpo\insiza-expo"

Set-Location $ProjectRoot

# Check for changes
$status = git status --porcelain
if (-not $status) {
    Write-Host "Nothing to commit — working tree is clean." -ForegroundColor Yellow
    exit 0
}

# Auto-generate message if none provided
if (-not $Message) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm"
    $Message   = "chore: auto-commit $timestamp"
}

Write-Host ""
Write-Host "Changes:" -ForegroundColor Cyan
$status | ForEach-Object { Write-Host "  $_" }
Write-Host ""
Write-Host "Commit: `"$Message`"" -ForegroundColor Cyan
Write-Host ""

git add .
if ($LASTEXITCODE -ne 0) { Write-Host "git add failed" -ForegroundColor Red; exit 1 }
Write-Host "✓ Staged" -ForegroundColor Green

git commit -m $Message
if ($LASTEXITCODE -ne 0) { Write-Host "git commit failed" -ForegroundColor Red; exit 1 }
Write-Host "✓ Committed" -ForegroundColor Green

git push
if ($LASTEXITCODE -ne 0) { Write-Host "git push failed" -ForegroundColor Red; exit 1 }
Write-Host "✓ Pushed to https://github.com/hwalima/insiza-expo" -ForegroundColor Green
