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
    d['description'] = """."""
    d['arguments'] = [
        dict(type='station', name='station', default='IA2203',
             label='Select Station:'),
        dict(type='month', name='month', default='3',
             label='Select Month:')
    ]
    return d


def plotter(fdict):
    """ Go """
    pgconn = psycopg2.connect(database='coop', host='iemdb', user='nobody')
    cursor = pgconn.cursor(cursor_factory=psycopg2.extras.DictCursor)

    station = fdict.get('station', 'IA2203')
    month = int(fdict.get('month', 3))
    ts = datetime.datetime(2000, month, 1)
    ets = ts + datetime.timedelta(days=35)
    ets = ets.replace(day=1)
    days = int((ets - ts).days)

    table = "alldata_%s" % (station[:2],)
    nt = NetworkTable("%sCLIMATE" % (station[:2],))

    cursor.execute("""
        with ranks as (
            select day, high, low,
            rank() OVER (PARTITION by year ORDER by high ASC) as high_rank,
            rank() OVER (PARTITION by year ORDER by low ASC) as low_rank
            from """ + table + """ where station = %s and month = %s),
        highs as (
            SELECT extract(day from day) as dom, count(*) from ranks
            where high_rank = 1 GROUP by dom),
        lows as (
            SELECT extract(day from day) as dom, count(*) from ranks
            where low_rank = 1 GROUP by dom)

        select coalesce(h.dom, l.dom), h.count, l.count from
        highs h FULL OUTER JOIN lows l on (h.dom = l.dom) ORDER by h.dom
    """, (station, month))
    high_hits = np.zeros((31,), 'f')
    low_hits = np.zeros((31,), 'f')
    for row in cursor:
        high_hits[int(row[0])-1] = row[1]
        low_hits[int(row[0])-1] = row[2]

    fig, ax = plt.subplots(2, 1, sharex=True)

    ax[0].set_title(("[%s] %s\nFrequency of Day in %s\n"
                     "Having Coldest High Temperature of March"
                     ) % (station, nt.sts[station]['name'],
                          calendar.month_name[month]), fontsize=10)
    ax[0].set_ylabel("Years (ties split)")

    ax[0].grid(True)
    ax[0].bar(np.arange(1, 32) - 0.4, high_hits)

    ax[1].set_title(("Having Coldest Low Temperature of %s"
                     ) % (calendar.month_name[month], ), fontsize=10)
    ax[1].set_ylabel("Years (ties split)")
    ax[1].grid(True)
    ax[1].set_xlabel("Day of %s" % (calendar.month_name[month], ))
    ax[1].bar(np.arange(1, 32) - 0.4, low_hits)
    ax[1].set_xlim(0.5, days + 0.5)

    return fig
