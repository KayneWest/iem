#!/mesonet/python-2.4/bin/python
# Something to dump current warnings to a shapefile
# 28 Aug 2004 port to iem40

import shapelib, dbflib, mx.DateTime, zipfile, os, sys, shutil, cgi
from pyIEM import wellknowntext, iemdb

i = iemdb.iemdb()
mydb = i["postgis"]
#import pg
#mydb = pg.connect('postgis', 'mesonet-db1.agron.iastate.edu',user='nobody')

mydb.query("SET TIME ZONE 'GMT'")

# Get CGI vars
#form = cgi.FormContent()
#year = int(form["year"][0])
#month1 = int(form["month1"][0])
#month2 = int(form["month2"][0])
#day1 = int(form["day1"][0])
#day2 = int(form["day2"][0])
#hour1 = int(form["hour1"][0])
#hour2 = int(form["hour2"][0])
#minute1 = int(form["minute1"][0])
#minute2 = int(form["minute2"][0])

#sTS = mx.DateTime.DateTime(year, month1, day1, hour1, minute1)
#eTS = mx.DateTime.DateTime(year, month2, day2, hour2, minute2)

os.chdir("/tmp/")
fp = "watch_by_county"

shp = shapelib.create(fp, shapelib.SHPT_POLYGON)

dbf = dbflib.create(fp)
dbf.add_field("WFO", dbflib.FTString, 3, 0)
dbf.add_field("ISSUED", dbflib.FTString, 12, 0)
dbf.add_field("EXPIRED", dbflib.FTString, 12, 0)
dbf.add_field("PHENOM", dbflib.FTString, 2, 0)
dbf.add_field("GTYPE", dbflib.FTString, 1, 0)
dbf.add_field("SIG", dbflib.FTString, 1, 0)
dbf.add_field("ETN", dbflib.FTInteger, 4, 0)
dbf.add_field("STATUS", dbflib.FTString, 3, 0)
dbf.add_field("NWS_UGC", dbflib.FTString, 6, 0)

sql = "select phenomena, eventid, astext(multi(geomunion(geom))) as tgeom \
       from warnings WHERE significance = 'A' and \
       phenomena IN ('TO','SV') and issue <= now() and \
       expire > now() GROUP by phenomena, eventid ORDER by phenomena ASC"
rs = mydb.query(sql).dictresult()

cnt = 0
for i in range(len(rs)):
	s = rs[i]["tgeom"]
	if (s == None or s == ""):
		continue
	f = wellknowntext.convert_well_known_text(s)

	t = rs[i]["phenomena"]
	#issue = mx.DateTime.strptime(rs[i]["issue"][:16], "%Y-%m-%d %H:%M")
	#expire = mx.DateTime.strptime(rs[i]["expire"][:16],"%Y-%m-%d %H:%M")
	d = {}
	#d["ISSUED"] = issue.strftime("%Y%m%d%H%M")
	#d["EXPIRED"] = expire.strftime("%Y%m%d%H%M")
	d["PHENOM"] = t
	#d["GTYPE"] = g
	#d["SIG"] = rs[i]["significance"]
	#d["WFO"] = rs[i]["wfo"]
	d["ETN"] = rs[i]["eventid"]
	#d["STATUS"] = rs[i]["status"]
	#d["NWS_UGC"] = rs[i]["ugc"]

	obj = shapelib.SHPObject(shapelib.SHPT_POLYGON, 1, f )
	shp.write_object(-1, obj)
	dbf.write_record(cnt, d)
	del(obj)
	cnt += 1

if (cnt == 0):
	obj = shapelib.SHPObject(shapelib.SHPT_POLYGON, 1, [[(0.1, 0.1), (0.2, 0.2), (0.3, 0.1), (0.1, 0.1)]])
	d = {}
	d["PHENOM"] = "ZZ"
	d["ETN"] = 0
	shp.write_object(-1, obj)
	dbf.write_record(0, d)

del(shp)
del(dbf)

# Create zip file, send it back to the clients
shutil.copyfile("/mesonet/data/gis/meta/4326.prj", fp+".prj")
z = zipfile.ZipFile(fp+".zip", 'w', zipfile.ZIP_DEFLATED)
z.write(fp+".shp")
z.write(fp+".shx")
z.write(fp+".dbf")
z.write(fp+".prj")
z.close()

print "Content-type: application/octet-stream"
print "Content-Disposition: attachment; filename=%s.zip" % (fp,)
print

print file(fp+".zip", 'r').read(),

os.remove(fp+".zip")
os.remove(fp+".shp")
os.remove(fp+".shx")
os.remove(fp+".dbf")
os.remove(fp+".prj")
