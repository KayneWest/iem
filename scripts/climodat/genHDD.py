# GDD module
# Daryl Herzmann 4 Mar 2004

_REPORTID = "18"

def chdd(high, low):
  hdd = 0
  try:
    a = float(int(high) + int(low)) / 2.00
  except:
    return 0
  if (a < 65):
    hdd = (65.0 - a)

  return hdd  

def go(mydb, rs, stationID, updateAll=False):
    import mx.DateTime, constants
    if updateAll:
        s = constants.startts(stationID)
    else:
        s = constants._ENDTS - mx.DateTime.RelativeDateTime(years=1)
    e = constants._ENDTS
    interval = mx.DateTime.RelativeDateTime(months=+1)

    now = s
    db = {}
    while (now < e):
        db[now] = 0
        now += interval

    for i in range(len(rs)):
        ts = mx.DateTime.strptime( rs[i]["day"] , "%Y-%m-%d")
        if ts < s:
            continue
        mo = ts + mx.DateTime.RelativeDateTime(day=1)
        db[mo] += chdd(rs[i]["high"], rs[i]["low"])

    for mo in db.keys():
        mydb.query("""UPDATE r_monthly SET hdd = %s WHERE 
          station = '%s' and monthdate = '%s' """ % (
                                db[mo], stationID, mo.strftime("%Y-%m-%d") ) )

def write(mydb, stationID):
  import mx.DateTime, constants
  YRCNT = constants.yrcnt(stationID)
  out = open("reports/%s_%s.txt" % (stationID, _REPORTID), 'w')
  constants.writeheader(out, stationID)
  out.write("""# THESE ARE THE MONTHLY HEATING DEGREE DAYS %s-%s FOR STATION # %s
YEAR    JAN    FEB    MAR    APR    MAY    JUN    JUL    AUG    SEP    OCT    NOV    DEC\n""" \
   % (constants.startyear(stationID), constants._ENDYEAR, stationID,) )

  rs = mydb.query("SELECT * from r_monthly WHERE station = '%s'" \
   % (stationID,) ).dictresult()
  db = {}
  for i in range(len(rs)):
    mo = mx.DateTime.strptime( rs[i]["monthdate"], "%Y-%m-%d")
    db[mo] = rs[i]["hdd"]

  moTot = {}
  for mo in range(1,13):
   moTot[mo] = 0

  yrCnt = 0
  for yr in range(constants.startyear(stationID), constants._ENDYEAR):
    yrCnt += 1
    out.write("%4i" % (yr,) )
    for mo in range(1, 13):
      ts = mx.DateTime.DateTime(yr, mo, 1)
      if (ts >= constants._ARCHIVEENDTS):
        out.write("%7s" % ("M",) )
        continue
      if (ts < constants._ARCHIVEENDTS):
        moTot[mo] += db[ts]
      out.write("%7.0f" % (db[ts],) )
    out.write("\n")

  out.write("MEAN")
  for mo in range(1, 13):
    out.write("%7.0f" % ( float(moTot[mo]) / float( YRCNT[mo] ) ) )
  out.write("\n")

  out.close()

