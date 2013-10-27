"""
  Check to make sure the station metadata is reasonable!

"""
import iemdb
MESOSITE = iemdb.connect('mesosite', bypass=True)
mcursor = MESOSITE.cursor()

mcursor.execute("""
 SELECT id, network, ST_x(geom), ST_y(geom), modified from stations WHERE
 x(geom) >= 180 or x(geom) <= -180 or y(geom) > 90 or y(geom) < -90
""")
for row in mcursor:
    print 'LOC QC FAIL', row
