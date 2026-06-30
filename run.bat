@echo off
TITLE HexTravel Log Server
echo Starting PHP Development Server...
echo Application will be available at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server.
echo.
start http://localhost:8000
php -S localhost:8000
pause
