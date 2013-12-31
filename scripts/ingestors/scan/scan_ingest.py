"""
Download and process the scan dataset
"""

import urllib
import urllib2
import datetime
import mesonet
import access
import network
nt = network.Table("SCAN")
import psycopg2
SCAN = psycopg2.connect(database='scan', host='iemdb')
scursor = SCAN.cursor()
ACCESS = psycopg2.connect(database='iem', host='iemdb')
icursor = ACCESS.cursor()

mapping = {
    'Site Id': {'iemvar': 'station', 'multiplier': 1},
    'Date': {'iemvar': '', 'multiplier': 1},
    'Time (CST)': {'iemvar': '', 'multiplier': 1},
    'Time (CDT)': {'iemvar': '', 'multiplier': 1},
   'PREC.I-1 (in)': {'iemvar': '', 'multiplier': 1},
   'PREC.I-2 (in)': {'iemvar': '', 'multiplier': 1},
   'TOBS.I-1 (degC)': {'iemvar': 'tmpc', 'multiplier': 1},
   'TMAX.H-1 (degC)':{'iemvar': '', 'multiplier': 1},
   'TMIN.H-1 (degC)': {'iemvar': '', 'multiplier': 1},
   'TAVG.H-1 (degC)':{'iemvar': '', 'multiplier': 1},
   'PRCP.H-1 (in)':{'iemvar': 'phour', 'multiplier': 1},
   'SMS.I-1:-2 (pct)':{'iemvar': 'c1smv', 'multiplier': 1},
   'SMS.I-1:-4 (pct)':{'iemvar': 'c2smv', 'multiplier': 1},
   'SMS.I-1:-8 (pct)':{'iemvar': 'c3smv', 'multiplier': 1},
   'SMS.I-1:-20 (pct)':{'iemvar': 'c4smv', 'multiplier': 1},
   'SMS.I-1:-40 (pct)':{'iemvar': 'c5smv', 'multiplier': 1},
   'STO.I-1:-2 (degC)':{'iemvar': 'c1tmpc', 'multiplier': 1},
   'STO.I-1:-4 (degC)':{'iemvar': 'c2tmpc', 'multiplier': 1},
   'STO.I-1:-8 (degC)':{'iemvar': 'c3tmpc', 'multiplier': 1},
   'STO.I-1:-20 (degC)':{'iemvar': 'c4tmpc', 'multiplier': 1},
   'STO.I-1:-40 (degC)':{'iemvar': 'c5tmpc', 'multiplier': 1},
   'STO.I-2:-2 (degC)':{'iemvar': '', 'multiplier': 1},
   'STO.I-2:-4 (degC)':{'iemvar': '', 'multiplier': 1},
   'STO.I-2:-8 (degC)':{'iemvar': '', 'multiplier': 1},
   'STO.I-2:-20 (degC)':{'iemvar': '', 'multiplier': 1},
   'STO.I-2:-40 (degC)':{'iemvar': '', 'multiplier': 1},
   'SAL.I-1:-2 (gram/l)':{'iemvar': '', 'multiplier': 1},
   'SAL.I-1:-4 (gram/l)':{'iemvar': '', 'multiplier': 1},
   'SAL.I-1:-8 (gram/l)':{'iemvar': '', 'multiplier': 1},
   'SAL.I-1:-20 (gram/l)':{'iemvar': '', 'multiplier': 1},
   'SAL.I-1:-40 (gram/l)':{'iemvar': '', 'multiplier': 1},
   'RDC.I-1:-2 (unitless)':{'iemvar': '', 'multiplier': 1},
   'RDC.I-1:-4 (unitless)':{'iemvar': '', 'multiplier': 1},
   'RDC.I-1:-8 (unitless)':{'iemvar': '', 'multiplier': 1},
   'RDC.I-1:-20 (unitless)':{'iemvar': '', 'multiplier': 1},
   'RDC.I-1:-40 (unitless)':{'iemvar': '', 'multiplier': 1},
   'BATT.I-1 (volt)':{'iemvar': '', 'multiplier': 1},
   'BATT.I-2 (volt)':{'iemvar': '', 'multiplier': 1},
   
   'WDIRV.H-1:144 (degree)':{'iemvar': 'drct', 'multiplier': 1},
   'WDIRV.H-1:101 (degree)':{'iemvar': 'drct', 'multiplier': 1},
   'WDIRV.H-1:120 (degree)':{'iemvar': 'drct', 'multiplier': 1},
   'WDIRV.H-1:140 (degree)':{'iemvar': 'drct', 'multiplier': 1},
   'WDIRV.H-1:117 (degree)':{'iemvar': 'drct', 'multiplier': 1},

   'WSPDX.H-1:144 (mph)':{'iemvar': 'gust', 'multiplier': 0.8689},
   'WSPDX.H-1:101 (mph)':{'iemvar': 'gust', 'multiplier': 0.8689},
   'WSPDX.H-1:120 (mph)':{'iemvar': 'gust', 'multiplier': 0.8689},
   'WSPDX.H-1:140 (mph)':{'iemvar': 'gust', 'multiplier': 0.8689},
   'WSPDX.H-1:117 (mph)':{'iemvar': 'gust', 'multiplier': 0.8689},
   
   'WSPDV.H-1:144 (mph)':{'iemvar': 'sknt', 'multiplier': 0.8689},
   'WSPDV.H-1:101 (mph)':{'iemvar': 'sknt', 'multiplier': 0.8689},
   'WSPDV.H-1:120 (mph)':{'iemvar': 'sknt', 'multiplier': 0.8689},
   'WSPDV.H-1:140 (mph)':{'iemvar': 'sknt', 'multiplier': 0.8689},
   'WSPDV.H-1:117 (mph)':{'iemvar': 'sknt', 'multiplier': 0.8689},
   
   'RHUM.I-1 (pct)':{'iemvar': 'relh', 'multiplier': 1},
   'PRES.I-1 (inch_Hg)':{'iemvar': 'pres', 'multiplier': 1},
   'SRADV.H-1 (watt/m2)':{'iemvar': 'srad', 'multiplier': 1},
   'DPTP.H-1 (degC)':{'iemvar': 'dwpc', 'multiplier': 1},
   'PVPV.H-1 (kPa)':{'iemvar': '', 'multiplier': 1},
   'RHUMN.H-1 (pct)':{'iemvar': '', 'multiplier': 1},
   'RHUMX.H-1 (pct)':{'iemvar': '', 'multiplier': 1},
   'SVPV.H-1 (kPa)':{'iemvar': '', 'multiplier': 1},
}

