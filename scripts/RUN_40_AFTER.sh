#Run at 40 minutes after the hour, there are some expensive scripts here
YYYY6=$(date -u --date '6 hours ago' +'%Y')
MM6=$(date -u --date '6 hours ago' +'%m')
DD6=$(date -u --date '6 hours ago' +'%d')
HH6=$(date -u --date '6 hours ago' +'%H')

cd hrrr
python plot_ref.py &

cd ../dl
python download_hrrr_rad.py

cd ../sbw
python polygonMosaic.py S
sleep 2
python polygonMosaic.py T
sleep 2
python polygonMosaic.py W

cd ../qc
python check_webcams.py
python check_isusm_online.py

cd ../outgoing
python wxc_iemrivers.py

cd ../iemplot
./RUN.csh

cd ../ingestors/squaw
python ingest_squaw.py

cd ../scan
python scan_ingest.py

cd ../madis
python extractMADIS.py
python extractMetarQC.py

cd ../cocorahs
python cocorahs_stations.py IA
python cocorahs_stations.py IL
python cocorahs_data_ingest.py IL
python cocorahs_data_ingest.py IA

cd ../../plots
./RUN_PLOTS
cd black
./surfaceContours.csh

cd ../../model
python motherlode_ingest.py $YYYY6 $MM6 $DD6 $HH6
