#!/usr/bin/env python

import sys
sys.path.insert(1, "/mesonet/www/apps/iemwebsite/scripts/lib/")
import os
import cgi
import iemre
try:
    import netCDF4 as netCDF3
except:
    import netCDF3
import mx.DateTime
import json
import mesonet

form = cgi.FormContent()
ts1 = mx.DateTime.strptime( form["date1"][0], "%Y-%m-%d")
ts2 = mx.DateTime.strptime( form["date2"][0], "%Y-%m-%d")
if ts1 >= ts2:
    print 'Content-type: text/plain\n'
    print json.dumps( {'error': 'Date1 Larger than Date2', } )
    sys.exit()
if ts1.year != ts2.year:
    print 'Content-type: text/plain\n'
    print json.dumps( {'error': 'Multi-year query not supported yet...', } )
    sys.exit()
# Make sure we aren't in the future
tsend = mx.DateTime.today()
if ts2 >= tsend:
    ts2 = tsend - mx.DateTime.RelativeDateTime(days=1)

lat = float( form["lat"][0] )
lon = float( form["lon"][0] )
format = form["format"][0]

i,j = iemre.find_ij(lon, lat)
offset1 = int((ts1 - (ts1 + mx.DateTime.RelativeDateTime(month=1,day=1))).days)
offset2 = int((ts2 - (ts2 + mx.DateTime.RelativeDateTime(month=1,day=1))).days) +1

# Get our netCDF vars
fp = "/mesonet/data/iemre/%s_mw_daily.nc" % (ts1.year,)
nc = netCDF3.Dataset(fp, 'r')
hightemp = mesonet.k2f(nc.variables['high_tmpk'][offset1:offset2,j,i])
lowtemp = mesonet.k2f(nc.variables['low_tmpk'][offset1:offset2,j,i])
precip = nc.variables['p01d'][offset1:offset2,j,i] / 25.4
nc.close()

# Get our climatology vars
c2000 = ts1 + mx.DateTime.RelativeDateTime(year=2000)
coffset1 = int((c2000 - (mx.DateTime.DateTime(2000,1,1))).days)
c2000 = ts2 + mx.DateTime.RelativeDateTime(year=2000)
coffset2 = int((c2000 - (mx.DateTime.DateTime(2000,1,1))).days) +1
cnc = netCDF3.Dataset("/mesonet/data/iemre/mw_dailyc.nc", 'r')
chigh = mesonet.k2f(cnc.variables['high_tmpk'][coffset1:coffset2,j,i])
clow = mesonet.k2f(cnc.variables['low_tmpk'][coffset1:coffset2,j,i])
cprecip = cnc.variables['p01d'][coffset1:coffset2,j,i] / 25.4
cnc.close()

res = {'data': [], }

for i in range(0, offset2 - offset1):
    now = ts1 + mx.DateTime.RelativeDateTime(days=i)
    res['data'].append({
                'date': now.strftime("%Y-%m-%d"),
    'daily_high_f': "%.1f" % (hightemp[i],),
    'climate_daily_high_f': "%.1f" % (chigh[i],),
    'daily_low_f': "%.1f" % (lowtemp[i],),
    'climate_daily_low_f': "%.1f" % (clow[i],),
    'daily_precip_in': "%.2f" % (precip[i],),
    'climate_daily_precip_in': "%.2f" % (cprecip[i],),
       })


print 'Content-type: text/plain\n'
print json.dumps( res )
