"""
 Mine grid point extracted values for our good and the good of the IEM
 Use Unidata's motherlode server :)

"""
import sys
import iemdb, network
table = network.Table( ['AWOS', 'IA_ASOS'] )
MOS = iemdb.connect('mos')
mcursor = MOS.cursor()

import csv, urllib2
import mx.DateTime

BASE_URL = "http://motherlode.ucar.edu:9080/thredds/ncss/grid/grib/NCEP/"
URLS = { 
 'NAM' : "NAM/CONUS_12km/conduit/files/NAM-CONUS_12km-conduit_%Y%m%d_%H00.grib2",
 'GFS' : "GFS/Global_0p5deg/files/GFS-Global_0p5deg_%Y%m%d_%H00.grib2",
 'RAP' : "RAP/CONUS_13km/files/RR_CONUS_13km_%Y%m%d_%H00.grib2",
}
VLOOKUP = {
 'sbcape': {'NAM': 'Convective_available_potential_energy_surface',
            'GFS': 'Convective_available_potential_energy_surface',
            'RAP': 'Convective_available_potential_energy_surface'},
 'sbcin': {'NAM': 'Convective_inhibition_surface',
           'GFS': 'Convective_inhibition_surface',
           'RAP': 'Convective_inhibition_surface'},
 'pwater': {'NAM': 'Precipitable_water',
            'GFS': 'Precipitable_water',
            'RAP': 'Precipitable_water'},
 'precipcon': {'RAP': 'Convective_precipitation',
            'NAM': 'Convective_precipitation',
            'GFS': 'Convective_precipitation',
           },
 'precipnon': {'RAP': 'Large_scale_precipitation_non-convective',
            'NAM': None,
            'GFS': None
           },
 'precip': {'RAP': None,
            'NAM': 'Total_precipitation',
            'GFS': 'Total_precipitation',
           }
}

def clean(v):
    if str(v) == "NaN":
        return "Null"
    return float(v)

def run(model, station, lon, lat, ts):
    """
    Ingest the model data for a given Model, stationid and timestamp
    """

    vstring = ""
    for v in VLOOKUP:
        if VLOOKUP[v][model] is not None:
            vstring += "var=%s&" % (VLOOKUP[v][model],)

    url = "%s%s?%slatitude=%s&longitude=%s&temporal=all&vertCoord=&accept=csv&point=true" % (BASE_URL, ts.strftime( URLS[model] ), vstring, lat, lon)
    try:
        fp = urllib2.urlopen( url )
    except:
        print 'FAIL ts: %s station: %s model: %s' % (ts.strftime("%Y-%m-%d %H"), 
                                                     station, model)
        return

    sql = """DELETE from model_gridpoint_%s WHERE station = '%s' and 
          model = '%s' and runtime = '%s+00' """ % (ts.year, station,
          model,ts.strftime("%Y-%m-%d %H:%M") )
    mcursor.execute( sql )

    count = 0
    r = csv.DictReader( fp )
    for row in r:
        for k in row.keys():
            row[ k[:k.find("[")] ] = row[k]
        sbcape = clean( row[ VLOOKUP['sbcape'][model] ] )
        sbcin = clean( row[ VLOOKUP['sbcin'][model] ] )
        pwater = clean( row[ VLOOKUP['pwater'][model] ] )
        precipcon = clean( row[ VLOOKUP['precipcon'][model] ] )
        if model == "RAP":
            precip = float(row[ VLOOKUP['precipcon'][model] ]) + float(row[ VLOOKUP['precipnon'][model] ])
        else:
            precip = clean( row[ VLOOKUP['precip'][model] ] )
        if precip < 0:
            precip = "Null"
        if precipcon < 0:
            precipcon = "Null"
        fts = mx.DateTime.strptime(row['date'], '%Y-%m-%dT%H:%M:%SZ')
        sql = """INSERT into model_gridpoint_%s(station, model, runtime, 
              ftime, sbcape, sbcin, pwater, precipcon, precip) 
              VALUES ('%s','%s', '%s+00',
              '%s+00', %s, %s, %s, %s, %s )""" % ( ts.year, station, model,
              ts.strftime("%Y-%m-%d %H:%M"), fts.strftime("%Y-%m-%d %H:%M"),
              sbcape, sbcin, pwater, precipcon, precip)
        mcursor.execute( sql.replace(' nan ', 'Null') )
        count += 1
    return count

if __name__ == '__main__':
    gts = mx.DateTime.gmt()
    if len(sys.argv) == 5:
        gts = mx.DateTime.DateTime(int(sys.argv[1]), int(sys.argv[2]),int(sys.argv[3]),
                                   int(sys.argv[4]))
    if gts.hour % 6 == 0:
        ts = gts - mx.DateTime.RelativeDateTime(hours=6,minute=0,second=0)
        for id in table.sts.keys():
            cnt = run("GFS", "K"+id, table.sts[id]['lon'], table.sts[id]['lat'], ts)
            if cnt == 0:
                print 'No data', "K"+id, ts, 'GFS'
            cnt = run("NAM", "K"+id, table.sts[id]['lon'], table.sts[id]['lat'], ts)
            if cnt == 0:
                print 'No data', "K"+id, ts, 'GFS'
    for id in table.sts.keys():
        ts = gts - mx.DateTime.RelativeDateTime(hours=2,minute=0,second=0)
        run("RAP", "K"+id, table.sts[id]['lon'], table.sts[id]['lat'], ts)
    mcursor.close()
    MOS.commit()
    MOS.close()
