"""
  Fall Minimum by Date
"""
import psycopg2.extras
import matplotlib
matplotlib.use('agg')
import matplotlib.pyplot as plt
import numpy as np
import datetime
import calendar
from pyiem.network import Table as NetworkTable

def get_description():
    """ Return a dict describing how to call this plotter """
    d = dict()
    d['arguments'] = [
        dict(type='station', name='station', default='IA0200', 
             label='Select Station:'),
        dict(type='text', name='days', default='4', 
             label='Number of Trailing Days:'),
    ]
    return d


def plotter( fdict ):
    """ Go """
    pgconn = psycopg2.connect(database='coop', host='iemdb', user='nobody')
    cursor = pgconn.cursor(cursor_factory=psycopg2.extras.DictCursor)

    station = fdict.get('station', 'IA0200')
    days = int(fdict.get('days', 4))

    table = "alldata_%s" % (station[:2],)
    nt = NetworkTable("%sCLIMATE" % (station[:2],))
    
    cursor.execute("""
    WITH data as (
     select day, high, 
     max(high) OVER (ORDER by day ASC rows between %s PRECEDING and 1 PRECEDING) as up,
     min(high) OVER (ORDER by day ASC rows between %s PRECEDING and 1 PRECEDING) as down
     from """+table+""" where station = %s
    )
    SELECT extract(week from day) as wk, max(high - up), min(high - down) from data
    GROUP by wk ORDER by wk ASC
    """, (days, days, station))

    weeks = []
    jump_up = []
    jump_down = []    
    for row in cursor:
        weeks.append(row[0]-1)
        jump_up.append(row[1])
        jump_down.append(row[2])
    weeks = np.array(weeks)
    
    extreme = max([max(jump_up),0-min(jump_down)]) + 10

    sts = datetime.datetime(2012,1,1)
    xticks = []
    for i in range(1,13):
        ts = sts.replace(month=i)
        xticks.append(int(ts.strftime("%j")))
    
    (fig, ax) = plt.subplots(1,1, sharex=True)
    ax.bar(weeks*7, jump_up, width=7, fc='r', ec='r')
    ax.bar(weeks*7, jump_down, width=7, fc='b', ec='b')
    ax.grid(True)
    ax.set_ylabel("Temperature Change $^\circ$F")
    ax.set_title("%s %s\nMax Change in High Temp by Week of Year" % (station, 
                                        nt.sts[station]['name']))
    ax.set_xticks(xticks)
    ax.set_xticklabels(calendar.month_abbr[1:])
    ax.set_xlim(0,366)
    ax.set_ylim(0-extreme, extreme)
    ax.text(183, extreme-5, "Maximum Jump in High Temp vs Max High over past %s days"% (days,),
            color='red', va='center', ha='center',bbox=dict(color='white'))
    ax.text(183, 0-extreme+5, "Maximum Dip in High Temp vs Min High over past %s days"% (days,),
            color='blue', va='center', ha='center',bbox=dict(color='white'))

    return fig
