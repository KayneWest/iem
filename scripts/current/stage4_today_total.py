"""
 Sum up the hourly precipitation from NCEP stage IV and produce maps
"""

import pygrib
import datetime
from pyiem.plot import MapPlot
import os
import sys
import pytz
import numpy as np

def doday(ts):
    """
    Create a plot of precipitation stage4 estimates for some day
    
    We should total files from 1 AM to midnight local time
    """
    sts = ts.replace(hour=1)
    ets = sts + datetime.timedelta(hours=24)
    interval = datetime.timedelta(hours=1)
    now = sts
    total = None
    lts = None
    while now < ets:
        gmt = now.astimezone(pytz.timezone("UTC"))
        fn = gmt.strftime(("/mesonet/ARCHIVE/data/%Y/%m/%d/"
                           +"stage4/ST4.%Y%m%d%H.01h.grib"))
        if os.path.isfile(fn):
            lts = now
            grbs = pygrib.open(fn)

            if total is None:
                g = grbs[1]
                total = g["values"]
                lats, lons = g.latlons()
            else:
                total += grbs[1]["values"]
            grbs.close()
        now += interval
    
    if lts is None and ts.hour > 1:
        print 'Missing StageIV data!'
    if lts is None:
        return
    lts = lts - datetime.timedelta(minutes=1)
    subtitle = "Total between 12:00 AM and %s" % (lts.strftime("%H:%M %p %Z"),)
    for sector in ['iowa', 'midwest', 'conus']:
        pqstr = "plot ac %s00 %s_stage4_1d.png %s_stage4_1d.png png" % (
                ts.strftime("%Y%m%d%H"), sector, sector )
        
        m = MapPlot(sector=sector,
                    title="%s NCEP Stage IV Today's Precipitation" % (
                                                    ts.strftime("%-d %b %Y"),),
                    subtitle=subtitle)
            
        clevs = np.arange(0,0.2,0.05)
        clevs = np.append(clevs, np.arange(0.2, 1.0, 0.1))
        clevs = np.append(clevs, np.arange(1.0, 5.0, 0.25))
        clevs = np.append(clevs, np.arange(5.0, 10.0, 1.0))
        clevs[0] = 0.01
    
        m.pcolormesh(lons, lats, total / 24.5, clevs, units='inch')
    
        #map.drawstates(zorder=2)
        if sector == 'iowa':
            m.drawcounties()
        m.postprocess(pqstr=pqstr)  
    
    
if __name__ == "__main__":
    ''' Go Main Go 
    
    So the past hour's stage IV is available by about 50 after, so we should 
    run for a day that is 90 minutes in the past by default
    
    '''
    if len(sys.argv) == 4:
        date = datetime.datetime(int(sys.argv[1]), int(sys.argv[2]), 
                                 int(sys.argv[3]), 12, 0)
    else:
        date = datetime.datetime.now()
        date = date - datetime.timedelta(minutes=90)
        date = date.replace(hour=12, minute=0, second=0, microsecond=0)
    # Stupid pytz timezone dance
    date = date.replace(tzinfo=pytz.timezone("UTC"))
    date = date.astimezone(pytz.timezone("America/Chicago"))
    doday(date)