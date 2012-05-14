
import pg, mx.DateTime

_THISYEAR = 2013
_ENDYEAR = 2013
#_ENDYEAR = 1951

_ARCHIVEENDTS = mx.DateTime.now() - mx.DateTime.RelativeDateTime(days=1)
_ENDTS = mx.DateTime.DateTime(2013,1,1)
#_YEARS = 58
#_YRCNT = [0,58,58,58,57,57,57,57,57,57,57,57,57]
mydb = pg.connect('coop', 'iemdb',user='nobody')
import iemdb
mesosite = iemdb.connect('mesosite', bypass=True)
mcursor = mesosite.cursor()
mcursor.execute("""SELECT propvalue from properties 
    where propname = 'iaclimate.end'""")
row = mcursor.fetchone()
_QCENDTS = mx.DateTime.strptime(row[0], '%Y-%m-%d')
mcursor.close()

longterm = ['ia1635','ia4063','ia3509','ia3473','ia4389','ia5769','ia1319',
'ia7147','ia2171','ia1533','ia5952','ia2110','ia6243','ia5131','ia3290',
'ia8266','ia7979','ia0133','ia1833','ia7161','ia8806',
'ia4735','ia6327','ia8706','ia4894','ia0200','ia2864','ia0364',
'ia7842','ia2789','ia8688','ia5198','ia2203','ia2364',
'ia0000']

def get_table(sid):
    """
    Return the table which has the data for this siteID
    """
    return "alldata_%s" % (sid.lower()[:2],)

def yrcnt(sid):
  """ Compute the number of years each month will have in the records """
  sts = startts(sid)
  r = [0]*13
  for m in range(1,13):
    ts = mx.DateTime.now() + mx.DateTime.RelativeDateTime(month=m,day=1)
    if (ts > _ARCHIVEENDTS):
      r[m] = _ARCHIVEENDTS.year - sts.year
    else:
      r[m] = _ARCHIVEENDTS.year - sts.year + 1

  return r



def climatetable(sid):
  if (startyear(sid) == 1951):
    return "climate51"
  return "climate"

def startts(sid):
  return mx.DateTime.DateTime(startyear(sid),1,1)

def startyear(sid):
  if (longterm.__contains__(sid.lower())):
    return 1893
  return 1951

def writeheader(out, stationID):
  import network
  import string, mx.DateTime
  now = mx.DateTime.now()
  stationID = string.upper(stationID)
  nt = network.Table("%sCLIMATE" % (stationID[:2].upper(),))
  out.write("""# IEM Climodat http://mesonet.agron.iastate.edu/climodat/
# Report Generated: %s 
# Climate Record: %s -> %s (data after %s is preliminary)
# Site Information: [%s] %s
# Contact Information: Daryl Herzmann akrherz@iastate.edu 515.294.5978\n""" % \
  (now.strftime("%d %b %Y"), startts(stationID).strftime("%d %b %Y"), \
   _ARCHIVEENDTS.strftime("%d %b %Y"), \
   _QCENDTS.strftime("%d %b %Y"), stationID, nt.sts[stationID]["name"]) )
