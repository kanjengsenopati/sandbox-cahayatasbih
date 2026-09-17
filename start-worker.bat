@echo off
cd /d "%~dp0"
echo Memulai Queue Worker Cahaya Tasbih...
php artisan queue:work --timeout=3600 --queue=default --sleep=3 --tries=3
