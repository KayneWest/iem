"""
 Assign a climate site to each site in the mesosite database, within reason
"""

import iemdb
MESOSITE = iemdb.connect('mesosite')
mcursor = MESOSITE.cursor()
mcursor2 = MESOSITE.cursor()

# Query out all sites with a null climate_site
mcursor.execute("""
	SELECT id, geom, state from stations 
	WHERE climate_site IS NULL and country = 'US' and 
	state not in ('PR','DC','GU','PU','P3', 'P4')
""")

for row in mcursor:
	thisID = row[0]
	thisGEOM = row[1]
	st = row[2]
	# Find the closest site
	sql = """select id from stations WHERE network = '%sCLIMATE'
                 and substr(id,3,4) != '0000' and substr(id,3,1) != 'C'
		 ORDER by ST_distance(geom, '%s') ASC LIMIT 1""" % (
                   st, thisGEOM)
	mcursor2.execute(sql)
	row2 = mcursor2.fetchone()
	if row2 is None:
		print 'Could not find Climate Site for: %s' % (thisID,)
	else:
		sql = """UPDATE stations SET climate_site = '%s' WHERE
             id = '%s'""" % (row2[0], thisID)
		mcursor2.execute(sql)
		print 'Set Climate: %s for ID: %s' % (row2[0], thisID)

mcursor2.close()
MESOSITE.commit()
