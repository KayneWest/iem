#!/usr/bin/env python
"""
 Geocoder used by:
  - IEM Rainfall App
"""
import cgi
import urllib
import urllib2
import sys
import json


def main():
    ''' Go main go '''
    form = cgi.FieldStorage()
    if 'address' in form:
        address = form["address"].value
    elif 'street' in form and 'city' in form:
        address = "%s, %s" % (form["street"].value, form["city"].value)
    else:
        sys.stdout.write("APIFAIL")

    address = urllib.urlencode({'address': address})
    uri = ('http://maps.googleapis.com/maps/api/geocode/json'
           '?'+address+'&sensor=true')
    data = json.loads(urllib2.urlopen(uri).read())
    if len(data['results']) > 0:
        print "%s,%s" % (data['results'][0]['geometry']['location']['lat'],
                         data['results'][0]['geometry']['location']['lng'])
    else:
        sys.stderr.write(str(data))
        print "ERROR"

sys.stdout.write('Content-type: text/plain \n\n')
try:
    main()
except Exception, exp:
    sys.stderr.write(str(exp))
    print "ERROR"
