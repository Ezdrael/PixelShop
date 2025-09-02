@echo off
REM Отримати структуру папок і файлів
tree /f /a > structure.txt

REM Відкрити файл для перегляду
notepad structure.txt
