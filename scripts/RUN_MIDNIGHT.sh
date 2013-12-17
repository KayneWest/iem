# Runs at Midnight
DD=$(date -u +'%d')
MM=$(date -u +'%m')
YYYY=$(date -u +'%Y')

cd webalizer
sh processlogs.sh &

cd ../qc
python adjust_snet_precip.py
python check_hilo.py

cd ../dbutil
sh save_snet_raw.sh
python asos2archive.py

cd ../smos
python plot.py 0

# Wait a bit before doing this
sleep 600
cd ../qc
python correctGusts.py
python check_station_geom.py
python check_vtec_eventids.py

cd ../outgoing
python wxc_moon.py

cd ../iemre
python merge_mrms_q3.py

cd ../dbutil 
python hads_delete_dups.py

if [ $DD -eq "28" ]
then
	cd ../coop
	python fetch_merra.py
	MM=$(date -u --date '1 month ago' +'%m')
	YYYY=$(date -u --date '1 month ago' +'%Y')
	python merra_solarrad.py $YYYY $MM
fi
