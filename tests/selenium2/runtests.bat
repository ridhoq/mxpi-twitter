@echo off
REM
REM Magento
REM
REM NOTICE OF LICENSE
REM
REM This source file is subject to the Open Software License (OSL 3.0)
REM that is bundled with this package in the file LICENSE.txt.
REM It is also available through the world-wide-web at this URL:
REM http://opensource.org/licenses/osl-3.0.php
REM If you did not receive a copy of the license and are unable to
REM obtain it through the world-wide-web, please send an email
REM to license@magentocommerce.com so we can send you a copy immediately.
REM
REM DISCLAIMER
REM
REM Do not edit or add to this file if you wish to upgrade Magento to newer
REM versions in the future. If you wish to customize Magento for your
REM needs please refer to http://www.magentocommerce.com for more information.
REM
REM @category    tests
REM @package     selenium
REM @subpackage  runner
REM @author      Magento Core Team <core@magentocommerce.com>
REM @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
REM @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
REM

echo "You can pass the parameters to the script and execute several"
echo " configuration at the same time. In case you would like to"
echo "use this ability use the command in such way: "
echo "runtests.bat mage:firefox, enterprise:iexplore, mage:googlechrome"
echo "Mentioned command will execute 3 instances of phpunit tests"
echo "with different configuration: application 'mage' will be executed in"
echo "'firefox' browser, application 'enterprise' will be executed in"
echo "'iexplore' browser and so on. In case you use next method of"
echo "execution new directory will be created for each instance."

setlocal enabledelayedexpansion
if "%PHPBIN%" == "" set PHPBIN=php.exe
if not exist "%PHPBIN%" if "%PHP_PEAR_PHP_BIN%" neq "" set PHPBIN=%PHP_PEAR_PHP_BIN%

set folders=(config fixture framework testsuite var)
set tufn=%time:~0,2%%time:~3,2%%time:~6,2%%time:~9,2%
set Count=0
set ConfCount=1
set strArrayNumber=0
set strConfNumber=0
set workingDir=%cd%
:loop

if not "%1"=="" (
	set /a count+=1
	set parameter[!count!]=%1
	shift
	GOTO loop
)

for /l %%a in (1,1,%count%) do (
	set strArrayValue=!parameter[%%a]!
	call:functionArray

)

if "%strArrayName.1%"=="" (
	set BASEDIR=%~dp0
	"%PHPBIN%" "%PHP_PEAR_BIN_DIR%\phpunit" --configuration "%BASEDIR%phpunit.xml" >var\logs\PHPUnitReport%tufn%.txt%*
) else (
	call:functionCreateDir
	call:functionCopy
	call:functionReplace
)

:functionArray
set /a strConfNumber=%strConfNumber% + 1
set strConfName.%strConfNumber%=%strArrayValue%
set /a strArrayNumber=%strArrayNumber% + 1
set config=%strArrayValue::=%
set strArrayName.%strArrayNumber%=%config%
GOTO :EOF

:functionCreateDir
mkdir %tufn%
GOTO :EOF

:functionCopy
For /F "usebackq delims==. tokens=1-3" %%i IN (`set strArrayName`) DO (
	mkdir %tufn%\%%k_%%j
	xcopy  *.* %tufn%\%%k_%%j\
	call:functionCop
)
GOTO :EOF

:functionCop
for /D %%i in %folders% DO (
	set conf=%%k
	set id=%%j
	echo "%%i"
	if "%%i"=="var" (
		xcopy /E/I/Q/T "%workingDir%\%%i" "%workingDir%\%tufn%\%%k_%%j\%%i"
	) else (
		xcopy /E/I/Q "%workingDir%\%%i" "%workingDir%\%tufn%\%%k_%%j\%%i"
	)
)
GOTO :EOF

:functionReplace
set defaultapp=    default: *mage
set defaultbrowser=    default: *firefox
For /F "usebackq delims=.=: tokens=2,3,4" %%a IN (`set strConfName`) DO (
	set counter=%%a
	set browser=%%c
	set app=%%b
	set newdefaultapp=    default: *%%b
	set newdefaultbrowser=    default: *%%c
	call:functionRepAnExec
)
GOTO :EOF

:functionRepAnExec
for /F "tokens=* delims=" %%i in (%workingDir%\%tufn%\%app%%browser%_%counter%\config\config.yml) do (
	if "%%i"=="%defaultbrowser%" (
		(echo %newdefaultbrowser%)>> %workingDir%\%tufn%\%app%%browser%_%counter%\config\confignew.yml
	) else (
		if "%%i"=="%defaultapp%" (
			(echo %newdefaultapp%)>> %workingDir%\%tufn%\%app%%browser%_%counter%\config\confignew.yml
		) else (
			(echo %%i)>> %workingDir%\%tufn%\%app%%browser%_%counter%\config\confignew.yml
		)
	)
)
move %workingDir%\%tufn%\%app%%browser%_%counter%\config\confignew.yml %workingDir%\%tufn%\%app%%browser%_%counter%\config\config.yml
start cmd /X/V:ON/K "cd /d %workingDir%\%tufn%\%app%%browser%_%counter%&%PHPBIN% %PHP_PEAR_BIN_DIR%\phpunit --configuration phpunit.xml >var\logs\PHPUnitReport.txt"
GOTO :EOF

:EOF