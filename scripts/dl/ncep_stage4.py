# Script to download the NCEP stage4 data and then inject into LDM for
# sweet archival action

import mx.DateTime, urllib2, gzip, os, zlib, base64
import StringIO


def download(now, offset ):
    """
    Download a given timestamp from NCEP and inject into LDM
    Example:  ftp://ftpprd.ncep.noaa.gov/pub/data/nccf/com/hourly/prod/
              nam_pcpn_anal.20090916/ST4.2009091618.01h.Z
    """
    ts = now - mx.DateTime.RelativeDateTime(hours=offset)
    hours = [1,]
    if ts.hour % 6 == 0:
        hours.append( 6 )
    if ts.hour == 12 and offset != 0:
        hours.append( 24 )
    for hr in hours:
        url = "%s.%02ih.Z" % ( ts.strftime("ftp://ftpprd.ncep.noaa.gov/pub/data/nccf/com/hourly/prod/nam_pcpn_anal.%Y%m%d/ST4.%Y%m%d%H"), hr)
        #try:
        data = urllib2.urlopen( url ).read()
        #except:
        #    print "Download FAIL %s" % (url,)
        #    continue
        # Same temp file
        o = open("tmp.grib.Z", 'wb')
        o.write( data )
        o.close()
        os.system("gunzip -f tmp.grib.Z")
        # Inject into LDM
        cmd = "/home/ldm/bin/pqinsert -p 'data a %s blah stage4/ST4.%s.%02ih.grib grib' tmp.grib" % (ts.strftime("%Y%m%d%H%M"), ts.strftime("%Y%m%d%H"), hr)
        os.system( cmd )
        os.remove('tmp.grib')

if __name__ == "__main__":
    # We want this hour GMT
    now = mx.DateTime.gmt() + mx.DateTime.RelativeDateTime(minute=0,second=0)
    for offset in [33,9,3,0]:
        download( now, offset )
