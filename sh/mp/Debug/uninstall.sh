#!/bin/bash

CWD="$(pwd)/../../.."

CONFIG_FILE="$CWD/app/etc/local.xml"
INDEXER_FILE="$CWD/shell/indexer.php"

PHP_BIN=`which php`

echo "Do you want to completely uninstall the extension?(y/n)"
read UNINSTALL

if [ "$UNINSTALL" == "y" ]; then


	rm -f $CWD/app/etc/modules/MP_Debug.xml
	rm -fr $CWD/app/code/community/MP/Debug
	rm -fr $CWD/app/design/frontend/base/default/layout/mp_debug.xml
	rm -fr $CWD/app/design/frontend/base/default/template/mp_debug
	rm -fr $CWD/skin/frontend/base/default/mp_debug/
	rm -f $CWD/app/design/adminhtml/default/default/layout/mp_debug.xml
	rm -fr $CWD/app/design/adminhtml/default/default/template/mp_debug
	rm -fr $CWD/skin/adminhtml/default/default/mp_debug/
	rm -f $CWD/var/connect/Magneto_Debug.xml			  
		
	rm -fr $CWD/var/cache
		
	$PHP_BIN $INDEXER_FILE --reindexall	
fi
