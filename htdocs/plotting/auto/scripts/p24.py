
import psycopg2.extras
import calendar
from pyiem.plot import MapPlot
import matplotlib.cm as cm
import datetime

def get_description():
    """ Return a dict describing how to call this plotter """
    d = dict()
    d['arguments'] = [
        dict(type='year', name='year', default='2014', 
             label='Select Year:',
             min=1893), # Comes back to python as yyyy-mm-dd
        dict(type='month', name='month', default='9', 
             label='Select Month:'),
    ]
    return d

def plotter( fdict ):
    """ Go """
    pgconn = psycopg2.connect(database='coop', host='iemdb', user='nobody')
    cursor = pgconn.cursor(cursor_factory=psycopg2.extras.DictCursor)

    year = int(fdict.get('year', 2014))
    month = int(fdict.get('month', 9))

    lastyear = datetime.date.today().year
    years = lastyear - 1893 + 1

    cursor.execute("""
    with monthly as (
        SELECT year, station, sum(precip) as p from alldata
        WHERE substr(station,3,1) = 'C' and month = %s 
        GROUP by year, station), 
    ranks as (
        SELECT station, year, 
        rank() OVER (PARTITION by station ORDER by p DESC) from monthly)
    
    SELECT station, rank from ranks where year = %s """, (month, year))
    data = {}
    for row in cursor:
        data[row['station']] = row['rank']

    m = MapPlot(sector='midwest', axisbg='white',
                title='%s %s Precipitation Total Ranks by Climate District' % (
                        year, calendar.month_name[month]),
                subtitle=('Based on IEM Estimates, '
                          +'1 is wettest out of %s total years (1893-%s)') % (
                                                    years, lastyear)
                )
    cmap = cm.get_cmap("BrBG_r")
    cmap.set_under('white')
    cmap.set_over('black')
    m.fill_climdiv(data, bins=[1,5,10,25,50,75,100,years-10,years-5,years], cmap=cmap)

    return m.fig
