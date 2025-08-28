@echo off
chcp 65001 > nul
title Сервер чату на WebSocket

echo Запуск сервера WebSocket на порту 8080...
echo Шлях до проєкту: C:\xampp\htdocs\mvc-vue-new
echo Щоб зупинити сервер, натисніть Ctrl+C у цьому вікні.
echo.

:: Запускаємо PHP-скрипт сервера
php chat-server.php

echo.
echo Сервер зупинено.
pause