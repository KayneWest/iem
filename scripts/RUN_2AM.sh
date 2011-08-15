#!/bin/sh

cd climodat
/mesonet/python/bin/python daily_estimator.py
/mesonet/python/bin/python compute_ia0000.py
./run.sh >& a

cd ../iemre
/mesonet/python/bin/python grid_climodat.py

cd ../cache
/mesonet/python/bin/python warn_cache.py

cd ../dbutil
/mesonet/python/bin/python asos2archive.py
/mesonet/python/bin/python clean_afos.py

cd ../qc
/mesonet/python/bin/python check_iem_precip.py
python hads_nwsli_squawk.py

cd ../ingestors/cocorahs
/mesonet/python/bin/python redo_day.py IA
/mesonet/python/bin/python redo_day.py IL

cd ../../windrose
python daily_drive_network.py