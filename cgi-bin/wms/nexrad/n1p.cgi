#!/bin/sh

MS_MAPFILE=/mesonet/www/apps/iemwebsite/data/wms/nexrad/n1p.map
export MS_MAPFILE

/mesonet/www/apps/iemwebsite/cgi-bin/mapserv/mapserv
