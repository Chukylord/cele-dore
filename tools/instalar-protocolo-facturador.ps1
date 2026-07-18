$ErrorActionPreference = 'Stop'

Write-Host ''
Write-Host 'Configurando acceso al Facturador local...' -ForegroundColor Cyan

$desktopPaths = @(
    [Environment]::GetFolderPath('Desktop'),
    [Environment]::GetFolderPath('CommonDesktopDirectory')
) | Where-Object { $_ -and (Test-Path $_) } | Select-Object -Unique

$shortcut = $null

foreach ($desktopPath in $desktopPaths) {
    $shortcut = Get-ChildItem -Path $desktopPath -Filter '*.lnk' -File -ErrorAction SilentlyContinue |
        Where-Object { $_.BaseName -like '*Facturador*' } |
        Select-Object -First 1

    if ($shortcut) {
        break
    }
}

if (-not $shortcut) {
    throw 'No se encontró un acceso directo cuyo nombre contenga Facturador en el Escritorio. Verificá que el acceso directo exista y volvé a ejecutar este archivo.'
}

$wshShell = New-Object -ComObject WScript.Shell
$shortcutInfo = $wshShell.CreateShortcut($shortcut.FullName)

$targetPath = [string]$shortcutInfo.TargetPath
$arguments = [string]$shortcutInfo.Arguments
$workingDirectory = [string]$shortcutInfo.WorkingDirectory

if (-not $targetPath -or -not (Test-Path $targetPath)) {
    throw "El acceso directo fue encontrado, pero su programa de destino no existe: $targetPath"
}

$protocolRoot = 'HKCU:\Software\Classes\virfacturador'

New-Item -Path $protocolRoot -Force | Out-Null
Set-Item -Path $protocolRoot -Value 'URL:Vir Tisone Facturador' -Force
New-ItemProperty -Path $protocolRoot -Name 'URL Protocol' -Value '' -PropertyType String -Force | Out-Null

$iconKey = Join-Path $protocolRoot 'DefaultIcon'
New-Item -Path $iconKey -Force | Out-Null
Set-Item -Path $iconKey -Value ('"{0}",0' -f $targetPath) -Force

$commandKey = Join-Path $protocolRoot 'shell\open\command'
New-Item -Path $commandKey -Force | Out-Null

$command = '"{0}"' -f $targetPath

if ($arguments) {
    $command += ' ' + $arguments
}

Set-Item -Path $commandKey -Value $command -Force

if ($workingDirectory -and (Test-Path $workingDirectory)) {
    $workingKey = Join-Path $protocolRoot 'shell\open'
    New-ItemProperty -Path $workingKey -Name 'WorkingDirectory' -Value $workingDirectory -PropertyType String -Force | Out-Null
}

Write-Host ''
Write-Host 'Configuración terminada correctamente.' -ForegroundColor Green
Write-Host ('Acceso directo encontrado: {0}' -f $shortcut.FullName)
Write-Host ('Programa configurado: {0}' -f $targetPath)
Write-Host ''
Write-Host 'Ahora el botón Facturar del sistema podrá abrir el programa.' -ForegroundColor Green
Write-Host 'La primera vez, Chrome puede pedir confirmación para abrir la aplicación externa.' -ForegroundColor Yellow
Write-Host ''
Read-Host 'Presioná Enter para cerrar'
