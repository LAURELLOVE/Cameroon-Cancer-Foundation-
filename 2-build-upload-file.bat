@echo off
cd /d "%~dp0"
echo STEP 2 - building the file to upload for www.camcancerfoundation.org
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0build-for-hosting.ps1" -Domain https://www.camcancerfoundation.org
echo.
echo Upload this file to Network Solutions: %~dp0ccf-website-upload.zip
echo.
pause
