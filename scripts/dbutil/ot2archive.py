"""
Dump iem database of OT data to archive
Runs at 00 and 12 UTC
"""
import datetime
import psycopg2
import sys
import iemdb
import pytz
import psycopg2.extras

OTHER = psycopg2.connect(database='other', host='iemdb')
ocursor = OTHER.cursor(cursor_factory=psycopg2.extras.DictCursor)
IEM = psycopg2.connect(database='iem', host='iemdb')
icursor = IEM.cursor(cursor_factory=psycopg2.extras.DictCursor)

ts = datetime.datetime( int(sys.argv[1]), int(sys.argv[2]), int(sys.argv[3]))
ts = ts.replace(hour=0, second=0, minute=0, microsecond=0,
                tzinfo=pytz.timezone("UTC"))
ts2 = ts + datetime.timedelta(hours=24)

# Delete any obs from yesterday
sql = "DELETE from t%s WHERE valid >= '%s' and valid < '%s'" % (ts.year, 
      ts, ts2 )
ocursor.execute(sql)

# Get obs from Access
sql = """SELECT c.*, t.id from current_log c JOIN stations t on 
    (t.iemid = c.iemid) WHERE valid >= '%s' and valid < '%s'  
    and t.network = 'OT'""" % (ts, ts2 )
icursor.execute( sql )

for row in icursor:
    pday = 0
    if (row['pday'] is not None and float(row['pday']) > 0):
        pday = row['pday'] 
    alti = row['alti']
    if alti is None and row['mslp'] is not None:
        alti = row['mslp'] * .03 
    sql = """INSERT into t%s (station, valid, tmpf, dwpf, drct, sknt,  alti, 
         pday, gust, c1tmpf,srad) values('%s','%s',%s,%s,%s,%s,%s,%s,%s,%s,%s)
         """ % (ts.year, row['id'], row['valid'], (row['tmpf'] or "Null"), 
  (row['dwpf'] or "Null"), (row['drct'] or "Null"), (row['sknt'] or "Null"),
  (alti or "Null"), pday, (row['gust'] or "Null") , 
   (row['c1tmpf'] or "Null"), (row['srad'] or "Null"))
    ocursor.execute(sql)

ocursor.close()
OTHER.commit()