
cd 00z
/mesonet/python/bin/python awos_rtp.py

cd ../ingestors
/mesonet/python/bin/python elnino.py

cd ../summary
python max_reflect.py

# Rerun today
cd ../dbutil
/mesonet/python/bin/python rwis2archive.py 1

cd ../iemre
/mesonet/python/bin/python stage4_12z_adjust.py
