"""
Ingest files provided by NLAE containing flux information
"""
import pg
import mx.DateTime
import traceback
other = pg.DB('other', 'iemdb')

# Figure out max valid times
maxts = {}
rs = other.query("""SELECT station, max(valid) from flux%s 
    GROUP by station""" % (mx.DateTime.now().year - 1, ) ).dictresult()
for i in range(len(rs)):
    maxts[ rs[i]['station'] ] = mx.DateTime.strptime(rs[i]['max'][:16], 
                                                     '%Y-%m-%d %H:%M')
rs = other.query("""SELECT station, max(valid) from flux%s 
    GROUP by station""" % (mx.DateTime.now().year, ) ).dictresult()
for i in range(len(rs)):
    maxts[ rs[i]['station'] ] = mx.DateTime.strptime(rs[i]['max'][:16], 
                                                     '%Y-%m-%d %H:%M')

DIR = "/mnt/home/mesonet/ot/ot0005/incoming/Fluxdata/"
fp = {'Flux10_AF.dat': 'nstl10', 
       'Anc10_AF.dat': 'nstl10',
       'Flux11_AF.dat': 'nstl11', 
       'Anc11_AF.dat': 'nstl11',
       '30ft.dat': 'nstl30ft', 
       'NSP_Flux.dat': 'nstlnsp'}

dbcols = [ 
 'fc_wpl',
 'le_wpl',
 'hs',
 'tau',
 'u_star',
 'cov_uz_uz',
 'cov_uz_ux',
 'cov_uz_uy',
 'cov_uz_co2',
 'cov_uz_h2o',
 'cov_uz_ts',
 'cov_ux_ux',
 'cov_ux_uy',
 'cov_ux_co2',
 'cov_ux_h2o',
 'cov_ux_ts',
 'cov_uy_uy',
 'cov_uy_co2',
 'cov_uy_h2o',
 'cov_uy_ts',
 'cov_co2_co2',
 'cov_h2o_h2o',
 'cov_ts_ts',
 'ux_avg',
 'uy_avg',
 'uz_avg',
 'co2_avg',
 'h2o_avg',
 'ts_avg',
 'rho_a_avg',
 'press_avg',
 'panel_temp_avg',
 'wnd_dir_compass',
 'wnd_dir_csat3',
 'wnd_spd',
 'rslt_wnd_spd',
 'batt_volt_avg',
 'std_wnd_dir',
 'fc_irga',
 'le_irga',
 'co2_wpl_le',
 'co2_wpl_h',
 'h2o_wpl_le',
 'h2o_wpl_h',
 'h2o_hmp_avg',
 't_hmp_avg',
 'par_avg',
 'solrad_avg',
 'rain_tot',
 'shf1_avg',
 'shf2_avg',
 'soiltc1_avg',
 'soiltc2_avg',
 'soiltc3_avg',
 'soiltc4_avg',
 'irt_can_avg',
 'irt_cb_avg',
 'incoming_sw',
 'outgoing_sw',
 'incoming_lw_tcor',
 'terrest_lw_tcor',
 'rn_short_avg',
 'rn_long_avg',
 'rn_total_avg',
 'rh_hmp_avg',
 'temps_c1_avg',
 'corrtemp_avg',
 'rn_total_tcor_avg',
 'incoming_lw_avg',
 'terrestrial_lw_avg',
 'wfv1_avg']

convert = {
'Incoming_SW_Avg': 'incoming_sw',
'Outgoing_SW_Avg': 'outgoing_sw',
'Incoming_LW_TCor_Avg': 'incoming_lw_tcor',
'Terrest_LW_TCor_Avg': 'terrest_lw_tcor',
           }

def c(v):
    if (v == "NAN" or v == "-INF" or v == "INF"):
        return None
    return v

data = {'nstl10': {},
        'nstl11': {},
        'nstl30ft': {},
        'nstlnsp': {},
        }

for fn in fp.keys():
    station = fp[fn]
    lines = open("%s%s" % (DIR, fn), 'r').readlines()
    if len(lines) < 2:
        print 'flux_ingest.py file: %s has %s lines?' % (fn, len(lines))
        continue
    keys = lines[1].replace('"','').replace("\r\n", '').split(",")
    for linenum, obline in enumerate(lines[3:]):
        tokens = obline.replace('"', '').split(",")
        if len(tokens) != len(keys):
            print '%s line: %s has %s tokens, header has %s' % (fn, linenum,
                                        len(tokens), len(keys))
            continue
        if tokens[0] == '':
            continue
        try:
            ts = mx.DateTime.strptime(tokens[0][:16], '%Y-%m-%d %H:%M')
        except:
            print '%s line: %s has invalid time %s' %(fn, linenum, tokens[0])
            continue
        if ts < maxts.get(station, mx.DateTime.DateTime(2011,1,1)):
            continue
        if not data[station].has_key(ts):
            data[station][ts] = {'valid': tokens[0][:16], 'station': station }
        for i in range(len(tokens)):
            key = convert.get(keys[i], keys[i]).lower()
            if key in ['record','timestamp']:
                continue
            if key not in dbcols:
                #print 'Missing', key
                continue
            data[station][ts][key] = c( tokens[i] )

cnt = 0
for station in data.keys():
    for ts in data[station].keys():
        gts = ts.gmtime()
        try:
            other.insert("flux%s" % (gts.year,) , data[station][ts])
            cnt += 1
        except:
            print station, ts, data[station][ts].keys()
            print traceback.print_exc()

if cnt == 0:
    print "NLAE flux inget found no records"
