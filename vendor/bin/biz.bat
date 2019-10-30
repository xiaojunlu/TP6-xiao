@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../codeages/biz-framework/bin/biz
php "%BIN_TARGET%" %*
