# Download and save bufkit data

cd /mesonet/tmp

set hh="$1"
set yyyymmdd="`date -u +'%Y%m%d'`"
set mdl="rap"

foreach city (aia che crl kalo dvn kbrl kdbq kcid kdsm kfrm kfsd koma klse kmcw kotm krfd krst kstj ksux ktop rdd)
    wget --timeout=10 -q -O ${mdl}_${yyyymmdd}${hh}_${city}.buf http://www.crh.noaa.gov/bufkit/dmx/${mdl}_${city}.buf
end
zip ${mdl}_${yyyymmdd}${hh}.zip ${mdl}_${yyyymmdd}${hh}_* >& /dev/null

/home/ldm/bin/pqinsert -p "plot a ${yyyymmdd}${hh}00 bogus bufkit/${mdl}_${yyyymmdd}${hh}.zip zip" ${mdl}_${yyyymmdd}${hh}.zip

rm *.buf ${mdl}_${yyyymmdd}${hh}.zip