postvars = {
    'sitenum': '2031',
    'report': 'ALL',
    'timeseries': 'Hourly',
    'interval': 'DAY', # PST ?
    'format': 'copy',
   # 'site_name': 'Ames',
   # 'state_name': 'Iowa',
    'site_network': 'scan',
    'time_zone': 'CST',
}
URI = 'http://www.wcc.nrcs.usda.gov/nwcc/view'

def savedata( data , maxts ):
    """
    Save away our data into IEM Access
    """
    if data.has_key('Time'):
        tstr = "%s %s" % (data['Date'], data['Time'])
    elif data.has_key('Time (CST)'):
        tstr = "%s %s" % (data['Date'], data['Time (CST)'])
    else:
        tstr = "%s %s" % (data['Date'], data['Time (CDT)'])
    ts = datetime.datetime.strptime(tstr, '%Y-%m-%d %H:%M')
    sid = "S%s" % (data['Site Id'],)
    
    if maxts.has_key(sid) and maxts[sid] >= ts:
        return
    maxts[sid] = ts
    iem = access.Ob(sid, 'SCAN')
    iem.txn = icursor

    iem.data['ts'] = ts
    iem.data['year'] = ts.year
    for key in data.keys():
        if mapping.has_key(key) and mapping[key]['iemvar'] != "" and key != 'Site Id':
            iem.data[ mapping[key]['iemvar'] ] = data[key].strip()

    iem.data['valid'] = ts
    iem.data['tmpf'] = mesonet.c2f(float(iem.data.get('tmpc')))
    iem.data['dwpf'] = mesonet.c2f(float(iem.data.get('dwpc')))
    iem.data['c1tmpf'] = mesonet.c2f(float(iem.data.get('c1tmpc')))
    iem.data['c2tmpf'] = mesonet.c2f(float(iem.data.get('c2tmpc')))
    iem.data['c3tmpf'] = mesonet.c2f(float(iem.data.get('c3tmpc')))
    iem.data['c4tmpf'] = mesonet.c2f(float(iem.data.get('c4tmpc')))
    iem.data['c5tmpf'] = mesonet.c2f(float(iem.data.get('c5tmpc')))     
    iem.data['c1smv'] = float(iem.data.get('c1smv'))
    iem.data['c2smv'] = float(iem.data.get('c2smv'))
    iem.data['c3smv'] = float(iem.data.get('c3smv'))
    iem.data['c4smv'] = float(iem.data.get('c4smv'))
    iem.data['c5smv'] = float(iem.data.get('c5smv'))
    iem.data['phour'] = float(iem.data.get('phour'))
    if not iem.updateDatabase():
        print 'scan_ingest.py iemaccess for sid: %s ts: %s updated no rows' % (
                                                    sid, ts)

    sql = """INSERT into t%(year)s_hourly (station, valid, tmpf, 
        dwpf, srad, 
         sknt, drct, relh, pres, c1tmpf, c2tmpf, c3tmpf, c4tmpf, 
         c5tmpf, 
         c1smv, c2smv, c3smv, c4smv, c5smv, phour) 
        VALUES 
        (%(station)s, '%(valid)s', %(tmpf)s, %(dwpf)s,
         %(srad)s,%(sknt)s,
        %(drct)s, %(relh)s, %(pres)s, %(c1tmpf)s, 
        %(c2tmpf)s, 
        %(c3tmpf)s, %(c4tmpf)s, %(c5tmpf)s, %(c1smv)s,
         %(c2smv)s, 
        %(c3smv)s, %(c4smv)s, %(c5smv)s, %(phour)s)
        """ % iem.data
    scursor.execute(sql)

