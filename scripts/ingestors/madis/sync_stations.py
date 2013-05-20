"""
Extract station data from file and update any new stations we find, please
"""

import netCDF4
import iemdb
import sys
MESOSITE = iemdb.connect('mesosite', bypass=True)
mcursor = MESOSITE.cursor()

fn = sys.argv[1]
nc = netCDF4.Dataset(fn)

stations   = nc.variables["stationId"]
names   = nc.variables["stationName"]
providers  = nc.variables["dataProvider"]
latitudes  = nc.variables["latitude"]
longitudes  = nc.variables["longitude"]
elevations  = nc.variables["elevation"]


MY_PROVIDERS = ["MNDOT", "KSDOT", "WIDOT", "INDOT", "NDDOT",
 "NEDOR", "WYDOT", "OHDOT", "MDDOT", "NHDOT", "WVDOT", "NVDOT",
 "AKDOT", "VTDOT", "WIDOT", "MEDOT", "VADOT","CODOT", "FLDOT",
 "GADOT", "KYTC-RWIS", "KYMN"]

def provider2network(p):
    """ Convert a MADIS network ID to one that I use, here in IEM land"""
    if p in ['KYMN']:
        return p
    return '%s_RWIS' % (p[:2],)

for recnum in range(len(providers)):
    thisProvider = providers[recnum].tostring().replace('\x00','')
    if not thisProvider in MY_PROVIDERS:
        continue
    stid = stations[recnum].tostring().replace('\x00','')
    name = names[recnum].tostring().replace("'", "").replace('\x00','')
    network = provider2network(thisProvider)
    mcursor.execute("""SELECT * from stations where id = %s and network = %s""",
                    (stid, network))
    if mcursor.rowcount > 0:
        continue
    print 'Adding network: %s station: %s' % (network, stid)
    sql = """INSERT into stations(id, network, synop, country, plot_name,
    name, state, elevation, online, geom, metasite) VALUES ('%s', '%s', 9999, 'US',
    '%s', '%s', '%s', %s, 't', 'SRID=4326;POINT(%s %s)', 'f')""" % (stid,
    network, name, name, network[:2], elevations[recnum], longitudes[recnum],
    latitudes[recnum])
    mcursor.execute(sql)
nc.close()
mcursor.close()
MESOSITE.commit()
MESOSITE.close()
