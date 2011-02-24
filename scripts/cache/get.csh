#!/bin/csh

# channel 5 12.0um
# channel 4 10.70um
# channel 3 6.75um
# channel 2 3.9um 
# channel 1 0.65um  Vis
# EAST -110 65.5 -29 -15
# West -180 65.5 -102 -15

rm *.tif >& /dev/null
rm *.tfw >& /dev/null

set tm="`./ts.py $1 %j%H%M`"
set ftm="`./ts.py $1 %Y%m%d%H%M`"
set atm="`./ts.py $1 %H%M`"
set wtm="`./wdownload.py $1`"


wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESwest/GoesWest1V_latest.tif
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESwest/GoesWest1V_latest.tfw
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESeast/GoesEast1V_latest.tif
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESeast/GoesEast1V_latest.tfw

wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESwest/GoesWest04I4_latest.tif
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESwest/GoesWest04I4_latest.tfw
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESeast/GoesEast04I4_latest.tif
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESeast/GoesEast04I4_latest.tfw

wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESwest/GoesWest04I3_latest.tif
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESwest/GoesWest04I3_latest.tfw
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESeast/GoesEast04I3_latest.tif
wget -q ftp://satepsanone.nesdis.noaa.gov/GIS/GOESeast/GoesEast04I3_latest.tfw

# Merge Visible, please
set szw="`stat -c %s GoesWest1V_latest.tif`"
set sze="`stat -c %s GoesEast1V_latest.tif`"
if ($szw > 1000 && $sze > 1000) then
	/mesonet/local/bin/gdal_merge.py -q -o vis.tif  -ul_lr -126 50 -66 24 -ps 0.04 0.04 GoesWest1V_latest.tif GoesEast1V_latest.tif
	/home/ldm/bin/pqinsert -p "gis ac $ftm gis/images/4326/sat/conus_goes_vis4km.tif GIS/sat/conus_goes_vis4km_$atm.tif tif" vis.tif
  	# Create 1km VIS variant for Google Maps, CONUS
	/mesonet/local/bin/gdal_merge.py -q -o vis.tif  -ul_lr -126 50 -66 24 -ps 0.01 0.01 GoesWest1V_latest.tif GoesEast1V_latest.tif
	/mesonet/local/bin/gdalwarp -q -s_srs EPSG:4326 -t_srs EPSG:900913 -tr 1000.0 1000.0 vis.tif vis_900913.tif
	/home/ldm/bin/pqinsert -p "gis c $ftm gis/images/900913/sat/conus_goes_vis1km.tif bogus tif" vis_900913.tif
	# Create full 1km VIS
	rm vis_900913.tif vis.tif
	/mesonet/local/bin/gdal_merge.py -q -o vis.tif  -ul_lr -180 65 -30 -15 -ps 0.01 0.01 GoesWest1V_latest.tif GoesEast1V_latest.tif
	/mesonet/local/bin/gdalwarp -q -s_srs EPSG:4326 -t_srs EPSG:900913 -tr 1000.0 1000.0 vis.tif vis_900913.tif
	/mesonet/local/bin/gdaladdo -q vis_900913.tif 2 4 6 18
endif

set sz="`stat -c %s GoesEast04I4_latest.tif`"
if ($sz > 1000) then
	/mesonet/local/bin/gdal_merge.py -q -o ir.tif  -ul_lr -126 50 -66 24 -ps 0.04 0.04 GoesWest04I4_latest.tif GoesEast04I4_latest.tif
	/home/ldm/bin/pqinsert -p "gis ac $ftm gis/images/4326/sat/conus_goes_ir4km.tif GIS/sat/conus_goes_ir4km_$atm.tif tif" ir.tif
	# Create 4km IR variant for Google Maps
	/mesonet/local/bin/gdalwarp -q -s_srs EPSG:4326 -t_srs EPSG:900913 -tr 4000.0 4000.0 ir.tif ir_900913.tif
	/home/ldm/bin/pqinsert -p "gis c $ftm gis/images/900913/sat/conus_goes_ir4km.tif bogus tif" ir_900913.tif
    # Full res!
	rm ir_900913.tif ir.tif
	/mesonet/local/bin/gdal_merge.py -q -o ir.tif  -ul_lr -180 65 -30 -15 -ps 0.04 0.04 GoesWest04I4_latest.tif GoesEast04I4_latest.tif
	/mesonet/local/bin/gdalwarp -q -s_srs EPSG:4326 -t_srs EPSG:900913 -tr 4000.0 4000.0 ir.tif ir_900913.tif
	/mesonet/local/bin/gdaladdo -q ir_900913.tif 2 4 6 18
endif