def load_times():
    """
    Load the latest ob times from the database
    """
    icursor.execute("""SELECT t.id, valid from current c, stations t
        WHERE t.iemid = c.iemid and t.network = 'SCAN'""")
    d = {}
    for row in icursor:
        d[ row[0] ] = datetime.datetime.strptime( 
                                            str(row[1])[:16], '%Y-%m-%d %H:%M')
    return d

def main():
    maxts = load_times()
    for sid in nt.sts.keys():
        # iem uses S<id> and scan site uses just <id>
        postvars['sitenum'] = sid[1:]
        data = urllib.urlencode(postvars)
        req = urllib2.Request(URI, data)
        try:
            response = urllib2.urlopen(req, timeout=15)
        except Exception, exp:
            print 'scan_ingest.py Failed to download: %s %s' % (sid, exp)
            continue
        lines = response.readlines()
        cols = lines[2].split(",")
        data = {}
        for row in lines[3:]:
            if row.strip() == "":
                continue
            tokens = row.replace("'",'').split(",")
            for col, token in zip(cols, tokens):
                if col.strip() == '':
                    continue
                col = col.replace('  (loam)', '').replace('  (silt)', '')
                data[col.strip()] = token
            if data.has_key('Date'):
                savedata( data , maxts)
main()
icursor.close()
scursor.close()
ACCESS.commit()
SCAN.commit()
