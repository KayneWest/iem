/*
 * Static Javascript stuff to support the Time Machine :) 
 * daryl herzmann akrherz@iastate.edu
 */
Ext.BLANK_IMAGE_URL = '/ext/resources/images/default/s.gif';

Ext.onReady( function(){

var currentURI = "";
var appTime = new Date();
var pageLoadTime = new Date();
var appDT   = 60;

/*
 * Need a way to prevent missing images from messing up the page!
 */
Ext.get("imagedisplay").dom.onerror = function(){
   Ext.get("imagedisplay").dom.src = "/images/missing-320x240.jpg";
};

/* Provides handy way to convert from local browser time to UTC */
Ext.override(Date, {
    toUTC : function() {
        return this.add(Date.MINUTE, this.getTimezoneOffset());
    },
    fromUTC : function() {
        return this.add(Date.MINUTE, -this.getTimezoneOffset());
    }
}); 

/*
 * Logic to make the application auto refresh if it believes we are in 
 * auto refreshing mode. 
 */
var task = {
  run: function(){
      if (Ext.getCmp("appMode").realtime){
        //console.log("Refreshing");
        appTime = new Date();
        setTime();
        updateDT();
      } 
  },
  interval: 300000
}

var ys = new Ext.Slider({
    id       : 'YearSlider',
    width    : 214,
    minValue : 1893,
    maxValue : (new Date()).format("Y"),
    listeners: {
          'drag': function(){ updateDT(); }
    }
});

var ds = new Ext.Slider({
    id       : 'DaySlider',
    width    : 732,
    minValue : 0,
    maxValue : 365,
    colspan  : 7,
    listeners: {
          'drag': function(){ updateDT(); }
    }
});

var ms = new Ext.Slider({
    id       : 'MinuteSlider',
    width    : 120,
    minValue : 0,
    maxValue : 59,
    increment: 1,
    listeners: {
          'drag': function(){ updateDT(); }
    }
});

var hs = new Ext.Slider({
    id       : 'HourSlider',
    width    : 120,
    minValue : 0,
    maxValue : 23,
    increment: 1,
    listeners: {
        'drag': function(){ updateDT(); }
    }
 });

var store = new Ext.data.JsonStore({
    autoLoad  : true,
    fields    : [
            'id',
            {name: 'time_offset', type: 'int'},
            'name',
            'groupname',
            'template',
            {name: 'sts', type: 'date', dateFormat: 'Y-m-d'},
            {name: 'interval', type: 'int'},
            {name: 'avail_lag', type: 'int'}
    ],
    idProperty : 'id',
    root       : 'products',
    url        : '../json/products.php'
    });

var displayDT = new Ext.Toolbar.TextItem({
    text      : 'Application Loading.....',
    width     : 180,
    isInitial : true, 
    style     : {'font-weight': 'bold'}
});

var combo = new Ext.form.ComboBox({
    id            : 'cb',
    triggerAction : 'all',
    lazyRender    : false,
    autoLoad      : true,
    mode          : 'local',
    editable      : false,
    matchFieldWidth : false,
    minListWidth    :300,
    allowBlank    : false,
    forceSelection: true,
    store         : store,
    valueField    : 'id',
    tpl           : new Ext.XTemplate(
		'<tpl for=".">',
		'<tpl if="this.groupname != values.groupname">',
		'<tpl exec="this.groupname = values.groupname"></tpl>',
		'<span class="dropdown-header">{groupname}</span>',
		'</tpl>',
		'<div class="x-combo-list-item">{name}</div>',
		'</tpl>'
	),
    displayField  : 'name',
    listeners     : {
      select      : function(cb, record, idx){
        appDT = record.data.interval ;

        /* If we don't have sub hourly data, disable the minute selector */
        if (record.data.interval >= 60){ 
          //console.log("Disabling MS"); 
          ms.disable(); 
        } else { ms.enable(); }

        /* If we don't have sub daily data, disable the hour selector */
        if (record.data.interval >= (60*24)){  hs.disable(); }
        else { hs.enable(); }

        /* If we don't have hourly data */
        if (record.data.interval > 60){  
           Ext.getCmp('plushour').disable();
           Ext.getCmp('minushour').disable();
        }
        else {
           Ext.getCmp('plushour').enable();
           Ext.getCmp('minushour').enable();
        }

        ms.increment = record.data.interval;
        //console.log("Setting MS Increment to "+ ms.increment );
        ys.minValue = record.data.sts.format("Y");
        ys.setValue( ys.getValue()-1 );
        ys.setValue( ys.getValue()+1 );
        updateDT();
      }
   }
});

/*
 * This will be our hacky initializer 
 */
store.on('load', function(){ 
  /* Figure out if the desired product is specified in the URL */
  var tokens = window.location.href.split('#');
  if (tokens.length == 2){
    var tokens2 = tokens[1].split(".");
    idx = tokens2[0];
    if (tokens2[1] != "0"){
      gts = Date.parseDate( tokens2[1], "YmdHi" );
      appTime = gts.fromUTC();
    } else {
    	lag = store.getById(idx).data.avail_lag;
    	appTime = appTime.add(Date.MINUTE, 0 - lag);
    }
  } else {
    /* We are going to default to the IEM Plot */
    idx = 1;
  }
  /* Make sure that our form gets reset based on settings for record */
  setTime();
  combo.setValue( idx );
  combo.fireEvent('select', combo, store.getById(idx), idx);
  Ext.TaskMgr.start(task);
});


function dayofyear(d) {   // d is a Date object
	var yn = d.getFullYear();
	var mn = d.getMonth();
	var dn = d.getDate();
	var d1 = new Date(yn,0,1,12,0,0); // noon on Jan. 1
	var d2 = new Date(yn,mn,dn,12,0,0); // noon on input date
	var ddiff = Math.round((d2-d1)/864e5);
	return ddiff+1;
};

/* Helper function to set the sliders to a given time! */
function setTime(){
  now = new Date();
  if (Ext.getCmp("appMode").realtime &&
      now.add(Date.MINUTE, -3.0 * appDT) > appTime ){
    Ext.getCmp("appMode").setText("Archive");
    Ext.getCmp("appMode").realtime = false;
  } 
  if (! Ext.getCmp("appMode").realtime &&
      now.add(Date.MINUTE, -3.0 * appDT) < appTime ){
    Ext.getCmp("appMode").setText("Realtime");
    Ext.getCmp("appMode").realtime = true;
  }
  //console.log("setTime() appTime: "+ appTime +" delta3x: "+ now.add(Date.MINUTE, -3.0 * appDT) );
  /* Our new values */
  g = parseInt( appTime.format('G') );
  z = dayofyear( appTime ) - 1;
  y = parseInt( appTime.format('Y') );
  i = parseInt( appTime.format('i') );

  hs.setValue( g );
  ds.setValue( z ); 
  ys.setValue( y );
  ms.setValue( i );
  //console.log("Setting ms to "+ i );
}

/* Called whenever either the sliders update, the combobox */
function updateDT(){
  //console.log("updateDT() appTime is "+ appTime );
  y = ys.getValue();
  d = ds.getValue();
  h = hs.getValue();
  i = ms.disabled ? 0:  ms.getValue();
  //console.log("y ["+ y +"] d ["+ d +"] h ["+ h +"] i ["+ i +"]");
  
  newTime = new Date('01/01/'+y).add(Date.DAY, d).add(Date.HOUR, h).add(Date.MINUTE,i);
  //console.log("updateDT() newTime is "+ newTime );
  if (newTime == appTime && ! displayDT.isInitial){ 
    //console.log("Shortcircut!");
    return; 
  }
  displayDT.isInitial = false;
  appTime = newTime;
  meta = store.getById( combo.getValue() );
  //console.log( meta);
  //console.log( combo.getValue() );
  if (! meta ){ 
    //console.log("Couldn't find metadata!");
    return; 
  }
  ceiling = (new Date()).add(Date.MINUTE, 0 - meta.data.avail_lag);
  //console.log("Ceiling is "+ ceiling);
  /* Make sure we aren't in the future! */
  if (appTime.add(Date.MINUTE,-1) > ceiling){
    //console.log("Date is: "+ (new Date()));
    //console.log("appTime is: "+ appTime);
    //console.log("Future timestamp: "+ (appTime.add(Date.MINUTE,-1) - (new Date())) +" diff");
    appTime = ceiling; 
    setTime(); 
    //return; 
  }

  /* Make sure we aren't in the past! */
  if (appTime < meta.data.sts){ 
    //console.log("Timestamp too early...");
    appTime = meta.data.sts; 
    setTime(); 
    //return; 
  }

  /* 
   * We need to make sure that we are lined up with where we have data...
   */
  gdt = appTime.toUTC();
  min_from_0z = parseInt( gdt.format('G') ) * 60 + parseInt(gdt.format('i')) - meta.data.time_offset;
  offset = min_from_0z % meta.data.interval;
  //console.log("TmCheck gdt= "+ gdt +" offset= "+ offset +", min_from_0z= "+ min_from_0z);
  if (offset != 0){
    gdt = gdt.add(Date.MINUTE, 0 - offset); 
    appTime = gdt.fromUTC();
    setTime();
  }

  displayDT.setText( appTime.format('D M d Y g:i A T') );

  tpl = meta.data.template.replace(/%Y/g, '{0}').replace(/%m/g, '{1}').replace(/%d/g, '{2}').replace(/%H/g,'{3}').replace(/%i/g,'{4}').replace(/%y/g, '{5}');

  uri = String.format(tpl, gdt.format("Y"), gdt.format("m"), gdt.format("d"), gdt.format("H"), gdt.format("i"), gdt.format("y") );
  if (uri != currentURI){
    Ext.get("imagedisplay").dom.src = uri;
    currentURI = uri;
  }
  window.location.href = String.format("#{0}.{1}", combo.getValue(), gdt.format('YmdHi')); 
}




new Ext.form.FormPanel({
    renderTo: 'theform',
    layout  : 'table',
    layoutConfig: {
        columns: 8
    },
    buttonAlign: 'left',
    fbar: [
      new Ext.Button({
        id       : 'appMode',
        realtime : true,
        text     : 'RealTime',
        handler  : function(btn){
          if (btn.realtime){
            btn.realtime = false;
            btn.setText("Archive");
          } else {
            btn.realtime = true;
            btn.setText("RealTime");
            appTime = new Date();
            setTime();
            updateDT();
          }
        }
      }),
      new Ext.Button({
        text: '<< Year',
        handler: function(){
            appTime = appTime.add(Date.YEAR, -1);
            setTime();
            updateDT();
        }
      }),
      new Ext.Button({
        text: '<< Day',
        handler: function(){
            appTime = appTime.add(Date.DAY, -1);
            setTime();
            updateDT();
        }
      }),
      new Ext.Button({
        id      : 'minushour',
        text    : '<< Hour',
        handler : function(){
            appTime = appTime.add(Date.HOUR, -1);
            setTime();
            updateDT();
        }
      }),
      new Ext.Button({
        text    : '<< Prev',
        handler : function(){
            appTime = appTime.add(Date.MINUTE, - appDT );
            setTime();
            updateDT();
        }
      }),
      displayDT,
      new Ext.Button({
        text    : 'Next >>',
        handler : function(){
            appTime = appTime.add(Date.MINUTE, appDT );
            setTime();
            updateDT();
        }
      }),
      new Ext.Button({
        id      : 'plushour',
        text: 'Hour >>',
        handler: function(){
            appTime = appTime.add(Date.HOUR, 1);
            setTime();
            updateDT();
        }
      }),
      new Ext.Button({
        text: 'Day >>',
        handler: function(){
            appTime = appTime.add(Date.DAY, 1);
            setTime();
            updateDT();
        }
      }),
      new Ext.Button({
        text: 'Year >>',
        handler: function(){
            appTime = appTime.add(Date.YEAR, 1);
            setTime();
            updateDT();
        }
      }),
      '->'],
    defaults : {
      bodyStyle: 'border:0px;padding-left:5px;'
    },
    items: [
      {html: 'Product: '}, combo, {html: 'Year: '}, ys,
      {html: 'Hour: '}, hs,       {html: 'Minute: '}, ms,
      {html: 'Day of Year: '}, ds
    ]
});


});
