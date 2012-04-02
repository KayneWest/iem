
cd dl
/mesonet/python/bin/python ncep_stage4.py

cd ../sbw
/mesonet/python/bin/python polygonMosaic.py S
sleep 4
/mesonet/python/bin/python polygonMosaic.py T
sleep 4
/mesonet/python/bin/python polygonMosaic.py W

cd ../iemre
/mesonet/python/bin/python stage4_hourlyre.py
/mesonet/python/bin/python stage4_hourlyre.py `date -u --date '3 hours ago' +'%Y %m %d %H'`
/mesonet/python/bin/python stage4_hourlyre.py `date -u --date '1 day ago' +'%Y %m %d %H'`

cd ../current
/mesonet/python/bin/python stage4_hourly.py
/mesonet/python/bin/python stage4_today_total.py

cd ../qc
/mesonet/python/bin/python checkWebcam.py

cd ../outgoing
/mesonet/python/bin/python wxc_iemrivers.py

cd ../iemplot
./RUN.csh

cd ../ingestors/squaw
./ingest.sh

cd ../scan
python scan_ingest.py

cd ../raws
./download.csh

cd ../madis
/mesonet/python/bin/python extractMADIS.py
/mesonet/python/bin/python extractMetarQC.py
/mesonet/python/bin/python extractMADIS.py ioc
/mesonet/python/bin/python extractMetarQC.py ioc

cd ../cocorahs
/mesonet/python/bin/python stations.py IA
/mesonet/python/bin/python stations.py IL
/mesonet/python/bin/python process.py IL
/mesonet/python/bin/python process.py IA

# This is intensive...
cd ../../week
/mesonet/python/bin/python plot_stage4.py

cd ../plots
./ruc2.csh
./RUN_PLOTS
cd black
./surfaceContours.csh



cd ../../model
/mesonet/python/bin/python ingest.py
