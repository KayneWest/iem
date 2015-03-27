"""Create simple maximum dbz composites for a given UTC date
"""
import time
import osgeo.gdal as gdal
from osgeo import gdalconst
import numpy as np
import datetime
import os
import urllib2
import pytz
import subprocess
import psycopg2
import sys

PGCONN = psycopg2.connect(database='mesosite', host='iemdb', user='nobody')
CURSOR = PGCONN.cursor()
PQINSERT = "/home/ldm/bin/pqinsert"
URLBASE = "http://iem.local/GIS/radmap.php?width=1280&height=720&"


def get_colortable(prod):
    """Get the color table for this prod

    Args:
      prod (str): product to get the table for

    Returns:
      colortable

    """
    CURSOR.execute("""
    select r,g,b from iemrasters_lookup l JOIN iemrasters r on
    (r.id = l.iemraster_id) WHERE r.name = %s ORDER by l.coloridx ASC
    """, ("composite_"+prod,))
    ct = gdal.ColorTable()
    for i, row in enumerate(CURSOR):
        ct.SetColorEntry(i, (row[0], row[1], row[2], 255))
    return ct


def run(prod, sts):
    """Create a max dbZ plot

    Args:
      prod (str): Product to run for, either n0r or n0q
      sts (datetime): date to run for
    """
    ets = sts + datetime.timedelta(days=1)
    interval = datetime.timedelta(minutes=5)

    n0rct = get_colortable(prod)

    # Loop over our archived files and do what is necessary
    maxn0r = None
    now = sts
    while now < ets:
        fn = now.strftime(("/mesonet/ARCHIVE/data/%Y/%m/%d/"
                           "GIS/uscomp/"+prod+"_%Y%m%d%H%M.png"))
        if not os.path.isfile(fn):
            print "MISSING:", fn
            now += interval
            continue
        n0r = gdal.Open(fn, 0)
        n0rd = n0r.ReadAsArray()
        if maxn0r is None:
            maxn0r = n0rd
        maxn0r = np.where(n0rd > maxn0r, n0rd, maxn0r)

        now += interval

    out_driver = gdal.GetDriverByName("gtiff")
    outdataset = out_driver.Create('max.tiff', n0r.RasterXSize,
                                   n0r.RasterYSize, n0r.RasterCount,
                                   gdalconst.GDT_Byte)
    # Set output color table to match input
    outdataset.GetRasterBand(1).SetRasterColorTable(n0rct)
    outdataset.GetRasterBand(1).WriteArray(maxn0r)
    del outdataset

    subprocess.call("convert max.tiff max.png", shell=True)
    # Insert into LDM
    cmd = ("%s -p 'plot a %s0000 bogus GIS/uscomp/max_%s_0z0z_%s.png "
           "png' max.png") % (PQINSERT, sts.strftime("%Y%m%d"), prod,
                              sts.strftime("%Y%m%d"))
    subprocess.call(cmd, shell=True)

    # Create tmp world file
    wldfn = '/tmp/tmpwld%s.wld' % (sts.strftime("%Y%m%d"),)
    out = open(wldfn, 'w')
    if prod == 'n0r':
        out.write("""0.01
0.0
0.0
-0.01
-126.0
50.0""")
    else:
        out.write("""0.005
0.0
0.0
-0.005
-126.0
50.0""")

    out.close()

    # Insert world file as well
    cmd = ("%s -i -p 'plot a %s0000 bogus GIS/uscomp/max_%s_0z0z_%s.wld "
           "wld' %s") % (PQINSERT, sts.strftime("%Y%m%d"), prod,
                         sts.strftime("%Y%m%d"), wldfn)
    subprocess.call(cmd, shell=True)

    # cleanup
    os.remove("max.tiff")
    os.remove("max.png")
    os.remove(wldfn)

    # Sleep for a bit
    time.sleep(60)

    # Iowa
    png = urllib2.urlopen("%slayers[]=uscounties&layers[]=%s&ts=%s" % (
        URLBASE, "nexrad_tc" if prod == 'n0r' else 'n0q_tc',
        sts.strftime("%Y%m%d%H%M"),))
    o = open('tmp.png', 'wb')
    o.write(png.read())
    o.close()
    cmd = ("%s -p 'plot ac %s0000 summary/max_%s_0z0z_comprad.png "
           "comprad/max_%s_0z0z_%s.png png' tmp.png"
           ) % (PQINSERT, sts.strftime("%Y%m%d"), prod, prod,
                sts.strftime("%Y%m%d"))
    subprocess.call(cmd, shell=True)

    # US
    png = urllib2.urlopen(("%ssector=conus&layers[]=uscounties&"
                           "layers[]=%s&ts=%s"
                           ) % (URLBASE,
                                "nexrad_tc" if prod == 'n0r' else 'n0q_tc',
                                sts.strftime("%Y%m%d%H%M"),))
    o = open('tmp.png', 'wb')
    o.write(png.read())
    o.close()
    cmd = ("%s -p 'plot ac %s0000 summary/max_%s_0z0z_usrad.png "
           "usrad/max_%s_0z0z_%s.png png' tmp.png"
           ) % (PQINSERT, sts.strftime("%Y%m%d"), prod, prod,
                sts.strftime("%Y%m%d"))
    subprocess.call(cmd, shell=True)
    os.remove("tmp.png")


def main():
    """Run main()"""
    # Default is to run for yesterday
    ts = datetime.datetime.utcnow() - datetime.timedelta(days=1)
    ts = ts.replace(tzinfo=pytz.timezone("UTC"))
    ts = ts.replace(hour=0, minute=0, second=0, microsecond=0)
    if len(sys.argv) == 4:
        ts = ts.replace(year=int(sys.argv[1]), month=int(sys.argv[2]),
                        day=int(sys.argv[3]))
    for prod in ['n0r', 'n0q']:
        run(prod, ts)

if __name__ == '__main__':
    # Do something
    main()
