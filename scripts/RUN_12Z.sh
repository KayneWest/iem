# Run at 12Z, but needs some manual crontab changing help

# DVN wants this to run at 12:10 UTC, so we start the cron script a bit late
cd 12z
python awos_rtp.py
python asos_low.py

cd ../ingestors/other
python feel_ingest.py

cd ../../cscap
python harvest_management.py
python harvest_agronomic.py 2012
python harvest_agronomic.py 2013
python harvest_agronomic.py 2014
python harvest_soil_nitrate.py 2012
python harvest_soil_nitrate.py 2013
python harvest_soil_nitrate.py 2014
python harvest_soil_bd.py 2013
python set_dashboard_links.py 2012
python set_dashboard_links.py 2013
python set_dashboard_links.py 2014
python set_dashboard_links.py 2015
python email_daily_changes.py 


# Rerun yesterday and today
cd ../dbutil
python rwis2archive.py $(date -u --date '1 days ago' +'%Y %m %d')
python rwis2archive.py $(date -u +'%Y %m %d')
python ot2archive.py $(date -u --date '1 days ago' +'%Y %m %d')
python ot2archive.py $(date -u +'%Y %m %d')

cd ../util
csh BACKUP.csh
