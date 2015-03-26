#!/usr/bin/env python
""" Soil Moisture Timeseries """
import matplotlib
matplotlib.use('agg')
import datetime
import numpy as np
import pytz
import sys
from pyiem.datatypes import temperature
from pyiem.network import Table as NetworkTable
nt = NetworkTable("ISUSM")

# Query out the CGI variables
import cgi
form = cgi.FieldStorage()
if ("year1" in form and "year2" in form and
        "month1" in form and "month2" in form and
        "day1" in form and "day2" in form and
        "hour1" in form and "hour2" in form):
    sts = datetime.datetime(int(form["year1"].value),
                            int(form["month1"].value), int(form["day1"].value),
                            int(form["hour1"].value), 0)
    ets = datetime.datetime(int(form["year2"].value),
                            int(form["month2"].value), int(form["day2"].value),
                            int(form["hour2"].value), 0)

station = form.getvalue('station', 'CAMI4')
opt = form.getvalue('opt', '1')
if station not in nt.sts:
    print 'Content-type: text/plain\n'
    print 'ERROR'
    sys.exit(0)

import psycopg2.extras
ISUAG = psycopg2.connect(database='isuag', host='iemdb', user='nobody')
icursor = ISUAG.cursor(cursor_factory=psycopg2.extras.DictCursor)

sql = """SELECT * from sm_hourly WHERE
    station = '%s' and valid BETWEEN '%s' and '%s' ORDER by valid ASC
    """ % (station, sts.strftime("%Y-%m-%d %H:%M"),
           ets.strftime("%Y-%m-%d %H:%M"))
icursor.execute(sql)
d12sm = []
d24sm = []
d50sm = []
d12t = []
d24t = []
d50t = []
tair = []
tsoil = []
valid = []
slrkw = []
rain = []
for row in icursor:
    slrkw.append(row['slrkw_avg'] or np.nan)
    d12sm.append(row['vwc_12_avg'] or np.nan)
    d12t.append(row['t12_c_avg'] or np.nan)
    d24t.append(row['t24_c_avg'] or np.nan)
    d50t.append(row['t50_c_avg'] or np.nan)
    d24sm.append(row['vwc_24_avg'] or np.nan)
    d50sm.append(row['vwc_50_avg'] or np.nan)
    valid.append(row['valid'])
    rain.append(row['rain_mm_tot'] or np.nan)
    tair.append(row['tair_c_avg'] or np.nan)
    tsoil.append(row['tsoil_c_avg'] or np.nan)

slrkw = np.array(slrkw)
rain = np.array(rain)
d12sm = np.array(d12sm)
d24sm = np.array(d24sm)
d50sm = np.array(d50sm)
d12t = np.array(d12t)
d24t = np.array(d24t)
d50t = np.array(d50t)
tair = np.array(tair)
tsoil = np.array(tsoil)

import matplotlib.pyplot as plt
import matplotlib.dates as mdates

maxy = max([np.max(d12sm), np.max(d24sm), np.max(d50sm)])
miny = min([np.min(d12sm), np.min(d24sm), np.min(d50sm)])

if opt == '2':
    (fig, ax) = plt.subplots(1, 1)
    ax.grid(True)
    ax.set_title(("ISUSM Station: %s Timeseries\nSoil Temperature at Depth\n "
                  ) % (nt.sts[station]['name'], ))
    ax.plot(valid, temperature(tsoil, 'C').value('F'), linewidth=2,
            color='brown', label='4 inch')
    ax.plot(valid, temperature(d12t, 'C').value('F'), linewidth=2, color='r',
            label='12 inch')
    ax.plot(valid, temperature(d24t, 'C').value('F'), linewidth=2,
            color='purple', label='24 inch')
    ax.plot(valid, temperature(d50t, 'C').value('F'), linewidth=2,
            color='black', label='50 inch')
    box = ax.get_position()
    ax.set_position([box.x0, box.y0, box.width,
                    box.height * 0.9])
    ax.legend(bbox_to_anchor=(0.5, 1.02), ncol=4, loc='center',
              fontsize=12)
    days = (ets - sts).days
    if days >= 3:
        interval = max(int(days/7), 1)
        ax.xaxis.set_major_locator(
            mdates.DayLocator(interval=interval,
                              tz=pytz.timezone("America/Chicago")))
        ax.xaxis.set_major_formatter(
            mdates.DateFormatter('%-d %b\n%Y',
                                 tz=pytz.timezone("America/Chicago")))
    else:
        ax.xaxis.set_major_locator(
            mdates.AutoDateLocator(maxticks=10,
                                   tz=pytz.timezone("America/Chicago")))
        ax.xaxis.set_major_formatter(
            mdates.DateFormatter('%-I %p\n%d %b',
                                 tz=pytz.timezone("America/Chicago")))
    ax.axhline(32, linestyle='--', lw=2, color='tan')
    ax.set_ylabel("Temperature $^\circ$F")
    sys.stdout.write("Content-Type: image/png\n\n")
    plt.savefig(sys.stdout, format='png')
    sys.exit()

