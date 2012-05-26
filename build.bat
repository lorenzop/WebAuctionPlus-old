@echo off
cls
set tempdir=\WebAuctionPlus
set outputfile=%CD%\WebAuctionPlus-1.0.xalpha.zip


IF EXIST %tempdir% (
  echo temp dir already exists!
  pause
  exit
)
IF EXIST %outputfile% (
  echo output file already exists!
  pause
  exit
)


echo output file %outputfile%
echo.
mkdir %tempdir%


echo Making copy to %tempdir% ..
echo.
mkdir %tempdir%\webauctionplus
xcopy .\www\WebInterface %tempdir%\webauctionplus /s /e /H
copy .\WebAuctionPlus*.jar %tempdir%


copy .\README %tempdir%
rename %tempdir%\README README.txt
copy .\changelog.txt %tempdir%


echo Compressing files..
echo.
zip.exe -r "%outputfile%" "%tempdir%"
echo.
echo.


echo.
echo Finished!
pause


echo.
echo Removing temp webauctionplus folder..
echo.
rmdir /S /Q "%tempdir%"
