'''
 My purpose in life is to sync the mesosite stations table to other
databases.  This will hopefully remove some hackery
'''

import datetime
import sys
import psycopg2
import psycopg2.extras
MESOSITE = psycopg2.connect(database="mesosite", host='iemdb')
subscribers = ["iem","coop","hads","asos", "postgis"]

if len(sys.argv) == 2:
    print 'Running laptop syncing from upstream, assume iemdb is localhost!'
    MESOSITE = psycopg2.connect(database='mesosite', host='mesonet.agron.iastate.edu', user='nobody')
    subscribers.append('mesosite')

def sync(dbname):
    """
    Actually do the syncing, please
    """
    # connect to synced database
    dbconn = psycopg2.connect(database=dbname, host='iemdb')
    dbcursor = dbconn.cursor()
    # Figure out our latest revision
    dbcursor.execute("""
    SELECT max(modified), max(iemid) from stations
    """)
    row = dbcursor.fetchone()
    maxTS = (row[0] or datetime.datetime(1980,1,1))
    maxID = (row[1] or -1)
    # figure out what has changed!
    cur = MESOSITE.cursor(cursor_factory=psycopg2.extras.DictCursor)
    cur.execute("""SELECT * from stations WHERE modified > %s or iemid > %s""",
    (maxTS, maxID) )
    for row in cur:
        if row['iemid'] > maxID:
            dbcursor.execute("""INSERT into stations(iemid, network, id) 
             VALUES(%s,%s,%s) """, (row['iemid'], row['network'], row['id']))
        # insert queried stations
        dbcursor.execute("""UPDATE stations SET name = %(name)s, 
       state = %(state)s, elevation = %(elevation)s, online = %(online)s, 
       geom = %(geom)s, params = %(params)s, county = %(county)s, 
       plot_name = %(plot_name)s, climate_site = %(climate_site)s,
       wfo = %(wfo)s, archive_begin = %(archive_begin)s, 
       archive_end = %(archive_end)s, remote_id = %(remote_id)s, 
       tzname = %(tzname)s, country = %(country)s, 
       modified = %(modified)s, network = %(network)s, metasite = %(metasite)s,
       sigstage_low = %(sigstage_low)s, sigstage_action = %(sigstage_action)s,
       sigstage_bankfull = %(sigstage_bankfull)s, sigstage_flood = %(sigstage_flood)s,
       sigstage_moderate = %(sigstage_moderate)s, sigstage_major = %(sigstage_major)s,
       sigstage_record = %(sigstage_record)s, ugc_county = %(ugc_county)s,
       ugc_zone = %(ugc_zone)s, id = %(id)s, ncdc81 = %(ncdc81)s
       WHERE iemid = %(iemid)s""",
       row)
    print 'DB: %-7s Mod %4s rows TS: %s IEMID: %s' % (dbname, 
       cur.rowcount, maxTS.strftime("%Y/%m/%d %H:%M"), maxID)
    # close connection
    dbcursor.close()
    dbconn.commit()
    dbconn.close()

for sub in subscribers:
    sync(sub)
MESOSITE.close()
