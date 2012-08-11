"""
 Process data from the mini and portables 
"""

import mx.DateTime
import access
import iemdb
import psycopg2.extras
IEM = iemdb.connect('iem', bypass=True)
icursor = IEM.cursor(cursor_factory=psycopg2.extras.DictCursor)
import tracker
track = tracker.Engine()
import network
import mesonet
nt = network.Table("IA_RWIS")

lkp = {
'miniExportM1.csv': 'RAII4',
'miniExportM2.csv': 'RMYI4',
'portableExportP1.csv': 'RSWI4',
'portableExportP2.csv': 'RAGI4',
'portableExportP3.csv': 'RLMI4',
#'portableExportPT.csv': 'ROCI4',
'portableExportPT.csv': 'RRCI4',
}

thres = mx.DateTime.now() - mx.DateTime.RelativeDateTime(minutes=180)

def processfile( fp ):
    o = open("/mesonet/data/incoming/rwis/%s" % (fp,), 'r').readlines()
    if len(o) < 2:
        return
    heading = o[0].split(",")
    cols = o[1].split(",")
    data = {}
    if len(cols) < len(heading):
        return
    for i in range(len(heading)):
        if cols[i].strip() != "/":
            data[ heading[i].strip() ] = cols[i].strip()

    #print data
    nwsli = lkp[ fp ]
    iem = access.Ob(nwsli, 'IA_RWIS')
    if fp == 'portableExportP1.csv':
        ts = mx.DateTime.strptime(data['date_time'][:16], '%Y-%m-%d %H:%M')
    else:
        ts = mx.DateTime.strptime(data['date_time'][:-6], '%Y-%m-%d %H:%M')
    if ts.year < 2010:
      print "BAD! timestamp", ts
      return
    iem.setObTime( ts )
    iem.ts = ts
    iem.data['ts'] = ts
    iem.load_and_compare(cursor=icursor)

    # IEM Tracker garbage
    iem.data['sname'] = nt.sts[nwsli]['name']
    if ts > thres:
        track.checkStation(nwsli, iem.data, "IA_RWIS", "iarwis", False)
    else: 
        track.doAlert(nwsli, iem.data, "IA_RWIS", "iarwis", False)


    if data.has_key('wind_speed') and data['wind_speed'] != '':
        iem.data['sknt'] = float( data['wind_speed'] ) * 1.17  # to knots
    if data.has_key('sub') and data['sub'] != '':
        iem.data['rwis_subf'] = float( data['sub'] )
    if data.has_key('air_temp') and data['air_temp'] != '':
        iem.data['tmpf'] = float( data['air_temp'] )
    if data.has_key('pave_temp') and data['pave_temp'] != '':
        iem.data['tsf0'] = float( data['pave_temp'] )
    if data.has_key('pave_temp2') and data['pave_temp2'] not in ['','n']:
        iem.data['tsf1'] = float( data['pave_temp2'] )
    if data.has_key('press') and data['press'] != '':
        iem.data['alti'] = float( data['press'] )
    if data.has_key('RH') and data['RH'] != '':
        iem.data['dwpf'] = mesonet.dwpf(iem.data['tmpf'], float( data['RH'] ))
    if data.has_key('wind_dir') and data['wind_dir'] != '':
        iem.data['drct'] = float( data['wind_dir'] )
    iem.updateDatabase(cursor=icursor)

for k in lkp.keys():
    processfile( k )
    
IEM.commit()
track.send()
