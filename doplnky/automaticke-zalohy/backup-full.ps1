$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$projectName = Split-Path -Leaf $projectRoot
$backupDirectory = if ($env:BACKUP_DIR) { $env:BACKUP_DIR } else { Join-Path (Split-Path -Parent $projectRoot) "$projectName-backups" }
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$archive = Join-Path $backupDirectory "$projectName-FULL-$timestamp.zip"
$statusFile = Join-Path $backupDirectory "$projectName-FULL-$timestamp-git-status.txt"

New-Item -ItemType Directory -Force -Path $backupDirectory | Out-Null

Push-Location $projectRoot
try {
    git status --short | Set-Content -Encoding ascii $statusFile
} finally {
    Pop-Location
}

Compress-Archive -Path (Join-Path $projectRoot '*'), $statusFile -DestinationPath $archive -CompressionLevel Optimal -Force

Write-Output 'Full backup created:'
Write-Output $archive