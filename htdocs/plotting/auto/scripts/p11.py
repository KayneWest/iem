import matplotlib
matplotlib.use('agg')
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
import psycopg2.extras
import numpy as np
import datetime
from pyiem.network import Table as NetworkTable

PDICT ={'max-dwpf': 'Maximum Dew Point'}

def get_description():
    """ Return a dict describing how to call this plotter """
    d = dict()
    d['arguments'] = [
        dict(type='zstation', name='zstation', default='AMW', label='Select Station:'),
        dict(type='text', name='year', default='2014', label='Enter Year:'),
        dict(type='select', name='type', default='max-dwpf', label='Which metric to plot?',
             options=PDICT), 
        dict(type='text', name='emphasis', default='-99', 
             label='Temperature(&deg;F) Line of Emphasis (-99 disables):'),
    ]
    return d

def plotter( fdict ):
    """ Go """
    IEM = psycopg2.connect(database='iem', host='iemdb', user='nobody')
    cursor = IEM.cursor(cursor_factory=psycopg2.extras.DictCursor)

    station = fdict.get('zstation', 'AMW')
    network = fdict.get('network', 'IA_ASOS')
    nt = NetworkTable(network)
    year = int(fdict.get('year', 2014))
    emphasis = int(fdict.get('emphasis', -99))
    plotvar = fdict.get('pvar', 'max-dwpf')
    
    table = "summary_%s" % (year,)

    cursor.execute("""
     select day, 
     (case when max_dwpf > -90 and max_dwpf < 120 
             then max_dwpf else null end) as "max-dwpf"
     from """+table+""" where iemid = (select iemid from stations where 
     id = %s and network = %s) ORDER by day ASC 
    """, (station, network))
    days = []
    vals = []
    days2 = []
    vals2 = []
    for row in cursor:
        if row[plotvar] is None:
            continue
        days.append( row['day'] )
        vals.append( row[plotvar] )
        if emphasis > -99 and row[plotvar] >= emphasis:
            days2.append(row['day'])
            vals2.append(row[plotvar] - emphasis)
        
    vals = np.array(vals)
        
    (fig, ax) = plt.subplots(1,1)
    if len(vals) > 0:
        ax.bar(days, vals, ec='g', fc='g', zorder=1)
        ax.xaxis.set_major_formatter(mdates.DateFormatter('%-d\n%b'))
    else:
        ax.text(0.5, 0.5, "No Data Found!")
    if len(vals2) > 0:
        ax.bar(days2, vals2, bottom=emphasis, ec='r', fc='r', zorder=2)
        ax.axhline(emphasis, lw=2, color='k')
        ax.text(days[-1] + datetime.timedelta(days=2), 
                emphasis, "%s" % (emphasis,), ha='left',
                va='center')
    ax.grid(True)
    ax.set_ylabel("Dew Point Temperature $^\circ$F")
    ax.set_title("%s [%s] %s Daily Maximum Dew Point\nPeriod: %s to %s" % (
                nt.sts[station]['name'], station, year,
                min(days).strftime("%-d %b"), max(days).strftime("%-d %b")))

    return fig
