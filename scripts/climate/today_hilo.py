# Generate a map of today's average high and low temperature

import sys, os

import iemplot

import mx.DateTime
now = mx.DateTime.now()

import network
nt = network.Table('IACLIMATE')
nt.sts["IA0200"]["lon"] = -93.6
nt.sts["IA5992"]["lat"] = 41.65
import iemdb
import psycopg2.extras
coop = iemdb.connect('coop', bypass=True)

# Compute normal from the climate database
sql = """SELECT station, high, low from climate WHERE valid = '2000-%s' 
    and substr(station,0,3) = 'IA'""" % (
  now.strftime("%m-%d"),)

lats = []
lons = []
highs = []
lows = []
labels = []
c = coop.cursor(cursor_factory=psycopg2.extras.DictCursor)
c.execute(sql)
for row in c:
    sid = row['station']
    if sid[2] == 'C' or sid[2:] == '0000' or not nt.sts.has_key(sid):
        continue
    labels.append( sid[2:] )
    lats.append( nt.sts[sid]['lat'] )
    lons.append( nt.sts[sid]['lon'] )
    highs.append( row['high'] )
    lows.append( row['low'] )


#---------- Plot the points

cfg = {
 'wkColorMap': 'gsltod',
 '_format': '%.0f',
# '_labels': labels,
 '_title'       : "Average High + Low Temperature [F] (1893-%s)" % (now.year,),
 '_valid'       : now.strftime("%d %b"),
}


tmpfp = iemplot.hilo_valplot(lons, lats, highs, lows, cfg)

pqstr = "plot ac %s0000 climate/iowa_today_avg_hilo_pt.png coop_avg_temp.png png" % (
  now.strftime("%Y%m%d"), )
iemplot.postprocess(tmpfp, pqstr)
