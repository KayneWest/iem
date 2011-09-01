# Generate a plot of normal GDD Accumulation since 1 May of this year

import sys, os
import iemplot

import mx.DateTime
now = mx.DateTime.now()
if now.month < 5 or now.month > 10:
  sys.exit(0)

from pyIEM import iemdb, stationTable
import network
nt = network.Table('IACLIMATE')
i = iemdb.iemdb()
coop = i['coop']

# Compute normal from the climate database
sql = """SELECT station, sum(gdd50) as gdd, sum(sdd86) as sdd 
   from climate WHERE gdd50 IS NOT NULL and sdd86 IS NOT NULL and 
   valid >= '2000-05-01' and valid <=
  ('2000-'||to_char(CURRENT_TIMESTAMP, 'mm-dd'))::date 
  and substr(station,0,3) = 'ia' GROUP by station"""

lats = []
lons = []
gdd50 = []
sdd86 = []
rs = coop.query(sql).dictresult()
for i in range(len(rs)):
  id = rs[i]['station'].upper()
  lats.append( nt.sts[id]['lat'] )
  lons.append( nt.sts[id]['lon'] )
  gdd50.append( rs[i]['gdd'] )
  sdd86.append( rs[i]['sdd'] )


cfg = {
 'wkColorMap': 'BlAqGrYeOrRe',
 'nglSpreadColorStart': 2,
 'nglSpreadColorEnd'  : -1,
 '_title'       : "1 May - %s Average GDD Accumulation" % (
                        now.strftime("%d %b"), ),
 'lbTitleString'      : "[base 50]",
 'pmLabelBarHeightF'  : 0.6,
 'pmLabelBarWidthF'   : 0.1,
 'lbLabelFontHeightF' : 0.025
}
# Generates tmp.ps
tmpfp = iemplot.simple_contour(lons, lats, gdd50, cfg)

pqstr = "plot c 000000000000 summary/gdd_norm_may1.png bogus png"
iemplot.postprocess(tmpfp, pqstr)

#---------- Plot the points

cfg = {
 'wkColorMap': 'BlAqGrYeOrRe',
 '_format': '%.0f',
 '_title'       : "1 May - %s Average GDD Accumulation" % (
                        now.strftime("%d %b"), ),
}


tmpfp = iemplot.simple_valplot(lons, lats, gdd50, cfg)

pqstr = "plot c 000000000000 summary/gdd_norm_may1_pt.png bogus png"
iemplot.postprocess(tmpfp, pqstr)
