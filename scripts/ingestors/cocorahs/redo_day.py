"""
Reprocess cocorahs data
"""

import urllib2
import datetime
import sys
import psycopg2
IEM = psycopg2.connect(database='iem', host='iemdb')
icursor = IEM.cursor()

state = sys.argv[1]


def safeP(v):
    v = v.strip()
    if (v == "T"):
        return 0.0001
    if (v == "NA"):
        return -99
    return float(v)


def runner(days):
    now = datetime.datetime.now() - datetime.timedelta(days=days)

    req = urllib2.Request(("http://data.cocorahs.org/Cocorahs/export/"
                           "exportreports.aspx?ReportType=Daily&dtf=1&"
                           "Format=CSV&State=%s&ReportDateType=Daily&"
                           "Date=%s&TimesInGMT=False"
                           "") % (state,
                                  now.strftime("%m/%d/%Y%%20%H:00%%20%P")))
    data = urllib2.urlopen(req).readlines()

    # Process Header
    header = {}
    h = data[0].split(",")
    for i in range(len(h)):
        header[h[i]] = i

    for row in data[1:]:
        cols = row.split(",")
        sid = cols[header["StationNumber"]].strip()

        t = "%s %s" % (cols[header["ObservationDate"]],
                       cols[header["ObservationTime"]].strip())
        ts = datetime.datetime.strptime(t, "%Y-%m-%d %I:%M %p")

        val = safeP(cols[header["TotalPrecipAmt"]])

        sql = """SELECT t.iemid, pday from summary_%s s JOIN stations t
            ON (t.iemid = s.iemid) WHERE t.id = '%s' and day = '%s'
            """ % (ts.year, sid, ts.strftime("%Y-%m-%d"))
        icursor.execute(sql)
        if icursor.rowcount == 0:
            print "%s - add summary for date %s" % (sid,
                                                    ts.strftime("%Y-%m-%d"))
            sql = """
            INSERT into summary_%s(iemid, day, pday)
              VALUES ((select iemid from stations where id = '%s'),
              '%s', %s) """ % (ts.year, sid, ts.strftime("%Y-%m-%d"), val)
            icursor.execute(sql)
        else:
            row = icursor.fetchone()
            dbval = row[1]
            if dbval != val:
                print(("%s - prec diff old: %s new: %s date: %s"
                       "") % (sid, dbval, val, ts.strftime("%Y-%m-%d")))
                sql = """
                    UPDATE summary_%s s SET pday = %s FROM stations t
                    WHERE t.iemid = s.iemid and t.id = '%s' and day = '%s'
                    """ % (ts.year, val, sid, ts.strftime("%Y-%m-%d"))
                icursor.execute(sql)

        val = safeP(cols[header["NewSnowDepth"]])

        sql = """
            SELECT snow from summary_%s s JOIN stations t ON
            (t.iemid = s.iemid) WHERE t.id = '%s' and day = '%s'
            """ % (ts.year, sid, ts.strftime("%Y-%m-%d"))
        icursor.execute(sql)
        if icursor.rowcount == 0:
            print 'NEED entry for %s %s' % (id, ts.strftime("%Y-%m-%d"))
        else:
            row = icursor.fetchone()
            dbval = row[0]
            if val >= 0 and (row[0] is None or float(row[0]) != val):
                print(("%s - snow diff old: %s new: %s date: %s"
                       "") % (sid, dbval, val, ts.strftime("%Y-%m-%d")))
                sql = """
                    UPDATE summary_%s s SET snow = %s FROM stations t
                    WHERE t.iemid = s.iemid and t.id = '%s' and day = '%s'
                    """ % (ts.year, val, sid, ts.strftime("%Y-%m-%d"))
                icursor.execute(sql)


for i in range(1, 15):
    runner(i)

icursor.close()
IEM.commit()
IEM.close()
