#!/bin/sh

MS_MAPFILE=/var/www/data/wms/goes/conus_ir.map
export MS_MAPFILE

/var/www/cgi-bin/mapserv/mapserv
