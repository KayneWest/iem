# Generate a map of this month's observed precip

import sys, os
import iemplot

import mx.DateTime
now = mx.DateTime.now()

import iemdb
IEM = iemdb.connect('iem', bypass=True)
icursor = IEM.cursor()

# Compute normal from the climate database
sql = """SELECT id,
      sum(CASE WHEN pday < 0 THEN 0 ELSE pday END) as precip,
      sum(CASE when pday < 0 THEN 1 ELSE 0 END) as missing,
      ST_x(s.geom) as lon, ST_y(s.geom) as lat from summary_%s c JOIN  stations s
     ON (s.iemid = c.iemid) 
     WHERE s.network in ('IA_COOP') and s.iemid = c.iemid and 
      extract(month from day) = %s and extract(year from day) = extract(year from now())
     GROUP by id, lat, lon""" % (now.year, 
  now.strftime("%m"),)

lats = []
lons = []
precip = []
labels = []
icursor.execute( sql )
for row in icursor:
  if row[2] > (now.day / 3):
    continue
  id = row[0]
  labels.append( id )
  lats.append( row[4] )
  lons.append( row[3] )
  precip.append( row[1] )


#---------- Plot the points

cfg = {
 'wkColorMap': 'gsltod',
 '_format': '%.2f',
 '_labels': labels,
 '_title'       : "This Month's Precipitation [inch] (NWS COOP Network)",
 '_valid'       : now.strftime("%b %Y"),
}


tmpfp = iemplot.simple_valplot(lons, lats, precip, cfg)

pqstr = "plot c 000000000000 coopMonthPlot.png bogus png"
iemplot.postprocess(tmpfp, pqstr)
