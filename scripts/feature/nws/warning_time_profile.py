import psycopg2
import numpy as np

POSTGIS = psycopg2.connect(database='postgis', host='iemdb', user='nobody')
cursor = POSTGIS.cursor()

cursor.execute("""
 SELECT issue, expire, init_expire from warnings where phenomena = 'WS'
 and significance = 'W' and issue > '2005-10-01' and init_expire is not null
""")

total = cursor.rowcount

counts = np.zeros(300)

for row in cursor:
    origdur = (row[2] - row[0]).days * 86400. + (row[2] - row[0]).seconds

    finaldur = (row[1] - row[0]).days * 86400. + (row[1] - row[0]).seconds
    if origdur > 0:
        ratio = int((finaldur / origdur) * 100.0)
        counts[:ratio] += 1
    
import matplotlib.pyplot as plt

(fig, ax) = plt.subplots(1,1)

ax.bar(np.linspace(0,3,300), counts / counts[0] * 100.0, fc='b', ec='b', width=0.01)
ax.grid(True)
ax.set_yticks([0,5,10,25,40,50,60,75,90,95,100])
ax.set_xlim(0,3.1)
ax.set_title("Duration of Winter Storm Warnings Relative to Issuance\n1 Oct 2005 - 28 Feb 2014")
ax.set_xlabel("Relative to Issuance Duration")
ax.set_ylabel("Percent by Forecast Zone Still Valid [%]")

fig.savefig('test.png')