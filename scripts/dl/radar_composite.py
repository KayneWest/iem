# Cache NEXRAD composites for the website

import urllib2, os, mx.DateTime, time, random, sys
from pyIEM import iemdb
i = iemdb.iemdb()
postgis = i['postgis']

opener = urllib2.build_opener()

def save(sectorName, file_name, dir_name, tstamp,bbox=None):
  layers = "layers[]=n0q&layers[]=watch_by_county&layers[]=sbw&layers[]=uscounties"
  uri = "http://iem50.local/GIS/radmap.php?sector=%s&ts=%s&%s" % \
        (sectorName,tstamp, layers)
  if (bbox is not None):
    uri = "http://iem50.local/GIS/radmap.php?bbox=%s&ts=%s&%s" % \
        (bbox,tstamp, layers)
  try:
    f = opener.open(uri)
  except:
    time.sleep(5)
    f = opener.open(uri)
  o = open('tmp.png', 'w')
  o.write( f.read() )
  o.close()

  cmd = "/home/ldm/bin/pqinsert -p 'plot ac %s %s %s/n0r_%s_%s.png png' tmp.png" % (tstamp, file_name, dir_name, tstamp[:8], tstamp[8:])
  os.system(cmd)

ts = mx.DateTime.gmt()
if len(sys.argv) == 6:
  ts = mx.DateTime.DateTime( int(sys.argv[1]), int(sys.argv[2]),
        int(sys.argv[3]), int(sys.argv[4]), int(sys.argv[5]) )
sts = ts.strftime("%Y%m%d%H%M")

save('conus', 'uscomp.png', 'usrad', sts)
save('iem', 'mwcomp.png', 'comprad', sts)
for i in ['lot','ict','sd','hun']:
  save(i, '%scomp.png'%(i,), '%srad' %(i,), sts)

# Now, we query for watches.
rs = postgis.query("select sel, xmax(geom), xmin(geom), ymax(geom), ymin(geom)\
     from watches_current ORDER by issued DESC").dictresult()
for i in range(len(rs)):
  xmin = float(rs[i]['xmin']) - 0.75 + (random.random() * 0.01)
  ymin = float(rs[i]['ymin']) - 0.75 + (random.random() * 0.01)
  xmax = float(rs[i]['xmax']) + 0.75 + (random.random() * 0.01)
  ymax = float(rs[i]['ymax']) + 0.75 + (random.random() * 0.01)
  bbox = "%s,%s,%s,%s" % (xmin,ymin,xmax,ymax)
  sel = rs[i]['sel'].lower()
  save('custom', '%scomp.png'%(sel,), '%srad'% (sel,), sts, bbox)
