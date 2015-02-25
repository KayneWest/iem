#!/usr/bin/env python
"""
 Plot accumulated stuff
  1. Accumulated GDD
  2. Accumulated Precip
  3. Accumulated SDD
"""
import cgi
import sys

import mx.DateTime
import matplotlib
matplotlib.use('agg')
from matplotlib import pyplot as plt
import matplotlib.dates as mdates

from pyiem.network import Table as NetworkTable
import psycopg2
COOP = psycopg2.connect(database='coop', host='iemdb', user='nobody')
ccursor = COOP.cursor()

def get_data(station, startdt, enddt):
    """ Get data for this period of choice, please """
    ccursor.execute("""
    SELECT o.day as oday, gdd50(o.high, o.low) as ogdd50, c.gdd50 as cgdd50,
    o.precip as oprecip, c.precip as cprecip, 
    sdd86(o.high, o.low) as ogdd86, c.sdd86 as csdd86
    from climate c JOIN alldata_"""+ station[:2] +""" o on
    (c.station = o.station and to_char(c.valid, 'mmdd') = o.sday)
    WHERE o.station = %s and o.day >= %s and o.day < %s and sday != '0229'
    ORDER by oday ASC
    """, (station, startdt.strftime("%Y-%m-%d"), enddt.strftime("%Y-%m-%d")))
    dates = []
    gdd50 = []
    d_gdd50 = []
    c_gdd50 = []
    gcount = 0
    gtotal = 0
    precip = []
    d_precip = []
    c_precip = []
    pcount = 0
    ptotal = 0
    sdd86 = []
    d_sdd86 = []
    c_sdd86 = []
    scount = 0
    stotal = 0
    cgtotal = 0
    cstotal = 0
    cptotal = 0
    for row in ccursor:
        dates.append( row[0] )
        gcount += (float(row[1]) - float(row[2]))
        pcount += (float(row[3]) - float(row[4]))
        scount += (float(row[5]) - float(row[6]))
        gtotal += float(row[1])
        ptotal += float(row[3])
        stotal += float(row[5])
        cgtotal += float(row[2])
        cptotal += float(row[4])
        cstotal += float(row[6])        
        gdd50.append( gtotal )
        precip.append( ptotal )
        sdd86.append( stotal )
    
        d_gdd50.append( gcount )
        d_precip.append( pcount )
        d_sdd86.append( scount )
        
        c_gdd50.append( cgtotal )
        c_sdd86.append( cstotal )
        c_precip.append( cptotal )
        
    return dates, gdd50, d_gdd50, c_gdd50, precip, d_precip, c_precip, sdd86, d_sdd86, c_sdd86

