Set WshShell = CreateObject("WScript.Shell")
' 0 = Hide window, 1 = Show window
WshShell.Run chr(34) & "start-worker.bat" & Chr(34), 0
Set WshShell = Nothing
