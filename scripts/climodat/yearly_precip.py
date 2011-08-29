# Generate a map of Yearly Precipitation

import sys, os
sys.path.append("../lib/")
import iemplot

import mx.DateTime
now = mx.DateTime.now()

from pyIEM import iemdb, stationTable
st = stationTable.stationTable("/mesonet/TABLES/coopClimate.stns")
st.sts["IA0200"]["lon"] = -93.4
st.sts["IA5992"]["lat"] = 41.65
i = iemdb.iemdb()
coop = i['coop']

def runYear(year):
  # Grab the data
  sql = """SELECT stationid, sum(precip) as total
           from alldata_ia WHERE year = %s and
           stationid != 'ia0000' GROUP by stationid""" % (year,)

  lats = []
  lons = []
  vals = []
  labels = []
  rs = coop.query(sql).dictresult()
  for i in range(len(rs)):
    if rs[i]['total'] < 10: # Arb Threshold
      continue
    id = rs[i]['stationid'].upper()
    if not st.sts.has_key(id):
      continue
    labels.append( id[2:] )
    lats.append( st.sts[id]['lat'] )
    lons.append( st.sts[id]['lon'] )
    vals.append( rs[i]['total'] )

    #---------- Plot the points

  cfg = {
     'wkColorMap': 'gsltod',
     '_format'   : '%.2f',
     '_labels'   : labels,
     '_valid'    : '1 Jan - %s' % (now.strftime("%d %B"),),
     '_title'    : "Total Precipitation [inch] (%s)" % (year,),
  }

  tmpfp = iemplot.simple_valplot(lons, lats, vals, cfg)
  pqstr = "plot m %s/summary/total_precip.png" % (year,)
  iemplot.postprocess(tmpfp, pqstr)


if __name__ == '__main__':
  runYear( sys.argv[1] )
