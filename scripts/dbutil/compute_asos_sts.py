"""Update metadata database with archive start for METAR sites.

Looks at the asos database and finds the first observation from a site.

"""

import psycopg2
from pyiem.network import Table as NetworkTable
import sys
import datetime
import pytz

basets = datetime.datetime.now()
basets = basets.replace(tzinfo=pytz.timezone("America/Chicago"))

asos = psycopg2.connect(database='asos', host='iemdb', user='nobody')
acursor = asos.cursor()
mesosite = psycopg2.connect(database='mesosite', host='iemdb')
mcursor = mesosite.cursor()

net = sys.argv[1]

table = NetworkTable(net)
keys = table.sts.keys()
if len(keys) > 1:
    ids = `tuple(keys)`
else:
    ids = "('%s')" % (keys[0],)

acursor.execute("""SELECT station, min(valid), max(valid) from alldata 
  WHERE station in %s GROUP by station 
  ORDER by min ASC""" % (ids,))
for row in acursor:
    station = row[0]
    if table.sts[station]['archive_begin'] != row[1]:
        print 'Updated %s STS WAS: %s NOW: %s' % (station, 
                    table.sts[station]['archive_begin'], row[1])
  
    mcursor.execute("""UPDATE stations SET archive_begin = %s 
         WHERE id = %s and network = %s""" , (row[1], station, net) )
    if mcursor.rowcount == 0:
        print 'ERROR: No rows updated'
  
    # Site without data in past year is offline!
    if (basets - row[2]).days  > 365:
        if table.sts[station]['archive_end'] != row[2]:
            print 'Updated %s ETS WAS: %s NOW: %s' % (station, 
                    table.sts[station]['archive_end'], row[2])
  
            mcursor.execute("""UPDATE stations SET archive_end = %s 
                 WHERE id = %s and network = %s""" , (row[2], station, net) )
    # If it was offline and now is on, correct this
    if ((basets - row[2]).days  < 365 and 
        table.sts[station]['archive_end'] is not None):
        print 'Updated %s ETS WAS: %s NOW: None' % (station, 
                    table.sts[station]['archive_end'])
  
        mcursor.execute("""UPDATE stations SET archive_end = null 
                 WHERE id = %s and network = %s""" , ( station, net) )
    
mcursor.close()
mesosite.commit()
mesosite.close()
