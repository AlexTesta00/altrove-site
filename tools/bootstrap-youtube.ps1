param([string]$ChannelId = 'UCFj4xNoHNyjg0OPDFM0BvLg')
$ErrorActionPreference = 'Stop'
if ($ChannelId -notmatch '^UC[A-Za-z0-9_-]{22}$') { throw 'ID canale non valido' }
$projectRoot = Split-Path -Parent $PSScriptRoot
$cacheDirectory = Join-Path $projectRoot 'app/storage/cache'
New-Item -ItemType Directory -Force -Path $cacheDirectory | Out-Null
$sourceFile = Join-Path $cacheDirectory 'youtube-source.xml'
# Trust store del sistema operativo; mai SkipCertificateCheck o TLS disabilitato.
$response = Invoke-WebRequest -Uri "https://www.youtube.com/feeds/videos.xml?channel_id=$ChannelId" -TimeoutSec 15
if ($response.StatusCode -ne 200 -or [System.Text.Encoding]::UTF8.GetByteCount($response.Content) -gt 524288) { throw 'Feed non valido o troppo grande' }
[System.IO.File]::WriteAllText($sourceFile, $response.Content, [System.Text.UTF8Encoding]::new($false))
& php (Join-Path $PSScriptRoot 'bootstrap-youtube.php') $sourceFile $ChannelId
if ($LASTEXITCODE -ne 0) { throw 'Bootstrap feed non riuscito' }
