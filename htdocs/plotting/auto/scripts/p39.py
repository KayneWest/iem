"""
  Fall Minimum by Date
"""
import psycopg2.extras
import matplotlib
matplotlib.use('agg')
import matplotlib.pyplot as plt
import numpy as np
import datetime
from pyiem.network import Table as NetworkTable

def get_description():
    """ Return a dict describing how to call this plotter """
    d = dict()
    d['description'] = """This plot compares the month to date average 
    temperature of this month against any previous month of your choice.
    The plot then contains this month's to date average temperature along 
    with the scenarios of the remaining days for this month from each of
    the past years.  These scenarios provide a good approximation of what is
    possible for the remainder of the month."""
    d['arguments'] = [
        dict(type='station', name='station', default='IA0200', 
             label='Select Station:'),
        dict(type='year', name='year', default='2014', min=1893, 
             label='Select Year to Compare With:'),
        dict(type='month', name='month', default='11',  
             label='Select Month to Compare With:'),
    ]
    return d

def plotter( fdict ):
    """ Go """
    pgconn = psycopg2.connect(database='coop', host='iemdb', user='nobody')
    cursor = pgconn.cursor(cursor_factory=psycopg2.extras.DictCursor)

    station = fdict.get('station', 'IA0200')
    year = int(fdict.get('year', 2014))
    month = int(fdict.get('month', 11))

    table = "alldata_%s" % (station[:2],)
    nt = NetworkTable("%sCLIMATE" % (station[:2],))

    thismonth = datetime.date.today()
    oldmonth = datetime.date(year, month, 1)
    
    # beat month
    cursor.execute("""SELECT extract(day from day), (high+low)/2. from
    """+table+""" WHERE station = %s and year = %s and month = %s
    ORDER by day ASC
    """, (station, year, month))
    
    prevmonth = []
    for row in cursor:
        prevmonth.append(float(row[1]))

    # build history
    cursor.execute("""SELECT year, day, (high+low)/2. from
    """+table+""" WHERE station = %s and month = %s
    ORDER by day ASC
    """, (station, thismonth.month))

    for i, row in enumerate(cursor):
        if i == 0:
            baseyear = row[0]
            data = np.ones((thismonth.year - row[0] + 1, 31)) * -99
        data[row[0]-baseyear, row[1].day-1] = row[2]

    # overwrite our current month's data
    for i in range(np.shape(data)[0] - 1):
        data[i,:thismonth.day-1] = data[-1, :thismonth.day-1]
    avgs = np.zeros(np.shape(data))
    days = np.shape(data)[1]
    prevavg = []
    for i in range(days):
        avgs[:-1,i] = np.sum(data[:-1,:i+1], 1) / float(i+1)
        prevavg.append( np.sum(prevmonth[:i+1]) / float(i+1))
    
    (fig, ax) = plt.subplots(1,1)
    
    beats = 0
    for yr in range(np.shape(data)[0]-1):
        if avgs[yr,-1] > prevavg[-1]:
            beats += 1
        ax.plot(np.arange(1,days+1), avgs[yr, :], zorder=1, color='tan')
    
    # this looks like a bug, but is legit
    ax.plot(np.arange(1,thismonth.day), avgs[-2, :thismonth.day-1], zorder=2, lw=2, color='brown',
            label="%s, %.2f$^\circ$F" % (thismonth.strftime("%b %Y"), 
                                avgs[-2, thismonth.day-1]))
    ax.plot(np.arange(1,len(prevavg)+1), prevavg, lw=2, color='k', zorder=3,
            label="%s, %.2f$^\circ$F" % (oldmonth.strftime("%b %Y"),
                                prevavg[-1]))
    
    ax.set_title(("[%s] %s scenarios for %s\n"
                  +"1-%s [%s] + %s-%s [%s-%s] beats %s %s/%s (%.1f%%)") % (
                        station,
                        nt.sts[station]['name'], thismonth.strftime("%b %Y"),
                        thismonth.day, thismonth.year,
                        thismonth.day +1, days, baseyear, thismonth.year-1,
                        oldmonth.strftime("%b %Y"), beats, np.shape(data)[0]-1,
                        beats / float(np.shape(data)[0]-1) * 100.))
    ax.set_xlim(1,days)
    ax.set_ylabel("Month to Date Average Temp $^\circ$F")
    ax.set_xlabel("Day of Month")
    ax.grid(True)
    ax.legend(loc='best', fontsize=10)

    return fig
