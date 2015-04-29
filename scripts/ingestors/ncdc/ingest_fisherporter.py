"""Ingest the Fisher/Porter rain gage data from NCDC"""
import psycopg2
import sys
import pandas as pd
import datetime
import urllib2
import os
from pyiem.network import Table as NetworkTable


def process(tmpfn):
    """Process a file of data"""
    pgconn = psycopg2.connect(database='other', host='iemdb')
    nt = NetworkTable("IA_HPD")
    data = []
    for line in open(tmpfn):
        tokens = line.split(",")
        if len(tokens) < 9:
            continue
        if tokens[0] not in nt.sts:
            continue
        sid = tokens[0]
        cst = datetime.datetime.strptime(tokens[1] + " " + tokens[2],
                                         "%Y/%m/%d %H:%M:%S")
        counter = tokens[4].strip()
        tmpc = tokens[5].strip()
        battery = tokens[8].strip()
        data.append(dict(sid=sid, cst=cst, counter=counter, tmpc=tmpc,
                         battery=battery))
    df = pd.DataFrame(data)
    sids = df['sid'].unique()
    print('Found %s records from %s stations' % (len(data), len(sids)))
    for sid in sids:
        cursor = pgconn.cursor()
        df2 = df[df['sid'] == sid]
        maxts = df2['cst'].max()
        mints = df2['cst'].min()
        # Delete out old data
        cursor.execute("""DELETE from hpd_alldata where station = '%s'
        and valid >= '%s-06' and valid <= '%s-06'
        """ % (sid, mints.strftime("%Y-%m-%d %H:%M:%S"),
               maxts.strftime("%Y-%m-%d %H:%M:%S")))
        if cursor.rowcount > 0:
            print(' - removed  %s rows for sid: %s' % (cursor.rowcount, sid))
        counter = None
        for (_, row) in df2.iterrows():
            if counter is None:
                counter = float(row['counter'])
            if counter is not None and row['counter'] is not None:
                precip = float(row['counter']) - counter
                if precip < 0:
                    precip = 0
            else:
                precip = 0
            if precip > 1:
                print(('sid: %s cst: %s precip: %s counter1: %s counter2: %s'
                       ) % (sid, row['cst'], precip, counter, row['counter']))
            counter = float(row['counter'])
            tbl = "hpd_%s" % (row['cst'] + datetime.timedelta(hours=6)).year
            cursor.execute("""INSERT into """ + tbl + """
            (station, valid, counter, tmpc, battery, calc_precip)
            VALUES ('%s', '%s-06', %s, %s, %s, %s)
            """ % (sid, row['cst'].strftime("%Y-%m-%d %H:%M:%S"),
                   row['counter'],
                   'null' if row['tmpc'] == '' else row['tmpc'],
                   'null' if row['battery'] == '' else row['battery'],
                   precip))
        print(' + inserted %s rows for sid: %s' % (len(df2), sid))
        cursor.close()
        pgconn.commit()


def do(valid):
    """Process a month's worth of data"""
    uri = valid.strftime(("http://www1.ncdc.noaa.gov/pub/data/hpd/data/"
                          "hpd_%Y%m.csv"))
    tmpfn = valid.strftime("/tmp/hpd_%Y%m.csv")
    if not os.path.isfile(tmpfn):
        print('Downloading %s from NCDC' % (tmpfn,))
        o = open(tmpfn, 'w')
        o.write(urllib2.urlopen(uri).read())
        o.close()

    process(tmpfn)


def main(argv):
    """Go Main Go"""
    valid = datetime.datetime(int(argv[1]), int(argv[2]), 1)
    do(valid)

if __name__ == '__main__':
    main(sys.argv)
