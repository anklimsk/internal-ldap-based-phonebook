@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../packagelist/yuicompressor-bin/bin/yuicompressor.jar
php "%BIN_TARGET%" %*
