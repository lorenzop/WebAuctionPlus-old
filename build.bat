@echo off
cls
set tempdir=\WebAuctionPlus
set outputfile=%CD%\WebAuctionPlus-1.1.xbeta.zip

set tempdiritem=\ItemPacks
set outputfileitem=%tempdir%\AdditionalItemPacks.zip


IF EXIST %tempdir% (
  echo temp dir already exists!
  pause
  exit
)
IF EXIST %tempdiritem% (
  echo items temp dir already exists!
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
echo.
echo.


echo Making copy to %tempdir% ..
echo.
mkdir %tempdir%\webauctionplus
xcopy .\www\WebInterface %tempdir%\webauctionplus /s /e /H
rmdir /S /Q %tempdir%\webauctionplus\resources
copy .\WebAuctionPlus*.jar %tempdir%
echo.
echo.


copy .\README %tempdir%
rename %tempdir%\README README.txt
copy .\changelog.txt %tempdir%
echo.
echo.


echo Making items copy to %tempdiritem% ..
echo.
mkdir %tempdiritem%
xcopy .\ItemPacks %tempdiritem% /s /e /H
echo.
echo.


echo Compressing item packs..
echo.
zip.exe -r "%outputfileitem%" "%tempdiritem%"
echo.
echo.


echo Compressing files..
echo.
zip.exe -r "%outputfile%" "%tempdir%"
echo.
echo.


echo.
echo Finished!
pause
echo.
echo.


echo Removing temp webauctionplus folder..
echo.
rmdir /S /Q "%tempdir%"
rmdir /S /Q "%tempdiritem%"
echo.
echo.

