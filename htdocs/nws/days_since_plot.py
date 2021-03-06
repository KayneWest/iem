#!/usr/bin/env python
"""
 Generate a plot of the number of days since the last VTEC product issued
 by the WFO, save this in memcache for 30 minutes before regenerating
"""

import sys
from pyiem import plot
from pyiem.nws import vtec
import psycopg2
import cgi
import datetime
import memcache

utc = datetime.datetime.utcnow()
bins = [0, 1, 14, 31, 91, 182, 273, 365, 730, 1460, 2920, 3800]


def run(phenomena, significance, mc, key):
    """ Figure out what the user wanted and do it! """
    POSTGIS = psycopg2.connect(database='postgis', host='iemdb', user='nobody')
    pcursor = POSTGIS.cursor()

    pcursor.execute("""
     select wfo,  extract(days from ('TODAY'::date - max(issue))) as m 
     from warnings where significance = %s and phenomena = %s 
     GROUP by wfo ORDER by m ASC
    """, (significance, phenomena))
    data = {}
    for row in pcursor:
        data[row[0]] = max([row[1], 0])

    p = plot.MapPlot(sector='nws', axisbg='white',
                 title='Days since Last %s %s by NWS Office' % (
                        vtec._phenDict.get(phenomena, phenomena),
                        vtec._sigDict.get(significance, significance)),
                 subtitle='Valid %s' % (utc.strftime("%d %b %Y %H%M UTC"),))
    p.fill_cwas(data, bins=bins)
    p.postprocess(web=True, memcache=mc, memcachekey=key, memcacheexpire=1800)

def main():
    """ See how we are called """
    mc = memcache.Client(['iem-memcached:11211'], debug=0)

    form = cgi.FieldStorage()
    phenomena = form.getfirst('phenomena', 'TO')[:2]
    significance = form.getfirst('significance', 'W')[0]
    key = "days_since_%s_%s.png" % (phenomena, significance)
    res = mc.get(key)
    if res:
        sys.stdout.write('Content-type: image/png\n\n')
        sys.stdout.write(res)
    else:
        run(phenomena, significance, mc, key)

if __name__ == '__main__':
    # Go Main
    main()
