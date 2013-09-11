# Script that processes the raw RWIS data into something usable
#  3 Dec 2002:	Use IEM python2.2
# 19 Nov 2003	Put this into a Main()

import rnetwork
import sys
import os
import subprocess

def Main():
    rn = rnetwork.rnetwork("/mesonet/data/incoming/rwis/rwis.txt", 
      "/mesonet/data/incoming/rwis/rwis_sf.txt")
    
    rn.iemtracker()
    
    f = open("/tmp/rwis.sao",'w')
    rn.genMETAR(f)
    f.close()
    
    f = open("/tmp/rwis2.sao",'w')
    rn.genMETAR2(f)
    f.close()
    
    #rn.currentWriteCDF()
    
    g = open("/tmp/rwis.csv",'w')
    rn.currentWriteCDFNWS(g)
    g.close()
    p = subprocess.Popen("/home/ldm/bin/pqinsert -p 'plot c 000000000000 csv/rwis.csv bogus csv' /tmp/rwis.csv",
                    shell=True,stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    stdout, stderr = p.communicate()
    #  rn.writeNWS()
    
    rn.updateIEMAccess()

Main()
