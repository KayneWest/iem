'''
 Loop over sites that we think are solely COOP, but report many times per day
 and so are likely DCP
'''
import psycopg2
IEM = psycopg2.connect(database='iem', host='iemdb')
icursor = IEM.cursor()
icursor2 = IEM.cursor()
MESOSITE = psycopg2.connect(database='mesosite', host='iemdb')
mcursor = MESOSITE.cursor()
mcursor2 = MESOSITE.cursor()

# Look for over-reporting COOPs
icursor.execute("""
 select id, network, count(*), max(raw) from current_log c JOIN stations s
 ON (s.iemid = c.iemid)
 where network ~* 'COOP' and valid > 'TODAY'::date
 GROUP by id, network ORDER by count DESC
""")
for row in icursor:
    sid = row[0]
    network = row[1]
    if row[2] < 5:
        continue
    # Look for how many entries are in mesosite
    mcursor.execute("""SELECT count(*) from stations where id = %s
      """, (sid,))
    row = mcursor.fetchone()
    if row[0] == 1:
        newnetwork = network.replace("_COOP", "_DCP")
        print 'We shall switch %s from %s to %s' % (sid, network, newnetwork)
        mcursor2.execute("""UPDATE stations SET network = '%s' WHERE id = '%s'
           """ % (newnetwork, sid))

IEM.commit()
icursor2.close()
MESOSITE.commit()
mcursor.close()