(fig, ax) = plt.subplots(3, 1, sharex=True, figsize=(8, 8))
ax[0].grid(True)
ax2 = ax[0].twinx()
ax2.set_yticks(np.arange(-0.6, 0., 0.1))
ax2.set_yticklabels(0 - np.arange(-0.6, 0.01, 0.1))
ax2.set_ylim(-0.6, 0)
ax2.set_ylabel("Hourly Precipitation [inch]")
b1 = ax2.bar(valid, 0 - rain / 25.4, width=0.04, fc='b', ec='b', zorder=1)

l1, = ax[0].plot(valid, d12sm * 100.0, linewidth=2, color='r', zorder=2)
l2, = ax[0].plot(valid, d24sm * 100.0, linewidth=2, color='purple', zorder=2)
l3, = ax[0].plot(valid, d50sm * 100.0, linewidth=2, color='black', zorder=2)
ax[0].set_ylabel("Volumetric Soil Water Content [%]", fontsize=10)

days = (ets - sts).days
if days >= 3:
    interval = max(int(days/7), 1)
    ax[0].xaxis.set_major_locator(
        mdates.DayLocator(interval=interval,
                          tz=pytz.timezone("America/Chicago")))
    ax[0].xaxis.set_major_formatter(
        mdates.DateFormatter('%-d %b\n%Y',
                             tz=pytz.timezone("America/Chicago")))
else:
    ax[0].xaxis.set_major_locator(
        mdates.AutoDateLocator(maxticks=10,
                               tz=pytz.timezone("America/Chicago")))
    ax[0].xaxis.set_major_formatter(
        mdates.DateFormatter('%-I %p\n%d %b',
                             tz=pytz.timezone("America/Chicago")))

ax[0].set_title("ISUSM Station: %s Timeseries" % (nt.sts[station]['name'], ))
box = ax[0].get_position()
ax[0].set_position([box.x0, box.y0 + box.height * 0.05, box.width,
                    box.height * 0.95])
box = ax2.get_position()
ax2.set_position([box.x0, box.y0 + box.height * 0.05, box.width,
                  box.height * 0.95])
ax[0].legend([l1, l2, l3, b1],
             ['12 inch', '24 inch', '50 inch', 'Hourly Precip'],
             bbox_to_anchor=(0.5, -0.15), ncol=4, loc='center',
             fontsize=12)

# ----------------------------------------
ax[1].plot(valid, temperature(d12t, 'C').value('F'), linewidth=2, color='r',
           label='12in')
ax[1].plot(valid, temperature(d24t, 'C').value('F'), linewidth=2,
           color='purple', label='24in')
ax[1].plot(valid, temperature(d50t, 'C').value('F'), linewidth=2,
           color='black', label='50in')
ax[1].grid(True)
ax[1].set_ylabel(r"Temperature $^\circ$F")
box = ax[1].get_position()
ax[1].set_position([box.x0, box.y0 + box.height * 0.05, box.width,
                    box.height * 0.95])

# ------------------------------------------------------

ax2 = ax[2].twinx()
l3, = ax2.plot(valid, slrkw, color='g', zorder=1, lw=2)
ax2.set_ylabel("Solar Radiation [W/m^2]", color='g')


l1, = ax[2].plot(valid, temperature(tair, 'C').value('F'), linewidth=2,
                 color='blue', zorder=2)
l2, = ax[2].plot(valid, temperature(tsoil, 'C').value('F'), linewidth=2,
                 color='brown', zorder=2)
ax[2].grid(True)
ax[2].legend([l1, l2, l3],
             ['Air', '4" Soil', 'Solar Radiation'],
             bbox_to_anchor=(0.5, 1.1), loc='center', ncol=3)
ax[2].set_ylabel(r"Temperature $^\circ$F")

ax[2].set_zorder(ax2.get_zorder()+1)
ax[2].patch.set_visible(False)
# Wow, strange bugs if I did not put this last
ax[0].set_xlim(min(valid), max(valid))

sys.stdout.write("Content-Type: image/png\n\n")
plt.savefig(sys.stdout, format='png')
