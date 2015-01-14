"""
Examples of widget types

dict(type='date', name='date2', default='2012/03/15', label='Bogus2:',
     min="1893/01/01"), # Comes back to python as yyyy-mm-dd

"""
# Association of plots
data = {'plots': [
    {'label': 'Daily', 'options': [
        {'id': "11", 'label': "ASOS/AWOS Daily Maximum Dew Point for a Year"},
        {'id': "32", 'label': "Daily Temperature Departures for One Year"},
        {'id': "21", 'label': "Change in NCDC 81 Daily Climatology over X Days"},
        {'id': "31", 'label': "Extreme Jumps or Dips in High Temperature over X days"},
        {'id': "7", 'label': "Growing Degree Day Periods for One Year by Planting Date"},
        {'id': "19", 'label': "Histogram of Daily High/Low Temperatures"},
        {'id': "35", 'label': "Histogram of X Hour Temperature Changes"},
        {'id': "34", 'label': "Maximum Stretch of Days with High Below Threshold"},
        {'id': "26", 'label': "Minimum Daily Low Temperature after 1 July"},
        {'id': "5", 'label': "Minimum Daily Temperature Range"},
        {'id': "22", 'label': "Percentage of Years within Temperature Range from Averages"},
        {'id': "38", 'label': "Solar Radiation Estimates from NARR"},
        {'id': "25", 'label': "Spread of Daily High and Low Temperatures"},
        {'id': "4", 'label': "State Areal Coverage of Precip Intensity over X Days"},
        {'id': "28", 'label': "Trailing Number of Days Precipitation Total Rank"},
    ]},
    {'label': 'Monthly', 'options': [
        {'id': "15", 'label': "Daily Temperature Change Frequencies by Month"},
        {'id': "29", 'label': "Frequency of Hourly Temperature within Range by Month"},
        {'id': "1", 'label': "July-August Days Above Temp v. May-June Precip"},
        {'id': "9", 'label': "Growing Degree Day Climatology and Daily Values for one Year"},
        {'id': "2", 'label': "Month Precipitation v Month Growing Degree Day Departures"},
        {'id': "3", 'label': "Monthly Temperature / Precipitation Statistics by Year"},
        {'id': "6", 'label': "Monthly Temperature/Precipitation Distributions"},
        {'id': "42", 'label': "Hourly Temperature Streaks Above/Below Threshold"},
        {'id': "24", 'label': "Monthly Precipitation Climate District Ranks"},
        {'id': "8", 'label': "Monthly Precipitation Reliability"},
        {'id': "23", 'label': "Monthly Station Departures + El Nino 3.4 Index"},
        {'id': "36", 'label': "Month warmer than other Month for Year"},
        {'id': "41", 'label': "Quantile / Quantile Plot of Daily Temperatures for Two Months"},
        {'id': "20", 'label': "Hours of Precipitation by Month"},
        {'id': "47", 'label': "Snowfall vs Precipitation Total for a Month"},
        {'id': "39", 'label': "Scenarios for this month besting some previous month"},
        #{'id': "17", 'label': "Daily Temperatures + Climatology for Year + Month"},
    ]},
    {'label': 'Yearly', 'options': [
        {'id': "12", 'label': "Days per year and latest date above given threshold"},
        {'id': "13", 'label': "End Date of Summer (warmest 91 day period) per Year"},
        {'id': "27", 'label': "First Fall Freeze then Killing Frost"},
        {'id': "53", 'label': "Hourly Frequency of Temperature within Certain Ranges"},
        {'id': "33", 'label': "Maximum Low Temperature Drop"},
        {'id': "46", 'label': "Minimum Wind Chill Temperature"},
        {'id': "30", 'label': "Monthly Temperature Range"},
        {'id': "44", 'label': "NWS Office Accumulated SVR+TOR Warnings"},
        {'id': "10", 'label': "Last Spring and First Fall Date above/below given threshold"},
        {'id': "14", 'label': "Yearly Precipitation Contributions by Daily Totals"},
    ]},
    {'label': 'METAR ASOS Special Plots', 'options': [
        {'id': "40", 'label': "Cloud Amount and Level Timeseries for One Month"},
        {'id': "54", 'label': "Difference between morning low temperatures between two sites"},
        {'id': "18", 'label': "Long term temperature time series"},
        {'id': "45", 'label': "Monthly Frequency of Overcast Conditions"},
        {'id': "37", 'label': "MOS Forecasted Temperature Ranges + Observations"},
        {'id': "16", 'label': "Wind Rose when specified criterion is meet"},
    ]},
    {'label': 'NWS Warning Plots', 'options': [
        {'id': "52", 'label': "Gaant Chart of WFO Issued Watch/Warning/Advisories"},
        {'id': "44", 'label': "NWS Office Accumulated SVR+TOR Warnings"},
        {'id': "48", 'label': "Time of Day Frequency for Given Warning / UGC"},
    ]},
    {'label': 'Sustainable Corn Project Plots', 'options': [
        {'id': "49", 'label': "Two Day Precipitation Total Frequencies"},
        {'id': "50", 'label': "Frequency of Measurable Daily Precipitation"},
        {'id': "51", 'label': "Frequency of No Daily Precipitation over 7 Days"},
    ]},
]}