set sz="`stat -c %s GoesEast04I3_latest.tif`"
if ($sz > 1000) then
	/mesonet/local/bin/gdal_merge.py -q -o wv.tif  -ul_lr -126 50 -66 24 -ps 0.04 0.04 GoesWest04I3_latest.tif GoesEast04I3_latest.tif
	/home/ldm/bin/pqinsert -p "gis ac $ftm gis/images/4326/sat/conus_goes_wv4km.tif GIS/sat/conus_goes_wv4km_$atm.tif tif" wv.tif
	# Create 4km WV variant for Google Maps
	/mesonet/local/bin/gdalwarp -q -s_srs EPSG:4326 -t_srs EPSG:900913 -tr 4000.0 4000.0 wv.tif wv_900913.tif
	/home/ldm/bin/pqinsert -p "gis c $ftm gis/images/900913/sat/conus_goes_wv4km.tif bogus tif" wv_900913.tif
    # Full res!
	rm wv_900913.tif wv.tif
	/mesonet/local/bin/gdal_merge.py -q -o wv.tif  -ul_lr -180 65 -30 -15 -ps 0.04 0.04 GoesWest04I3_latest.tif GoesEast04I3_latest.tif
	/mesonet/local/bin/gdalwarp -q -s_srs EPSG:4326 -t_srs EPSG:900913 -tr 4000.0 4000.0 wv.tif wv_900913.tif
	/mesonet/local/bin/gdaladdo -q wv_900913.tif 2 4 6 18
endif

foreach mach (iemvs100.local iemvs101.local iemvs102.local iemvs103.local iemvs104.local iemvs105.local iem50.local)
  scp -q GoesWest1V_latest.tif ldm@${mach}:/tmp/west1V_0.tif
  ssh -q ldm@${mach} "cat /tmp/west1V_0.tif | csh ~/pyWWA/rotate.csh gis/images/4326/goes/west1V_ tif"
  scp -q GoesWest1V_latest.tfw ldm@${mach}:/mesonet/data/gis/images/4326/goes/west1V_0.tfw
 

  scp -q GoesEast1V_latest.tif ldm@${mach}:/tmp/east1V_0.tif
  ssh -q ldm@${mach} "cat /tmp/east1V_0.tif | csh ~/pyWWA/rotate.csh gis/images/4326/goes/east1V_ tif"
  scp -q GoesEast1V_latest.tfw ldm@${mach}:/mesonet/data/gis/images/4326/goes/east1V_0.tfw

  scp -q GoesWest04I4_latest.tif ldm@${mach}:/tmp/west04I4_0.tif
  ssh -q ldm@${mach} "cat /tmp/west04I4_0.tif | csh ~/pyWWA/rotate.csh gis/images/4326/goes/west04I4_ tif"
  scp -q GoesWest04I4_latest.tfw ldm@${mach}:/mesonet/data/gis/images/4326/goes/west04I4_0.tfw

  scp -q GoesEast04I4_latest.tif ldm@${mach}:/tmp/east04I4_0.tif
  ssh -q ldm@${mach} "cat /tmp/east04I4_0.tif | csh ~/pyWWA/rotate.csh gis/images/4326/goes/east04I4_ tif"
  scp -q GoesEast04I4_latest.tfw ldm@${mach}:/mesonet/data/gis/images/4326/goes/east04I4_0.tfw

  scp -q GoesWest04I3_latest.tif ldm@${mach}:/tmp/west04I3_0.tif
  ssh -q ldm@${mach} "cat /tmp/west04I3_0.tif | csh ~/pyWWA/rotate.csh gis/images/4326/goes/west04I3_ tif"
  scp -q GoesWest04I3_latest.tfw ldm@${mach}:/mesonet/data/gis/images/4326/goes/west04I3_0.tfw

  scp -q GoesEast04I3_latest.tif ldm@${mach}:/tmp/east04I3_0.tif
  ssh -q ldm@${mach} "cat /tmp/east04I3_0.tif | csh ~/pyWWA/rotate.csh gis/images/4326/goes/east04I3_ tif"
  scp -q GoesEast04I3_latest.tfw ldm@${mach}:/mesonet/data/gis/images/4326/goes/east04I3_0.tfw

  scp -q vis_900913.tif ldm@${mach}:/tmp/vis_900913_0.tif
  ssh -q ldm@${mach} "mv /tmp/vis_900913_0.tif ~/data/gis/images/900913/goes/vis_0.tif"
  scp -q ir_900913.tif ldm@${mach}:/tmp/ir_900913_0.tif
  ssh -q ldm@${mach} "mv /tmp/ir_900913_0.tif ~/data/gis/images/900913/goes/ir_0.tif"
  scp -q wv_900913.tif ldm@${mach}:/tmp/wv_900913_0.tif
  ssh -q ldm@${mach} "mv /tmp/wv_900913_0.tif ~/data/gis/images/900913/goes/wv_0.tif"

 end

#rm -f *.tif
