"""
Dump out precipitation information that we have!
"""
import psycopg2
import subprocess
import datetime
import os
IEM = psycopg2.connect(database='iem', host='iemdb', user='nobody')
icursor = IEM.cursor()


def send_to_ldm():
    """Send our file on its happy way"""
    utcnow = datetime.datetime.utcnow()
    cmd = ("/home/ldm/bin/pqinsert -i -p 'data c %s ia_precip.txt "
           "ia_precip.txt txt' /tmp/ia_precip.txt"
           ) % (utcnow.strftime("%Y%m%d%H%M"),)
    subprocess.call(cmd, shell=True)
    os.unlink("/tmp/ia_precip.txt")


def dumper():
    """Actually dump our observations"""
    output = open('/tmp/ia_precip.txt', 'w')
    output.write("ID,LON,LAT,START_UTC,END_UTC,PRECIP_MM\n")
    icursor.execute(""" SELECT s.id, ST_x(s.geom), ST_y(s.geom),
        c.pday, c.phour, s.network, c.valid at time zone 'UTC'
        from current c JOIN stations s on (s.iemid = c.iemid)
        WHERE s.network in ('IA_ASOS','AWOS','KCCI','IA_COOP')
        and c.valid > 'TODAY' and (pday >= 0 or phour >= 0)""")
    for row in icursor:
        valid = row[6]
        network = row[5]
        precip = row[4]
        if network == 'IA_COOP':
            t0 = valid - datetime.timedelta(hours=24)
            precip = row[3]
        elif network in ['IA_ASOS', 'AWOS']:
            t0 = valid.replace(minute=0)
        elif network == 'KCCI':
            t0 = valid.replace(hour=0, minute=0)
        if precip is None:
            continue
        output.write(("%s,%.4f,%.4f,%s,%s,%.1f\n"
                      ) % (row[0], row[1], row[2],
                           t0.strftime("%Y-%m-%d %H:%M"),
                           valid.strftime("%Y-%m-%d %H:%M"), precip * 25.4))
    output.close()

if __name__ == '__main__':
    dumper()
    send_to_ldm()
