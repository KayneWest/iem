'''
 Backfill information into the IEM summary table, so the website tools are 
 happier
'''
import psycopg2
from pyiem.datatypes import temperature

ISUAG = psycopg2.connect(database='isuag', host='iemdb')
icursor = ISUAG.cursor()

IEM = psycopg2.connect(database='iem', host='iemdb')
iemcursor = IEM.cursor()

icursor.execute("""SELECT station, valid, rain_mm_tot, tair_c_max,
    tair_c_min from sm_daily WHERE tair_c_max is not null""")

for row in icursor:
    high = temperature(row[3],'C').value('F')
    low = temperature(row[4],'C').value('F')
    
    iemcursor.execute("""SELECT pday, max_tmpf, min_tmpf from summary s JOIN stations t on
    (t.iemid = s.iemid) WHERE day = %s and t.id = %s and t.network = 'ISUSM'
    """, (row[1], row[0]))
    if iemcursor.rowcount == 0:
        print 'Adding summary_%s row %s %s' % (row[1].year, row[0], row[1])
        iemcursor.execute("""INSERT into summary_"""+str(row[1].year)
                          +""" (iemid, day, pday, max_tmpf, min_tmpf) VALUES (
                          (SELECT iemid from stations where id = '%s' and
                          network = 'ISUSM'), '%s', %s, %s, %s
                          )
                          """ % (row[0], row[1], row[2] / 24.5, high, low))
    else:
        row2 = iemcursor.fetchone()
        if (row2[1] is None or row2[2] is None or 
            round(row2[0],2) != round((row[2] / 24.5),2) or
            round(high,2) != round(row2[1],2) or 
            round(low,2) != round(row2[2],2)):
            print 'Mismatch %s %s old: %s new: %s' % (row[0], row[1], row2[0],
                                                      row[2] / 24.5)
            
            iemcursor.execute("""UPDATE summary SET pday = %s, max_tmpf = %s,
            min_tmpf = %s WHERE
            iemid = (select iemid from stations WHERE network = 'ISUSM' and
            id = %s) and day = %s""", (row[2] / 24.5, high, low, row[0], row[1]))

iemcursor.close()
IEM.commit()
IEM.close()