def process_cgi(form):
    """ Do the processing, please """
    startdt = mx.DateTime.DateTime(int(form.getvalue('syear', 2012)),
                                    int(form.getvalue('smonth', 5)),
                                    int(form.getvalue('sday', 1))
                                   )
    enddt = mx.DateTime.DateTime(int(form.getvalue('eyear', 2012)),
                                    int(form.getvalue('emonth', 10)),
                                    int(form.getvalue('eday', 1))
                                   )
    
    station = form.getvalue('station', 'IA0200')
    nt = NetworkTable("%sCLIMATE" % (station[:2],))
    (dates, gdd50, d_gdd50, c_gdd50, precip, d_precip, c_precip, 
     sdd86, d_sdd86, c_sdd86) = get_data(station, startdt, enddt)
    
    fig = plt.figure( figsize=(9,12) )
    ax1 = fig.add_axes([0.1, 0.7, 0.8, 0.2] )
    ax2 = fig.add_axes([0.1, 0.6, 0.8, 0.1], sharex=ax1, axisbg='#EEEEEE' )
    ax3 = fig.add_axes([0.1, 0.35, 0.8, 0.2], sharex=ax1 )
    ax4 = fig.add_axes([0.1, 0.1, 0.8, 0.2], sharex=ax1 )
    
    ax1.set_title("Accumulated GDD(base=50), Precip, & SDD(base=86)\n%s %s" % (
                                                        station, nt.sts[station]['name']),
                    fontsize=18)
    yearlabel = startdt.year
    if startdt.year != enddt.year:
        yearlabel = "%s-%s" % (startdt.year, enddt.year)
    
    ax1.plot(dates, gdd50, color='r', label='%s' % (yearlabel,), lw=2)
    ax1.plot(dates, c_gdd50, color='k', label='Climatology', lw=2)
    ax1.set_ylabel("GDD Base 50 $^{\circ}\mathrm{F}$", fontsize=16)
    
    ax1.text(0.5, 0.9, "%s - %s" % (startdt.strftime("%b %-d"), 
                                    enddt.strftime("%b %-d")), transform=ax1.transAxes,
             ha='center')
    
    ax2.plot(dates, d_gdd50, color='r', linewidth=2, linestyle='--')
    #spread = max( max(d_gdd50), abs(min(d_gdd50))) * 1.1
    #ax2.set_ylim(0-spread, spread)
    ax2.text(0.02, 0.1, " Accumulated Departure ", transform=ax2.transAxes, 
             bbox=dict(facecolor='white', ec='#EEEEEE'))
    ax2.yaxis.tick_right()
    
    ax3.plot(dates, precip, color='r', lw=2)
    ax3.plot(dates, c_precip, color='k', lw=2)
    ax3.set_ylabel("Precipitation [inch]", fontsize=16)
    
    ax4.plot(dates, sdd86, color='r', lw=2)
    ax4.plot(dates, c_sdd86, color='k', lw=2)
    ax4.set_ylabel("SDD Base 86 $^{\circ}\mathrm{F}$", fontsize=16)

    ax1.grid(True)
    ax2.grid(True)
    ax3.grid(True)
    ax4.grid(True)
    
    if (dates[-1] - dates[0]).days < 32:
        ax1.xaxis.set_major_locator(mdates.DayLocator(interval=7))
        
    else:
        ax1.xaxis.set_major_locator(mdates.MonthLocator(interval=1))

    ax1.xaxis.set_major_formatter(mdates.DateFormatter('%-d\n%b'))
    if form.getvalue('year2'):
        startdt2 = mx.DateTime.DateTime(int(form.getvalue('year2')),
                                    int(form.getvalue('smonth', 5)),
                                    int(form.getvalue('sday', 1))
                                   )
        enddt2 = mx.DateTime.DateTime(int(form.getvalue('year2')) + (enddt.year - startdt.year),
                                    int(form.getvalue('emonth', 10)),
                                    int(form.getvalue('eday', 1))
                                   )
        
        dates2, gdd50, d_gdd50, c_gdd50, precip, d_precip, c_precip, sdd86, d_sdd86, c_sdd86 = get_data(station,
                                                                startdt2, enddt2)
    
        yearlabel = startdt2.year
        if startdt2.year != enddt2.year:
            yearlabel = "%s-%s" % (startdt2.year, enddt2.year)
        sz = len(dates)
        if len(gdd50) >= sz:
            ax1.plot(dates, gdd50[:sz], label="%s" % (yearlabel,), color='b', lw=2)
            ax3.plot(dates, precip[:sz], color='b', lw=2)
            ax4.plot(dates, sdd86[:sz], color='b', lw=2)
            ax2.plot(dates, d_gdd50[:sz], color='b', linewidth=2, linestyle='--')
    
    
    if form.getvalue('year3'):
        startdt3 = mx.DateTime.DateTime(int(form.getvalue('year3')),
                                    int(form.getvalue('smonth', 5)),
                                    int(form.getvalue('sday', 1))
                                   )
        enddt3 = mx.DateTime.DateTime(int(form.getvalue('year3')) + (enddt.year - startdt.year),
                                    int(form.getvalue('emonth', 10)),
                                    int(form.getvalue('eday', 1))
                                   )
        
        dates3, gdd50, d_gdd50, c_gdd50, precip, d_precip, c_precip, sdd86, d_sdd86, c_sdd86 = get_data(station,
                                                                startdt3, enddt3)
    
        yearlabel = startdt3.year
        if startdt3.year != enddt3.year:
            yearlabel = "%s-%s" % (startdt3.year, enddt3.year)
        sz = len(dates)
        if len(gdd50) >= sz:
            ax1.plot(dates, gdd50[:sz], label="%s" % (yearlabel,), color='g', lw=2)
            ax3.plot(dates, precip[:sz], color='g', lw=2)
            ax4.plot(dates, sdd86[:sz], color='g', lw=2)
            ax2.plot(dates, d_gdd50[:sz], color='g', linewidth=2, linestyle='--')

    if form.getvalue('year4'):
        startdt4 = mx.DateTime.DateTime(int(form.getvalue('year4')),
                                    int(form.getvalue('smonth', 5)),
                                    int(form.getvalue('sday', 1))
                                   )
        enddt4 = mx.DateTime.DateTime(int(form.getvalue('year4')) + (enddt.year - startdt.year),
                                    int(form.getvalue('emonth', 10)),
                                    int(form.getvalue('eday', 1))
                                   )
        
        dates4, gdd50, d_gdd50, c_gdd50, precip, d_precip, c_precip, sdd86, d_sdd86, c_sdd86 = get_data(station,
                                                                startdt4, enddt4)
    
        yearlabel = startdt4.year
        if startdt4.year != enddt4.year:
            yearlabel = "%s-%s" % (startdt4.year, enddt4.year)
        sz = len(dates)
        if len(gdd50) >= sz:
            ax1.plot(dates, gdd50[:sz], label="%s" % (yearlabel,), color='tan', lw=2)
            ax3.plot(dates, precip[:sz], color='tan', lw=2)
            ax4.plot(dates, sdd86[:sz], color='tan', lw=2)
            ax2.plot(dates, d_gdd50[:sz], color='tan', linewidth=2, linestyle='--')
    
    ax1.legend(loc=2, prop={'size': 14})

    for label in ax1.get_xticklabels():
        label.set_visible(False)


    fmt = form.getvalue('format', 'png')
    if fmt in ['eps',]:
        print "Content-Type: application/postscript\n"
    elif fmt in ['png',]:
        print "Content-Type: image/png\n"
    fig.savefig( sys.stdout, format=fmt )

if __name__ == '__main__':
    # Go Main Go
    form = cgi.FieldStorage()
    if form.has_key("station"):
        process_cgi(form)