# Runs at Midnight CST/CDT
DD=$(date -u +'%d')
MM=$(date -u +'%m')
YYYY=$(date -u +'%Y')

cd qc
python adjust_snet_precip.py

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
python check_afos_sources.py

cd ../outgoing
python wxc_moon.py

cd ../iemre
python merge_mrms_q3.py

cd ../dbutil 
python hads_delete_dups.py

cd ../mrms
python create_daily_symlink.py $(date --date '1 day ago' +'%Y %m %d')
python mrms_monthly_plot.py

# Assume we have MERRA data by the 28th each month
if [ $DD -eq "28" ]
then
	cd ../coop
	python fetch_merra.py
	MM=$(date -u --date '1 month ago' +'%m')
	YYYY=$(date -u --date '1 month ago' +'%Y')
	python merra_solarrad.py $YYYY $MM
fi
