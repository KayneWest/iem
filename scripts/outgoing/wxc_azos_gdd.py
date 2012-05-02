# Generate a Weather Central Formatted file of Growing Degree Days
# for our beloved ASOS/AWOS network

import mx.DateTime, os, Ngl, numpy, shutil
import sys
from pyIEM import iemdb, stationTable
import network
nt = network.Table("ISUAG")
i = iemdb.iemdb()
access = i['iem']
coop = i['coop']
mesosite = i['mesosite']
isuag = i['isuag']

def sampler(xaxis, yaxis, vals, x, y):
    i = 0
    while (xaxis[i] < x):
        i += 1
    j = 0
    while (yaxis[j] < y):
        j += 1
    return vals[i,j]


def load_soilt(data):
    soil_obs = []
    lats = [] 
    lons = []
    valid = 'YESTERDAY'
    if mx.DateTime.now().hour < 7:
        valid = '%s' % ((mx.DateTime.now() - mx.DateTime.RelativeDateTime(days=2)).strftime("%Y-%m-%d"), )
    rs = isuag.query("""SELECT station, c30 from daily WHERE 
         valid = '%s'""" % (valid,) ).dictresult()
    for i in range(len(rs)):
        stid = rs[i]['station']
        if not nt.sts.has_key(stid):
            continue
        soil_obs.append( rs[i]['c30'] )
        lats.append( nt.sts[stid]['lat'] )
        lons.append( nt.sts[stid]['lon'] )
    if len(lons) == 0:
        print 'No ISUAG Data for %s' % (valid,)
        sys.exit()
    numxout = 40
    numyout = 40
    xmin    = min(lons) - 1.
    ymin    = min(lats) - 1.
    xmax    = max(lons) + 1.
    ymax    = max(lats) + 1.
    xc      = (xmax-xmin)/(numxout-1)
    yc      = (ymax-ymin)/(numyout-1)

    xo = xmin + xc* numpy.arange(0,numxout)
    yo = ymin + yc* numpy.arange(0,numyout)

    analysis = Ngl.natgrid(lons, lats, soil_obs, list(xo), list(yo))
    for id in data.keys():
        data[id]['soilt'] = sampler(xo,yo,analysis, data[id]['lon'], data[id]['lat'])


def build_xref():
    rs = mesosite.query("SELECT id, climate_site from stations WHERE network in ('IA_ASOS','AWOS')").dictresult()
    data = {}
    for i in range(len(rs)):
        data[rs[i]['id']] = rs[i]['climate_site']
    return data

def compute_climate(sts, ets):
    sql = """SELECT station, sum(gdd50) as cgdd,
    sum(precip) as crain from climate WHERE valid >= '2000-%s' and valid < '2000-%s' and gdd50 is not null GROUP by station""" % (sts.strftime("%m-%d"), ets.strftime("%m-%d"))
    rs = coop.query(sql).dictresult()
    data = {}
    for i in range(len(rs)):
        data[rs[i]['station']] = rs[i]
    return data

def compute_obs(sts, ets):
    """ Compute the GS values given a start/end time and networks to look at
    """
    sql = """
SELECT
  id, x(s.geom) as lon, y(s.geom) as lat,
  sum( case when max_tmpf = -99 THEN 1 ELSE 0 END) as missing,
  sum( gdd50(max_tmpf, min_tmpf) ) as gdd,
  sum( case when pday > 0 THEN pday ELSE 0 END ) as precip
FROM 
  summary_%s c, stations s
WHERE
  s.network in ('IA_ASOS','AWOS') and
  day >= '%s' and day < '%s' and
  c.iemid = s.iemid
GROUP by s.id, lon, lat
    """ % (sts.year, sts.strftime("%Y-%m-%d"), ets.strftime("%Y-%m-%d"))
    rs = access.query(sql).dictresult()
    data = {}
    for i in range(len(rs)):
        data[rs[i]['id']] = rs[i]
    return data

def main():
    sts =  mx.DateTime.now() + mx.DateTime.RelativeDateTime(month=5,day=1)
    ets = mx.DateTime.now()
    if sts > ets:
        return

    output = open('wxc_iem_agdata.txt', 'w')
    output.write("""Weather Central 001d%s00 Surface Data
   8
   4 Station
   4 GDD_MAY1
   4 GDD_MAY1_NORM
   5 PRECIP_MAY1
   5 PRECIP_MAY1_NORM
   5 SOIL_4INCH
   6 Lat
   8 Lon
""" % (mx.DateTime.now().strftime("%H"),))
    days = (ets - sts).days
    data = compute_obs( sts, ets )
    load_soilt(data)
    cdata = compute_climate( sts, ets )
    xref = build_xref()
    for id in data.keys():
        if data[id]['missing'] > (days * 0.1):
            continue
        csite = xref[id]
        output.write("K%s %4.0f %4.0f %5.2f %5.2f %5.1f %6.3f %8.3f\n" % (id, 
        data[id]['gdd'], cdata[ csite ]['cgdd'],
        data[id]['precip'], cdata[ csite ]['crain'], data[id]['soilt'],
        data[id]['lat'], data[id]['lon'] ))
    output.close()
    os.system("/home/ldm/bin/pqinsert -p \"wxc_iem_agdata.txt\" wxc_iem_agdata.txt")
    shutil.copyfile("wxc_iem_agdata.txt", "/mesonet/share/pickup/wxc/wxc_iem_agdata.txt")
    os.remove("wxc_iem_agdata.txt")

if __name__ == '__main__':
    main()
