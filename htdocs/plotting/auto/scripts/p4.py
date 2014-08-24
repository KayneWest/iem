import matplotlib
matplotlib.use('agg')
import matplotlib.pyplot as plt
from matplotlib.patches import Circle
import psycopg2.extras
import numpy as np
from scipy import stats
from pyiem import network
import matplotlib.patheffects as PathEffects
import datetime
import calendar
import matplotlib.dates as mdates
import netCDF4
from pyiem import iemre

def get_description():
    """ Return a dict describing how to call this plotter """
    d = dict()
    d['arguments'] = [
        dict(type='text', name='year', default='2014', label='Enter Year (1893-)'),
        dict(type='text', name='threshold', default='1.0', label='Precipitation Threshold [inch]'),
        dict(type='text', name='period', default='7', label='Over Period of Days'),
        dict(type='clstate', name='state', default='IA', label='For State'),
    ]
    return d

STATES = {'IA': 'Iowa',
          'IL': 'Illinois',
          'MO': 'Missouri',
          'KS': 'Kansas',
          'NE': 'Nebraska',
          'SD': 'South Dakota',
          'ND': 'North Dakota',
          'MN': 'Minnesota',
          'WI': 'Wisconsin',
          'MI': 'Michigan',
          'OH': 'Ohio',
          'KY': 'Kentucky'}

def plotter( fdict ):
    """ Go """
    year = int(fdict.get('year', 2014))
    threshold = float(fdict.get('threshold', 1))
    period = int(fdict.get('period', 7))
    state = fdict.get('state', 'IA')[:2]

    nc2 = netCDF4.Dataset("/mesonet/data/iemre/state_weights.nc")
    iowa = nc2.variables[state][:]
    iowapts = np.sum(np.where(iowa > 0, 1, 0))
    nc2.close()

    nc = netCDF4.Dataset('/mesonet/data/iemre/%s_mw_daily.nc' % (year, ))
    precip = nc.variables['p01d']

    now = datetime.datetime(year, 1, 1)
    now += datetime.timedelta(days=(period-1))
    ets = datetime.datetime(year, 12, 31)
    days = []
    coverage = []
    while now < ets:
        idx = iemre.daily_offset(now)
        sevenday = np.sum(precip[(idx-period):idx,:,:], 0)
        pday = np.where(iowa > 0, sevenday[:,:], -1)
        tots = np.sum(np.where(pday >= (threshold * 25.4), 1, 0 ))
        days.append( now )
        coverage.append( tots / float(iowapts) * 100.0)

        now += datetime.timedelta(days=1)
    
    (fig, ax) = plt.subplots(1,1)
    ax.bar(days, coverage, fc='g', ec='g')
    ax.set_title("IEM Estimated Areal Coverage Percent of %s\n receiving %.2f inches of rain over trailing %s day period" % (STATES[state], 
                                                                                                                            threshold, period))
    ax.set_ylabel("Areal Coverage [%]")
    ax.xaxis.set_major_formatter(mdates.DateFormatter('%b\n%Y'))
    ax.grid(True)
    return fig
