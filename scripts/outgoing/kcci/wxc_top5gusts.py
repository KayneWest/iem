import os
import subprocess
import datetime
import sys
import tempfile
import tracker
qc = tracker.loadqc()
import iemdb
IEM = iemdb.connect("iem", bypass=True)
icursor = IEM.cursor()

icursor.execute("""SELECT t.id as station from current c, stations t 
    WHERE t.network = 'KCCI' and 
  valid > 'TODAY' and t.iemid = c.iemid  ORDER by gust DESC""")
data = {}

data['timestamp'] = datetime.datetime.now()
i = 1
for row in icursor:
    if i == 6:
        break
    if qc.get(row[0], {}).get('wind', False):
        continue
    data['sid%s' % (i,)] = row[0]
    i += 1

if i == 1:
    sys.exit()

fd, path = tempfile.mkstemp()
os.write(fd,  open('top5gusts.tpl','r').read() % data )
os.close(fd)

subprocess.call("/home/ldm/bin/pqinsert -p 'auto_top5gusts.scn' %s" % (path,),
                shell=True)
os.remove(path)

fd, path = tempfile.mkstemp()
os.write(fd,  open('top5gusts_time.tpl','r').read() % data )
os.close(fd)

subprocess.call("/home/ldm/bin/pqinsert -p 'auto_top5gusts_time.scn' %s" % (path,),
                shell=True)
os.remove(path)
