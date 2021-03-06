import matplotlib
matplotlib.use('agg')
import matplotlib.pyplot as plt
import psycopg2.extras
import pyiem.nws.vtec as vtec
import numpy as np
import datetime
from pyiem.network import Table as NetworkTable
import calendar


def get_description():
    """ Return a dict describing how to call this plotter """
    d = dict()
    d['cache'] = 86400
    d['description'] = """This chart shows the number of VTEC phenomena and
    significance combinations issued by a NWS Forecast Office for a given year.
    """
    d['arguments'] = [
        dict(type='networkselect', name='station', network='WFO',
             default='DMX', label='Select WFO:', all=True)
    ]
    return d


def plotter(fdict):
    """ Go """
    pgconn = psycopg2.connect(database='postgis', host='iemdb', user='nobody')
    cursor = pgconn.cursor(cursor_factory=psycopg2.extras.DictCursor)

    station = fdict.get('station', 'DMX')[:4]

    nt = NetworkTable('WFO')
    nt.sts['_ALL'] = {'name': 'All Offices'}

    (fig, ax) = plt.subplots(1, 1, sharex=True)

    if station == '_ALL':
        cursor.execute("""
            with obs as (
                SELECT distinct extract(year from issue) as yr,
                phenomena, significance from warnings WHERE
                phenomena is not null and significance is not null and
                issue > '2005-01-01' and issue is not null
            )
            SELECT yr, count(*) from obs GROUP by yr ORDER by yr ASC
            """)
    else:
        cursor.execute("""
            with obs as (
                SELECT distinct extract(year from issue) as yr,
                phenomena, significance from warnings WHERE
                wfo = %s and phenomena is not null and significance is not null
                and issue > '2005-01-01' and issue is not null
            )
            SELECT yr, count(*) from obs GROUP by yr ORDER by yr ASC
            """, (station, ))

    years = []
    count = []
    for row in cursor:
        years.append(int(row[0]))
        count.append(int(row[1]))

    ax.bar(np.array(years)-0.4, count, width=0.8, fc='b', ec='b')
    for yr, val in zip(years, count):
        ax.text(yr, val+1, "%s" % (val,), ha='center')
    ax.set_title(("[%s] NWS %s\nCount of Distinct VTEC Phenomena/"
                  "Significance - %i to %i"
                  ) % (station, nt.sts[station]['name'],
                       years[0], years[-1]))
    ax.grid()
    ax.set_ylabel("Count")

    return fig
