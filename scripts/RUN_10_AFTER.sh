#
# We are run at :10 after the hour, some of these processes are intensive 
# 
YYYY=$(date -u +'%Y')
MM=$(date -u +'%m')
DD=$(date -u +'%d')
HH=$(date -u +'%H')

cd mos
python current_bias.py NAM
python current_bias.py GFS

cd ../rtma
python wind_power.py &

cd ../plots
./RUN_PLOTS

cd ../delta
./RUN.csh

cd ../ingestors
python beloit.py
python soilm_ingest.py
python flux_ingest.py

cd ../outgoing
php wxc_cocorahs.php

cd ../current
python plot_hilo.py 0
python q3_today_total.py &


cd ../summary
python hourly_precip.py
python update_snet_precip.py

cd ../week
python plot_obs.py

cd ../iemplot
./RUN.csh

cd ../dbutil
python asos2archive.py iowa


cd ../iemre
python grid_asos.py
python grid_asos.py `date -u --date '2 hours ago' +'%Y %m %d %H'`

cd ../mrms
python mrms_pXXh_comp.py $YYYY $MM $DD $HH

cd ../smos
python ingest.py

cd ../qc
python check_awos_online.py

cd ../ua
if [ $HH -eq "02" ]
then
	python ingest_from_rucsoundings.py $YYYY $MM $DD 00
fi
if [ $HH -eq "14" ]
then
	python ingest_from_rucsoundings.py $YYYY $MM $DD 12
fi
