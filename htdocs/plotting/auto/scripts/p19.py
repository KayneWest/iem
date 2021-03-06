import matplotlib
matplotlib.use('agg')
import matplotlib.pyplot as plt
import psycopg2.extras
import numpy as np
from pyiem import network
import datetime
import sys

def get_description():
    """ Return a dict describing how to call this plotter """
    d = dict()
    d['arguments'] = [
        dict(type='station', name='station', default='IA0200', label='Select Station:'),
        dict(type='text', name='binsize', default='10', label='Histogram Bin Size:'),
    ]
    return d

def plotter( fdict ):
    """ Go """
    COOP = psycopg2.connect(database='coop', host='iemdb', user='nobody')
    ccursor = COOP.cursor(cursor_factory=psycopg2.extras.DictCursor)

    station = fdict.get('station', 'IA0200')
    binsize = int(fdict.get('binsize', 10))
    
    table = "alldata_%s" % (station[:2],)
    nt = network.Table("%sCLIMATE" % (station[:2],))

    ccursor.execute("""
    SELECT high, low from """+table+"""
      WHERE station = %s and year > 1892 and high >= low
    """, (station,))
    highs = []
    lows = []
    for row in ccursor:
        highs.append( row[0] )
        lows.append( row[1] )

    bins = np.arange(-20, 121, binsize)
        
    H, xedges, yedges = np.histogram2d(lows, highs, bins)
    years = float( 
        datetime.datetime.now().year - nt.sts[station]['archive_begin'].year
        )
    H = np.ma.array(H / years)
    H.mask = np.where(H < (1./years), True, False)
    ar = np.argwhere(H.max() == H)
        
    (fig, ax) = plt.subplots(1,1)
    res = ax.pcolormesh(xedges, yedges, H.transpose())
    fig.colorbar( res, label="Days per Year")
    ax.grid(True)
    ax.set_title("%s [%s]\nDaily High vs Low Temperature Histogram" % (
                nt.sts[station]['name'], station))
    ax.set_ylabel("High Temperature $^{\circ}\mathrm{F}$")
    ax.set_xlabel("Low Temperature $^{\circ}\mathrm{F}$")
        
    x = ar[0][0]
    y = ar[0][1]
    ax.text(0.65, 0.15, "Largest Frequency: %.1f days\nHigh: %.0f-%.0f Low: %.0f-%.0f" % (
                            H[x,y], yedges[y], yedges[y+1],
                                         xedges[x], xedges[x+1]), ha='center', 
            va='center', transform=ax.transAxes,
            bbox=dict(color='white'))
    ax.axhline(32, linestyle='-', lw=1, color='k')
    ax.text(120, 32, "32$^\circ$F", va='center', ha='right', color='white',
            bbox=dict(color='k'), fontsize=8)
    ax.axvline(32, linestyle='-', lw=1, color='k')
    ax.text(32, 120, "32$^\circ$F", va='top', ha='center', color='white',
            bbox=dict(color='k', edgecolor='none'), fontsize=8)
        
    return fig
