"""
My purpose in life is to update the hourly_YYYY with observed or
computed hourly precipitation
"""
import mx.DateTime
import psycopg2
IEM = psycopg2.connect(database='iem', host='iemdb')
icursor = IEM.cursor()
icursor.execute("SET TIME ZONE 'UTC'")

# Our period
t0 = mx.DateTime.utc() + mx.DateTime.RelativeDateTime(hours=-1,minute=0)
t1 = mx.DateTime.utc() + mx.DateTime.RelativeDateTime(hours=-0,minute=0)

sql = """INSERT into hourly_%s 
       (SELECT t.id, t.network, '%s+00'::timestamp as v, max(phour) as p, 
       t.iemid 
        from current_log c, stations t WHERE 
        (valid - '1 minute'::interval) >= '%s' 
        and (valid - '1 minute'::interval) < '%s' 
        and phour >= 0 and 
        t.network NOT IN ('KCCI','KELO','KIMT') 
        and c.iemid = t.iemid
        and t.network !~* 'DCP' 
        GROUP by t,id, t.network, v, t.iemid)""" % (
        t0.year, t0.strftime("%Y-%m-%d %H:%M"), 
        t0.strftime("%Y-%m-%d %H:%M"), t1.strftime("%Y-%m-%d %H:%M"))
icursor.execute( sql )
IEM.commit()
IEM.close()
